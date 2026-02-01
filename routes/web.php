<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\RoleAndPermissionController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\SuperAdmin\ProductController;
use App\Http\Controllers\SuperAdmin\ProductViewController;
use App\Http\Controllers\SuperAdmin\WebSettingController;
use App\Http\Controllers\SuperAdmin\ReportingLettersController;
use App\Http\Controllers\SuperAdmin\HoldCancelController;
use App\Http\Controllers\Superadmin\LabAnalystsController;
 use App\Http\Controllers\Superadmin\ProfileController;
use App\Http\Controllers\Accounts\MarketingExpenseController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Api\Attendance\EsslAdmsController;
use Illuminate\Http\Request;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Accounts\ManualInvoicePaymentController; 
 


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root auth
Route::get('/', [UserLoginController::class, 'index'])->name('login');
Route::post('/', [UserLoginController::class, 'login'])->name('login.submit');

// eSSL ADMS default endpoint (/iclock/cdata)
Route::any('/iclock/cdata', EsslAdmsController::class)->name('attendance.essl.adms');


// User dashboard
Route::middleware(['multi_auth:web,admin'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});


// Products
Route::resource('categories', ProductCategoryController::class);
Route::get('superadmin/viewproduct/pdf/{category?}', [ProductViewController::class, 'exportPdf'])->name('superadmin.viewproduct.pdf');
Route::get('superadmin/viewproduct/excel/{category?}', [ProductViewController::class, 'exportExcel'])->name('superadmin.viewproduct.excel');


// Web Settings (protected)
Route::middleware(['web', 'multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Web Settings
    Route::get('/web-settings', [WebSettingController::class, 'edit'])->name('websettings.edit')->middleware('permission:web-settings.edit');
    Route::post('/web-settings', [WebSettingController::class, 'update'])->name('websettings.update')->middleware('permission:web-settings.edit');
    Route::get('websettings/backed-booking', [WebSettingController::class, 'updateBackedBooking'])->name('websettings.backed_booking')->middleware('permission:web-settings.edit');

    // Quick AJAX role creation used on role create page
    Route::post('/roles/quick', [RoleAndPermissionController::class, 'quickStore'])->name('roles.quickstore');

});


// Reporting (protected)
Route::middleware(['web', 'multi_auth:web,admin'])
    ->middleware('permission:reporting.edit')->prefix('superadmin/reporting')->name('superadmin.reporting.')->group(function () {
    Route::get('/letters', [ReportingLettersController::class, 'index'])->name('letters.index');
    Route::post('/letters/upload', [ReportingLettersController::class, 'upload'])->name('letters.upload');
    Route::get('/letters/show/{job}/{filename}', [ReportingLettersController::class, 'show'])
        ->where('filename', '.*')
        ->name('letters.show');
    Route::delete('/letters/delete/{job}/{filename}', [ReportingLettersController::class, 'destroy'])
        ->where('filename', '.*')
        ->name('letters.destroy');

    Route::get('/hold-cancel', [HoldCancelController::class, 'index'])->name('holdcancel.index');
    Route::post('/hold/{id}', [HoldCancelController::class, 'hold'])->name('hold');
    Route::post('/unhold/{id}', [HoldCancelController::class, 'unhold'])->name('unhold');
    Route::post('/cancel/{id}', [HoldCancelController::class, 'cancel'])->name('cancel');
    Route::post('/hold-all', [HoldCancelController::class, 'holdAll'])->name('holdAll');
    Route::post('/cancel-all', [HoldCancelController::class, 'cancelAll'])->name('cancelAll');


});

// Marketing Reporting View (Read-only)
Route::middleware(['web', 'multi_auth:web,admin'])
    ->prefix('superadmin/reporting')->name('superadmin.reporting.marketing.')->group(function () {
    Route::get('/marketing-hold-cancel', [App\Http\Controllers\SuperAdmin\MarketingHoldCancelController::class, 'index'])->name('holdcancel.index');
    Route::post('/marketing-hold-cancel/enquiry', [App\Http\Controllers\SuperAdmin\MarketingHoldCancelController::class, 'storeEnquiry'])->name('enquiry.store');
});


// Lab Analysts (protected)
Route::prefix('superadmin')->name('superadmin.')->middleware(['web','auth'])->group(function(){
    Route::get('/lab-analysts/render', [LabAnalystsController::class, 'render'])->name('labanalysts.render')->middleware('permission:lab-analysts.view');
    // Separate name for POST to avoid duplicate route name in cache
    Route::post('/lab-analysts/render', [LabAnalystsController::class, 'render'])->name('labanalysts.render.submit')->middleware('permission:lab-analysts.edit');
});


 

 

// Superadmin Profile (protected)
Route::middleware(['web', 'auth:web,admin'])->prefix('superadmin')->as('superadmin.')->group(function(){
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Booking trend API for dashboard charts
    Route::get('/dashboard/booking-trend', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'bookingTrendChart'])->name('dashboard.bookingTrend');
    // Bookings by department (counts + amounts) for dashboard
    Route::get('/dashboard/bookings-by-department', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'bookingsByDepartmentChart'])->name('dashboard.bookingsByDepartment');
    // Bookings by marketing person (counts + amounts) for dashboard
    Route::get('/dashboard/bookings-by-marketing', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'bookingsByMarketingChart'])->name('dashboard.bookingsByMarketing');
});

// Cleared Expenses listing (saves PDFs under storage/public/marketing_expenses/in_account)
Route::middleware(['web','multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function(){
    Route::get('/accounts/cleared-expenses', [MarketingExpenseController::class, 'clearedExpenses'])
        ->name('accounts.cleared_expenses')
        ->middleware('permission:account.view');
});

 
// Chatbot query
Route::post('/chatbot/query', [ChatbotController::class, 'query']);

// Test Pusher route
Route::get('/pusher-test', function () {
    $dummyMessage = (object)[
        'content' => 'Test message from Pusher!',
        'user_id' => 1,
        'created_at' => now(),
    ];
    event(new MessageSent($dummyMessage));
    return 'Pusher test event sent!';
});

// Admin-only routes
Route::middleware('auth:admin,superadmin')->group(function () {
    Route::post('/chat/users/{user}/chat-admin', [ChatController::class, 'setChatAdmin'])
        ->name('chat.setChatAdmin');
});

// Basic chat routes (respect multi-auth guards so clicking doesn't redirect/log out)
Route::middleware(['web','multi_auth:web,admin'])->group(function () {
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages/{user}', [App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
    Route::get('/chat/search', [App\Http\Controllers\ChatController::class, 'search'])->name('chat.search');
    Route::get('/chat/contacts', [App\Http\Controllers\ChatController::class, 'contacts'])->name('chat.contacts');
    Route::post('/chat/messages', [App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/typing', [App\Http\Controllers\ChatController::class, 'typing'])->name('chat.typing');
    Route::post('/chat/messages/reaction', [App\Http\Controllers\ChatController::class, 'reaction'])->name('chat.reaction');
});

// Attendance export (monthly CSV)
Route::middleware(['web','multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/hr/attendance/download-monthly', [App\Http\Controllers\SuperAdmin\HR\AttendanceExportController::class, 'downloadMonthly'])->name('hr.attendance.downloadMonthly');
});

use Spatie\Browsershot\Browsershot; 






use App\Http\Controllers\SuperAdmin\VehicleController;

// Vehicle Registration routes
Route::middleware(['web', 'multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/vehicles/export/pdf', [VehicleController::class, 'exportPdf'])->name('vehicles.export.pdf');
    Route::get('/vehicles/export/excel', [VehicleController::class, 'exportExcel'])->name('vehicles.export.excel');
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/vehicles/{vehicle}/modal', [VehicleController::class, 'modal'])->name('vehicles.modal');
    Route::get('/vehicles/preview/{path}', [VehicleController::class, 'previewFile'])->where('path', '.*')->name('vehicles.preview');
    Route::post('/vehicles/{vehicle}/service', [VehicleController::class, 'storeService'])->name('vehicles.service.store');
    Route::get('/vehicles/download/{path}', [VehicleController::class, 'downloadFile'])->where('path', '.*')->name('vehicles.download');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
});

// Reception (Phone Directory)
Route::middleware(['web', 'multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/reception', [App\Http\Controllers\Superadmin\ReceptionController::class, 'index'])->name('reception.index');
    Route::get('/reception/export/pdf', [App\Http\Controllers\Superadmin\ReceptionController::class, 'exportPdf'])->name('reception.export.pdf');
    Route::get('/reception/export/excel', [App\Http\Controllers\Superadmin\ReceptionController::class, 'exportExcel'])->name('reception.export.excel');
    Route::get('/reception/create', [App\Http\Controllers\Superadmin\ReceptionController::class, 'create'])->name('reception.create');
    Route::post('/reception', [App\Http\Controllers\Superadmin\ReceptionController::class, 'store'])->name('reception.store');
    Route::get('/reception/{reception}', [App\Http\Controllers\Superadmin\ReceptionController::class, 'show'])->name('reception.show');
    Route::get('/reception/{reception}/edit', [App\Http\Controllers\Superadmin\ReceptionController::class, 'edit'])->name('reception.edit');
    Route::put('/reception/{reception}', [App\Http\Controllers\Superadmin\ReceptionController::class, 'update'])->name('reception.update');
    Route::delete('/reception/{reception}', [App\Http\Controllers\Superadmin\ReceptionController::class, 'destroy'])->name('reception.destroy');
});


// Simple Report routes (reads)
Route::middleware(['web', 'multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/report', [\App\Http\Controllers\Superadmin\ReportController::class, 'index'])->name('report.index');
    Route::get('/report/exportPdf', [\App\Http\Controllers\Superadmin\ReportController::class, 'exportPdf'])->name('report.exportPdf');
    Route::get('/report/exportExcel', [\App\Http\Controllers\Superadmin\ReportController::class, 'exportExcel'])->name('report.exportExcel');
    Route::get('/report/{booking}/reports', [\App\Http\Controllers\Superadmin\ReportController::class, 'reports'])->name('report.reports');
});

// Client booking card (print) — renders an HTML page suitable for printing
Route::middleware(['web','multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function(){
    Route::get('/bookings/cards/client/{booking}/{item?}', [\App\Http\Controllers\SuperAdmin\ShowBookingController::class, 'clientCard'])->name('bookings.cards.client');
});





