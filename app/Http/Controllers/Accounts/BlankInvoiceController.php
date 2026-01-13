<?php
namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlankInvoiceRequest;

use App\Models\BlankInvoice;
use App\Models\{SiteSetting, PaymentSetting, User, Client, NewBooking};

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Services\{InvoicePdfService, NumberToWordsService};



class BlankInvoiceController extends Controller
{


    protected $invoicePdfService;
    protected $numberToWordsService;

    public function __construct(InvoicePdfService $invoicePdfService, NumberToWordsService $numberToWordsService)
    {

        $this->invoicePdfService = $invoicePdfService;
        $this->numberToWordsService = $numberToWordsService;

    }

   public function index(Request $request)
{
    $search = trim($request->search);

    $invoices = BlankInvoice::query()
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('reference_no', 'LIKE', "%{$search}%")
                  ->orWhere('marketing_person', 'LIKE', "%{$search}%")
                  ->orWhere('invoice_type', 'LIKE', "%{$search}%");
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view(
        'superadmin.accounts.invoiceList.index_blank',
        compact('invoices', 'search')
    );
}



    public function create()
    {
        $bankInfo = PaymentSetting::first();

        return view('superadmin.accounts.invoiceList.blank', compact('bankInfo'));
    }


    public function edit(BlankInvoice $blankInvoice)
    {
        $bankInfo = PaymentSetting::first();

        // Load items
        $blankInvoice->load('items');

        return view('superadmin.accounts.invoiceList.blank_edit', [
            'invoice' => $blankInvoice,
            'bankInfo' => $bankInfo
        ]);
    }


    public function store(BlankInvoiceRequest $request)
    {
        $data = json_decode($request->invoice_data, true);

        try {
            $invoice = DB::transaction(function () use ($data) {

                // Prepare invoice data
                $newData = [
                    'booking_id' => 0,
                    'client_name' => $data['booking_info']['client_name'] ?? null,
                    'marketing_person' => $data['booking_info']['marketing_person'] ?? null,
                    'invoice_no' => $data['booking_info']['invoice_no'] ?? null,
                    'reference_no' => $data['booking_info']['reference_no'] ?? null,
                    'invoice_date' => $data['booking_info']['invoice_date'] ?? null,
                    'letter_date' => $data['booking_info']['letter_date'] ?? null,
                    'name_of_work' => $data['booking_info']['name_of_work'] ?? null,
                    'bill_issue_to' => $data['booking_info']['bill_issue_to'] ?? null,
                    'client_gstin' => $data['booking_info']['client_gstin'] ?? null,
                    'address' => $data['booking_info']['address'] ?? null,
                    'total_amount' => (float) ($data['totals']['total_amount'] ?? 0),
                    'discount_percent' => (float) ($data['totals']['discount_percent'] ?? 0),
                    'after_discount' => (float) ($data['totals']['after_discount'] ?? 0),
                    'cgst_percent' => (float) ($data['totals']['cgst_percent'] ?? 0),
                    'cgst_amount' => (float) ($data['totals']['cgst_amount'] ?? 0),
                    'sgst_percent' => (float) ($data['totals']['sgst_percent'] ?? 0),
                    'sgst_amount' => (float) ($data['totals']['sgst_amount'] ?? 0),
                    'igst_percent' => (float) ($data['totals']['igst_percent'] ?? 0),
                    'igst_amount' => (float) ($data['totals']['igst_amount'] ?? 0),
                    'round_off' => (float) ($data['totals']['round_off'] ?? 0),
                    'payable_amount' => (float) ($data['totals']['payable_amount'] ?? 0),
                    'invoice_type' => $data['invoice_type'] ?? 'proforma_invoice',
                ];

                // Create invoice
                $invoice = BlankInvoice::create($newData);

                // Store items (skip empty ones)
                foreach ($data['items'] as $item) {
                    $isNotEmpty = !empty($item['description']) ||
                        !empty($item['job_order_no']) ||
                        (isset($item['qty']) && $item['qty'] != '' && $item['qty'] != 0) ||
                        (isset($item['rate']) && $item['rate'] != '' && $item['rate'] != 0) ||
                        (isset($item['amount']) && $item['amount'] != '' && $item['amount'] != 0);

                    if ($isNotEmpty) {
                        $invoice->items()->create([
                            'description' => $item['description'] ?? '',
                            'job_order_no' => $item['job_order_no'] ?? '',
                            'qty' => is_numeric($item['qty']) ? $item['qty'] : 0,
                            'rate' => is_numeric($item['rate']) ? $item['rate'] : 0,
                            'amount' => is_numeric($item['amount']) ? $item['amount'] : 0,
                        ]);
                    }
                }

                return $invoice;
            });

            // Redirect directly to PDF generator route
            return $this->generateBlankInoive($invoice);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }


    public function update(BlankInvoiceRequest $request, BlankInvoice $blankInvoice)
    {
        $data = json_decode($request->invoice_data, true);

        try {
            DB::transaction(function () use ($data, $blankInvoice) {

                // Update invoice main data
                $blankInvoice->update([
                    'client_name' => $data['booking_info']['client_name'] ?? null,
                    'marketing_person' => $data['booking_info']['marketing_person'] ?? null,
                    'invoice_no' => $data['booking_info']['invoice_no'] ?? null,
                    'reference_no' => $data['booking_info']['reference_no'] ?? null,
                    'invoice_date' => $data['booking_info']['invoice_date'] ?? null,
                    'letter_date' => $data['booking_info']['letter_date'] ?? null,
                    'name_of_work' => $data['booking_info']['name_of_work'] ?? null,
                    'bill_issue_to' => $data['booking_info']['bill_issue_to'] ?? null,
                    'client_gstin' => $data['booking_info']['client_gstin'] ?? null,
                    'address' => $data['booking_info']['address'] ?? null,

                    'total_amount' => (float) ($data['totals']['total_amount'] ?? 0),
                    'discount_percent' => (float) ($data['totals']['discount_percent'] ?? 0),
                    'after_discount' => (float) ($data['totals']['after_discount'] ?? 0),
                    'cgst_percent' => (float) ($data['totals']['cgst_percent'] ?? 0),
                    'cgst_amount' => (float) ($data['totals']['cgst_amount'] ?? 0),
                    'sgst_percent' => (float) ($data['totals']['sgst_percent'] ?? 0),
                    'sgst_amount' => (float) ($data['totals']['sgst_amount'] ?? 0),
                    'igst_percent' => (float) ($data['totals']['igst_percent'] ?? 0),
                    'igst_amount' => (float) ($data['totals']['igst_amount'] ?? 0),
                    'round_off' => (float) ($data['totals']['round_off'] ?? 0),
                    'payable_amount' => (float) ($data['totals']['payable_amount'] ?? 0),
                    'invoice_type' => $data['invoice_type'] ?? 'tax_invoice',
                ]);

                // Remove old items
                $blankInvoice->items()->delete();

                // Re-create items
                foreach ($data['items'] as $item) {
                    if (
                        !empty($item['description']) ||
                        !empty($item['job_order_no']) ||
                        ($item['qty'] ?? 0) > 0 ||
                        ($item['rate'] ?? 0) > 0
                    ) {
                        $blankInvoice->items()->create([
                            'description' => $item['description'] ?? '',
                            'job_order_no' => $item['job_order_no'] ?? '',
                            'qty' => (float) ($item['qty'] ?? 0),
                            'rate' => (float) ($item['rate'] ?? 0),
                            'amount' => (float) ($item['amount'] ?? 0),
                        ]);
                    }
                }
            });

            return $this->generateBlankInoive($blankInvoice);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function destroy(BlankInvoice $blankInvoice)
    {
        try {
            $blankInvoice->delete();
            return redirect()->back()->with('success', 'Invoice deleted successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function generateBlankInoive(BlankInvoice $blankInvoice)
    {
        $invoice = $blankInvoice->load('items');
        $companyName = SiteSetting::value('company_name');
        $WordAmout = $this->numberToWordsService->convert($invoice->payable_amount);

        $qrcode = $this->invoicePdfService->generateQrCode($invoice->payable_amount, "Invoice #{$invoice->invoice_no}");

        $SACCODE = "998346";

        $paymentSetting = PaymentSetting::latest()->first();
        $bankDetails = [
            'instructions' => $paymentSetting->instructions ?? 'abc',
            'bank_name' => $paymentSetting->bank_name ?? '',
            'account_no' => $paymentSetting->account_no ?? '',
            'branch_name' => $paymentSetting->branch ?? '',
            'branch_holder_name' => $paymentSetting->branch_holder_name ?? '',
            'ifsc_code' => $paymentSetting->ifsc_code ?? '',
            'pan_code' => $paymentSetting->pan_code ?? '',
            'pan_no' => $paymentSetting->pan_no ?? '',
            'gstin' => $paymentSetting->gstin ?? '',
            'upi' => $paymentSetting->upi ?? '',
        ];

        $pdf = Pdf::loadView('superadmin.accounts.invoiceList.blank_invoice_pdf', [
            'invoice' => $invoice,
            'WordAmout' => $WordAmout,
            'companyName' => $companyName,
            'SACCODE' => $SACCODE,
            'bankDetails' => $bankDetails,
            'qrcode' => $qrcode,
        ]);

        // Stream PDF in browser (opens in new tab if target="_blank")
        return $pdf->stream('blank-invoice-' . $invoice->id . '.pdf');
    }

    public function getClients(Request $request)
    {
        $term = trim($request->term ?? '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        return Client::where('name', 'LIKE', "{$term}%")
            ->orWhere('gstin', 'LIKE', "{$term}%")
            ->limit(20)
            ->get(['id', 'name', 'gstin', 'address'])
            ->map(fn($c) => [
                'id' => $c->id,
                'label' => $c->name . ' (' . $c->gstin . ')',
                'name' => $c->name,
                'gstin' => $c->gstin,
                'address' => $c->address
            ]);
    }

}
