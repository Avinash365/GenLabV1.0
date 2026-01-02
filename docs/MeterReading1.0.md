Endpoints & JSON

GET /api/meter-reading

Auth: Bearer JWT header Authorization: Bearer <token>
Query params: page, per_page (25|100|250), search, month, year, marketing_person (admin only)
Success (200):
{
"success": true,
"data": {
"current_page": 1,
"data": [
{
"id": 16,
"description": null,
"starting_reading": 777,
"starting_at": "2025-12-31 18:31:56",
"starting_image": "/storage/meter_readings/IEQMWf6....png",
"ending_reading": 666,
"ending_at": "2025-12-31 18:39:45",
"ending_image": "/storage/meter_readings/eHt6tZ...png",
"total_reading": -111,
"marketing_person": {"id":36,"name":"Manish Sir","user_code":"MKT001"}
},
...
],
"first_page_url":"https://your-host/api/meter-reading?page=1",
"last_page":1,
"per_page":25,
"total":8
},
"meta": {"current_page":1,"last_page":1,"per_page":25,"total":8}
}
Errors: 401 Unauthorized → {"success":false,"message":"Unauthenticated"}
POST /api/meter-reading/upload

Auth: Bearer JWT header
Content-Type: multipart/form-data
Form fields: current_reading (required numeric), image (optional file), description (optional string)
Behavior: if user has open reading → closes it (sets ending_reading/ending_at/ending_image, computes total), else → creates starting reading.
Start success (201):
{
"success": true,
"message": "Reading started successfully",
"reading": {
"id": 17,
"starting_reading": 555,
"starting_at": "2025-12-31 20:16:31",
"starting_image": "/storage/meter_readings/U5Jm0k...png",
"ending_reading": null,
"ending_at": null,
"ending_image": null,
"total_reading": null
}
}
Close success (200):
{
"success": true,
"message": "Reading closed successfully",
"reading": {
"id": 16,
"starting_reading": 777,
"starting_at": "2025-12-31 18:31:56",
"starting_image": "/storage/meter_readings/IEQMWf...png",
"ending_reading": 666,
"ending_at": "2025-12-31 18:39:45",
"ending_image": "/storage/meter_readings/eHt6tZ...png",
"total_reading": -111
}
}
Validation error (422):
{
"success": false,
"errors": {
"current_reading": ["The current_reading field is required."],
"image": ["The image field must be an image."]
}
}
Auth error (401): {"success":false,"message":"Unauthenticated"}
Temporary local debug (development only)

GET /api/debug/meter-reading/proxy?token=<JWT> — proxies list request (use when Authorization header not forwarded).
POST /api/debug/meter-reading/upload-proxy?token=<JWT> — proxies upload multipart.
GET /api/debug/echo-headers — returns received headers (use to confirm header forwarding).
GET /api/debug/token-verify — shows JWTAuth parsed user vs auth('api')->user().
Curl examples

Login to get token:
curl -X POST -d "user_code=MKT001&password=12345678" https://your-host/api/user/login
List (header):
curl -H "Authorization: Bearer <token>" "https://your-host/api/meter-reading?per_page=25&page=1"
Upload (start):
curl -H "Authorization: Bearer <token>" -F "current_reading=100" -F "description=Start" -F "image=@/path/photo.jpg" https://your-host/api/meter-reading/upload
Upload via proxy (local dev):
curl -F "current_reading=100" -F "image=@/path/photo.jpg" "http://127.0.0.1:8000/api/debug/meter-reading/upload-proxy?token=<token>"
