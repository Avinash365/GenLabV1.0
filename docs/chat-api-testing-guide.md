# Chat API Testing Guide

## ✅ Status: WORKING!

The Chat API has been tested and is working correctly. You can use the test credentials below to get started.

## Step 1: Get Authentication Token

Before you can use the chat API, you need to authenticate and get a JWT token.

### Login Endpoint
**POST** `http://127.0.0.1:8000/api/user/login`

**Test Credentials (created for you):**
- User Code: `test123`
- Password: `password123`

**Request Body:**
```json
{
    "user_code": "test123",
    "password": "password123"
}
```

**Response:**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

## Step 2: Use the Token for Chat API

Use the `access_token` from step 1 in the Authorization header:

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: application/json
Accept: application/json
```

## Step 3: Test Chat Endpoints

### Get Chat Groups
**GET** `http://127.0.0.1:8000/api/chat/groups`

### Create a Chat Group
**POST** `http://127.0.0.1:8000/api/chat/groups`
```json
{
    "name": "Test Group",
    "description": "A test chat group",
    "type": "group",
    "is_private": false
}
```

### Send a Message
**POST** `http://127.0.0.1:8000/api/chat/messages`
```json
{
    "group_id": 1,
    "type": "text",
    "content": "Hello, this is a test message!"
}
```

## Troubleshooting

### 401 Unauthorized Error
This means:
1. No token provided in Authorization header
2. Invalid token format
3. Expired token
4. User doesn't exist

**Solution:** 
1. First login to get a valid token
2. Make sure the Authorization header format is: `Bearer YOUR_TOKEN`
3. Check if the token hasn't expired (default: 60 minutes)

### 422 Validation Error
Check your request body matches the required format.

### 403 Forbidden
You don't have permission to access this resource (e.g., not a member of the chat group).

## Postman Collection Example

### 1. Login Request
```
POST http://127.0.0.1:8000/api/user/login
Content-Type: application/json

{
    "user_code": "user123",
    "password": "password123"
}
```

### 2. Get Groups Request (with token)
```
GET http://127.0.0.1:8000/api/chat/groups
Authorization: Bearer {{token_from_login}}
Accept: application/json
```

## Quick Test Script (JavaScript)

```javascript
// Step 1: Login
async function login() {
    const response = await fetch('http://127.0.0.1:8000/api/user/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_code: 'your_user_code',
            password: 'your_password'
        })
    });
    
    const data = await response.json();
    return data.access_token;
}

// Step 2: Get chat groups
async function getChatGroups(token) {
    const response = await fetch('http://127.0.0.1:8000/api/chat/groups', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    return await response.json();
}

// Usage
login()
    .then(token => {
        console.log('Token:', token);
        return getChatGroups(token);
    })
    .then(groups => {
        console.log('Chat Groups:', groups);
    })
    .catch(error => {
        console.error('Error:', error);
    });
```