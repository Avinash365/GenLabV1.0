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

class ChatController extends Controller
{
    protected function user()
    {
        // Prefer admin, then web, to be consistent with currentGuard()
        return auth('admin')->user() ?: auth('web')->user();
    }

    protected function currentGuard(): ?string
    {
        if (auth('admin')->check()) return 'admin';
        if (auth('web')->check()) return 'web';
        return null;
    }

    protected function isSuperAdmin(): bool
    {
        return auth('admin')->check();
    }

    // Generate an avatar URL for a user based on stored profile pics or model fields; fallback to placeholder
    protected function avatarUrl(?User $u): ?string
    {
        if (!$u) return null;
        // 1) Check uploaded avatar under storage/app/public/avatars/{id}.{ext}
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $path = "avatars/{$u->id}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path);
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
        $isAdmin = $this->isSuperAdmin();

        // Ensure default groups exist
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $list = ChatGroup::orderBy('id')->get(['id','name','slug']);
        if ($isAdmin) {
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
                if (!$isAdmin && $g->slug === ('dm-' . $uid)) {
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
            if ($isAdmin) {
                $base->where(function($w){ $w->whereNull('sender_guard')->orWhere('sender_guard','!=','admin'); });
            } else {
                $slug = $g->slug;
                $isUsersDM = $slug === ('dm-' . $uid);
                $isDM2 = Str::startsWith($slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
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

            $latest = (clone $base)->with('user:id,name') // user for preview only
                                   ->orderBy('id','desc')->first();
            $mem = ChatGroupMember::where('group_id', $g->id)->where('user_id', $uid)->first();
            $lastSeen = (int)($mem->last_seen_id ?? 0);
            $unread = (int) ((clone $base)->where('id', '>', $lastSeen)->count());

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

        return $result;
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
        $isAdmin = $this->isSuperAdmin();

        $groupId = $request->integer('group_id');
        $group = ChatGroup::find($groupId);
        if ($isAdmin && $group && Str::startsWith($group->slug, 'dm2-')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $q = ChatMessage::with(['user:id,name,email', 'reactions.user:id,name']) // include email for avatar
            ->where('group_id', $groupId);

        if (!$isAdmin && $user) {
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            $isDM2 = $group && Str::startsWith($group->slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $group->slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
            if (!$isUsersDM && !$isDM2) {
                // Restrict non-admins in non-DM groups to own messages and admin replies to them
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

    public function messagesSince(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'after_id' => 'required|integer']);
        $user = $this->user();
        $isAdmin = $this->isSuperAdmin();

        $groupId = $request->integer('group_id');
        $group = ChatGroup::find($groupId);
        if ($isAdmin && $group && Str::startsWith($group->slug, 'dm2-')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $q = ChatMessage::with(['user:id,name,email', 'reactions.user:id,name']) // include email for avatar
            ->where('group_id', $groupId)
            ->where('id', '>', $request->integer('after_id'));

        if (!$isAdmin && $user) {
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            $isDM2 = $group && Str::startsWith($group->slug, 'dm2-') && preg_match('/^dm2-(\d+)-(\d+)$/', $group->slug, $m) && ((int)$m[1] === $uid || (int)$m[2] === $uid);
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
            $data['sender_guard'] = $this->currentGuard();
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

        $msg->load('user:id,name,email');
        return response()->json($this->serializeMessage($msg, $user), 201);
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
        if (!$this->isSuperAdmin()) { return response()->json(['message' => 'Forbidden'], 403); }
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
        // Admins should use legacy dm-<user> threads to avoid duplicates
        if ($this->isSuperAdmin()) { return $this->direct($user); }
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
        $isAdmin = $this->isSuperAdmin();
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
            // Do NOT create or update membership here; assume 0 if not found
            $mem = ChatGroupMember::where('group_id', $g->id)->where('user_id', $uid)->first();
            $lastSeen = (int)($mem->last_seen_id ?? 0);

            $q = ChatMessage::where('group_id', $g->id)
                ->where('id', '>', $lastSeen);

            if ($isAdmin) {
                $q->where(function($w){
                    $w->whereNull('sender_guard')->orWhere('sender_guard', '!=', 'admin');
                });
            } else {
                $slug = $g->slug;
                $isUsersDM = $slug === ('dm-' . $uid);
                $isDM2 = Str::startsWith($slug, 'dm2-')
                           && preg_match('/^dm2-(\d+)-(\d+)$/', $slug, $m)
                           && ((int)$m[1] === $uid || (int)$m[2] === $uid);
                if ($isUsersDM) {
                    // Legacy admin<->user DM: count admin messages to the user
                    $q->where('sender_guard', 'admin');
                } elseif ($isDM2) {
                    // User-to-user DM: count messages not authored by me
                    $q->where('user_id', '!=', $uid);
                } else {
                    // Public groups: count admin replies to my messages
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
        $fileUrl = $m->file_path ? Storage::url($m->file_path) : null;
        $hasUserRel = $m->relationLoaded('user') && $m->user;
        $senderGuard = $m->sender_guard ?: ($m->reply_to_message_id ? 'admin' : ($hasUserRel ? 'web' : 'admin'));
        $senderName = ($senderGuard === 'admin') ? 'Super Admin' : ($m->sender_name ?: ($hasUserRel ? $m->user->name : 'User'));

        $viewerGuard = $this->currentGuard();
        $viewer = $viewerGuard === 'admin' ? auth('admin')->user() : ($viewerGuard === 'web' ? auth('web')->user() : null);
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
            ];
        }

        $data = [
            'id' => $m->id,
            'group_id' => $m->group_id,
            'user_id' => $m->user_id,
            'user' => $displayUser,
            'sender_guard' => $senderGuard,
            'sender_name' => $senderName,
            'type' => $m->type,
            'content' => $m->content,
            'file_url' => $fileUrl,
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
}
