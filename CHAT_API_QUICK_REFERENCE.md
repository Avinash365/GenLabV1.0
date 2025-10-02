# Chat API - Quick Reference

## 🔐 Authentication
```
POST /api/user/login     - User login (user_code + password)
POST /api/admin/login    - Admin login (email + password)
```

## 💬 Chat Endpoints

### For Users (`/api/chat/`)
```
GET    /api/chat/groups                    - Get chat groups
POST   /api/chat/groups                    - Create group
GET    /api/chat/messages?group_id=1       - Get messages
POST   /api/chat/messages                  - Send message
POST   /api/chat/messages/1/reactions      - React to message
GET    /api/chat/unread-counts             - Get unread counts
GET    /api/chat/users/search?q=john       - Search users
```

### For Admins (`/api/admin/chat/`)
```
GET    /api/admin/chat/groups              - Get chat groups
POST   /api/admin/chat/groups              - Create group
GET    /api/admin/chat/messages?group_id=1 - Get messages
POST   /api/admin/chat/messages            - Send message
POST   /api/admin/chat/messages/1/reactions - React to message
GET    /api/admin/chat/unread-counts       - Get unread counts
GET    /api/admin/chat/users/search?q=john - Search users
```

## 📝 Required Headers
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

## 🧪 Test Credentials
```
User:  user_code="test123" password="password123"
Admin: email="admin@example.com" password="admin123"
```

## 📤 Send Message Format
```json
{
    "group_id": 1,
    "type": "text",
    "content": "Your message here"
}
```

## 🏠 Base URL
```
http://127.0.0.1:8000/api
```