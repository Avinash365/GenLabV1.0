<?php 

use App\Http\Controllers\MobileControllers\Auth\UserAuthController;
use App\Http\Controllers\MobileControllers\Auth\AdminAuthController;
 use App\Http\Controllers\Api\Attendance\EsslWebhookController;
 use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo; 
use App\Http\Controllers\Api\ExpenseApiController;
use App\Http\Controllers\Api\MarketingDashboardController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


// Static test file endpoint for client testing
Route::get('static/test-file', function() {
    $path = public_path('favicon.ico'); // guaranteed to exist in this project
    if (!file_exists($path)) {
        abort(404, 'Test file not found');
    }
    return response()->file($path);
});

Route::post('attendance/essl/webhook', EsslWebhookController::class)->name('api.attendance.essl.webhook');

// Debug helper routes removed. These were used for local E2E testing only.
// If you need similar functionality for local debugging, re-add guarded
// routes behind `app.debug` or remove before pushing to remote.

// User Auth Routes
Route::prefix('user')->group(function () {
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('multi_jwt:api');
    Route::post('/refresh', [UserAuthController::class, 'refresh'])->middleware('multi_jwt:api');
    Route::get('/profile', [UserAuthController::class, 'profile'])->middleware('multi_jwt:api');

    Route::post('/device-token', [UserAuthController::class, 'saveDeviceToken'])->middleware('multi_jwt:api');
});

// Admin Auth Routes

    Route::post('admin/login', [AdminAuthController::class, 'login']);

    Route::prefix('admin')->middleware('multi_jwt:api_admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
    });

  

Route::middleware(['multi_jwt:api'])->prefix('expenses')->group(function () {
    Route::get('/', [ExpenseApiController::class, 'index']);
    Route::post('/', [ExpenseApiController::class, 'store']);
    Route::post('/personal/send-for-approval', [ExpenseApiController::class, 'sendPersonalForApproval']);
    Route::get('/{expense}', [ExpenseApiController::class, 'show']);
    Route::match(['put', 'patch'], '/{expense}', [ExpenseApiController::class, 'update']);
    Route::delete('/{expense}', [ExpenseApiController::class, 'destroy']);
});

Route::middleware(['multi_jwt:api_admin'])->prefix('admin/expenses')->group(function () {
    Route::get('/', [ExpenseApiController::class, 'index']);
    Route::get('/{expense}', [ExpenseApiController::class, 'show']);
    Route::post('/{expense}/approve', [ExpenseApiController::class, 'approve']);
    Route::post('/{expense}/reject', [ExpenseApiController::class, 'reject']);
});


Route::middleware(['multi_jwt:api'])->prefix('marketing-person')->group(function () {

    // Fetch Bookings
    Route::get('{user_code}/bookings', 
        [MarketingPersonInfo::class, 'fetchBookings']
    );

    // Booking items by letter (mobile API) - mirrors bookingByLetter Blade view
    Route::get('{user_code}/bookings/by-letter', [MarketingPersonInfo::class, 'bookingByLetter'])->name('api.marketing.booking.by-letter');

    // Booking list for "Booking By Letter" view (includes items, reports, invoice)
    Route::get('{user_code}/bookings/showbooking', [MarketingPersonInfo::class, 'showBookingApi'])->name('api.marketing.booking.showbooking');

    // View-By-Letter bookings (mobile API) - mirrors view-by-letter.blade.php
    Route::get('{user_code}/bookings/view-by-letter', [MarketingPersonInfo::class, 'viewByLetterApi'])->name('api.marketing.bookings.view-by-letter');

    // Reports by Job Order (mobile API) - mirrors view-by-job-order.blade.php
    Route::get('{user_code}/reports/by-job-order', [MarketingPersonInfo::class, 'viewByJobOrderApi'])->name('api.marketing.reports.by-job-order');

    // Pending reports (mobile) - mirrors reporting/pendings.blade.php
    Route::get('{user_code}/reports/pendings', [MarketingPersonInfo::class, 'pendingsApi'])->name('api.marketing.reports.pendings');

    // Fetch Without Bill Bookings
    Route::get('{user_code}/bookings/without-bill', 
        [MarketingPersonInfo::class, 'WithoutBillBookings']
    );

    // Bookings pending invoice generation (mobile)
    Route::get('{user_code}/bookings/generate-invoice', [MarketingPersonInfo::class, 'generateInvoiceListApi'])->name('api.marketing.bookings.generate-invoice');

    // Fetch Invoices
    Route::get('{user_code}/invoices', 
        [MarketingPersonInfo::class, 'fetchInvoices']
    );

    // Invoice list for marketing index (mobile API)
    Route::get('{user_code}/invoices/list', [MarketingPersonInfo::class, 'invoiceListApi'])->name('api.marketing.invoices.list');

    // Fetch Invoice Transactions
    Route::get('{user_code}/invoice-transactions', 
        [MarketingPersonInfo::class, 'fetchInvoicesTransactions']
    );

    // Fetch Cash Transactions
    Route::get('{user_code}/cash-transactions', 
        [MarketingPersonInfo::class, 'fetchCashTransaction']
    );

    // Fetch Clients
    Route::get('{user_code}/clients',
        [MarketingPersonInfo::class, 'fetchClients']
    );

    // Client profile (mobile)
    Route::get('{user_code}/clients/{client_id}/profile', [MarketingPersonInfo::class, 'clientProfileApi'])->name('api.marketing.client.profile');

    // Personal expenses (mobile): list and create
    Route::get('{user_code}/personal/expenses', [MarketingPersonInfo::class, 'personalExpensesListApi'])->name('api.marketing.personal.expenses.list');
    Route::post('{user_code}/personal/expenses', [MarketingPersonInfo::class, 'personalExpensesStoreApi'])->name('api.marketing.personal.expenses.store');

});

// Checked-in (Cleared) personal/approved exports listing (paginated)
Route::middleware(['multi_jwt:api'])->get('superadmin/personal/checked-in', [\App\Http\Controllers\Accounts\MarketingExpenseController::class, 'checkedInApi']);

// Mobile Chat API (user)
Route::middleware(['multi_jwt:api'])->prefix('chat')->group(function () {
    Route::get('contacts', [\App\Http\Controllers\MobileControllers\ChatController::class, 'contacts']);
    Route::get('messages/{user}', [\App\Http\Controllers\MobileControllers\ChatController::class, 'messages']);
    Route::post('messages', [\App\Http\Controllers\MobileControllers\ChatController::class, 'send']);
    Route::post('typing', [\App\Http\Controllers\MobileControllers\ChatController::class, 'typing']);
    Route::post('messages/reaction', [\App\Http\Controllers\MobileControllers\ChatController::class, 'reaction']);
    Route::get('search', [\App\Http\Controllers\MobileControllers\ChatController::class, 'search']);
});

// Mobile Chat API (admin)
Route::middleware(['multi_jwt:api_admin'])->prefix('admin/chat')->group(function () {
    Route::get('contacts', [\App\Http\Controllers\MobileControllers\ChatController::class, 'contacts']);
    Route::get('messages/{user}', [\App\Http\Controllers\MobileControllers\ChatController::class, 'messages']);
    Route::post('messages', [\App\Http\Controllers\MobileControllers\ChatController::class, 'send']);
    Route::post('typing', [\App\Http\Controllers\MobileControllers\ChatController::class, 'typing']);
    Route::post('messages/reaction', [\App\Http\Controllers\MobileControllers\ChatController::class, 'reaction']);
    Route::get('search', [\App\Http\Controllers\MobileControllers\ChatController::class, 'search']);
});

/*
 | Marketing Dashboard API
 | Provides overview and compact summary for marketing dashboard widgets
*/
Route::middleware(['multi_jwt:api'])->prefix('marketing-dashboard')->group(function () {
    Route::get('{user_code}/overview', [MarketingDashboardController::class, 'overview']);
    Route::get('{user_code}/summary', [MarketingDashboardController::class, 'summary']);
    // Time-series / chart data endpoint
    Route::get('{user_code}/series', [MarketingDashboardController::class, 'series']);
});