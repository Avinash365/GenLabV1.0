Endpoints Covered

GET: GET /api/meter-reading (auth: Bearer JWT)
POST: POST /api/meter-reading/upload (auth: Bearer JWT; multipart/form-data)
Authentication error (401)

When token missing/invalid.
Example response:
{
"success": false,
"message": "Unauthenticated"
}
GET /api/meter-reading — Success (200)

Description: Returns paginated readings for the authenticated user (admins may filter by marketing_person).
Example request (headers):
Authorization: Bearer <token>
Query: ?per_page=25&page=1&search=&month=&year=2025
Example response:
{
"success": true,
"data": {
"current_page": 1,
"data": [
{
"id": 8,
"description": null,
"starting_reading": 500,
"starting_at": "2025-12-29 21:10:42",
"starting_image": null,
"ending_reading": null,
"ending_at": null,
"ending_image": null,
"total_reading": null,
"marketing_person": {
"id": 36,
"name": "Manish Sir",
"user_code": "MKT001"
}
},
{
"id": 7,
"description": null,
"starting_reading": 500,
"starting_at": "2025-12-29 21:09:51",
"starting_image": "/storage/meter_readings/pn4FO95BXf...png",
"ending_reading": 600,
"ending_at": "2025-12-29 21:10:10",
"ending_image": null,
"total_reading": 100,
"marketing_person": {
"id": 36,
"name": "Manish Sir",
"user_code": "MKT001"
}
}
],
"first_page_url": "https://your-host/api/meter-reading?page=1",
"from": 1,
"last_page": 1,
"last_page_url": "https://your-host/api/meter-reading?page=1",
"links": [
{"url": null, "label": "« Previous", "active": false},
{"url": "https://your-host/api/meter-reading?page=1", "label": "1", "active": true},
{"url": null, "label": "Next »", "active": false}
],
"next_page_url": null,
"path": "https://your-host/api/meter-reading",
"per_page": 25,
"prev_page_url": null,
"to": 2,
"total": 2
},
"meta": {
"current_page": 1,
"last_page": 1,
"per_page": 25,
"total": 2
}
}
GET — Common error (400/422)

Invalid query values → standard 422 with validation details (not commonly returned by index, but validation-driven endpoints use 422).
POST /api/meter-reading/upload — Request

Content-Type: multipart/form-data
Fields:
current_reading (required, numeric)
image (optional file, validated as image)
description (optional string)
Example curl (start new reading):
curl -H "Authorization: Bearer <token>"
-F "current_reading=100.5"
-F "description=Starting reading at site A"
-F "image=@/path/to/photo.jpg"
https://your-host/api/meter-reading/upload
POST — Behavior & Responses

Start a reading (no open reading for user)
Status: 201
Example response:
{
"success": true,
"message": "Reading started successfully",
"reading": {
"id": 12,
"starting_reading": 100.5,
"starting_at": "2025-12-30 18:05:00",
"starting_image": "https://your-host/storage/meter_readings/abc123.png",
"ending_reading": null,
"ending_at": null,
"ending_image": null,
"total_reading": null
}
}
Close an open reading (user has entry with starting_reading and no ending_reading)
Status: 200
Example request: current_reading=150
Example response:
{
"success": true,
"message": "Reading closed successfully",
"reading": {
"id": 12,
"starting_reading": 100.5,
"starting_at": "2025-12-30 18:05:00",
"starting_image": "https://your-host/storage/meter_readings/abc123.png",
"ending_reading": 150,
"ending_at": "2025-12-30 19:00:00",
"ending_image": "https://your-host/storage/meter_readings/xyz789.png",
"total_reading": 49.5
}
}
POST — Validation error (422)

Example (missing required current_reading):
{
"success": false,
"errors": {
"current_reading": [
"The current_reading field is required."
]
}
}
Example (invalid image type):
{
"success": false,
"errors": {
"image": [
"The image field must be an image."
]
}
}
Server/DB errors (500)

Example (missing DB column before migration): error message returned by Laravel in debug mode. Fix: run php artisan migrate.
Image URLs & storage

The API returns starting_image and ending_image as full asset URLs (e.g., https://your-host/storage/meter_readings/<file>). Ensure php artisan storage:link exists and public is writable.
Admin-only filter

Admins may pass query param marketing_person=<user_id> to GET /api/meter-reading to filter others' readings.
Temporary local debug proxies (not for production)

GET proxy: GET /api/debug/meter-reading/proxy?token=<JWT>
POST proxy: POST /api/debug/meter-reading/upload-proxy?token=<JWT> (multipart)
These were added for local environments where Authorization headers are not forwarded. Remove/guard before deploy.
Extras — quick test examples (repeatable)

Obtain token (mobile login):
curl -X POST -d "user_code=MKT001&password=12345678" https://your-host/api/user/login
(Response contains access_token)

List:
curl -H "Authorization: Bearer <token>" "https://your-host/api/meter-reading?per_page=25"

Start reading:
curl -H "Authorization: Bearer <token>" -F "current_reading=100" -F "description=Start" -F "image=@/tmp/photo.jpg" https://your-host/api/meter-reading/upload

Close reading:
curl -H "Authorization: Bearer <token>" -F "current_reading=200" https://your-host/api/meter-reading/upload

