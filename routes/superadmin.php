<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\SuperAdmin\RoleAndPermissionController;
use App\Http\Controllers\SuperAdmin\UserController;

// =======================
// Super Admin Login Routes
// =======================
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

// ==============================
// Super Admin Protected Routes
// ==============================
Route::middleware(['auth:admin', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Role & Permission Management
    Route::prefix('role-and-permissions')->name('roles.')->group(function () {
        Route::get('/', [RoleAndPermissionController::class, 'index'])->name('index');
        Route::get('create', [RoleAndPermissionController::class, 'create'])->name('create');
        Route::post('/', [RoleAndPermissionController::class, 'store'])->name('store');
        Route::get('{id}/edit', [RoleAndPermissionController::class, 'edit'])->name('edit');
        Route::put('{id}', [RoleAndPermissionController::class, 'update'])->name('update');
        Route::delete('{id}', [RoleAndPermissionController::class, 'destroy'])->name('destroy');
        Route::get('{id}', [RoleAndPermissionController::class, 'show'])->name('show');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/list', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{id}', [UserController::class, 'update'])->name('update');
        Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('{id}', [UserController::class, 'show'])->name('show');
    });
});
