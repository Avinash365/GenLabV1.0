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

    public function groups()
    {
        // Ensure default groups exist
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        $u = $this->user();
        $isAdmin = $this->isSuperAdmin();
        $list = ChatGroup::orderBy('id')->get(['id','name','slug']);
        // Non-admins: hide other users' DMs; only show their own DM plus non-DM groups
        if (!$isAdmin) {
            $uid = (int) ($u->id ?? 0);
            $list = $list->filter(function($g) use ($uid){
                return !Str::startsWith($g->slug, 'dm-') || $g->slug === ('dm-'.$uid);
            });
        }
        // If logged-in user has a DM, ensure it exists in the list
        if ($u) {
            $dm = ChatGroup::where('slug', 'dm-' . (int)$u->id)->first();
            if ($dm && !$list->contains('id', $dm->id)) { $list->push($dm); }
        }
        // Map to payload; for non-admin users, show DM as 'Super Admin'
        return $list->map(function($g) use ($u, $isAdmin) {
            $name = $g->name;
            if ($u && !$isAdmin && $g->slug === ('dm-' . (int)$u->id)) { $name = 'Super Admin'; }
            return ['id' => $g->id, 'name' => $name];
        })->values();
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

        $q = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $groupId);

        if (!$isAdmin && $user) {
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            if (!$isUsersDM) {
                // Restrict non-admins in non-DM groups to own messages and admin replies to them
                $q->where(function($qr) use ($uid) {
                    $qr->where('user_id', $uid)
                       ->orWhere(function($sub) use ($uid) {
                            $sub->where('sender_guard', 'admin')
                                ->whereIn('reply_to_message_id', function($sq) use ($uid) {
                                    $sq->select('id')->from('chat_messages')->where('user_id', $uid);
                                });
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

        $q = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $groupId)
            ->where('id', '>', $request->integer('after_id'));

        if (!$isAdmin && $user) {
            $uid = (int)$user->id;
            $isUsersDM = $group && $group->slug === ('dm-' . $uid);
            if (!$isUsersDM) {
                $q->where(function($qr) use ($uid) {
                    $qr->where('user_id', $uid)
                       ->orWhere(function($sub) use ($uid) {
                            $sub->where('sender_guard', 'admin')
                                ->whereIn('reply_to_message_id', function($sq) use ($uid) {
                                    $sq->select('id')->from('chat_messages')->where('user_id', $uid);
                                });
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
                        $mime = $value->getMimeType();
                        if ($type === 'image' && !str_starts_with($mime, 'image/')) $fail('Invalid image file');
                        if ($type === 'pdf' && $mime !== 'application/pdf') $fail('Invalid PDF file');
                        if ($type === 'voice' && !str_starts_with($mime, 'audio/')) $fail('Invalid audio file');
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

        $msg->load('user:id,name');
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

        $displayUser = ($senderGuard === 'admin') ? null : ($hasUserRel ? [ 'id' => $m->user->id, 'name' => $m->user->name ] : null);

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
