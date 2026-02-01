API Documentation — Dispatched Reports & Client Hand Over (Mobile)

Summary

Purpose: mobile endpoints to list and update dispatched and handed-over booking items for a marketing person.
Routes file: api.php
Controller: MarketingPersonInfo.php
Auth: JWT via Authorization: Bearer <token> (middleware: multi_jwt:api).
Common

Path param: {user_code} — marketing person code (e.g., MKT001).
Pagination: perPage, page.
Date filters: month, year.
Search: search (matches job order, sample description, booking reference or client name).
Response envelope (success):
{ "status": true, "message": "...", "data": { ... } }
Endpoints

GET /api/marketing-person/{user_code}/reports/dispatched

Description: list bookings (grouped) with items where dispatched_at is set.
Query params (optional): perPage, page, search, month, year
Example request:
GET /api/marketing-person/MKT001/reports/dispatched?perPage=20&search=REF123
Header: Authorization: Bearer <TOKEN>
Example response (200):
{
"status": true,
"message": "Dispatched reports fetched",
"data": {
"bookings": [
{
"id": 101,
"client_name": "Acme Ltd",
"reference_no": "REF123",
"items_count": 2,
"items": [
{
"id": 131,
"job_order_no": "JO-001",
"sample_description": "Cement",
"dispatched_at": "2026-01-30 10:12:00",
"dispatched_by_name": "John Doe"
}
]
}
],
"meta": { "total": 10, "per_page": 20, "current_page": 1, "last_page": 1 }
}
}
GET /api/marketing-person/{user_code}/reports/hand-over

Description: list bookings (grouped) with items where submitted_to is present (handed to client/person).
Query params: same as above.
Example request:
GET /api/marketing-person/MKT001/reports/hand-over?perPage=20
Example response (200):
{
"status": true,
"message": "Handed over items fetched",
"data": {
"bookings": [
{
"id": 101,
"client_name": "Acme Ltd",
"reference_no": "REF123",
"items_count": 1,
"items": [
{
"id": 131,
"job_order_no": "JO-001",
"sample_description": "Cement",
"submitted_to": "Mr. Client",
"submitted_at": "2026-01-30 11:00:00"
}
]
}
],
"meta": { "total": 3, "per_page": 20, "current_page": 1, "last_page": 1 }
}
}
POST /api/marketing-person/{user_code}/reports/dispatched

Description: mark multiple booking items as dispatched (bulk).
Body (JSON):
{
"ids": [131, 132],
"meta": { "dispatched_by_name": "John Doe" } // optional
}
Validations:
ids: required array of integers.
Behavior:
Sets dispatched_at = now(), dispatched_by_id = auth user id (if available), dispatched_by_name (from meta or auth user).
Only updates items whose booking belongs to {user_code} (marketing owner), unless the auth user is admin.
Example curl:
Example response (200):
{
"status": true,
"message": "Marked dispatched",
"data": { "updated": 2, "ids": [131,132] }
}
POST /api/marketing-person/{user_code}/reports/hand-over

Description: mark multiple booking items as handed over (set submitted_to).
Body (JSON):
{
"ids": [131, 132],
"meta": { "submitted_to": "Client X" } // required
}
Validations:
ids: required array of integers.
meta.submitted_to: required string.
Behavior:
Sets submitted_to field for matched items and updates updated_at.
Only updates items whose booking belongs to {user_code} (marketing owner), unless the auth user is admin.
Example curl:
Example response (200):
{
"status": true,
"message": "Marked handed over",
"data": { "updated": 1, "ids": [131], "submitted_to": "Client X" }
}
Errors

400: validation errors (JSON body missing or invalid).
401: invalid or missing JWT.
403: forbidden (marketing auth user trying to act for different {user_code}).
404: {user_code} not found.
