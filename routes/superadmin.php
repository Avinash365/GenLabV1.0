<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\SuperAdmin\RoleAndPermissionController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SuperAdmin\ProductController;
use App\Http\Controllers\SuperAdmin\ProductViewController;
use App\Http\Controllers\SuperAdmin\CategoriesController;
use App\Http\Controllers\SuperAdmin\StoreController;
use App\Http\Controllers\SuperAdmin\SupplierController;
use App\Http\Controllers\SuperAdmin\UnitController;
use App\Http\Controllers\SuperAdmin\IssueController;
use App\Http\Controllers\SuperAdmin\IssueViewController;
use App\Http\Controllers\SuperAdmin\PurchaseListController;
use App\Http\Controllers\SuperAdmin\PurchaseAddController;
use App\Http\Controllers\SuperAdmin\ShowBookingController;
use App\Http\Controllers\SuperAdmin\DepartmentController;
use App\Http\Controllers\SuperAdmin\IsCodesController;
use App\Http\Controllers\SuperAdmin\CalibrationController;
use App\Http\Controllers\SuperAdmin\LeaveController;

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
Route::middleware(['multi_auth:web,admin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Role & Permission Management
    Route::prefix('role-and-permissions')
        ->name('roles.')
        ->middleware('permission:role.manage')
        ->group(function () {
            Route::get('/', [RoleAndPermissionController::class, 'index'])->name('index');
            Route::get('create', [RoleAndPermissionController::class, 'create'])->name('create');
            Route::post('/', [RoleAndPermissionController::class, 'store'])->name('store');
            Route::get('{id}/edit', [RoleAndPermissionController::class, 'edit'])->name('edit');
            Route::put('{id}', [RoleAndPermissionController::class, 'update'])->name('update');
            Route::delete('{id}', [RoleAndPermissionController::class, 'destroy'])->name('destroy');
            Route::get('{id}', [RoleAndPermissionController::class, 'show'])->name('show');
    });


    // User Management
    Route::prefix('users')
        ->name('users.')
        ->middleware('permission:user.manage')
        ->group(function () {
            Route::get('/list', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            Route::put('{id}', [UserController::class, 'update'])->name('update');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Booking Management
    Route::prefix('bookings')
        ->name('bookings.')
        ->middleware('permission:booking.manage')
        ->group(function () {
            Route::get('/',[BookingController::class,'index'])->name('newbooking'); 
            Route::post('/', [BookingController::class, 'store'])->name('newbooking.store');
            Route::delete('{id}', [BookingController::class, 'destroy'])->name('destroy');
            Route::put('{id}', [BookingController::class, 'update'])->name('update');
    });


    //Product 
     Route::prefix('products')
        ->name('products.')
        ->middleware('permission:product.manage')
        ->group(function () {
        
            Route::get('/', [ProductController::class, 'index'])->name('addProduct');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            

            // Update existing product
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');

            // Delete product
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        
    });

    //Product 
     Route::prefix('viewproduct')->name('viewproduct.')->group(function () {
        Route::get('/{categoryId?}', [ProductViewController::class, 'index'])->name('viewProduct');
    });

   // Categories 
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoriesController::class, 'index'])->name('Categories');
        });

        // Store
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('Store');
        });

        // Supplier 
        Route::prefix('supplier')->name('supplier.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('Supplier');
        });

        // Supplier 
        Route::prefix('unit')->name('unit.')->group(function () {
            Route::get('/', [UnitController::class, 'index'])->name('Unit');
        });

        // Supplier 
        Route::prefix('issue')->name('issue.')->group(function () {
            Route::get('/', [IssueController::class, 'index'])->name('Issue');
        });

        // Supplier 
        Route::prefix('issueview')->name('issueview.')->group(function () {
            Route::get('/', [IssueViewController::class, 'index'])->name('issueView');
        });

         // Purchase List 
        Route::prefix('purchaselist')->name('purchaselist.')->group(function () {
            Route::get('/', [PurchaseListController::class, 'index'])->name('purchaseList');
        });

         // Purchase List 
        Route::prefix('purchaseadd')->name('purchaseadd.')->group(function () {
            Route::get('/', [PurchaseAddController::class, 'index'])->name('purchaseAdd');
        });

         // ShowBooking List 
        Route::prefix('showbooking')->name('showbooking.')->group(function () {
            Route::get('/', [ShowBookingController::class, 'index'])->name('showBooking');
        });

         // ShowBooking List 
        Route::prefix('department')->name('department.')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('Department');
        });
        // IsCode List 
        Route::prefix('iscodes')->name('iscode.')->group(function () {
            Route::get('/', [IsCodesController::class, 'index'])->name('Iscode');
        });
        // Caqlibration List 
        Route::prefix('calibrations')->name('calibration.')->group(function () {
            Route::get('/', [CalibrationController::class, 'index'])->name('Calibration');
        });
        // Caqlibration List 
        Route::prefix('leaves')->name('leave.')->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('Leave');
        });
});


