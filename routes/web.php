<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\SuperAdmin\ProductController;
use App\Http\Controllers\SuperAdmin\ProductViewController;
use App\Http\Controllers\SuperAdmin\WebSettingController;
use App\Http\Controllers\SuperAdmin\ReportingLettersController;
use App\Http\Controllers\SuperAdmin\HoldCancelController;
use App\Http\Controllers\Superadmin\LabAnalystsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Superadmin\ProfileController;
use App\Http\Controllers\ChatbotController;

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
    Route::get('/web-settings', [WebSettingController::class, 'edit'])->name('websettings.edit');
    Route::post('/web-settings', [WebSettingController::class, 'update'])->name('websettings.update');
    Route::get('websettings/backed-booking', [WebSettingController::class, 'updateBackedBooking'])->name('websettings.backed_booking');
    
});


// Reporting (protected)
Route::middleware(['web', 'multi_auth:web,admin'])->prefix('superadmin/reporting')->name('superadmin.reporting.')->group(function () {
    Route::get('/letters', [ReportingLettersController::class, 'index'])->name('letters.index');
    Route::post('/letters/upload', [ReportingLettersController::class, 'upload'])->name('letters.upload');
    Route::get('/hold-cancel', [HoldCancelController::class, 'index'])->name('holdcancel.index');
    Route::post('/hold/{id}', [HoldCancelController::class, 'hold'])->name('hold');
    Route::post('/unhold/{id}', [HoldCancelController::class, 'unhold'])->name('unhold');
    Route::post('/cancel/{id}', [HoldCancelController::class, 'cancel'])->name('cancel');
    Route::post('/hold-all', [HoldCancelController::class, 'holdAll'])->name('holdAll');
    Route::post('/cancel-all', [HoldCancelController::class, 'cancelAll'])->name('cancelAll');
});


// Lab Analysts (protected)
Route::prefix('superadmin')->name('superadmin.')->middleware(['web','auth'])->group(function(){
    Route::get('/lab-analysts/render', [LabAnalystsController::class, 'render'])->name('labanalysts.render');
    Route::post('/lab-analysts/render', [LabAnalystsController::class, 'render'])->name('labanalysts.render');
});


// Chat
Route::prefix('chat')->group(function(){
    Route::get('/groups', [ChatController::class, 'groups'])->name('chat.groups');
    Route::post('/groups', [ChatController::class, 'createGroup'])->name('chat.groups.create');

    Route::get('/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('/messages/since', [ChatController::class, 'messagesSince'])->name('chat.messages.since');
    Route::post('/messages', [ChatController::class, 'send'])->name('chat.messages.send');

    Route::post('/messages/{message}/reactions', [ChatController::class, 'react'])->name('chat.messages.react');

    // New: ensure/get direct chat with user (admin->user legacy DM)
    Route::get('/direct/{user}', [ChatController::class, 'direct'])->name('chat.direct');
    // New: search users and symmetric DM between users
    Route::get('/users/search', [ChatController::class, 'searchUsers'])->name('chat.users.search');
    Route::get('/direct-with/{user}', [ChatController::class, 'directWith'])->name('chat.directWith');
});

// Chat unread counts + seen marker
Route::get('/chat/unread-counts', [ChatController::class, 'unreadCounts'])->name('chat.unread-counts');
Route::post('/chat/mark-seen', [ChatController::class, 'markSeen'])->name('chat.mark-seen');


// Superadmin Profile (protected)
Route::middleware(['web', 'auth:web,admin'])->prefix('superadmin')->as('superadmin.')->group(function(){
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Delete chat message
Route::delete('/chat/messages/{id}', [\App\Http\Controllers\ChatController::class, 'destroy'])->name('chat.messages.destroy');

// Chatbot query
Route::post('/chatbot/query', [ChatbotController::class, 'query']);

