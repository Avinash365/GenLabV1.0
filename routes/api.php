<?php 

use App\Http\Controllers\MobileControllers\Auth\UserAuthController;
use App\Http\Controllers\MobileControllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

// User Auth Routes
Route::prefix('user')->group(function () {
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('multi_jwt:api');
    Route::post('/refresh', [UserAuthController::class, 'refresh'])->middleware('multi_jwt:api');
    Route::get('/profile', [UserAuthController::class, 'profile'])->middleware('multi_jwt:api');
});

// Admin Auth Routes

    Route::post('admin/login', [AdminAuthController::class, 'login']);

    Route::prefix('admin')->middleware('multi_jwt:api_admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
    });