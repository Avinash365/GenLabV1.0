# Chat API Documentation

## Overview

The Chat API provides endpoints for managing chat groups, sending messages, and handling real-time communication. All endpoints require authentication using JWT tokens.

## Base URL
```
/api/chat/
```

## Authentication

All endpoints require authentication. Include the JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

## Endpoints

### 1. Get Chat Groups

**GET** `/api/chat/groups`

Retrieve all chat groups/conversations for the authenticated user.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Project Team",
            "description": "Main project discussion",
            "type": "group",
            "is_private": false,
            "created_by": 1,
            "created_at": "2025-09-29T10:00:00Z",
            "updated_at": "2025-09-29T12:00:00Z",
            "members_count": 5,
            "last_message": {
                "id": 123,
                "content": "Hello everyone!",
                "created_at": "2025-09-29T12:00:00Z"
            },
            "unread_count": 3
        }
    ],
    "message": "Chat groups retrieved successfully"
}
```

### 2. Create Chat Group

**POST** `/api/chat/groups`

Create a new chat group.

**Request Body:**
```json
{
    "name": "Project Team",
    "description": "Main project discussion",
    "type": "group",
    "is_private": false,
    "members": [2, 3, 4]
}
```

**Parameters:**
- `name` (required): Group name
- `description` (optional): Group description
- `type` (required): "group" or "direct"
- `is_private` (optional): Boolean, default false
- `members` (optional): Array of user IDs to add as members

**Response:** Same as Get Chat Groups for the created group.

### 3. Get Messages

**GET** `/api/chat/messages`

Retrieve messages for a specific chat group.

**Query Parameters:**
- `group_id` (required): Chat group ID
- `page` (optional): Page number for pagination
- `per_page` (optional): Messages per page (max 100, default 50)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "group_id": 1,
            "user_id": 1,
            "type": "text",
            "content": "Hello everyone!",
            "file_path": null,
            "original_name": null,
            "sender_guard": "api",
            "sender_name": "John Doe",
            "reply_to_message_id": null,
            "created_at": "2025-09-29T12:00:00Z",
            "updated_at": "2025-09-29T12:00:00Z",
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "reactions": {
                "like": {
                    "type": "like",
                    "count": 2,
                    "users": [...]
                }
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 50,
        "total": 250,
        "has_more_pages": true
    },
    "message": "Messages retrieved successfully"
}
```

### 4. Send Message

**POST** `/api/chat/messages`

Send a new message to a chat group.

**Request Body (Text Message):**
```json
{
    "group_id": 1,
    "type": "text",
    "content": "Hello everyone!",
    "reply_to_message_id": null
}
```

**Request Body (File Message):**
```json
{
    "group_id": 1,
    "type": "file",
    "file": "<file-upload>",
    "reply_to_message_id": null
}
```

**Parameters:**
- `group_id` (required): Chat group ID
- `type` (required): "text", "file", or "image"
- `content` (required for text): Message content
- `file` (required for file/image): File upload (max 10MB)
- `reply_to_message_id` (optional): ID of message being replied to

**Response:** Returns the created message object.

### 5. React to Message

**POST** `/api/chat/messages/{messageId}/reactions`

Add, update, or remove a reaction to a message.

**Request Body:**
```json
{
    "type": "like"
}
```

**Parameters:**
- `type` (required): "like", "love", "laugh", "angry", "sad", "wow"

**Response:** Returns the updated message with reactions.

### 6. Search Users

**GET** `/api/chat/users/search`

Search for users to add to chats.

**Query Parameters:**
- `q` (required): Search query (minimum 2 characters)
- `limit` (optional): Maximum results (max 50, default 20)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "avatar": null,
            "created_at": "2025-09-29T10:00:00Z"
        }
    ],
    "message": "Users found successfully"
}
```

### 7. Get Unread Counts

**GET** `/api/chat/unread-counts`

Get unread message counts for all chat groups.

**Response:**
```json
{
    "success": true,
    "data": {
        "1": 3,
        "2": 0,
        "3": 7
    },
    "message": "Unread counts retrieved successfully"
}
```

### 8. Delete Message

**DELETE** `/api/chat/messages/{messageId}`

Delete a message. Users can only delete their own messages unless they are group admins.

**Response:**
```json
{
    "success": true,
    "message": "Message deleted successfully"
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

**Common HTTP Status Codes:**
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Internal Server Error

## Admin Routes

Admin routes are available under `/api/admin/chat/` with the same endpoints but require admin authentication using the `multi_jwt:api_admin` middleware.

## File Upload Guidelines

- Maximum file size: 10MB
- Supported file types: All types allowed
- Files are stored in `storage/app/public/chat_files/`
- Original filename is preserved in `original_name` field

## Real-time Features

For real-time chat functionality, consider implementing:
- WebSocket connections for instant message delivery
- Push notifications for offline users
- Typing indicators
- Online/offline status

## Example Usage

### JavaScript/Fetch Example

```javascript
// Get chat groups
const response = await fetch('/api/chat/groups', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});

// Send a text message
const messageResponse = await fetch('/api/chat/messages', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        group_id: 1,
        type: 'text',
        content: 'Hello, world!'
    })
});

// Upload a file
const formData = new FormData();
formData.append('group_id', '1');
formData.append('type', 'file');
formData.append('file', fileInput.files[0]);

const fileResponse = await fetch('/api/chat/messages', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    },
    body: formData
});
```