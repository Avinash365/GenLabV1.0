<?php 

use App\Http\Controllers\MobileControllers\Auth\UserAuthController;
use App\Http\Controllers\MobileControllers\Auth\AdminAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Api\ChatApiController;
use Illuminate\Support\Facades\Route;

// User Auth Routes
Route::prefix('user')->group(function () {
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('multi_jwt:api');
    Route::post('/refresh', [UserAuthController::class, 'refresh'])->middleware('multi_jwt:api');
    Route::get('/profile', [UserAuthController::class, 'profile'])->middleware('multi_jwt:api');
});

// Admin Auth Routes

    Route::post('admin/login', [AdminAuthController::class, 'login']);

    Route::prefix('admin')->middleware('multi_jwt:api_admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
    });

// Chat API Routes (Protected)
Route::middleware(['multi_jwt:api'])->prefix('chat')->name('api.chat.')->group(function () {
    // Get chat groups/conversations
    Route::get('/groups', [ChatApiController::class, 'getGroups']);
    
    // Create a new group chat
    Route::post('/groups', [ChatApiController::class, 'createGroup']);
    
    // Get messages for a specific chat/group
    Route::get('/messages', [ChatApiController::class, 'getMessages']);
    
    // Send a new message
    Route::post('/messages', [ChatApiController::class, 'sendMessage']);
    // Upload message with file (multipart) via legacy controller
    Route::post('/messages/upload', [ChatController::class, 'send']);
    // Get a single message details
    Route::get('/messages/{messageId}', [ChatApiController::class, 'getMessage']);
    // Reply to a specific message
    Route::post('/messages/{messageId}/reply', [ChatApiController::class, 'replyToMessage']);
    // Forward/share a message to one or more groups
    Route::post('/messages/{messageId}/forward', [ChatApiController::class, 'forwardMessage']);
    Route::post('/messages/{messageId}/share', [ChatApiController::class, 'shareMessage']);
    // Set message status (hold/booked/cancel)
    Route::post('/messages/{messageId}/status', [ChatApiController::class, 'setMessageStatus']);
    
    // React to a message (like/emoji)
    Route::post('/messages/{messageId}/reactions', [ChatApiController::class, 'reactToMessage']);
    
    // Search users for starting new chats
    Route::get('/users/search', [ChatApiController::class, 'searchUsers']);
    
    // Get unread message counts
    Route::get('/unread-counts', [ChatApiController::class, 'getUnreadCounts']);
    
    // Delete a message
    Route::delete('/messages/{messageId}', [ChatApiController::class, 'deleteMessage']);
    
    // Legacy support routes (using existing ChatController for backwards compatibility)
    Route::get('/messages/since', [ChatController::class, 'messagesSince']);
    Route::get('/direct/{user}', [ChatController::class, 'direct']);
    Route::get('/direct-with/{user}', [ChatController::class, 'directWith']);
    Route::post('/mark-seen', [ChatController::class, 'markSeen']);
    Route::post('/messages/{messageId}/prompt-delete', [ChatController::class, 'promptDelete']);
    Route::post('/users/{user}/set-admin', [ChatController::class, 'setChatAdmin']);
});

// Chat API Routes for Admin (Protected with admin auth)
Route::middleware(['multi_jwt:api_admin'])->prefix('admin/chat')->name('api.admin.chat.')->group(function () {
    // Admin chat routes (same as user routes but with admin authentication)
    Route::get('/groups', [ChatApiController::class, 'getGroups']);
    Route::post('/groups', [ChatApiController::class, 'createGroup']);
    Route::get('/messages', [ChatApiController::class, 'getMessages']);
    Route::post('/messages', [ChatApiController::class, 'sendMessage']);
    // Upload message with file (multipart) via legacy controller
    Route::post('/messages/upload', [ChatController::class, 'send']);
    // Get a single message details
    Route::get('/messages/{messageId}', [ChatApiController::class, 'getMessage']);
    // Reply to a specific message
    Route::post('/messages/{messageId}/reply', [ChatApiController::class, 'replyToMessage']);
    // Forward/share a message to one or more groups
    Route::post('/messages/{messageId}/forward', [ChatApiController::class, 'forwardMessage']);
    Route::post('/messages/{messageId}/share', [ChatApiController::class, 'shareMessage']);
    // Set message status (hold/booked/cancel)
    Route::post('/messages/{messageId}/status', [ChatApiController::class, 'setMessageStatus']);
    Route::post('/messages/{messageId}/reactions', [ChatApiController::class, 'reactToMessage']);
    Route::get('/users/search', [ChatApiController::class, 'searchUsers']);
    Route::get('/unread-counts', [ChatApiController::class, 'getUnreadCounts']);
    Route::delete('/messages/{messageId}', [ChatApiController::class, 'deleteMessage']);
    
    // Legacy support routes
    Route::get('/messages/since', [ChatController::class, 'messagesSince']);
    Route::get('/direct/{user}', [ChatController::class, 'direct']);
    Route::get('/direct-with/{user}', [ChatController::class, 'directWith']);
    Route::post('/mark-seen', [ChatController::class, 'markSeen']);
    Route::post('/messages/{messageId}/prompt-delete', [ChatController::class, 'promptDelete']);
    Route::post('/users/{user}/set-admin', [ChatController::class, 'setChatAdmin']);
});