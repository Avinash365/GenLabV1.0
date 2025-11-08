<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class ChatApiController extends Controller
{
    // Build a public URL for a message's file and ensure it's accessible under public/storage
    private function ensurePublicCopy(?string $relative): void
    {
        if (!$relative) return;
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
        } catch (\Throwable $e) { /* ignore */ }
    }

    private function fileUrlFor(ChatMessage $message): ?string
    {
        if (empty($message->file_path)) return null;
        // Normalize: strip leading 'public/' if present (storage public disk)
        $rel = ltrim(Str::after($message->file_path, 'public/'), '/');
        if ($rel === $message->file_path) { $rel = ltrim($rel, '/'); }
        $this->ensurePublicCopy($rel);
        return url('storage/' . $rel);
    }

    private function transformMessage(ChatMessage $m): array
    {
        $arr = $m->toArray();
        $arr['file_url'] = $this->fileUrlFor($m);
        return $arr;
    }

    /**
     * Get the authenticated user based on route
     */
    private function getAuthenticatedUser()
    {
        if (request()->is('api/admin/*')) {
            return auth('api_admin')->user();
        }
        return auth('api')->user();
    }

    /**
     * Get all chat groups for authenticated user
     */
    public function getGroups(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $groupIds = ChatGroupMember::where('user_id', $user->id)->pluck('group_id');
            if ($groupIds->isEmpty()) {
                return response()->json(['success' => true, 'data' => [], 'message' => 'No groups found']);
            }

            $groups = ChatGroup::whereIn('id', $groupIds)->orderBy('updated_at', 'desc')->get();

            return response()->json(['success' => true, 'data' => $groups, 'message' => 'Groups retrieved']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new chat group
     */
    public function createGroup(Request $request): JsonResponse
    {
        try {
            $request->validate(['name' => 'required|string|max:255']);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $group = ChatGroup::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
                'created_by' => $user->id,
            ]);

            ChatGroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'joined_at' => now()
            ]);

            return response()->json(['success' => true, 'data' => $group, 'message' => 'Group created'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get messages for a group
     */
    public function getMessages(Request $request): JsonResponse
    {
        try {
            $request->validate(['group_id' => 'required|exists:chat_groups,id']);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $groupId = (int)$request->group_id;
            $isMember = ChatGroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();

            if (!$isMember) {
                return response()->json(['success' => false, 'message' => 'Not a member'], 403);
            }

            $messages = ChatMessage::where('group_id', $groupId)
                ->with(['user'])
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            $items = collect($messages->items())
                ->reverse()
                ->values()
                ->map(function($m){ return $this->transformMessage($m); })
                ->all();

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'total' => $messages->total(),
                    'has_more_pages' => $messages->hasMorePages()
                ],
                'message' => 'Messages retrieved'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'group_id' => 'required|exists:chat_groups,id',
                'type' => 'required|in:text,file,image',
                'content' => 'required_if:type,text|string'
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $groupId = $request->group_id;
            $isMember = ChatGroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();

            if (!$isMember) {
                return response()->json(['success' => false, 'message' => 'Not a member'], 403);
            }

            $message = ChatMessage::create([
                'group_id' => $groupId,
                'user_id' => $user->id,
                'type' => $request->type,
                'content' => $request->content,
                // Normalize to guards used by ChatController serializer
                'sender_guard' => request()->is('api/admin/*') ? 'admin' : 'web',
                'sender_name' => $user->name
            ]);

            ChatGroup::where('id', $groupId)->touch();

            return response()->json(['success' => true, 'data' => $this->transformMessage($message), 'message' => 'Message sent'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get single message details
     */
    public function getMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

            $message = ChatMessage::with('user')->findOrFail($messageId);
            $isMember = ChatGroupMember::where('group_id', $message->group_id)->where('user_id', $user->id)->exists();
            if (!$isMember) return response()->json(['success' => false, 'message' => 'Not a member'], 403);

            return response()->json(['success' => true, 'data' => $this->transformMessage($message)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reply to a message in the same group
     */
    public function replyToMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:text,file,image',
                'content' => 'required_if:type,text|string'
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

            $parent = ChatMessage::findOrFail($messageId);
            $groupId = $parent->group_id;
            $isMember = ChatGroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();
            if (!$isMember) return response()->json(['success' => false, 'message' => 'Not a member'], 403);

            $msg = ChatMessage::create([
                'group_id' => $groupId,
                'user_id' => $user->id,
                'type' => $request->type,
                'content' => $request->content,
                'reply_to_message_id' => $parent->id,
                'sender_guard' => request()->is('api/admin/*') ? 'admin' : 'web',
                'sender_name' => $user->name,
            ]);
            ChatGroup::where('id', $groupId)->touch();
            return response()->json(['success' => true, 'data' => $this->transformMessage($msg), 'message' => 'Reply sent'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Forward a message to one or more target groups
     */
    public function forwardMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $request->validate([
                'target_group_ids' => 'required|array|min:1',
                'target_group_ids.*' => 'integer|exists:chat_groups,id'
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

            $src = ChatMessage::findOrFail($messageId);
            $created = [];
            foreach ($request->target_group_ids as $gid) {
                $isMember = ChatGroupMember::where('group_id', $gid)->where('user_id', $user->id)->exists();
                if (!$isMember) continue; // skip groups user isn't part of
                $created[] = ChatMessage::create([
                    'group_id' => (int)$gid,
                    'user_id' => $user->id,
                    'type' => $src->type,
                    'content' => $src->content,
                    'file_path' => $src->file_path,
                    'original_name' => $src->original_name,
                    'sender_guard' => request()->is('api/admin/*') ? 'admin' : 'web',
                    'sender_name' => $user->name,
                ]);
                ChatGroup::where('id', $gid)->touch();
            }
            return response()->json([
                'success' => true,
                'data' => array_map(function($m){ return $this->transformMessage($m); }, $created),
                'message' => 'Message forwarded'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Share a message (alias of forward) with a single target or multiple
     */
    public function shareMessage(Request $request, int $messageId): JsonResponse
    {
        // Accept either target_group_id or target_group_ids[]
        $ids = $request->input('target_group_ids');
        if (!$ids && $request->filled('target_group_id')) {
            $ids = [(int)$request->input('target_group_id')];
            $request->merge(['target_group_ids' => $ids]);
        }
        return $this->forwardMessage($request, $messageId);
    }

    /**
     * Set message status (Hold, Booked, Cancel)
     */
    public function setMessageStatus(Request $request, int $messageId): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:hold,booked,cancel'
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

            $message = ChatMessage::findOrFail($messageId);
            $isMember = ChatGroupMember::where('group_id', $message->group_id)->where('user_id', $user->id)->exists();
            if (!$isMember) return response()->json(['success' => false, 'message' => 'Not a member'], 403);

            // Map to existing reaction types used for status display
            $map = [
                'hold' => 'Hold',
                'booked' => 'Booked',
                'cancel' => 'Unbooked',
            ];
            $type = $map[strtolower($request->input('status'))] ?? 'Hold';

            // Upsert reaction for this user/message to satisfy unique constraint
            $rx = \App\Models\ChatReaction::where('message_id', $message->id)
                ->where('user_id', $user->id)
                ->first();
            if ($rx) {
                $rx->type = $type;
                $rx->save();
            } else {
                \App\Models\ChatReaction::create([
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'type' => $type,
                ]);
            }

            ChatGroup::where('id', $message->group_id)->touch();
            return response()->json(['success' => true, 'message' => 'Status updated', 'data' => ['status' => strtolower($type)]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * React to a message
     */
    public function reactToMessage(Request $request, $messageId): JsonResponse
    {
        try {
            $request->validate(['type' => 'required|in:like,love,laugh,angry,sad,wow']);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            return response()->json(['success' => true, 'message' => 'Reaction added']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Search users
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $request->validate(['q' => 'required|string|min:2']);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $query = $request->q;
            $users = User::where('name', 'LIKE', "%{$query}%")->limit(20)->get();

            return response()->json(['success' => true, 'data' => $users, 'message' => 'Users found']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get unread counts
     */
    public function getUnreadCounts(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $groupIds = ChatGroupMember::where('user_id', $user->id)->pluck('group_id');
            $unreadCounts = [];
            
            foreach ($groupIds as $groupId) {
                $unreadCounts[$groupId] = ChatMessage::where('group_id', $groupId)
                    ->where('user_id', '!=', $user->id)
                    ->where('created_at', '>', now()->subDays(1))
                    ->count();
            }

            return response()->json(['success' => true, 'data' => $unreadCounts, 'message' => 'Counts retrieved']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, $messageId): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $message = ChatMessage::findOrFail($messageId);

            if ($message->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Cannot delete'], 403);
            }

            $message->delete();
            return response()->json(['success' => true, 'message' => 'Message deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
