<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageBroadcast;
use App\Events\MessageSent;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected function user()
    {
        // Prefer superadmin if guard exists, then admin, then web
        $super = config('auth.guards.superadmin') ? auth('superadmin')->user() : null;
        $admin = auth('admin')->user();
        $apiAdmin = config('auth.guards.api_admin') ? auth('api_admin')->user() : null;
        $web = auth('web')->user();
        
        return $super ?: ($admin ?: ($apiAdmin ?: $web));
    }
    protected function guardName()
    {
        if (config('auth.guards.superadmin') && auth('superadmin')->check()) return 'superadmin';
        if (auth('admin')->check()) return 'admin';
        if (config('auth.guards.api_admin') && auth('api_admin')->check()) return 'api_admin';
        if (auth('web')->check()) return 'web';
        return null;
    }

    protected function isAdminUser($user = null): bool
    {
        $u = $user ?: $this->user();
        if ($u && ($u->is_chat_admin ?? false)) return true;
        $g = $this->guardName();
        return in_array($g, ['admin','superadmin','super_admin','api_admin'], true);
    }

    protected function isBookingsGroup(ChatGroup $group = null): bool
    {
        if (!$group) return false;
        $slug = Str::slug($group->slug ?: $group->name ?: '');
        return $slug === 'bookings';
    }

    protected function isDmGroup(ChatGroup $group = null): bool
    {
        if (!$group || !$group->slug) return false;
        return str_starts_with((string)$group->slug, 'dm-');
    }

    protected function dmParticipants(ChatGroup $group): array
    {
        if (!$this->isDmGroup($group)) return [];
        $parts = explode('-', (string)$group->slug);
        if (count($parts) !== 3) return [];
        return [ (int)$parts[1], (int)$parts[2] ];
    }

    protected function groupHasMembers(ChatGroup $group): bool
    {
        static $cache = [];
        if (array_key_exists($group->id, $cache)) return $cache[$group->id];
        return $cache[$group->id] = ChatGroupMember::where('group_id', $group->id)->exists();
    }

    protected function memberSetForUser(int $userId): array
    {
        static $cache = [];
        if (array_key_exists($userId, $cache)) return $cache[$userId];
        $ids = ChatGroupMember::where('user_id', $userId)->pluck('group_id')->all();
        return $cache[$userId] = array_flip($ids);
    }

    protected function userInGroup(ChatGroup $group = null, $user = null): bool
    {
        if (!$group || !$user) return false;
        if ($this->isDmGroup($group)) {
            $ids = $this->dmParticipants($group);
            return in_array($user->id, $ids, true);
        }
        // Open groups (Bookings/Reports/etc.) should remain visible to everyone even
        // after last_seen rows get created, so only gate access if the group is truly
        // member-restricted and the viewer is not an admin.
        $slug = Str::slug($group->slug ?: $group->name ?: '');
        $defaultPublic = ['bookings','reports','invoices','management','amendment-reports'];

        // Admin-like roles always see the group
        if ($this->isAdminUser($user)) return true;

        // If membership rows exist, allow access when the user is listed OR when the
        // group is one of the public defaults (avoid hiding groups after one user
        // marks them seen).
        if ($this->groupHasMembers($group)) {
            $memberSet = $this->memberSetForUser($user->id);
            if (array_key_exists($group->id, $memberSet)) return true;
            if (in_array($slug, $defaultPublic, true)) return true;
            return false;
        }

        // No membership defined: treat as public
        return true;
    }

    protected function filterVisibleMessages($messages, $group, $viewer)
    {
        if (!$group || !$this->isBookingsGroup($group)) return $messages;
        if ($this->isAdminUser($viewer)) return $messages;
        $viewerId = $viewer?->id;
        return $messages->filter(function($m) use ($viewerId){
            $guard = Str::slug($m->sender_guard ?? '');
            $isAdminMsg = in_array($guard, ['admin','super-admin','superadmin'], true);
            $isMine = $viewerId && $m->user_id === $viewerId;
            return $isAdminMsg || $isMine;
        })->values();
    }

    protected function buildGroupPayload(ChatGroup $group, $viewerId = null)
    {
        // Pick last message visible to this viewer (important for Bookings filtering)
        $viewer = $viewerId ? User::find($viewerId) : null;
        $all = $group->messages()->latest('id')->take(50)->get();
        $visible = $this->filterVisibleMessages($all, $group, $viewer);
        $last = $visible->sortByDesc('id')->first();
        // For deterministic DM slugs dm-{a}-{b}, show the peer's name to the viewer
        $displayName = $group->name;
        if ($viewerId && str_starts_with((string)$group->slug, 'dm-')) {
            $parts = explode('-', $group->slug);
            if (count($parts) === 3) {
                $a = (int)$parts[1]; $b = (int)$parts[2];
                $peerId = $viewerId === $a ? $b : $a;
                if ($peerId > 0) {
                    $peer = User::select('id','name')->find($peerId);
                    if ($peer && $peer->name) { $displayName = $peer->name; }
                }
            }
        }
        // Determine avatar: for DM groups prefer the peer user's avatar
        $avatar = null;
        if ($viewerId && str_starts_with((string)$group->slug, 'dm-')) {
            $parts = explode('-', $group->slug);
            if (count($parts) === 3) {
                $a = (int)$parts[1]; $b = (int)$parts[2];
                $peerId = $viewerId === $a ? $b : $a;
                if ($peerId > 0) {
                    // Use a plain find() to avoid selecting columns that may not exist
                    // (some deployments don't have `profile_picture`/`avatar` columns).
                    $peer = User::find($peerId);
                    if ($peer) {
                        $avatar = $this->userAvatarUrl($peer);
                    }
                }
            }
        }

        return [
            'id' => $group->id,
            'slug' => $group->slug,
            'name' => $displayName,
            'avatar' => $avatar,
            'last_msg_id' => $last?->id,
            'last_msg_at' => $last?->created_at?->toISOString(),
            'latest' => $last ? [
                'id' => $last->id,
                'type' => $last->type,
                'content' => $last->content,
                'original_name' => $last->original_name,
                'sender_guard' => $last->sender_guard,
                'sender_name' => $last->sender_name,
                'user' => $last->user ? ['id'=>$last->user->id, 'name'=>$last->user->name, 'avatar' => $this->userAvatarUrl($last->user)] : null,
                'created_at' => $last->created_at?->toISOString(),
            ] : null,
            'unread' => 0,
        ];
    }

    public function markSeen(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'last_id' => 'nullable|integer']);
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json(['message' => 'Forbidden'], 403);

        $lastId = $request->integer('last_id') ?: ChatMessage::where('group_id', $group->id)->max('id');
        if ($lastId === null) $lastId = 0;

        ChatGroupMember::updateOrCreate(
            ['group_id' => $group->id, 'user_id' => $user->id],
            ['last_seen_id' => $lastId]
        );

        return response()->json(['status' => 'ok']);
    }

    public function unreadCounts(Request $request)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $groups = ChatGroup::orderBy('id')->get()->filter(function($g) use ($user){
            return $this->userInGroup($g, $user);
        });

        $result = [];
        foreach ($groups as $g){
            $lastSeen = ChatGroupMember::where('group_id', $g->id)->where('user_id', $user->id)->value('last_seen_id') ?? 0;
            $msgs = ChatMessage::with('user:id,name,is_chat_admin')
                ->where('group_id', $g->id)
                // Do not count the viewer's own messages as unread
                ->where('user_id', '!=', $user->id)
                ->where('id', '>', $lastSeen)
                ->orderBy('id')
                ->limit(200)
                ->get();
            $visible = $this->filterVisibleMessages($msgs, $g, $user);
            $count = $visible->count();
            $result[] = ['group_id' => $g->id, 'count' => $count];
        }

        $total = array_sum(array_map(fn($x)=> $x['count'], $result));
        return response()->json(['total' => $total, 'groups' => $result]);
    }

    protected function ensureDmGroup($aId, $bId, $displayName)
    {
        [$lo, $hi] = [$aId, $bId];
        if ($lo > $hi) { [$lo, $hi] = [$hi, $lo]; }
        $slug = 'dm-'.$lo.'-'.$hi;
        $group = ChatGroup::firstOrCreate(
            ['slug' => $slug],
            ['name' => $displayName, 'created_by' => $aId]
        );
        return $group;
    }

    public function groups()
    {
        try {
            // Ensure default groups exist
            $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
            foreach ($defaults as $name) {
                ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            }
            $user = $this->user();
            $groups = ChatGroup::orderBy('id')->get();
            // Show only groups the viewer belongs to (DMs and member-guarded groups)
            $filtered = $groups->filter(function($g) use ($user){
                return $user && $this->userInGroup($g, $user);
            });
            $payload = $filtered->map(fn($g)=> $this->buildGroupPayload($g, $user?->id))->values();
            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('ChatController::groups failed: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to load chat groups', 'detail' => $e->getMessage()], 500);
        }
    }

    public function clearGroup(ChatGroup $group)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        if (!$this->isDmGroup($group)) return response()->json(['message' => 'Only personal chats can be cleared'], 403);
        if (!$this->userInGroup($group, $user)) return response()->json(['message' => 'Forbidden'], 403);

        $messages = ChatMessage::where('group_id', $group->id)->get();
        foreach ($messages as $msg) {
            if ($msg->file_path) { try { Storage::delete($msg->file_path); } catch (_) {} }
        }
        ChatMessage::where('group_id', $group->id)->delete();

        return response()->json(['status' => 'cleared']);
    }

    public function destroyGroup(ChatGroup $group)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        if (!$this->isDmGroup($group)) return response()->json(['message' => 'Only personal chats can be deleted'], 403);
        if (!$this->userInGroup($group, $user)) return response()->json(['message' => 'Forbidden'], 403);

        $messages = ChatMessage::where('group_id', $group->id)->get();
        foreach ($messages as $msg) {
            if ($msg->file_path) { try { Storage::delete($msg->file_path); } catch (_) {} }
        }
        ChatMessage::where('group_id', $group->id)->delete();
        ChatGroupMember::where('group_id', $group->id)->delete();
        $group->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function messages(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id']);
        $user = $this->user();
        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json([], 403);
        $list = ChatMessage::with(['user:id,name,is_chat_admin', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->orderBy('id')
            ->limit(200)
            ->get();
        $list = $this->filterVisibleMessages($list, $group, $user)
            ->map(function(ChatMessage $m) use ($user) { return $this->serializeMessage($m, $user); });
        return response()->json($list);
    }

    public function searchUsers(Request $request)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json([], 401);
        $q = trim((string) $request->get('q', ''));
        if ($q === '') return response()->json([]);
        $users = User::query()
            ->select('id','name','email')
            ->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")
                  ->orWhere('email','like',"%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
        return response()->json($users);
    }

    public function direct($userId)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json(['message'=>'Unauthorized'], 401);
        $other = User::findOrFail($userId);
        if ($viewer->id === $other->id) return response()->json(['message'=>'Cannot chat with yourself'], 422);
        $group = $this->ensureDmGroup($viewer->id, $other->id, $other->name ?? 'Chat');
        return response()->json($this->buildGroupPayload($group, $viewer->id));
    }

    public function directWith($userId)
    {
        // Symmetric user-to-user DM for all roles
        return $this->direct($userId);
    }

    public function destroy($id)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json(['message'=>'Unauthorized'], 401);

        $msg = ChatMessage::find($id);
        if (!$msg) return response()->json(['message'=>'Not found'], 404);

        $group = ChatGroup::find($msg->group_id);
        if (!$this->userInGroup($group, $viewer)) return response()->json(['message'=>'Forbidden'], 403);

        $isAdmin = $this->isAdminUser($viewer);
        $isOwner = (int)$msg->user_id === (int)$viewer->id;
        if (!$isAdmin && !$isOwner) return response()->json(['message'=>'Forbidden'], 403);

        // Delete attached file if exists
        if ($msg->file_path) { try { Storage::delete($msg->file_path); } catch(_) {} }

        $msg->delete();
        return response()->json(['status'=>'deleted']);
    }

    public function messagesSince(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'after_id' => 'required|integer']);
        $user = $this->user();
        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json([], 403);
        $list = ChatMessage::with(['user:id,name,is_chat_admin', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->where('id', '>', $request->integer('after_id'))
            ->orderBy('id')
            ->limit(200)
            ->get();
        $list = $this->filterVisibleMessages($list, $group, $user)
            ->map(function(ChatMessage $m) use ($user) { return $this->serializeMessage($m, $user); });
        return response()->json($list);
    }

    public function send(Request $request)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate([
            'group_id' => 'required|integer|exists:chat_groups,id',
            'type' => 'required|string|in:text,image,pdf,voice',
            'content' => 'nullable|string|max:5000',
            'message' => 'nullable|string|max:5000',
            'body' => 'nullable|string|max:5000',
            'text' => 'nullable|string|max:5000',
            'description' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:10240', // 10MB
            'reply_to_message_id' => 'nullable|integer|exists:chat_messages,id',
        ]);

        $group = ChatGroup::find($request->integer('group_id'));
        if (!$this->userInGroup($group, $user)) return response()->json(['message' => 'Forbidden'], 403);

        $type = (string) $request->string('type');
        // Gather text from any alias
        $contentAliases = [
            $request->input('content'),
            $request->input('message'),
            $request->input('body'),
            $request->input('text'),
            $request->input('description'),
        ];
        $firstNonEmpty = null;
        foreach ($contentAliases as $v){ if (is_string($v) && trim($v) !== '') { $firstNonEmpty = trim($v); break; } }

        $filePath = null; $original = null;

        if (in_array($type, ['image','pdf','voice'])) {
            $request->validate([
                'file' => [
                    'required','file','max:20480',
                    function ($attr, $value, $fail) use ($type) {
                        $mime = $value->getMimeType();
                        if ($type === 'image' && !str_starts_with($mime, 'image/')) $fail('Invalid image file');
                        if ($type === 'pdf' && $mime !== 'application/pdf') $fail('Invalid PDF file');
                        if ($type === 'voice') {
                            $ok = str_starts_with($mime, 'audio/')
                                || $mime === 'video/webm'
                                || $mime === 'application/octet-stream'; // safari/edge sometimes send octet-stream
                            if (!$ok) $fail('Invalid audio file');
                        }
                    }
                ]
            ]);
            $original = $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->store('public/chat/'.$group->id);
        } else if ($type === 'text') {
            if ($firstNonEmpty === null) {
                return response()->json(['message' => 'Text content is required'], 422);
            }
        }

        $replyId = $request->input('reply_to_message_id');
        if ($replyId) {
            $parent = ChatMessage::find($replyId);
            if (!$parent || (int)$parent->group_id !== (int)$group->id) {
                return response()->json(['message' => 'Invalid reply target'], 422);
            }
        }

        // Normalize sender guard/name so admin/superadmin messages are labeled correctly
        $senderGuard = $this->guardName();
        if (!$senderGuard && $this->isAdminUser($user)) {
            $senderGuard = 'admin';
        }
        $senderName = $user->name ?? ($senderGuard === 'superadmin' ? 'Super Admin' : ($senderGuard === 'admin' ? 'Admin' : null));

        $msg = ChatMessage::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'type' => $type,
            'content' => $type === 'text' ? $firstNonEmpty : null,
            'file_path' => $filePath,
            'original_name' => $original,
            'sender_guard' => $senderGuard,
            'sender_name' => $senderName,
            'reply_to_message_id' => $replyId,
        ]);

        $msg->load('user:id,name,is_chat_admin');
        // Recompute group payload for sidebar ordering
        $payload = $this->serializeMessage($msg, $user);
        $groupPayload = $this->buildGroupPayload(ChatGroup::find($msg->group_id), $user->id ?? null);

        // Broadcast in real-time
        try {
            $payload['socket_id'] = request()->header('X-Socket-Id');
            broadcast(new ChatMessageBroadcast($payload));
            broadcast(new MessageSent($payload));
        } catch (\Throwable $e) {
            // swallow broadcast errors to not block send
        }

        return response()->json(['message' => $payload, 'group' => $groupPayload], 201);
    }

    public function react(Request $request, ChatMessage $message)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $request->validate(['type' => 'required|string|max:32']);
        ChatReaction::updateOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'type' => $request->string('type')
        ]);
        return response()->json(['status' => 'ok']);
    }

    public function setChatAdmin(Request $request, User $user)
    {
        $viewer = $this->user();
        if (!$viewer) return response()->json(['message' => 'Unauthorized'], 401);
        if (!$this->isAdminUser($viewer)) return response()->json(['message' => 'Forbidden'], 403);

        $data = $request->validate([
            'is_admin' => 'required|boolean',
        ]);

        $user->is_chat_admin = (bool) $data['is_admin'];
        $user->save();

        return response()->json([
            'status' => 'ok',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_chat_admin' => (bool) $user->is_chat_admin,
            ],
        ]);
    }

    protected function fileUrl(?string $path): ?string
    {
        if (!$path) return null;
        // Primary: Storage::url
        try {
            $u = Storage::url($path);
            if ($u) return $u;
        } catch (\Throwable $e) {}

        // Fallbacks for common stored shapes
        $trim = ltrim($path, '/');
        // Normalize absolute storage/app/public/... to storage/...
        if (str_starts_with($trim, 'storage/app/public/')) {
            $trim = substr($trim, strlen('storage/app/public/'));
            try { return Storage::disk('public')->url($trim); } catch (\Throwable $e) {}
            return '/storage/'.$trim;
        }
        if (str_starts_with($trim, 'app/public/')) {
            $trim = substr($trim, strlen('app/public/'));
            try { return Storage::disk('public')->url($trim); } catch (\Throwable $e) {}
            return '/storage/'.$trim;
        }
        if (str_starts_with($trim, 'public/')) {
            $rel = substr($trim, strlen('public/'));
            try { return Storage::disk('public')->url($rel); } catch (\Throwable $e) {}
            return '/storage/'.$rel;
        }
        if (str_starts_with($trim, 'storage/')) {
            return '/'. $trim;
        }
        try { return Storage::disk('public')->url($trim); } catch (\Throwable $e) {}
        return '/storage/'.$trim;
    }

    protected function serializeMessage(ChatMessage $m, $viewer)
    {
        // Prefer stored file_path, fallback to legacy file_url column if present
        $rawPath = $m->file_path ?: ($m->file_url ?? null);
        $fileUrl = $this->fileUrl($rawPath);
        $viewerId = $viewer?->id;
        $senderName = $m->sender_name ?: ($m->relationLoaded('user') && $m->user ? $m->user->name : null);
        $data = [
            'id' => $m->id,
            'group_id' => $m->group_id,
            'user_id' => $m->user_id,
            'user' => $m->relationLoaded('user') && $m->user ? [
                'id' => $m->user->id,
                'name' => $m->user->name,
                'is_chat_admin' => (bool) ($m->user->is_chat_admin ?? false),
                'avatar' => null,
            ] : null,
            'sender_guard' => $m->sender_guard,
            'sender_name' => $senderName,
            'type' => $m->type,
            'content' => $m->content,
            'file_url' => $fileUrl,
            'file_path' => $m->file_path,
            'original_name' => $m->original_name,
            'created_at' => $m->created_at?->toISOString(),
            'mine' => $viewerId !== null && $viewerId === $m->user_id,
            'reply_to_message_id' => $m->reply_to_message_id,
        ];
        // Reactions visible only to the message sender (owner)
        if ($viewer && $viewer->id === $m->user_id) {
            $data['reactions'] = $m->relationLoaded('reactions') ? $m->reactions->map(function(ChatReaction $r){
                return [ 'type' => $r->type, 'user_id' => $r->user_id, 'user' => $r->relationLoaded('user') && $r->user ? [ 'id'=>$r->user->id, 'name'=>$r->user->name ] : null ];
            })->values() : [];
        } else {
            $data['reactions'] = [];
        }
        // Ensure sender_name exists for admin/superadmin messages even if DB missing
        if (!$data['sender_name']) {
            if ($this->isAdminUser($m->user)) {
                $data['sender_name'] = 'Admin';
                $data['sender_guard'] = $data['sender_guard'] ?: 'admin';
            } else if ($m->sender_guard && in_array($m->sender_guard, ['admin','superadmin','super_admin'])) {
                $data['sender_name'] = ucfirst(str_replace('_',' ', $m->sender_guard));
            }
        }
        // If user is present, attempt to include avatar URL from common fields
        if (!empty($data['user']) && $m->relationLoaded('user') && $m->user) {
            $data['user']['avatar'] = $this->userAvatarUrl($m->user);
        }

        return $data;
    }

    protected function userAvatarUrl($user): ?string
    {
        if (!$user) return null;
        // Prefer direct profile_photo_url if present (Jetstream/etc.)
        if (!empty($user->profile_photo_url)) {
            return (string) $user->profile_photo_url;
        }
        // Common DB-backed candidate fields
        $cand = $user->profile_picture ?? $user->avatar ?? $user->photo ?? null;
        if ($cand) {
            return $this->fileUrl((string)$cand);
        }
        // Fallback: check public avatars by user id (avatars/{id}.{ext})
        $exts = ['png','jpg','jpeg','webp','gif'];
        foreach ($exts as $ext) {
            $rel = "avatars/{$user->id}.{$ext}";
            try {
                if (Storage::disk('public')->exists($rel)) {
                    return Storage::disk('public')->url($rel);
                }
            } catch (\Throwable $e) {
                // ignore storage issues
            }
        }
        return null;
    }

}

