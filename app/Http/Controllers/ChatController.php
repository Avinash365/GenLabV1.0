<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\ChatGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\File;
use App\Events\MessageSent;

class ChatController extends Controller
{
    // NEW: helpers to safely access optional guards (e.g., 'superadmin')
    protected function guardAvailable(string $name): bool
    {
        return (bool) config("auth.guards.$name");
    }
    protected function guardUser(string $name)
    {
        return $this->guardAvailable($name) ? auth($name)->user() : null;
    }
    protected function guardCheck(string $name): bool
    {
        return $this->guardAvailable($name) ? auth($name)->check() : false;
    }

    protected function user()
    {
        // Prefer superadmin, then admin/api_admin, then api, then web
        return $this->guardUser('superadmin')
            ?: $this->guardUser('admin')
            ?: $this->guardUser('api_admin')
            ?: $this->guardUser('api')
            ?: $this->guardUser('web');
    }

    protected function currentGuard(): ?string
    {
        // Treat superadmin/admin/api_admin as 'admin'. Regular users (web/api) as 'web'.
        if ($this->guardCheck('superadmin') || $this->guardCheck('admin') || $this->guardCheck('api_admin')) return 'admin';
        if ($this->guardCheck('api') || $this->guardCheck('web')) return 'web';
        return null;
    }

    // Root admin = either real superadmin (if guard exists) or real admin
    protected function isRootAdmin(): bool
    {
        return $this->guardCheck('superadmin') || $this->guardCheck('admin') || $this->guardCheck('api_admin');
    }

    // NEW: helper to check if a web user is chat admin via users.is_chat_admin
    protected function userIsChatAdmin(?User $u): bool
    {
        if (!$u) return false;
        if (!Schema::hasColumn('users', 'is_chat_admin')) return false;
        try { return (bool) $u->is_chat_admin; } catch (\Throwable $e) { return false; }
    }

    protected function isSuperAdmin(): bool
    {
        // Root admin OR a web user flagged as chat admin
        if ($this->isRootAdmin()) return true;
        if ($this->guardCheck('web')) return $this->userIsChatAdmin(auth('web')->user());
        if ($this->guardCheck('api')) return $this->userIsChatAdmin(auth('api')->user());
        return false;
    }

    // NEW: copy storage/app/public/<relative> to public/storage/<relative> if not present
    protected function ensurePublicCopy(string $relative): void
    {
        $relative = ltrim($relative, '/');
        $src = storage_path('app/public/' . $relative);
        $dst = public_path('storage/' . $relative);
        try {
            if (is_file($src) && !is_file($dst)) {
                $dir = dirname($dst);
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                @File::copy($src, $dst);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    // Generate an avatar URL for a user based on stored profile pics or model fields; fallback to placeholder
    protected function avatarUrl(?User $u): ?string
    {
        if (!$u) return null;
        // 1) Check uploaded avatar under storage/app/public/avatars/{id}.{ext}
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $path = "avatars/{$u->id}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                // NEW: make sure it's reachable without symlink
                $this->ensurePublicCopy($path);
                return url('storage/' . $path);
            }
        }
        // 2) Fallback to model-provided URLs if present
        $candidates = [
            $u->profile_photo_url ?? null,
            $u->avatar ?? null,
            $u->photo ?? null,
        ];
        foreach ($candidates as $url) {
            if (is_string($url) && trim($url) !== '') return $url;
        }
        // 3) Final fallback to a local placeholder asset
        return url('assets/img/profiles/avator1.jpg');
    }

    public function groups()
    {
        $u = $this->user();
        // IMPORTANT: only real admins get admin inbox behavior for list shape (dm2 rejected)
        $isRoot = $this->isRootAdmin();

        // Ensure default groups exist
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $list = ChatGroup::orderBy('id')->get(['id','name','slug']);
        if ($isRoot) {
            $list = $list->reject(function($g){ return Str::startsWith($g->slug, 'dm2-'); });
        } else {
            $uid = (int) ($u->id ?? 0);
            $list = $list->filter(function($g) use ($uid){
                if (!Str::startsWith($g->slug, 'dm')) return true; // public groups
                if ($g->slug === ('dm-'.$uid)) return true; // admin<->me
                if (Str::startsWith($g->slug, 'dm2-')){
                    $parts = explode('-', $g->slug); $a=(int)($parts[1]??0); $b=(int)($parts[2]??0);
                    return $a===$uid || $b===$uid; // only my dm2
                }
                return false;
            });
        }
        if ($u) {
            $dm = ChatGroup::where('slug', 'dm-' . (int)$u->id)->first();
            if ($dm && !$list->contains('id', $dm->id)) { $list->push($dm); }
        }

        $uid = (int) ($u->id ?? 0);
        $result = [];
        foreach ($list as $g) {
            $displayName = $g->name;
            $groupAvatar = null;
            if ($u){
                if (!$isRoot && $g->slug === ('dm-' . $uid)) {
                    $displayName = 'Super Admin';
                    // Optional: you can set a static admin avatar here if available
                }
                if (Str::startsWith($g->slug, 'dm2-')){
                    $parts = explode('-', $g->slug); $a=(int)($parts[1]??0); $b=(int)($parts[2]??0);
                    $peerId = $a === $uid ? $b : ($b === $uid ? $a : null);
                    if ($peerId){ $peer = User::find($peerId); if ($peer){ $displayName = $peer->name ?: ('User '.$peerId); $groupAvatar = $this->avatarUrl($peer); } }
                } elseif (preg_match('/^dm-(\d+)$/', $g->slug, $m)) {
                    $target = User::find((int)$m[1]); if ($target) { $groupAvatar = $this->avatarUrl($target); }
                }
            } else {
                // Not authenticated, still try dm-<id>
                if (preg_match('/^dm-(\d+)$/', $g->slug, $m)) {
                    $target = User::find((int)$m[1]); if ($target) { $groupAvatar = $this->avatarUrl($target); }
                }
            }

            $base = ChatMessage::where('group_id', $g->id);

            // Per-group elevation: root OR (chat-admin and bookings group)
            $isElevated = $isRoot || ($this->isChatAdminOnly() && strtolower($g->slug) === 'bookings');

            if ($isElevated) {
                $base->where(function($w){ $w->whereNull('sender_guard')->orWhere('sender_guard','!=','admin'); });
            } else {
                // ...existing non-admin base restrictions...
                $slug = $g->slug;
                $isUsersDM = $slug === ('dm-' . $uid);
                $isDM2 = \Illuminate\Support\Str::startsWith($slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
                if ($isUsersDM) {
                    $base->where('sender_guard', 'admin');
                } elseif ($isDM2) {
                    $base->where('user_id', '!=', $uid);
                } else {
                    $base->where('sender_guard', 'admin')
                         ->whereIn('reply_to_message_id', function($sq) use ($uid){
                             $sq->select('id')->from('chat_messages')->where('user_id', $uid);
                         });
                }
            }

            $latest = (clone $base)->with('user:id,name')
                                   ->orderBy('id','desc')->first();

            // Unread: compute against "other side" after last seen, with fallback when no membership exists
            $mem = ChatGroupMember::where('group_id', $g->id)->where('user_id', $uid)->first();
            $lastIdInGroup = (int) (ChatMessage::where('group_id', $g->id)->max('id') ?? 0);
            $lastSeen = $mem && (int)($mem->last_seen_id ?? 0) > 0 ? (int)$mem->last_seen_id : $lastIdInGroup;

            $unreadQ = ChatMessage::where('group_id', $g->id)->where('id', '>', $lastSeen);
            if ($isElevated) {
                // Admin-like unread for elevated scope
                $unreadQ->where(function($w){ $w->whereNull('sender_guard')->orWhere('sender_guard','!=','admin'); });
            } else {
                // ...existing non-admin unread rules...
                $slug = $g->slug;
                $isUsersDM = $slug === ('dm-' . $uid);
                $isDM2 = \Illuminate\Support\Str::startsWith($slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
                if ($isUsersDM) {
                    $unreadQ->where('sender_guard', 'admin');
                } elseif ($isDM2) {
                    $unreadQ->where('user_id', '!=', $uid);
                } else {
                    $unreadQ->where('sender_guard', 'admin')
                            ->whereIn('reply_to_message_id', function($sq) use ($uid){
                                $sq->select('id')->from('chat_messages')->where('user_id', $uid);
                            });
                }
            }
            $unread = (int) $unreadQ->count();

            $result[] = [
                'id' => $g->id,
                'name' => $displayName,
                'avatar' => $groupAvatar,
                'latest' => $latest ? [
                    'id' => $latest->id,
                    'type' => $latest->type,
                    'content' => $latest->content,
                    'original_name' => $latest->original_name,
                    'sender_guard' => $latest->sender_guard,
                    'sender_name' => $latest->sender_name,
                    'user' => ($latest->relationLoaded('user') && $latest->user) ? ['id'=>$latest->user->id, 'name'=>$latest->user->name] : null,
                    'created_at' => $latest->created_at?->toISOString(),
                ] : null,
                'last_msg_id' => $latest ? (int)$latest->id : 0,
                'last_msg_at' => $latest && $latest->created_at ? $latest->created_at->toISOString() : null,
                'unread' => $unread,
            ];
        }

        // Sort by last message id desc, fallback by name
        usort($result, function($a,$b){
            $ai = $a['last_msg_id'] ?? 0; $bi = $b['last_msg_id'] ?? 0;
            if ($ai === $bi) return strcmp($a['name'], $b['name']);
            return $bi <=> $ai;
        });

        // CHANGED: always return a JSON response
        return response()->json($result);
    }

    protected function membership(int $groupId, int $userId)
    {
        $mem = ChatGroupMember::where('group_id', $groupId)->where('user_id', $userId)->first();
        $lastId = (int) (ChatMessage::where('group_id', $groupId)->max('id') ?? 0);
        if ($mem) {
            // Repair legacy records with null/zero last_seen_id
            $needsUpdate = ($mem->last_seen_id === null) || ((int)$mem->last_seen_id === 0);
            if ($needsUpdate) {
                $mem->last_seen_id = $lastId;
                if (empty($mem->joined_at)) { $mem->joined_at = now(); }
                $mem->save();
            }
            return $mem;
        }
        return ChatGroupMember::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'last_seen_id' => $lastId,
            'joined_at' => now(),
        ]);
    }

    public function messages(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id']);
        $user = $this->user();
        $groupId = $request->integer('group_id');
        $group = ChatGroup::find($groupId);

        // Admin (root) cannot open dm2 in admin view
        $isRoot = $this->isRootAdmin();
        if ($isRoot && $group && \Illuminate\Support\Str::startsWith($group->slug, 'dm2-')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Elevation only for Bookings when user is promoted (chat-admin)
        $isElevated = $isRoot || ($this->isChatAdminOnly() && $group && strtolower($group->slug) === 'bookings');

        $q = ChatMessage::with([
                'reactions.user:id,name',
                'user' => function($q){
                    $cols = ['id','name','email'];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users','is_chat_admin')) $cols[] = 'is_chat_admin';
                    $q->select($cols);
                }
            ])
            ->where('group_id', $groupId);

        if (!$isElevated && $user) {
            // ...existing non-admin visibility restriction...
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            $isDM2 = $group && \Illuminate\Support\Str::startsWith($group->slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $group->slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
            if (!$isUsersDM && !$isDM2) {
                $q->where(function($qr) use ($uid) {
                    $qr->where('user_id', $uid)
                       ->orWhere(function($sub) use ($uid) {
                            $sub->where('sender_guard', 'admin')
                                ->whereIn('reply_to_message_id', function($sq) use ($uid) { $sq->select('id')->from('chat_messages')->where('user_id', $uid); });
                       });
                });
            }
        }

        // Old (problem): ->orderBy('id')->limit(200)->get()
        // New: get latest 200 first, then sort ascending for proper chronology
        $msgs = $q->orderBy('id','desc')->limit(200)->get();
        $msgs = $msgs->sortBy('id')->values();

        $list = $msgs->map(function(ChatMessage $m) use ($user) {
            return $this->serializeMessage($m, $user);
        });

        return response()->json($list);
    }

    public function messagesSince(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'after_id' => 'required|integer']);
        $user = $this->user();
        $groupId = $request->integer('group_id');
        $group = ChatGroup::find($groupId);

        $isRoot = $this->isRootAdmin();
        if ($isRoot && $group && \Illuminate\Support\Str::startsWith($group->slug, 'dm2-')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $isElevated = $isRoot || ($this->isChatAdminOnly() && $group && strtolower($group->slug) === 'bookings');

        $q = ChatMessage::with([
                'reactions.user:id,name',
                'user' => function($q){
                    $cols = ['id','name','email'];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users','is_chat_admin')) $cols[] = 'is_chat_admin';
                    $q->select($cols);
                }
            ])
            ->where('group_id', $groupId)
            ->where('id', '>', $request->integer('after_id'));

        if (!$isElevated && $user) {
            // ...existing non-admin restriction...
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            $isDM2 = $group && \Illuminate\Support\Str::startsWith($group->slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $group->slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
            if (!$isUsersDM && !$isDM2) {
                $q->where(function($qr) use ($uid) {
                    $qr->where('user_id', $uid)
                       ->orWhere(function($sub) use ($uid) {
                            $sub->where('sender_guard', 'admin')
                                ->whereIn('reply_to_message_id', function($sq) use ($uid) { $sq->select('id')->from('chat_messages')->where('user_id', $uid); });
                       });
                });
            }
        }

        $list = $q->orderBy('id')->limit(200)->get()
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
            'file' => 'nullable|file|max:20480',
            'reply_to_message_id' => 'nullable|integer|exists:chat_messages,id',
        ]);

        $type = (string) $request->string('type');
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
                    'required','file',
                    function ($attr, $value, $fail) use ($type) {
                        $mime = strtolower((string)$value->getMimeType());
                        $ext = strtolower((string)$value->getClientOriginalExtension());
                        $ok = false;
                        if ($type === 'image') {
                            $ok = str_starts_with($mime, 'image/') || in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','heic','heif']);
                        } elseif ($type === 'pdf') {
                            $ok = ($mime === 'application/pdf') || ($ext === 'pdf');
                        } elseif ($type === 'voice') {
                            // Accept common audio types and webm/m4a containers even if reported differently
                            $ok = str_starts_with($mime, 'audio/')
                                  || in_array($mime, ['video/webm','application/octet-stream'])
                                  || in_array($ext, ['mp3','wav','m4a','ogg','oga','webm','aac']);
                        }
                        if (!$ok) $fail('Invalid file type for '.$type);
                    }
                ]
            ]);
            $original = $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->store('public/chat/'.$request->integer('group_id'));
            // NEW: immediately expose a public copy for direct web access
            $relative = ltrim(str_replace('public/', '', $filePath), '/'); // chat/<gid>/<file>
            $this->ensurePublicCopy($relative);
        } else if ($type === 'text') {
            if ($firstNonEmpty === null) {
                return response()->json(['message' => 'Text content is required'], 422);
            }
        }

        $data = [
            'group_id' => $request->integer('group_id'),
            'user_id' => $user->id,
            'type' => $type,
            'content' => $type === 'text' ? $firstNonEmpty : null,
            'file_path' => $filePath,
            'original_name' => $original,
        ];
        if (Schema::hasColumn('chat_messages', 'sender_guard')) {
            $data['sender_guard'] = $this->currentGuard(); // CHANGED: promoted users emit 'admin'
        }
        if (Schema::hasColumn('chat_messages', 'sender_name')) {
            // Use actual display name for both admin and user so recipients see proper names
            $data['sender_name'] = ($user->name ?? null);
        }
        if ($request->filled('reply_to_message_id')) {
            $data['reply_to_message_id'] = $request->integer('reply_to_message_id');
        }

        $msg = ChatMessage::create($data);
        // Ensure membership exists for sender
        if ($this->currentGuard() !== 'admin') {
            $this->membership($request->integer('group_id'), (int)$user->id);
        }

        // CHANGED: include is_chat_admin in the eager load if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('users','is_chat_admin')) {
            $msg->load(['user' => function($q){ $q->select('id','name','email','is_chat_admin'); }]);
        } else {
            $msg->load('user:id,name,email');
        }

        // Build two payloads:
        // - payload: scoped for current requester (keeps correct "mine")
        // - broadcast: neutral so every receiver can compute "mine" locally
        $payload = $this->serializeMessage($msg, $user);
        $broadcast = $payload;
        unset($broadcast['mine']); // ensure neutral; receivers will set it

        event(new \App\Events\ChatMessageBroadcast($broadcast));
        broadcast(new MessageSent($msg))->toOthers();
        return response()->json($payload, 201);
    }

    public function react(Request $request, ChatMessage $message)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        if (!$this->isSuperAdmin()) return response()->json(['message' => 'Forbidden'], 403);
        $request->validate(['type' => 'required|string|max:32']);
        ChatReaction::updateOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'type' => $request->string('type')
        ]);
        return response()->json(['status' => 'ok']);
    }

    public function createGroup(Request $request)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $request->validate(['name' => 'required|string|max:120']);
        $name = trim((string) $request->input('name'));
        $base = Str::slug($name) ?: 'session';
        $slug = $base; $i = 1;
        while (ChatGroup::where('slug', $slug)->exists()) { $slug = $base.'-'.$i++; }
        $group = ChatGroup::create([
            'name' => $name,
            'slug' => $slug,
            'created_by' => $user->id,
        ]);
        return response()->json(['id' => $group->id, 'name' => $group->name], 201);
    }

    public function direct(User $user)
    {
        // Only real admins can open legacy dm-<user> threads
        if (!$this->isRootAdmin()) { return response()->json(['message' => 'Forbidden'], 403); }
        $slug = 'dm-' . $user->id;
        $group = ChatGroup::firstOrCreate(['slug' => $slug], [ 'name' => $user->name ?: 'Direct Message' ]);
        // If name is still a legacy value like "DM: name", update to plain user name
        if ($group->name !== ($user->name ?: 'Direct Message')){
            $group->name = $user->name ?: 'Direct Message';
            $group->save();
        }
        $this->membership((int)$group->id, (int)$user->id);
        return response()->json(['id' => $group->id, 'name' => $group->name]);
    }

    // Search users by name/email
    public function searchUsers(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if ($q === '') return response()->json([]);
        $users = User::query()
            ->where(function($w) use ($q){
                $w->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('id', $q);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id','name','email']);
        return response()->json($users);
    }

    // Ensure/get symmetric DM between two users (admin or web)
    public function directWith(User $user)
    {
        $me = $this->user(); if (!$me) return response()->json(['message' => 'Unauthorized'], 401);
        // CHANGED: only real admins use legacy dm-<user>; chat-admins use dm2
        if ($this->isRootAdmin()) { return $this->direct($user); }
        $a = min((int)$me->id, (int)$user->id); $b = max((int)$me->id, (int)$user->id);
        $slug = 'dm2-'.$a.'-'.$b; $name = $user->name ?: ('User '.$user->id);
        $group = ChatGroup::firstOrCreate(['slug' => $slug], [ 'name' => $name ]);
        $this->membership((int)$group->id, (int)$me->id);
        $this->membership((int)$group->id, (int)$user->id);
        return response()->json(['id' => $group->id, 'name' => $name]);
    }

    /**
     * Return unread counts per group and total for current viewer.
     */
    public function unreadCounts(Request $request)
    {
        $me = $this->user();
        if (!$me) {
            return response()->json(['total' => 0, 'groups' => []]);
        }
        // Only real admins get admin unread logic
        $isAdmin = $this->isRootAdmin();
        $uid = (int) $me->id;

        $all = ChatGroup::orderBy('id')->get(['id','name','slug']);
        if ($isAdmin) {
            $groups = $all->reject(function($g){ return Str::startsWith($g->slug, 'dm2-'); });
        } else {
            $groups = $all->filter(function($g) use ($uid){
                if (!Str::startsWith($g->slug, 'dm')) return true; // public groups
                if ($g->slug === ('dm-'.$uid)) return true; // admin<->me
                if (Str::startsWith($g->slug, 'dm2-')){
                    $parts = explode('-', $g->slug); $a=(int)($parts[1]??0); $b=(int)($parts[2]??0);
                    return $a===$uid || $b===$uid; // only my dm2
                }
                return false;
            });
            // Ensure own legacy DM presence
            $dm = ChatGroup::where('slug', 'dm-' . $uid)->first();
            if ($dm && !$groups->contains('id', $dm->id)) { $groups->push($dm); }
        }

        $result = [];
        $total = 0;
        foreach ($groups as $g) {
            if (!$g) continue;
            $mem = ChatGroupMember::where('group_id', $g->id)->where('user_id', $uid)->first();
            $lastIdInGroup = (int) (ChatMessage::where('group_id', $g->id)->max('id') ?? 0);
            // Fallback to current latest id if membership not found (no flood on first visit)
            $lastSeen = $mem && (int)($mem->last_seen_id ?? 0) > 0 ? (int)$mem->last_seen_id : $lastIdInGroup;

            $q = ChatMessage::where('group_id', $g->id)
                ->where('id', '>', $lastSeen);

            // Per-group elevation: root OR (chat-admin and bookings)
            $isElevated = $isAdmin || ($this->isChatAdminOnly() && strtolower($g->slug) === 'bookings');

            if ($isElevated) {
                $q->where(function($w){
                    $w->whereNull('sender_guard')->orWhere('sender_guard', '!=', 'admin');
                });
            } else {
                // ...existing non-admin unread query...
                $slug = $g->slug;
                $isUsersDM = $slug === ('dm-' . $uid);
                $isDM2 = \Illuminate\Support\Str::startsWith($slug, 'dm2-')
                           && preg_match('/^dm2-(\d+)-(\d+)$/', $slug, $m)
                           && ((int)$m[1] === $uid || (int)$m[2] === $uid);
                if ($isUsersDM) {
                    $q->where('sender_guard', 'admin');
                } elseif ($isDM2) {
                    $q->where('user_id', '!=', $uid);
                } else {
                    $q->where('sender_guard', 'admin')
                      ->whereIn('reply_to_message_id', function($sq) use ($uid){
                          $sq->select('id')->from('chat_messages')->where('user_id', $uid);
                      });
                }
            }

            $count = (int) $q->count();
            if ($count > 0) {
                $latest = (clone $q)->with('user:id,name')->orderBy('id','desc')->first();
                $latestData = null;
                if ($latest) {
                    $latestData = [
                        'id' => $latest->id,
                        'type' => $latest->type,
                        'content' => $latest->content,
                        'original_name' => $latest->original_name,
                        'sender_guard' => $latest->sender_guard,
                        'sender_name' => $latest->sender_name,
                        'user' => ($latest->relationLoaded('user') && $latest->user) ? [ 'id'=>$latest->user->id, 'name' => $latest->user->name ] : null,
                    ];
                }
                $result[] = [
                    'group_id' => $g->id,
                    'group_name' => $g->name,
                    'count' => $count,
                    'latest' => $latestData,
                ];
                $total += $count;
            }
        }

        return response()->json(['total' => $total, 'groups' => $result]);
    }

    /**
     * Mark a group as seen up to a certain message id (or latest if not provided)
     */
    public function markSeen(Request $request)
    {
        $me = $this->user();
        if (!$me) return response()->json(['message' => 'Unauthorized'], 401);
        $request->validate([
            'group_id' => 'required|integer|exists:chat_groups,id',
            'last_id' => 'nullable|integer',
        ]);
        $gid = $request->integer('group_id');
        $lastId = $request->has('last_id') ? (int)$request->integer('last_id') : ((int)(ChatMessage::where('group_id', $gid)->max('id') ?? 0));

        $mem = ChatGroupMember::where('group_id', $gid)->where('user_id', $me->id)->first();
        if (!$mem) { $mem = $this->membership($gid, (int)$me->id); }
        $mem->last_seen_id = max((int)($mem->last_seen_id ?? 0), $lastId);
        if (empty($mem->joined_at)) { $mem->joined_at = now(); }
        $mem->save();

        return response()->json(['status' => 'ok', 'last_seen_id' => (int)$mem->last_seen_id]);
    }

    protected function serializeMessage(ChatMessage $m, $viewer)
    {
        // NEW: normalize and ensure public copy, then build absolute URL
        $fileUrl = null;
        if ($m->file_path) {
            $relative = ltrim(Str::after($m->file_path, 'public/'), '/'); // chat/<gid>/<file>
            $this->ensurePublicCopy($relative);
            $fileUrl = url('storage/' . $relative);
        }

        $hasUserRel = $m->relationLoaded('user') && $m->user;
        $senderGuard = $m->sender_guard ?: ($m->reply_to_message_id ? 'admin' : ($hasUserRel ? 'web' : 'admin'));
        $senderName = ($senderGuard === 'admin') ? 'Super Admin' : ($m->sender_name ?: ($hasUserRel ? $m->user->name : 'User'));

    // Determine viewer and guard category; support api/api_admin as well
    $viewerGuard = $this->currentGuard();
    // Prefer the provided viewer (from caller), else resolve via helpers
    $viewer = $viewer ?: $this->user();
    $mine = $viewer ? ((int)$viewer->id === (int)$m->user_id && $viewerGuard === $senderGuard) : false;

        // Determine status from latest reaction of interest
        $status = null;
        if ($m->relationLoaded('reactions') && $m->reactions) {
            $rx = $m->reactions->filter(function($r){ return in_array($r->type, ['Hold','Booked','Unbooked'], true); })
                               ->sortByDesc('id')
                               ->first();
            if ($rx) { $status = strtolower($rx->type); }
        }

        $displayUser = null;
        if ($senderGuard !== 'admin' && $hasUserRel) {
            $displayUser = [
                'id' => $m->user->id,
                'name' => $m->user->name,
                'avatar' => $this->avatarUrl($m->user),
                // NEW: include chat-admin flag if present
                'is_chat_admin' => (Schema::hasColumn('users','is_chat_admin') ? (bool)($m->user->is_chat_admin ?? false) : false),
            ];
        }

        $data = [
            'id' => $m->id,
            'group_id' => $m->group_id,
            'user_id' => $m->user_id,
            'user' => $displayUser,
            'sender_name' => $senderName,
            'sender_guard' => $senderGuard,
            'type' => $m->type,
            'content' => $m->content,
            'file_url' => $fileUrl, // CHANGED: now absolute URL and guaranteed copied
            'original_name' => $m->original_name,
            'created_at' => $m->created_at?->toISOString(),
            'mine' => $mine,
            'reply_to_message_id' => $m->reply_to_message_id,
            'status' => $status,
        ];

        if ($viewer && (int)$viewer->id === (int)$m->user_id && $viewerGuard === $senderGuard) {
            $data['reactions'] = $m->relationLoaded('reactions') ? $m->reactions->map(function(ChatReaction $r){
                return [ 'type' => $r->type, 'user_id' => $r->user_id, 'user' => $r->relationLoaded('user') && $r->user ? [ 'id'=>$r->user->id, 'name'=>$r->user->name ] : null ];
            })->values() : [];
        } else {
            $data['reactions'] = [];
        }
        return $data;
    }

    // Add this method to support DELETE for messages:
    public function destroy($id)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $msg = ChatMessage::find($id);
        if (!$msg) return response()->json(['message' => 'Not found'], 404);
        // Only allow sender or admin to delete
        if ($this->isSuperAdmin() || $msg->user_id === $user->id) {
            $msg->delete();
            return response()->json(['status' => 'deleted']);
        }
        return response()->json(['message' => 'Forbidden'], 403);
    }

    public function promptDelete($messageId)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $msg = ChatMessage::find($messageId);
        if (!$msg) return response()->json(['message' => 'Not found'], 404);
        // Only allow sender or admin to delete
        if ($this->isSuperAdmin() || $msg->user_id === $user->id) {
            $msg->delete();
            return response()->json(['status' => 'deleted']);
        }
        return response()->json(['message' => 'Forbidden'], 403);
    }

    // NEW: promote/demote a user to chat admin (root admin only)
    public function setChatAdmin(Request $request, User $user)
    {
        if (!$this->isRootAdmin()) return response()->json(['message' => 'Forbidden'], 403);
        $request->validate(['is_admin' => 'required|boolean']);
        if (!Schema::hasColumn('users','is_chat_admin')) {
            return response()->json(['message' => 'Missing users.is_chat_admin column'], 422);
        }
        $user->is_chat_admin = $request->boolean('is_admin');
        $user->save();
        return response()->json([
            'id' => (int)$user->id,
            'name' => $user->name,
            'is_chat_admin' => (bool)$user->is_chat_admin
        ]);
    }

    // NEW: returns true only for promoted web users (non-root)
    protected function isChatAdminOnly(): bool
    {
        if ($this->isRootAdmin()) return false;
        // Allow promoted chat admins via either web or api guard
        if ($this->guardCheck('web') && $this->userIsChatAdmin(auth('web')->user())) return true;
        if ($this->guardCheck('api') && $this->userIsChatAdmin(auth('api')->user())) return true;
        return false;
    }
}
