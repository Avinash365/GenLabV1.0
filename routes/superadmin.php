<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\ISCodeController;

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\LoginController;
use App\Http\Controllers\SuperAdmin\RoleAndPermissionController;
use App\Http\Controllers\SuperAdmin\UserController;
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
use App\Http\Controllers\SuperAdmin\ShowBookingByLetterController; 

use App\Http\Controllers\SuperAdmin\IsCodesController;

use App\Http\Controllers\SuperAdmin\LeaveController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductStockEntryController;
use App\Http\Controllers\Department\DepartmentController as DeptController;
use App\Http\Controllers\Attachments\ProfileController; 
use App\Http\Controllers\Attachments\ApprovalController; 
use App\Http\Controllers\Attachments\ImportantLetterController; 
use App\Http\Controllers\Attachments\DocumentController; 
use App\Http\Controllers\SuperAdmin\LabAnalystController;
use App\Http\Controllers\SuperAdmin\ReportingController;

use App\Http\Controllers\Accounts\GenerateInvoiceStatusController;
use App\Http\Controllers\Accounts\InvoiceController;
use App\Http\Controllers\Accounts\QuotationController;



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
        ->group(function () {
            Route::get('/', [RoleAndPermissionController::class, 'index'])->name('index');
            Route::get('create', [RoleAndPermissionController::class, 'create'])->name('create');
            Route::post('/', [RoleAndPermissionController::class, 'store'])->name('store');
            Route::get('{role}/edit', [RoleAndPermissionController::class, 'edit'])->name('edit');
            Route::put('{role}', [RoleAndPermissionController::class, 'update'])->name('update');
            Route::delete('{role}', [RoleAndPermissionController::class, 'destroy'])->name('destroy');
            Route::get('{role}', [RoleAndPermissionController::class, 'show'])->name('show');
    });


    // User Management
    Route::prefix('users')
        ->name('users.')
        ->group(function () {
            Route::get('/list', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            
            Route::post('/', [UserController::class, 'store'])->name('store');

            Route::put('{user}', [UserController::class, 'update'])->name('update');
            Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('updatePermissions'); 

            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');

    });

    // Booking Management
    Route::prefix('bookings')
        ->name('bookings.')
        ->group(function () {
            
            Route::get('/',[BookingController::class,'create'])->name('newbooking'); 
            Route::post('/', [BookingController::class, 'store'])->name('newbooking.store');
            Route::delete('{new_booking}', [BookingController::class, 'destroy'])->name('destroy');

            Route::put('{new_booking}', [BookingController::class, 'update'])->name('update');
            
            
            Route::get('{new_booking}/edit', [BookingController::class, 'edit'])->name('edit');

            Route::get('/get-job-orders', [BookingController::class, 'getJobOrders'])->name('get.job.orders');
            
            Route::get('/labAnalyst', [BookingController::class, 'getLabAnalyst'])->name('get.labAnalyst');
            Route::get('/marketingCodes', [BookingController::class, 'getMarketingPerson'])->name('get.marketingCodes');
            
            Route::get('/users/role/{roleSlug}', [UserController::class, 'getUsersByRole'])->name('get.userByRole');

            Route::get('/bookingByLetter', [ShowBookingByLetterController::class, 'index'])->name('bookingByLetter.index'); 
            Route::delete('/bookingByLetter/{bookingItem}', [ShowBookingByLetterController::class, 'destroy'])->name('bookingByLetter.destroy');

            Route::get('/superadmin/bookings/autocomplete', [BookingController::class, 'getAutocomplete'])->name('autocomplete');

            Route::post('/superadmin/bookings/item/save', [BookingController::class, 'saveItem'])->name('item.save');

            Route::get('/get-ref-no', [BookingController::class, 'getReferenceNo'])->name('get.ref_no');
    });
                                    


    //Product 
     Route::prefix('products')
        ->name('products.')
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
        Route::resource('categories', ProductCategoryController::class);
        Route::resource('productStockEntry', ProductStockEntryController::class);
        Route::resource('departments', DeptController::class);
        Route::resource('profiles', ProfileController::class);
        Route::resource('approvals', ApprovalController::class);
        Route::resource('importantLetter', ImportantLetterController::class);
        Route::resource('documents', DocumentController::class);
        Route::resource('calibrations', CalibrationController::class);
        Route::resource('iscodes', ISCodeController::class);
        Route::resource('bookingInvoiceStatuses', GenerateInvoiceStatusController::class);
        
        Route::post('bookingInvoiceStatuses/generate-invoice/{booking}', [GenerateInvoiceStatusController::class, 'generateInvoice'])
              ->name('bookingInvoiceStatuses.generateInvoice');
        
        Route::resource('invoices', InvoiceController::class);
        Route::PUT('invoices/generate-invoice/{invoices}', [InvoiceController::class, 'generateInvoice'])
              ->name('invoices.generateInvoice');

        Route::resource('quotations', QuotationController::class);
        Route::GET('quotations/generate-quotations/{quotations}', [QuotationController::class, 'generateQuotations'])
              ->name('quotations.generateQuotations');

        Route::post('/gstin/upload', [InvoiceController::class, 'uploadFile'])->name('gstin.upload');
        

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
            Route::get('/{department?}', [ShowBookingController::class, 'index'])->name('showBooking');
        });

         // ShowBooking List 
        Route::prefix('department')->name('department.')->group(function () {
            Route::get('/', [DeptController::class, 'index'])->name('Department');
        });
        
        // Caqlibration List / Leaves
        Route::prefix('leaves')->name('leave.')->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('Leave');
        });

        // Lab Analysts - reports dropdown and viewer
        Route::prefix('lab-analysts')->name('labanalysts.')->group(function () {
            Route::get('/', [LabAnalystController::class, 'index'])->name('index');
            Route::get('/view', [LabAnalystController::class, 'view'])->name('view');
            Route::get('/render', [LabAnalystController::class, 'render'])->name('render');
            Route::get('/preview', [LabAnalystController::class, 'preview'])->name('preview');
            Route::get('/pdf', [LabAnalystController::class, 'pdf'])->name('pdf');
            Route::post('/save', [LabAnalystController::class, 'save'])->name('save');
        });

        // Reporting
        Route::prefix('reporting')->name('reporting.')->group(function () {
            Route::get('/received', [ReportingController::class, 'received'])->name('received');
            Route::post('/receive/{item}', [ReportingController::class, 'receiveOne'])->name('receive');
            Route::post('/receive-all', [ReportingController::class, 'receiveAll'])->name('receiveAll');
            Route::post('/submit-all', [ReportingController::class, 'submitAll'])->name('submitAll');
        });
});


