<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\ISCodeController;
use App\Http\Controllers\OtpAuthController;   
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\UserBankAccountController; 

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

use App\Http\Controllers\ListController;

use App\Http\Controllers\SuperAdmin\IsCodesController;

use App\Http\Controllers\SuperAdmin\LeaveController;
use App\Http\Controllers\SuperAdmin\LeaveApproveController;
use App\Http\Controllers\SuperAdmin\EmployeeController;
use App\Http\Controllers\SuperAdmin\HR\AttendanceController;
use App\Http\Controllers\SuperAdmin\HR\PayrollController;
use App\Http\Controllers\SuperAdmin\HR\HolidayController;
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
use App\Http\Controllers\Accounts\MarketingQuotationController;
use App\Http\Controllers\Accounts\BlankInvoiceController;
use App\Http\Controllers\Accounts\PaymentSettingController;
use App\Http\Controllers\Accounts\MarketingExpenseController;
use App\Http\Controllers\Accounts\MarketingPersonLedger;
use App\Http\Controllers\Accounts\CashLetterController;
use App\Http\Controllers\Accounts\ChequeController;
use App\Http\Controllers\Accounts\BankController;
use App\Http\Controllers\Accounts\ChequeTemplateController;
use App\Http\Controllers\Accounts\VoucherController;
use App\Http\Controllers\Accounts\PurchaseBillController;

use App\Http\Controllers\Accounts\AccountsLetterController;
use App\Http\Controllers\Accounts\PayrollReviewController;

use App\Models\MeterReading;
use App\Http\Controllers\SuperAdmin\MeterReadingController;

use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientLedgerController;


use App\Http\Controllers\Transactions\CashPaymentController;
use App\Http\Controllers\Transactions\WithoutBillTransactionController;

use App\Http\Controllers\BankTransactionController;

use App\Http\Controllers\ReportEditorController;
use App\Http\Controllers\OnlyOfficeController;

use App\Http\Controllers\Email\EmailController;
use App\Http\Controllers\Accounts\ManualInvoicePaymentController;





use App\Http\Controllers\SuperAdmin\WhatsappSettingController;
use App\Http\Controllers\SuperAdmin\WhatsappChatController;

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



Route::middleware(['multi_auth:web,admin','ensure_user_active'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // Marketing Expenses
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/expenses', [MarketingExpenseController::class, 'index'])->name('expenses.view');
        Route::get('/expenses/approved', [MarketingExpenseController::class, 'approved'])->name('expenses.approved');
        Route::get('/expenses/rejected', [MarketingExpenseController::class, 'rejected'])->name('expenses.rejected');
        Route::get('/expenses/export/pdf', [MarketingExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');
        Route::get('/expenses/in-account', [MarketingExpenseController::class, 'inAccount'])->name('expenses.in_account');
        Route::get('/expenses/export/excel', [MarketingExpenseController::class, 'exportExcel'])->name('expenses.export.excel');

        // Quotations
        Route::get('/quotations', [MarketingQuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/export/pdf', [MarketingQuotationController::class, 'exportPdf'])->name('quotations.export.pdf');
        Route::get('/quotations/export/csv', [MarketingQuotationController::class, 'exportExcel'])->name('quotations.export.excel');
        Route::post('/expenses', [MarketingExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/persons', [MarketingExpenseController::class, 'persons'])->name('persons');
        Route::patch('/expenses/{expense}/approve', [MarketingExpenseController::class, 'approve'])->name('expenses.approve');
        Route::patch('/expenses/{expense}/reject', [MarketingExpenseController::class, 'reject'])->name('expenses.reject');
        Route::post('/expenses/bulk-approve', [MarketingExpenseController::class, 'bulkApprove'])->name('expenses.bulk_approve');
    });

    // Office Expenses (view only for now)
    Route::prefix('office')->name('office.')->group(function () {
        Route::get('/expenses', [MarketingExpenseController::class, 'office'])->name('expenses.view');
        Route::get('/expenses/export/pdf', [MarketingExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');
        Route::get('/expenses/export/excel', [MarketingExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
        Route::get('/persons', [MarketingExpenseController::class, 'officePersons'])->name('persons');
    });

    // Personal Expenses (mirrors Office UX/logic)
    Route::prefix('personal')->name('personal.')->group(function () {
        Route::get('/expenses', [MarketingExpenseController::class, 'personal'])->name('expenses.index');
        Route::get('/expenses/export/pdf', [MarketingExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');
        Route::get('/expenses/export/excel', [MarketingExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
        Route::get('/persons', [MarketingExpenseController::class, 'officePersons'])->name('persons');
        Route::post('/expenses/send-for-approval', [MarketingExpenseController::class, 'sendPersonalForApproval'])->name('expenses.send');
        Route::put('/expenses/{expense}', [MarketingExpenseController::class, 'updatePersonal'])->name(name: 'expenses.update');
        Route::delete('/expenses/{expense}', [MarketingExpenseController::class, 'destroyPersonal'])->name('expenses.destroy');
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/payload', [DashboardController::class, 'payload'])->name('dashboard.payload');
    Route::get('dashboard/invoice-payment-chart', [DashboardController::class, 'invoicePaymentChart'])
        ->name('dashboard.invoicePaymentChart');    Route::get('dashboard/accounts-invoices-chart', [DashboardController::class, 'accountsInvoicesChart'])
        ->name('dashboard.accountsInvoicesChart');

    Route::get('dashboard/gst-overview', [DashboardController::class, 'gstOverview'])->name('dashboard.gstOverview');

    
    
    // Role & Permission Management
    Route::prefix('role-and-permissions')
        ->name('roles.')
        ->group(function () {
            Route::get('/', [RoleAndPermissionController::class, 'index'])->name('index');
            Route::get('create', [RoleAndPermissionController::class, 'create'])->name('create');
            Route::post('/', [RoleAndPermissionController::class, 'store'])->name('store');
            Route::get('{role}/edit', [RoleAndPermissionController::class, 'edit'])->name('edit');
            Route::put('{role}', [RoleAndPermissionController::class, 'update'])->name('update');
            Route::put('bulk/toggle-login-restriction', [RoleAndPermissionController::class, 'bulkToggleLoginRestriction'])->name('bulk-toggle-login-restriction');
            Route::put('{role}/toggle-login-restriction', [RoleAndPermissionController::class, 'toggleLoginRestriction'])->name('toggle-login-restriction');
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

            // Toggle active status (activate / deactivate)
            Route::put('{user}/toggle', [UserController::class, 'toggleStatus'])->name('toggleStatus');

            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('{id}/send-notification', [UserController::class, 'sendNotification'])->name('sendNotification');

        });
    

    // WhatsApp Chats
    Route::group(['prefix' => 'whatsapp', 'as' => 'whatsapp.'], function() {
        Route::get('chats', [WhatsappChatController::class, 'index'])->name('chats.index');
        Route::get('chats/{phoneNumber}', [WhatsappChatController::class, 'show'])->name('chat.show');
    });

    // Booking Management
    Route::prefix('bookings')
        ->name('bookings.')
        ->group(function () {

            Route::get('/', [BookingController::class, 'create'])->name('newbooking');
            Route::post('/', [BookingController::class, 'store'])->name('newbooking.store');
            Route::delete('{new_booking}', [BookingController::class, 'destroy'])->name('destroy');

            Route::put('{new_booking}', [BookingController::class, 'update'])->name('update');


            Route::get('{new_booking}/edit', [BookingController::class, 'edit'])->name('edit');

            Route::get('/get-job-orders', [BookingController::class, 'getJobOrders'])->name('get.job.orders');

            Route::get('/labAnalyst', [BookingController::class, 'getLabAnalyst'])->name('get.labAnalyst');
            Route::get('/marketingCodes', [BookingController::class, 'getMarketingPerson'])->name('get.marketingCodes');

            Route::get('/users/role/{roleSlug}', [UserController::class, 'getUsersByRole'])->name('get.userByRole');

            Route::get('/bookingByLetter', [ShowBookingByLetterController::class, 'index'])->name('bookingByLetter.index');
            Route::get('/marketing/bookingByLetter', [ShowBookingByLetterController::class, 'marketingIndex'])->name('bookingByLetter.marketing');
            Route::delete('/bookingByLetter/{bookingItem}', [ShowBookingByLetterController::class, 'destroy'])->name('bookingByLetter.destroy');
            Route::get('/bookingByLetter/export/pdf', [ShowBookingByLetterController::class, 'exportPdf'])->name('bookingByLetter.exportPdf');
            Route::get('/bookingByLetter/export/excel', [ShowBookingByLetterController::class, 'exportExcel'])->name('bookingByLetter.exportExcel');
            Route::post('/bookingByLetter/export/queue', [ShowBookingByLetterController::class, 'queueExport'])->name('bookingByLetter.queueExport');
            Route::get('/bookingByLetter/export/status/{token}', [ShowBookingByLetterController::class, 'exportStatus'])->name('bookingByLetter.exportStatus');
            Route::get('/bookingByLetter/export/download/{token}', [ShowBookingByLetterController::class, 'downloadExport'])->name('bookingByLetter.downloadExport');

            Route::get('/superadmin/bookings/autocomplete', [BookingController::class, 'getAutocomplete'])->name('autocomplete');

            Route::post('/superadmin/bookings/item/save', [BookingController::class, 'saveItem'])->name('item.save');

            Route::get('/get-ref-no', [BookingController::class, 'getReferenceNo'])->name('get.ref_no');

            Route::get('/booking/{bookingId}/cards', [BookingController::class, 'showBookingCards'])->name('cards.all');
            Route::get('/booking/{bookingId}/cards/{itemId}', [BookingController::class, 'showBookingCards'])->name('cards.single');
            
            Route::patch('/bookings/{id}/payment-option',[BookingController::class, 'changePaymentOption'])->name('change.payment.option');
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
    Route::resource('employees', EmployeeController::class);
    Route::delete('employees/{employee}/documents/{index}', [EmployeeController::class, 'destroyDocument'])->name('employees.documents.destroy')->middleware('permission:employees.edit');
    Route::resource('calibrations', CalibrationController::class);
    Route::resource('iscodes', ISCodeController::class);


  


        Route::resource('blank-invoices', BlankInvoiceController::class);


        Route::get('/blank-invoice/get-clients', [BlankInvoiceController::class, 'getClients'])->name('get.clients');

        Route::get('bookingInvoiceStatuses/export/pdf', [GenerateInvoiceStatusController::class, 'exportPdf'])->name('bookingInvoiceStatuses.exportPdf');
        Route::get('bookingInvoiceStatuses/export/excel', [GenerateInvoiceStatusController::class, 'exportExcel'])->name('bookingInvoiceStatuses.exportExcel');
        
        Route::resource('bookingInvoiceStatuses', GenerateInvoiceStatusController::class);
                

        Route::post('bookingInvoiceStatuses/generate-invoice/{booking}', [GenerateInvoiceStatusController::class, 'generateInvoice'])
            ->middleware(['permission:invoice.create'])->name('bookingInvoiceStatuses.generateInvoice');


        Route::get('booking-invoice-statuses/bulk-generate', [GenerateInvoiceStatusController::class, 'bulkGenerate'])
            ->middleware(['permission:invoice.create'])->name('bookingInvoiceStatuses.bulkGenerate');


        Route::post('/booking-invoice-statuses/store-bulk', [GenerateInvoiceStatusController::class, 'storeBulk'])
            ->middleware(['permission:invoice.create'])->name('bookingInvoiceStatuses.storeBulk');

        
        
        // ===================== EDIT GENERATE INVOICE =====================
        // Route::get('bookingInvoiceStatuses/edit-generate-invoice/{id}', [GenerateInvoiceStatusController::class, 'editGenerateInvoice'])->name('bookingInvoiceStatuses.editGenerateInvoice');

        Route::get('invoices/export/pdf', [InvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');
        Route::get('invoices/export/excel', [InvoiceController::class, 'exportExcel'])->name('invoices.export.excel');
        
        Route::resource('invoices', InvoiceController::class);
          


        Route::PUT('invoices/generate-invoice/{invoices}', [InvoiceController::class, 'generateInvoice'])
            ->name('invoices.generateInvoice');

        Route::put('invoices/bulk-update/{invoice}', [InvoiceController::class, 'updateBulk'])
               ->middleware(['permission:invoice.edit'])->name('invoices.bulkUpdate');

        Route::post('/gstin/upload', [InvoiceController::class, 'uploadFile'])
               ->middleware(['permission:invoice.edit'])->name('gstin.upload'); 

        Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
              ->middleware(['permission:invoice.edit'])->name('invoices.cancel');


        // Meter Reading - DB-backed (meter_readings table)
        Route::prefix('meter-reading')->name('meter-reading.')->group(function () {
            Route::get('/', [MeterReadingController::class, 'index'])->name('index');
            Route::post('/upload', [MeterReadingController::class, 'upload'])->name('upload');
            Route::get('/export/pdf', [MeterReadingController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [MeterReadingController::class, 'exportExcel'])->name('export.excel');
        });

        Route::resource('quotations', QuotationController::class);
        Route::get('quotations-export/pdf', [QuotationController::class, 'exportPdf'])->name('quotations.export.pdf');
        Route::get('quotations-export/csv', [QuotationController::class, 'exportExcel'])->name('quotations.export.excel');
        Route::GET('quotations/generate-quotations/{quotations}', [QuotationController::class, 'generateQuotations'])->name('quotations.generateQuotations');


        Route::get('payment-settings/call-function/{id}', [PaymentSettingController::class, 'callFunction'])->name('payment-settings.callFunction');

        
        
        Route::resource('marketing-person-ledger', MarketingPersonLedger::class)->only(['index', 'show']);


        // AJAX routes
        Route::get('/{user_code}/bookings', [MarketingPersonLedger::class, 'fetchBookings'])->name('marketing.bookings');
        Route::get('/{user_code}/without-bill', [MarketingPersonLedger::class, 'fetchWithoutBillBookings'])->name('marketing.withoutBill');
        Route::get('/{user_code}/invoices', [MarketingPersonLedger::class, 'fetchInvoices'])->name('marketing.invoices');
        Route::get('/{user_code}/transactions', [MarketingPersonLedger::class, 'fetchInvoicesTransactions'])->name('marketing.transactions');
        Route::get('/{user_code}/cash-transactions', [MarketingPersonLedger::class, 'fetchCashTransaction'])->name('marketing.cashTransactions');
        Route::get('/{user_code}/cash-all-transactions', [MarketingPersonLedger::class, 'fetchClientAllBookings'])->name('marketing.cashAllTransactions');
        Route::get('/{user_code}/all-clients', [MarketingPersonLedger::class, 'fetchGroupedBookings'])->name('marketing.allClients');


        Route::resource('clients', ClientController::class)
                ->only(['index', 'store', 'destroy']);
           

        Route::put('clients/{client}', [ClientController::class, 'update'])->middleware(['permission:client_assigned.edit'])->name('clients.update');
        
        Route::post('clients/{client}/assign-booking', [ClientController::class, 'assignBooking'])->middleware(['permission:client_assigned.edit'])->name('clients.assignBooking');
        Route::post('/clients/assign-bulk-bookings', [ClientController::class, 'assignBulkBookings'])->middleware(['permission:client_assigned.edit'])->name('clients.assignBulkBookings');
        Route::patch('/bookings/{booking}/unassign-client',[ClientController::class, 'unassignBooking'])->middleware(['permission:client_assigned.edit'])->name('bookings.unassignClient');

        Route::get('client-ledger', [ClientLedgerController::class, 'index'])->middleware(['permission:client_assigned.view'])->name('client-ledger.index');
        Route::get('client-ledger/{id}', [ClientLedgerController::class, 'show'])->middleware(['permission:client_assigned.view'])->name('client-ledger.show');




        // AJAX routes
        Route::get('client/{id}/bookings', [ClientLedgerController::class, 'fetchBookings'])->name('client.bookings');
        Route::get('client/{id}/without-bill', [ClientLedgerController::class, 'fetchWithoutBillBookings'])->name('client.withoutBill');
        Route::get('client/{id}/all-transaction', [ClientLedgerController::class, 'fetchAllBankTransaction'])->name('client.bankTransaction');

        Route::get('client/{id}/invoices', [ClientLedgerController::class, 'fetchInvoices'])->name('client.invoices');

        Route::get('client/{id}/transactions', [ClientLedgerController::class, 'fetchInvoicesTransactions'])->name('client.transactions');
        Route::get('client/{id}/cash-transactions', [ClientLedgerController::class, 'fetchCashTransaction'])->name('client.cashTransactions');
        Route::get('client/{id}/cash-all-transactions', [ClientLedgerController::class, 'fetchClientAllBookings'])->name('client.cashAllTransactions');
        Route::get('client/{id}/cash-transactions', [ClientLedgerController::class, 'fetchCashTransaction'])->name('client.cashTransactions');


        // Cheque Alignment Setup
        Route::get('banks/create', [BankController::class, 'create'])->name('banks.create');
        Route::post('banks', [BankController::class, 'store'])->name('banks.store');
        Route::delete('banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
        Route::get('cheque-templates/{bank}', [ChequeTemplateController::class, 'editor'])->name('cheque-templates.editor');
        Route::post('cheque-templates/{bank}', [ChequeTemplateController::class, 'store'])->name('cheque-templates.store');
        Route::get('cheque-templates/{bank}/fetch', [ChequeTemplateController::class, 'fetch'])->name('cheque-templates.fetch');


        Route::get('cash-payments/create/{id}', [CashPaymentController::class, 'create'])
               ->middleware(['permission:invoice_payment.create'])->name('cashPayments.create');


        Route::post('cash-payments/store', [CashPaymentController::class, 'store'])
              ->middleware(['permission:invoice_payment.create'])->name('cashPayments.store');

        Route::get('cash-payments/repay/{id}', [CashPaymentController::class, 'repay'])
              ->middleware(['permission:invoice_payment.edit'])->name('cashPayments.repay'); 

        Route::get('cash-payments/export/pdf', [CashPaymentController::class, 'exportPdf'])->name('cashPayments.exportPdf');
        Route::get('cash-payments/export/excel', [CashPaymentController::class, 'exportExcel'])->name('cashPayments.exportExcel');
        
        Route::get('cash-payments/', [CashPaymentController::class, 'index'])
               ->middleware(['permission:invoice_payment.view'])->name('cashPayments.index');

        Route::post('superadmin/cash-repay-payment/{invoice}', [CashPaymentController::class, 'storeRepay'])
               ->middleware(['permission:invoice_payment.edit'])->name('cashPayments.storeRepay');


        // Cash Transaction
        Route::post('/withoutbilltransactions/store', [WithoutBillTransactionController::class, 'store'])->name('withoutbilltransactions.store');
        Route::post('withoutbilltransactions/storeRepay/{id}', [WithoutBillTransactionController::class, 'storeRepay'])->name('withoutbilltransactions.storeRepay');

        Route::get('cash-letter/index', [WithoutBillTransactionController::class, 'index'])->name('cashLetterTransactions.index');
        Route::get('cash-letter/export/pdf', [WithoutBillTransactionController::class, 'exportPdf'])->name('cashLetterTransactions.exportPdf');
        Route::get('cash-letter/export/excel', [WithoutBillTransactionController::class, 'exportExcel'])->name('cashLetterTransactions.exportExcel');
        Route::patch('/without-bill-payments/{id}/settle', [WithoutBillTransactionController::class, 'settle'])->name('cashLetterPaymet.settle');


        // Exports for Account Bookings (Letters)
        Route::get('accountBookingsLetters/export/pdf', [AccountsLetterController::class, 'exportPdf'])->name('accountBookingsLetters.exportPdf');
        Route::get('accountBookingsLetters/export/excel', [AccountsLetterController::class, 'exportExcel'])->name('accountBookingsLetters.exportExcel');

        Route::resource('accountBookingsLetters', AccountsLetterController::class);
        Route::get('accounts/payroll', [PayrollReviewController::class, 'index'])->name('accounts.payroll.index');
        Route::get('accounts/payroll/{cycle}/download-bank', [PayrollReviewController::class, 'downloadBankCsv'])->name('accounts.payroll.download-bank');


        // Cheques
        Route::get('cheques', [ChequeController::class, 'index'])->middleware(['permission:cheque.view'])->name('cheques.index');
        Route::post('cheques', [ChequeController::class, 'store'])->middleware(['permission:cheque.create'])->name('cheques.store');
        Route::post('cheques/{cheque}/receive', [ChequeController::class, 'receive'])->middleware(['permission:cheque.create'])->name('cheques.receive');
        Route::post('cheques/receive', [ChequeController::class, 'storeReceived'])->middleware(['permission:cheque.create'])->name('cheques.storeReceived');
        Route::post('cheques/{cheque}/toggle-deposit', [ChequeController::class, 'toggleDeposit'])->middleware(['permission:cheque.edit'])->name('cheques.toggleDeposit');
        Route::get('cheques/{cheque}/edit', [ChequeController::class, 'edit'])->middleware(['permission:cheque.edit'])->name('cheques.edit');
        Route::put('cheques/{cheque}', [ChequeController::class, 'update'])->middleware(['permission:cheque.edit'])->name('cheques.update');
        Route::delete('cheques/{cheque}', [ChequeController::class, 'destroy'])->middleware(['permission:cheque.delete'])->name('cheques.destroy');
        Route::get('cheques/{cheque}/print-preview', [ChequeController::class, 'printPreview'])->middleware(['permission:cheque.view'])->name('cheques.printPreview');


        Route::get('cash-letter/payments', [CashLetterController::class, 'showMultiple'])->name('cashLetter.payments.showMultiple');

        // Vouchers - Generate & Approve
        Route::prefix('vouchers')->name('vouchers.')->group(function () {
            Route::get('create', [VoucherController::class, 'create'])->name('create');
            Route::post('/', [VoucherController::class, 'store'])->name('store');
            Route::get('/', [VoucherController::class, 'index'])->name('index');
            Route::get('approve', [VoucherController::class, 'approveIndex'])->name('approve');
            Route::get('export/pdf', [VoucherController::class, 'exportPdf'])->name('export.pdf');
            Route::get('export/excel', [VoucherController::class, 'exportExcel'])->name('export.excel');
            Route::patch('{voucher}/approve', [VoucherController::class, 'approve'])->name('approve.action');
            Route::patch('{voucher}/reject', [VoucherController::class, 'reject'])->name('reject.action');
            // Update payment status (paid/unpaid)
            Route::patch('{voucher}/payment', [VoucherController::class, 'payment'])->name('payment');
            Route::get('{voucher}/generate', [VoucherController::class, 'generate'])->name('generate');
            Route::get('{voucher}/edit', [VoucherController::class, 'edit'])->name('edit');
            Route::put('{voucher}', [VoucherController::class, 'update'])->name('update');
            Route::delete('{voucher}', [VoucherController::class, 'destroy'])->name('destroy');
        });

        // Purchase Bills - simple index and upload (stores files to storage/app/public/purchase_bills)
        Route::prefix('purchase-bills')->name('purchase_bills.')->group(function () {
            // Export to PDF (printable HTML view)
            Route::get('export/pdf', function (\Illuminate\Http\Request $request) {
                $users = \App\Models\User::orderBy('name')->get()->keyBy('id');
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files('purchase_bills');
                $files = array_values(array_filter($files, function ($path) {
                    return preg_match('/\.(pdf|jpg|jpeg|png)$/i', $path);
                }));

                $collection = collect($files)->map(function ($path) use ($users) {
                    $metaPath = $path . '.json';
                    $meta = [];
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($metaPath)) {
                        try {
                            $meta = json_decode(\Illuminate\Support\Facades\Storage::disk('public')->get($metaPath), true) ?: [];
                        } catch (\Throwable $e) {
                            $meta = [];
                        }
                    }

                    $userName = null;
                    if (!empty($meta['user_id']) && $users->has($meta['user_id'])) {
                        $userName = $users->get($meta['user_id'])->name;
                    }

                    return [
                        'name' => basename($path),
                        'url' => asset('storage/' . $path),
                        'uploaded_at' => date('d M Y H:i', \Illuminate\Support\Facades\Storage::disk('public')->lastModified($path)),
                        'amount' => $meta['amount'] ?? null,
                        'bill_date' => $meta['bill_date'] ?? null,
                        'description' => $meta['description'] ?? null,
                        'user_name' => $userName,
                    ];
                })->values()->all();

                // allow filtering similarly to index (search + month/year)
                if ($request->filled('search')) {
                    $q = strtolower($request->get('search'));
                    $collection = array_values(array_filter($collection, function ($item) use ($q) {
                        return strpos(strtolower($item['name']), $q) !== false || strpos(strtolower($item['user_name'] ?? ''), $q) !== false;
                    }));
                }

                if ($request->filled('month') || $request->filled('year') || $request->filled('financial_year')) {
                    $m = $request->get('month');
                    $y = $request->get('year');
                    $fy = $request->get('financial_year');
                    $collection = array_values(array_filter($collection, function ($item) use ($m, $y, $fy) {
                        // If financial year is provided, use it (April 1 - Mar 31)
                        if (!empty($fy)) {
                            $startYear = (int) preg_replace('/[^0-9].*/', '', $fy);
                            if ($startYear <= 0)
                                return false;
                            $startTs = strtotime($startYear . '-04-01');
                            $endTs = strtotime(($startYear + 1) . '-03-31 23:59:59');
                            $d = empty($item['bill_date']) ? null : strtotime($item['bill_date']);
                            if (!$d)
                                return false;
                            return $d >= $startTs && $d <= $endTs;
                        }

                        if (empty($item['bill_date']))
                            return false;
                        $ts = strtotime($item['bill_date']);
                        if ($ts === false)
                            return false;
                        if ($m && (int) date('n', $ts) !== (int) $m)
                            return false;
                        if ($y && (int) date('Y', $ts) !== (int) $y)
                            return false;
                        return true;
                    }));
                }

                return view('superadmin.accounts.purchase_bills.print', ['purchaseBills' => $collection]);
            })->name('export.pdf');

            // Export to Excel (CSV)
            Route::get('export/excel', function (\Illuminate\Http\Request $request) {
                $users = \App\Models\User::orderBy('name')->get()->keyBy('id');
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files('purchase_bills');
                $files = array_values(array_filter($files, function ($path) {
                    return preg_match('/\.(pdf|jpg|jpeg|png)$/i', $path);
                }));

                $collection = collect($files)->map(function ($path) use ($users) {
                    $metaPath = $path . '.json';
                    $meta = [];
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($metaPath)) {
                        try {
                            $meta = json_decode(\Illuminate\Support\Facades\Storage::disk('public')->get($metaPath), true) ?: [];
                        } catch (\Throwable $e) {
                            $meta = [];
                        }
                    }

                    $userName = null;
                    if (!empty($meta['user_id']) && $users->has($meta['user_id'])) {
                        $userName = $users->get($meta['user_id'])->name;
                    }

                    return [
                        'name' => basename($path),
                        'url' => asset('storage/' . $path),
                        'uploaded_at' => date('d M Y H:i', \Illuminate\Support\Facades\Storage::disk('public')->lastModified($path)),
                        'amount' => $meta['amount'] ?? null,
                        'bill_date' => $meta['bill_date'] ?? null,
                        'description' => $meta['description'] ?? null,
                        'user_name' => $userName,
                    ];
                })->values()->all();

                // allow filtering (search + month/year) before CSV generation
                if ($request->filled('search')) {
                    $q = strtolower($request->get('search'));
                    $collection = array_values(array_filter($collection, function ($item) use ($q) {
                        return strpos(strtolower($item['name']), $q) !== false || strpos(strtolower($item['user_name'] ?? ''), $q) !== false;
                    }));
                }

                if ($request->filled('month') || $request->filled('year') || $request->filled('financial_year')) {
                    $m = $request->get('month');
                    $y = $request->get('year');
                    $fy = $request->get('financial_year');
                    $collection = array_values(array_filter($collection, function ($item) use ($m, $y, $fy) {
                        if (!empty($fy)) {
                            $startYear = (int) preg_replace('/[^0-9].*/', '', $fy);
                            if ($startYear <= 0)
                                return false;
                            $startTs = strtotime($startYear . '-04-01');
                            $endTs = strtotime(($startYear + 1) . '-03-31 23:59:59');
                            $d = empty($item['bill_date']) ? null : strtotime($item['bill_date']);
                            if (!$d)
                                return false;
                            return $d >= $startTs && $d <= $endTs;
                        }

                        if (empty($item['bill_date']))
                            return false;
                        $ts = strtotime($item['bill_date']);
                        if ($ts === false)
                            return false;
                        if ($m && (int) date('n', $ts) !== (int) $m)
                            return false;
                        if ($y && (int) date('Y', $ts) !== (int) $y)
                            return false;
                        return true;
                    }));
                }

                // CSV generation
                $filename = 'purchase_bills_' . date('Ymd_His') . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function () use ($collection) {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['User', 'Amount', 'Bill Date', 'Description', 'Uploaded At', 'File URL']);
                    foreach ($collection as $row) {
                        fputcsv($out, [
                            $row['user_name'] ?? '',
                            isset($row['amount']) ? number_format((float) $row['amount'], 2) : '',
                            $row['bill_date'] ?? '',
                            $row['description'] ?? '',
                            $row['uploaded_at'] ?? '',
                            $row['url'] ?? '',
                        ]);
                    }
                    fclose($out);
                };

                return response()->streamDownload($callback, $filename, $headers);
            })->name('export.excel');


            Route::get('/', function (\Illuminate\Http\Request $request) {
                // Fetch users to populate select and to resolve user names for metadata
                $users = \App\Models\User::orderBy('name')->get()->keyBy('id');

                $files = \Illuminate\Support\Facades\Storage::disk('public')->files('purchase_bills');

                // Only include allowed file types (exclude .json sidecars)
                $files = array_values(array_filter($files, function ($path) {
                    return preg_match('/\.(pdf|jpg|jpeg|png)$/i', $path);
                }));

                $collection = collect($files)->map(function ($path) use ($users) {
                    $metaPath = $path . '.json';
                    $meta = [];
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($metaPath)) {
                        try {
                            $meta = json_decode(\Illuminate\Support\Facades\Storage::disk('public')->get($metaPath), true) ?: [];
                        } catch (\Throwable $e) {
                            $meta = [];
                        }
                    }

                    $userName = null;
                    if (!empty($meta['user_id']) && $users->has($meta['user_id'])) {
                        $userName = $users->get($meta['user_id'])->name;
                    }

                    return [
                        'path' => $path,
                        'name' => basename($path),
                        'url' => asset('storage/' . $path),
                        'uploaded_at' => date('d M Y H:i', \Illuminate\Support\Facades\Storage::disk('public')->lastModified($path)),
                        'amount' => $meta['amount'] ?? null,
                        'bill_date' => $meta['bill_date'] ?? null,
                        'description' => $meta['description'] ?? null,
                        'user_id' => $meta['user_id'] ?? null,
                        'user_name' => $userName,
                    ];
                })->values()->all();

                // Simple search by file name if provided
                if ($request->filled('search')) {
                    $q = strtolower($request->get('search'));
                    $collection = array_values(array_filter($collection, function ($item) use ($q) {
                        return strpos(strtolower($item['name']), $q) !== false || strpos(strtolower($item['user_name'] ?? ''), $q) !== false;
                    }));
                }

                // Filter by month/year or financial_year if provided (applies to bill_date metadata)
                if ($request->filled('month') || $request->filled('year') || $request->filled('financial_year')) {
                    $m = $request->get('month');
                    $y = $request->get('year');
                    $fy = $request->get('financial_year');
                    $collection = array_values(array_filter($collection, function ($item) use ($m, $y, $fy) {
                        if (!empty($fy)) {
                            $startYear = (int) preg_replace('/[^0-9].*/', '', $fy);
                            if ($startYear <= 0)
                                return false;
                            $startTs = strtotime($startYear . '-04-01');
                            $endTs = strtotime(($startYear + 1) . '-03-31 23:59:59');
                            $d = empty($item['bill_date']) ? null : strtotime($item['bill_date']);
                            if (!$d)
                                return false;
                            return $d >= $startTs && $d <= $endTs;
                        }

                        if (empty($item['bill_date']))
                            return false;
                        $ts = strtotime($item['bill_date']);
                        if ($ts === false)
                            return false;
                        if ($m && (int) date('n', $ts) !== (int) $m)
                            return false;
                        if ($y && (int) date('Y', $ts) !== (int) $y)
                            return false;
                        return true;
                    }));
                }

                return view('superadmin.accounts.purchase_bills.index', ['purchaseBills' => $collection, 'users' => \App\Models\User::orderBy('name')->get()]);
            })->name('index');

            Route::post('/', function (\Illuminate\Http\Request $request) {
                $request->validate([
                    'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                    'user_id' => 'required|exists:users,id',
                    'bill_date' => 'required|date',
                    'description' => 'nullable|string',
                    'amount' => 'nullable|numeric',
                ]);

                $file = $request->file('attachment');
                $path = $file->store('purchase_bills', 'public');

                // Save metadata sidecar JSON next to file
                $meta = [
                    'original_name' => $file->getClientOriginalName(),
                    'amount' => $request->input('amount'),
                    'bill_date' => $request->input('bill_date'),
                    'description' => $request->input('description'),
                    'user_id' => $request->input('user_id'),
                    'uploaded_at' => now()->toDateTimeString(),
                ];

                $metaPath = $path . '.json';
                \Illuminate\Support\Facades\Storage::disk('public')->put($metaPath, json_encode($meta));

                return redirect()->route('superadmin.purchase_bills.index')->with('success', 'Purchase bill uploaded successfully');
            })->name('store');

            // Update metadata for a purchase bill (file path is base64 encoded)
            Route::put('{file}', function ($file, \Illuminate\Http\Request $request) {
                $path = base64_decode($file);
                if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return redirect()->route('superadmin.purchase_bills.index')->with('error', 'File not found');
                }

                $request->validate([
                    'user_id' => 'required|exists:users,id',
                    'bill_date' => 'required|date',
                    'description' => 'nullable|string',
                    'amount' => 'nullable|numeric',
                ]);

                $metaPath = $path . '.json';
                $meta = [];
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($metaPath)) {
                    $meta = json_decode(\Illuminate\Support\Facades\Storage::disk('public')->get($metaPath), true) ?: [];
                }

                $meta = array_merge($meta, [
                    'user_id' => $request->input('user_id'),
                    'bill_date' => $request->input('bill_date'),
                    'description' => $request->input('description'),
                    'amount' => $request->input('amount'),
                    'updated_at' => now()->toDateTimeString(),
                ]);

                \Illuminate\Support\Facades\Storage::disk('public')->put($metaPath, json_encode($meta));

                return redirect()->route('superadmin.purchase_bills.index')->with('success', 'Purchase bill updated');
            })->where('file', '.*')->name('update');

            // Delete file and its metadata (file path is base64 encoded)
            Route::delete('{file}', function ($file) {
                $path = base64_decode($file);
                if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return redirect()->route('superadmin.purchase_bills.index')->with('error', 'File not found');
                }

                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                $metaPath = $path . '.json';
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($metaPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($metaPath);
                }

                return redirect()->route('superadmin.purchase_bills.index')->with('success', 'Purchase bill deleted');
            })->where('file', '.*')->name('destroy');
        });


        // Bank Transactions

        Route::get('/bank/upload', [BankTransactionController::class, 'index'])->name('bank.upload');
        Route::post('/bank/upload', [BankTransactionController::class, 'upload'])->name('bank.upload.post');
        Route::get('/bank/export/pdf', [BankTransactionController::class, 'exportPdf'])->name('bank.export.pdf');
        Route::get('/bank/export/excel', [BankTransactionController::class, 'exportExcel'])->name('bank.export.excel');

        Route::post('/bank/note/{id}', [BankTransactionController::class, 'addNote'])->name('bank.addNote');
        Route::patch('/bank/soft-delete/{id}', [BankTransactionController::class, 'softDeleteOrUndo'])->name('bank.softDeleteOrUndo');


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
        Route::get('/marketing/{department?}', [ShowBookingController::class, 'marketing'])->name('marketing.showBooking');
        Route::get('/{department?}', [ShowBookingController::class, 'index'])->name('showBooking');
        Route::get('/export/pdf/{department?}', [ShowBookingController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/export/excel/{department?}', [ShowBookingController::class, 'exportExcel'])->name('exportExcel');
    });

    // ShowBooking List
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/', [DeptController::class, 'index'])->name('Department');
    });

    // Caqlibration List / Leaves
    Route::prefix('leaves')->name('leave.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('Leave');
    });
    // Leave Management
    Route::prefix('leaves')->name('leave.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('Leave');
        Route::get('/export/pdf', [LeaveController::class, 'exportPdf'])->name('export.pdf');
        // View for approving/reviewing leaves
        Route::get('/approve', [LeaveApproveController::class, 'index'])->name('approve.view');
        Route::get('/export/excel', [LeaveController::class, 'exportExcel'])->name('export.excel');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
        Route::get('/users/{user}/summary', [LeaveController::class, 'userLeaveSummary'])->name('user.summary');
        Route::put('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');
        Route::post('/calculate-days', [LeaveController::class, 'calculateDays'])->name('calculate-days');
    });

    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('/payroll/cycle', [PayrollController::class, 'store'])->name('payroll.store');
        Route::post('/payroll/cycle/{cycle}/refresh', [PayrollController::class, 'refresh'])->name('payroll.refresh');
        Route::patch('/payroll/cycle/{cycle}', [PayrollController::class, 'updateStatus'])->name('payroll.update-status');
        Route::patch('/payroll/entries/{entry}', [PayrollController::class, 'updateEntry'])->name('payroll.entries.update');
        Route::post('/payroll/entries/bulk-status', [PayrollController::class, 'bulkUpdateStatus'])->name('payroll.entries.bulk-status');
        Route::get('/payroll/{cycle}/download-bank', [PayrollController::class, 'downloadBankCsv'])->name('payroll.download-bank');
        Route::get('/payroll/{cycle}/download', [PayrollController::class, 'download'])->name('payroll.download');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.store-manual');
        Route::post('/attendance/import-biometric', [AttendanceController::class, 'importBiometric'])->name('attendance.import-biometric');
        // Holidays resource (index/store/update/destroy)
        Route::resource('holidays', HolidayController::class)->only(['index','store','update','destroy']);
    });

    // Backwards-compatible alias: some views reference superadmin.holidays.index
    // Define a top-level named route to avoid RouteNotFound exceptions.
    Route::get('holidays', [HolidayController::class, 'index'])->name('holidays.index');

    // Lab Analysts - reports dropdown and viewer
    Route::prefix('lab-analysts')->name('labanalysts.')->group(function () {
        Route::get('/', [LabAnalystController::class, 'index'])
            ->middleware('permission:lab-analysts.view')->name('index');

        Route::get('/view', [LabAnalystController::class, 'view'])
            ->middleware('permission:lab-analysts.view')->name('view');

        Route::get('/render', [LabAnalystController::class, 'render'])
            ->middleware('permission:lab-analysts.view')->name('render');

        Route::get('/preview', [LabAnalystController::class, 'preview'])
            ->middleware('permission:lab-analysts.view')->name('preview');

        Route::get('/pdf', [LabAnalystController::class, 'pdf'])
            ->middleware('permission:lab-analysts.view')->name('pdf');

        Route::post('/save', [LabAnalystController::class, 'save'])
            ->middleware('permission:lab-analysts.create')->name('save');
    });



    // Reporting
    Route::prefix('reporting')->name('reporting.')->group(function () {
        Route::get('/received', [ReportingController::class, 'received'])->name('received');
        Route::get('/pendings', [ReportingController::class, 'pendings'])->name('pendings');
        Route::get('/pendings/export-pdf', [ReportingController::class, 'pendingsExportPdf'])->name('pendings.exportPdf');
        Route::get('/pendings/export-excel', [ReportingController::class, 'pendingsExportExcel'])->name('pendings.exportExcel');
        Route::get('/dispatch', [ReportingController::class, 'dispatch'])->name('dispatch');
        // Dispatched-only listing and quick exports
        Route::get('/dispatched', [ReportingController::class, 'dispatched'])->name('dispatched');
        Route::get('/dispatched/export/pdf', [ReportingController::class, 'dispatchedExportPdf'])->name('dispatched.export.pdf');
        Route::get('/dispatched/export/excel', [ReportingController::class, 'dispatchedExportExcel'])->name('dispatched.export.excel');
        Route::post('/handover/{item}', [ReportingController::class, 'handoverOne'])->name('handoverOne');
        Route::get('/handover/{item}/history', [ReportingController::class, 'handoverHistory'])->name('handover.history');
        Route::post('/dispatch/{item}', [ReportingController::class, 'dispatchOne'])->name('dispatchOne');
        Route::post('/dispatch-bulk', [ReportingController::class, 'dispatchBulk'])->name('dispatchBulk');
        Route::post('/receive/{item}', [ReportingController::class, 'receiveOne'])->name('receive');
        Route::post('/receive-all', [ReportingController::class, 'receiveAll'])->name('receiveAll');
        Route::post('/account-receive/{item}', [ReportingController::class, 'accountReceiveOne'])->name('accountReceiveOne');
        Route::post('/account-receive-bulk', [ReportingController::class, 'accountReceiveBulk'])->name('accountReceiveBulk');
        Route::post('/submit-all', [ReportingController::class, 'submitAll'])->name('submitAll');
        Route::get('/generate', [ReportingController::class, 'generate'])->name('generate');
        Route::get('/view-by-letter', [ReportingController::class, 'viewByLetter'])->name('viewByLetter');
        Route::get('/view-by-job-order', [ReportingController::class, 'viewByJobOrder'])->name('viewByJobOrder');

        // Analyst Activities
        Route::get('/analyst-activities', [\App\Http\Controllers\SuperAdmin\AnalystActivityController::class, 'index'])->name('analyst-activities.index');
        Route::post('/analyst-activities/assign', [\App\Http\Controllers\SuperAdmin\AnalystActivityController::class, 'assignJob'])->name('analyst-activities.assign');
        Route::post('/analyst-activities/transfer', [\App\Http\Controllers\SuperAdmin\AnalystActivityController::class, 'transferJob'])->name('analyst-activities.transfer');


        Route::patch('/header/{booking}', [ReportingController::class, 'updateHeader'])->name('header.update');

        Route::post('/reporting/assign/{item}', [ReportingController::class, 'assignReport'])->name('assignReport');

    });

    // Report Format Upload & Listing
    Route::get('/report-formats', [\App\Http\Controllers\SuperAdmin\ReportFormatController::class, 'index'])->name('reporting.report-formats.index');
    Route::post('/report-formats', [\App\Http\Controllers\SuperAdmin\ReportFormatController::class, 'store'])->name('reporting.report-formats.store');
    Route::get('/report-formats/{reportFormat}', [\App\Http\Controllers\SuperAdmin\ReportFormatController::class, 'show'])->name('report-formats.show');
    Route::get('/report-formats/{reportFormat}/content', [\App\Http\Controllers\SuperAdmin\ReportFormatContentController::class, 'edit'])->name('report-formats.content.edit');
    Route::put('/report-formats/{reportFormat}/content', [\App\Http\Controllers\SuperAdmin\ReportFormatContentController::class, 'update'])->name('report-formats.content.update');
    Route::get('/report-formats/{reportFormat}/export-pdf', [\App\Http\Controllers\SuperAdmin\ReportFormatContentController::class, 'exportPdf'])->name('report-formats.content.exportPdf');


    // bank details
    Route::resource('payment-settings', PaymentSettingController::class)
        ->middleware('permission:bank_detail.view')->only(['index', 'store', 'update']);
});



Route::middleware(['multi_auth:web,admin'])->group(function () {
    // list of clients
    Route::get('/clients/list', [ListController::class, 'clients'])->name('api.clients.list');
    Route::get('/invoices/list', [ListController::class, 'invoices'])->name('api.invoices.list');
    Route::get('/refnos/list', [ListController::class, 'refNos'])->name('api.refnos.list');

    Route::get('/test/list', [ListController::class, 'view'])->name('test.list');



    //report editor
    Route::get('/editor', [ReportEditorController::class, 'index'])->name('editor.index')->middleware('permission:report-format.create');
    Route::post('/editor/save', [ReportEditorController::class, 'save'])->name('editor.save')->middleware('permission:report-format.create');
    Route::delete('/editor/delete/{id}', [ReportEditorController::class, 'destroy'])->name('editor.delete')->middleware('permission:report-format.delete');

    // report genration
    Route::post('generateReportPDF/editor/', [ReportEditorController::class, 'generateReportPDF'])
        ->middleware('permission:report-generate.create')->name('generateReportPDF.generatePdf');

    Route::post('generateReportPDF/editor/28days', [ReportEditorController::class, 'generatePdf28Days'])
        ->middleware('permission:report-generate.create')->name('generateReportPDF.generatePdf28Days');

    Route::post('generateReportPDF/word/', [ReportEditorController::class, 'generateReportWord'])
        ->middleware('permission:report-generate.create')->name('generateReportPDF.generateReportWord');

    Route::get('generateReportPDF/generate/{item}/{type?}', [ReportEditorController::class, 'generate'])
        ->middleware('permission:report-generate.view')->name('generateReportPDF.generate');


    Route::get('generateReportPDF/edit/{pivotId}/{type?}', [ReportEditorController::class, 'editReport'])
        ->middleware('permission:report-generate.edit')->name('generateReportPDF.editReport');

    Route::get('/view-pdf/{filename}', [ReportEditorController::class, 'viewPdf'])->name('viewPdf');


    Route::get('/booking/{bookingId}/download-merged-pdf', [ReportEditorController::class, 'downloadMergedBookingPDF'])->name('booking.downloadMergedPDF');
    Route::get('/report/varification/{no}', [ReportEditorController::class, 'varify'])->name('varification.view');

    Route::post('/reports/live-preview', [ReportEditorController::class, 'livePreview'])
        ->name('reports.livePreview');
    Route::post('/download-qr', [ReportEditorController::class, 'downloadQR'])->name('download.qr');



    Route::get('/document/new', [OnlyOfficeController::class, 'newDocument'])->name('onlyoffice.new');
    Route::post('/document/save', [OnlyOfficeController::class, 'save'])->name('onlyoffice.save');



    // email route
    Route::get('/email/{id?}', [EmailController::class, 'index'])->name('email.index');
    Route::post('/email/store', [EmailController::class, 'store'])->name('email.store');
    Route::get('/emails/fetch/{id}', [EmailController::class, 'fetchInbox'])->name('emails.fetch');
    Route::get('/emails/{id}/reply/{uid}/{type?}', [EmailController::class, 'reply'])->name('emails.reply');
    Route::get('/ajax-switch/{id}', [EmailController::class, 'ajaxSwitch'])->name('email.ajaxSwitch');
    Route::post('/emails/reply', [EmailController::class, 'sendReply'])->name('emails.sendReply');
    Route::post('/emails/send', [EmailController::class, 'send'])->name('emails.send');

    // Route::get('/emails/sendEmail/{id}', [EmailController::class, 'getSentEmails'])->name('emails.allSendEmail');
// Route::get('/emails/sentEmail/{id}/{uid}/{type?}', [EmailController::class, 'getSentEmailByUid'])->name('emails.sent.show');

    Route::get('/email/sentEmail/{id?}', [EmailController::class, 'sentIndex'])->name('email.allSentEmail');

    Route::post('/emails/{id}/reply', [EmailController::class, 'replyOnEmail'])->name('emails.replyOnEmail');

    // delete email from list route
    Route::delete('/emails/{id}', [EmailController::class, 'destroy'])->name('emails.destroy');

    Route::get('bookingInvoiceStatuses/edit-generate-invoice/{id}', [GenerateInvoiceStatusController::class, 'editGenerateInvoice'])
            ->middleware(['permission:invoice.edit'])->name('bookingInvoiceStatuses.editGenerateInvoice');


    Route::get('invoices/{invoice}/download', [GenerateInvoiceStatusController::class, 'downloadInvoice'])
            ->middleware(['permission:invoice.view'])->name('invoices.download'); 

    Route::get('/invoices/missing', [InvoiceController::class, 'missingInvoices'])->name('invoices.missing');


    Route::prefix('manual-invoice-payment')
        ->name('manual-invoice-payment.')
        ->group(function () {

            Route::post('/', [ManualInvoicePaymentController::class, 'store'])->name('store');
            Route::put('{id}', [ManualInvoicePaymentController::class, 'update'])->name('update');
            Route::delete('{id}', [ManualInvoicePaymentController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/export-pdf', [PurchaseBillController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/export-excel', [PurchaseBillController::class, 'exportExcel'])->name('exportExcel');
        Route::get('/', [PurchaseBillController::class, 'index'])->name('index');
        Route::get('/upload', [PurchaseBillController::class, 'create'])->name('create');
        Route::post('/', [PurchaseBillController::class, 'store'])->name('store');
        Route::put('/{id}', [PurchaseBillController::class, 'update'])->name('update');
        Route::delete('/{id}', [PurchaseBillController::class, 'destroy'])->name('destroy');
    });


    Route::get(
        '/job-order/{jobOrderNumber}/items',
        [JobOrderController::class, 'index']
    )->name('job-order.items');

    Route::get('/job-order/search', function (Request $request) {
        return redirect()->route(
            'job-order.items',
            ['jobOrderNumber' => $request->jobOrderNumber]
        );
    })->name('job-order.search'); 


    Route::get('/bank-accounts/manage',[UserBankAccountController::class,'manage'])->name('bankAccounts.manage');

    Route::post('/bank-accounts/store',[UserBankAccountController::class,'store'])->name('bankAccounts.store');

    Route::put('/bank-accounts/update/{id}',[UserBankAccountController::class,'update'])->name('bankAccounts.update');

    Route::delete('/bank-accounts/delete/{id}',[UserBankAccountController::class,'destroy'])->name('bankAccounts.destroy');

    /* Zoom Meetings Routes */
    Route::middleware(['multi_auth:web,admin','ensure_user_active'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::group(['prefix' => 'zoom-meetings', 'as' => 'zoom-meetings.'], function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'store'])->name('store');
            Route::get('/{id}/sync-recording', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'syncRecording'])->name('syncRecording');
            Route::get('/{id}/join', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'join'])->name('join');
            Route::delete('/{id}', [\App\Http\Controllers\SuperAdmin\ZoomMeetingController::class, 'destroy'])->name('destroy');
        });
    });

});