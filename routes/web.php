<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\ProductController;
use App\Http\Controllers\SuperAdmin\ProductViewController;
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



Route::get('/',[UserLoginController::class, 'index'])->name('login');
Route::post('/', [UserLoginController::class, 'login'])->name('login.submit'); 


Route::middleware(['multi_auth:web,admin'])->prefix('user')->name('user.')->group(function () {
    
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});

Route::get('superadmin/viewproduct/pdf/{category?}', [ProductViewController::class, 'exportPdf'])
    ->name('superadmin.viewproduct.pdf');

Route::get('superadmin/viewproduct/excel/{category?}', [ProductViewController::class, 'exportExcel'])
    ->name('superadmin.viewproduct.excel');

