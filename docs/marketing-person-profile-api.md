Marketing Person Profile API

Endpoint: GET /api/marketing-person/{user_code}/profile
Purpose: Consolidated profile + dashboard stats used by the profile.blade.php view (bookings, invoices, cash letters, transactions, personal expenses, clients), plus helper links for related paginated endpoints.
Authentication

Bearer token required (mobile JWT). Middleware: multi_jwt:api
Header: Authorization: Bearer {token}
Path parameters

user_code (string, required) — marketing person's user_code (e.g. MKT001)
Query parameters

None for this endpoint. Use the data.endpoints URLs for filtered lists (support month, year, search, perPage, etc.).
Success response (200) — shape

JSON object:
status: boolean
message: string
data:
profile: { id: int, name: string, user_code: string, email: string|null, phone: string|null }
avatar: string (URL)
stats: object (keys mirror blade usage)
Examples of keys:
totalBookings (int)
totalBookingAmount (float)
billBookings, totalBillBookingAmount
withoutBillBookings, totalWithoutBillBookings
notGeneratedInvoices, totalNotGeneratedInvoicesAmount
partialTaxInvoices, totalPartialTaxInvoiceAmount
unpaidInvoices, totalUnpaidInvoiceAmount
canceledGeneratedInvoices, totalcanceledGeneratedInvoicesAmount
GeneratedPIs, totalPIAmount, paidPiInvoices, totalPaidPIAmount
transactions, totalTransactionsAmount
cashPaidLetters, totalCashPaidLettersAmount
cashUnpaidLetters, totalCashUnpaidAmounts
cashPartialLetters, totalDueAmount
cashSettledLetters, totalSettledAmount
allClients
tdsAmount (float; placeholder)
totalPersonalExpensesAmount, totalApprovedPersonalExpensesAmount
recent_transactions: [ { id, invoice_no, amount_received, payment_mode, transaction_date } ... ]
endpoints: { bookings, invoices, transactions, cash_transactions, personal_expenses } — URLs to call for full paginated lists
Success example

Errors

401 Unauthorized — missing/invalid token.
422 Validation — if user_code format invalid for other endpoints.
500 Server error — e.g., DB schema mismatch. (Controller avoids using non-existent new_bookings.total_amount by aggregating booking_items.amount.)
Implementation / Files

Documentation file: marketing-person-profile-api.md
Controller method: MarketingPersonInfo.php — method profileOverviewApi
Route: registered in api.php
Notes

avatar resolution prefers storage public/avatars/{id|user_code}.{ext} or profile_picture fields; falls back to /assets/img/profiles/avator1.jpg.
stats keys are intentionally aligned with the blade so frontend can map values directly.
tdsAmount is a placeholder (not computed) — implement if needed.
For filtered lists and pagination, call the URLs in data.endpoints.