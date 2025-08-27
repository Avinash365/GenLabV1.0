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

