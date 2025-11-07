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
use App\Http\Controllers\ListController;

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
use App\Http\Controllers\Accounts\BlankInvoiceController;
use App\Http\Controllers\Accounts\PaymentSettingController;
use App\Http\Controllers\Accounts\MarketingExpenseController;
use App\Http\Controllers\Accounts\MarketingPersonLedger;
use App\Http\Controllers\Accounts\CashLetterController;
use App\Http\Controllers\Accounts\ChequeController;
use App\Http\Controllers\Accounts\BankController;
use App\Http\Controllers\Accounts\ChequeTemplateController;

use App\Http\Controllers\Accounts\AccountsLetterController;

use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientLedgerController;


use App\Http\Controllers\Transactions\CashPaymentController;
use App\Http\Controllers\Transactions\WithoutBillTransactionController;

use App\Http\Controllers\BankTransactionController;

use App\Http\Controllers\ReportEditorController;
use App\Http\Controllers\OnlyOfficeController;

use App\Http\Controllers\Email\EmailController;



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

    // Marketing Expenses
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/expenses', [MarketingExpenseController::class, 'index'])->name('expenses.view');
        Route::get('/expenses/approved', [MarketingExpenseController::class, 'approved'])->name('expenses.approved');
        Route::get('/expenses/rejected', [MarketingExpenseController::class, 'rejected'])->name('expenses.rejected');
        Route::get('/expenses/export/pdf', [MarketingExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');
        Route::get('/expenses/export/excel', [MarketingExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
        Route::post('/expenses', [MarketingExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/persons', [MarketingExpenseController::class, 'persons'])->name('persons');
        Route::patch('/expenses/{expense}/approve', [MarketingExpenseController::class, 'approve'])->name('expenses.approve');
        Route::patch('/expenses/{expense}/reject', [MarketingExpenseController::class, 'reject'])->name('expenses.reject');
    });

    // Office Expenses (view only for now)
    Route::prefix('office')->name('office.')->group(function () {
        Route::get('/expenses', [MarketingExpenseController::class, 'office'])->name('expenses.view');
        Route::get('/expenses/export/pdf', [MarketingExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');
        Route::get('/expenses/export/excel', [MarketingExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
        Route::get('/persons', [MarketingExpenseController::class, 'officePersons'])->name('persons');
    });

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
            Route::get('/bookingByLetter/export/pdf', [ShowBookingByLetterController::class, 'exportPdf'])->name('bookingByLetter.exportPdf');
            Route::get('/bookingByLetter/export/excel', [ShowBookingByLetterController::class, 'exportExcel'])->name('bookingByLetter.exportExcel');

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


    Route::middleware(['permission:account.edit'])->group(function () {

        Route::resource('blank-invoices', BlankInvoiceController::class);

        Route::resource('bookingInvoiceStatuses', GenerateInvoiceStatusController::class);

        Route::post('bookingInvoiceStatuses/generate-invoice/{booking}', [GenerateInvoiceStatusController::class, 'generateInvoice'])
              ->name('bookingInvoiceStatuses.generateInvoice');

        Route::get('booking-invoice-statuses/bulk-generate', [GenerateInvoiceStatusController::class, 'bulkGenerate'])
                ->name('bookingInvoiceStatuses.bulkGenerate');

        Route::post('/booking-invoice-statuses/store-bulk', [GenerateInvoiceStatusController::class, 'storeBulk'])
             ->name('bookingInvoiceStatuses.storeBulk');

        Route::resource('invoices', InvoiceController::class);
        Route::PUT('invoices/generate-invoice/{invoices}', [InvoiceController::class, 'generateInvoice'])
              ->name('invoices.generateInvoice');

        Route::post('/gstin/upload', [InvoiceController::class, 'uploadFile'])->name('gstin.upload');
        Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');



        Route::resource('quotations', QuotationController::class);
        Route::GET('quotations/generate-quotations/{quotations}', [QuotationController::class, 'generateQuotations'])
              ->name('quotations.generateQuotations');


        Route::get('payment-settings/call-function/{id}', [PaymentSettingController::class, 'callFunction'])->name('payment-settings.callFunction');


        Route::resource('marketing-person-ledger', MarketingPersonLedger::class)->only(['index','show']);


        // AJAX routes
        Route::get('/{user_code}/bookings', [MarketingPersonLedger::class, 'fetchBookings'])->name('marketing.bookings');
        Route::get('/{user_code}/without-bill', [MarketingPersonLedger::class, 'fetchWithoutBillBookings'])->name('marketing.withoutBill');
        Route::get('/{user_code}/invoices', [MarketingPersonLedger::class, 'fetchInvoices'])->name('marketing.invoices');
        Route::get('/{user_code}/transactions', [MarketingPersonLedger::class, 'fetchInvoicesTransactions'])->name('marketing.transactions');
        Route::get('/{user_code}/cash-transactions', [MarketingPersonLedger::class, 'fetchCashTransaction'])->name('marketing.cashTransactions');
        Route::get('/{user_code}/cash-all-transactions', [MarketingPersonLedger::class, 'fetchClientAllBookings'])->name('marketing.cashAllTransactions');


        Route::resource('clients', ClientController::class)->only(['index','store','destroy']);
        Route::post('clients/{client}/assign-booking', [ClientController::class, 'assignBooking'])->name('clients.assignBooking');

        Route::get('client-ledger', [ClientLedgerController::class, 'index'])->name('client-ledger.index');
        Route::get('client-ledger/{id}', [ClientLedgerController::class, 'show'])->name('client-ledger.show');

        // AJAX routes
        Route::get('client/{id}/bookings', [ClientLedgerController::class, 'fetchBookings'])->name('client.bookings');
        Route::get('client/{id}/without-bill', [ClientLedgerController::class, 'fetchWithoutBillBookings'])->name('client.withoutBill');
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


        Route::get('cash-payments/create/{id}', [CashPaymentController::class, 'create'])->name('cashPayments.create');
        Route::post('cash-payments/store', [CashPaymentController::class, 'store'])->name('cashPayments.store');
        Route::get('cash-payments/repay/{id}', [CashPaymentController::class, 'repay'])->name('cashPayments.repay');
        Route::get('cash-payments/', [CashPaymentController::class, 'index'])->name('cashPayments.index');

        Route::post('superadmin/cash-repay-payment/{invoice}', [CashPaymentController::class, 'storeRepay'])->name('cashPayments.storeRepay');


        // Cash Transaction
        Route::post('/withoutbilltransactions/store', [WithoutBillTransactionController::class, 'store'])->name('withoutbilltransactions.store');
        Route::post('withoutbilltransactions/storeRepay/{id}', [WithoutBillTransactionController::class, 'storeRepay'])->name('withoutbilltransactions.storeRepay');

        Route::get('cash-letter/index', [WithoutBillTransactionController::class, 'index'])->name('cashLetterTransactions.index');
        Route::patch('/without-bill-payments/{id}/settle', [WithoutBillTransactionController::class, 'settle'])->name('cashLetterPaymet.settle');


        Route::resource('accountBookingsLetters', AccountsLetterController::class);

        // Cheques
        Route::get('cheques', [ChequeController::class, 'index'])->name('cheques.index');
        Route::post('cheques', [ChequeController::class, 'store'])->name('cheques.store');
        Route::post('cheques/{cheque}/receive', [ChequeController::class, 'receive'])->name('cheques.receive');
        Route::post('cheques/receive', [ChequeController::class, 'storeReceived'])->name('cheques.storeReceived');
        Route::post('cheques/{cheque}/toggle-deposit', [ChequeController::class, 'toggleDeposit'])->name('cheques.toggleDeposit');
        Route::get('cheques/{cheque}/edit', [ChequeController::class, 'edit'])->name('cheques.edit');
        Route::put('cheques/{cheque}', [ChequeController::class, 'update'])->name('cheques.update');
        Route::delete('cheques/{cheque}', [ChequeController::class, 'destroy'])->name('cheques.destroy');
        Route::get('cheques/{cheque}/print-preview', [ChequeController::class, 'printPreview'])->name('cheques.printPreview');

        Route::get('cash-letter/payments', [CashLetterController::class, 'showMultiple'])->name('cashLetter.payments.showMultiple');


        // Bank Transactions

        Route::get('/bank/upload', [BankTransactionController::class, 'index'])->name('bank.upload');
        Route::post('/bank/upload', [BankTransactionController::class, 'upload'])->name('bank.upload.post');

        Route::post('/bank/note/{id}', [BankTransactionController::class, 'addNote'])->name('bank.addNote');
        Route::patch('/bank/soft-delete/{id}', [BankTransactionController::class, 'softDeleteOrUndo'])->name('bank.softDeleteOrUndo');

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
                Route::get('/{department?}', [ShowBookingController::class, 'index'])->name('showBooking');
                Route::get('/export/pdf/{department?}', [ShowBookingController::class, 'exportPdf'])->name('exportPdf');
                Route::get('/export/excel/{department?}', [ShowBookingController::class, 'exportExcel'])->name('exportExcel');
            });

            // ShowBooking List
            Route::prefix('department')->name('department.')->group(function () {
                Route::get('/', [DeptController::class, 'index'])->name('Department');
            });
        // Leave Management
        Route::prefix('leaves')->name('leave.')->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('Leave');
            Route::post('/', [LeaveController::class, 'store'])->name('store');
            Route::put('/{leave}', [LeaveController::class, 'update'])->name('update');
            Route::put('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
            Route::delete('/{leave}', [LeaveController::class, 'destroy'])->name('destroy');
            Route::post('/calculate-days', [LeaveController::class, 'calculateDays'])->name('calculate-days');
        });

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
            Route::prefix('reporting')->middleware('permission:reporting.edit')->name('reporting.')->group(function () {
                Route::get('/received', [ReportingController::class, 'received'])->name('received');
                Route::get('/pendings', [ReportingController::class, 'pendings'])->name('pendings');
                Route::get('/pendings/export-pdf', [ReportingController::class, 'pendingsExportPdf'])->name('pendings.exportPdf');
                Route::get('/pendings/export-excel', [ReportingController::class, 'pendingsExportExcel'])->name('pendings.exportExcel');
                Route::get('/dispatch', [ReportingController::class, 'dispatch'])->name('dispatch');
                Route::post('/dispatch/{item}', [ReportingController::class, 'dispatchOne'])->name('dispatchOne');
                Route::post('/dispatch-bulk', [ReportingController::class, 'dispatchBulk'])->name('dispatchBulk');
                Route::post('/receive/{item}', [ReportingController::class, 'receiveOne'])->name('receive');
                Route::post('/receive-all', [ReportingController::class, 'receiveAll'])->name('receiveAll');
                Route::post('/account-receive/{item}', [ReportingController::class, 'accountReceiveOne'])->name('accountReceiveOne');
                Route::post('/account-receive-bulk', [ReportingController::class, 'accountReceiveBulk'])->name('accountReceiveBulk');
                Route::post('/submit-all', [ReportingController::class, 'submitAll'])->name('submitAll');
                Route::get('/generate', [ReportingController::class, 'generate'])->name('generate');

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
                ->middleware('permission:bank-details.view')->only(['index','store', 'update']);
    });


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
