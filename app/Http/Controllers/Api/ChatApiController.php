<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatApiController extends Controller
{
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

            $groupId = $request->group_id;
            $isMember = ChatGroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();

            if (!$isMember) {
                return response()->json(['success' => false, 'message' => 'Not a member'], 403);
            }

            $messages = ChatMessage::where('group_id', $groupId)->with(['user'])->latest()->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $messages->items(),
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
                'sender_guard' => request()->is('api/admin/*') ? 'admin' : 'api',
                'sender_name' => $user->name
            ]);

            ChatGroup::where('id', $groupId)->touch();

            return response()->json(['success' => true, 'data' => $message, 'message' => 'Message sent'], 201);
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
