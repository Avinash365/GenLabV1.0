<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, InvoiceTds, InvoiceTransaction, Client, User};

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePaymentRequest;
use Illuminate\Http\Request; 
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

class CashPaymentController extends Controller
{
    private function getFilteredQuery(Request $request)
    {
        $query = InvoiceTransaction::with(['invoice', 'client', 'marketingPerson']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('invoice', fn($sub) => $sub->where('invoice_no', 'like', "%$search%"))
                  ->orWhereHas('client', fn($sub) => $sub->where('name', 'like', "%$search%"))
                  ->orWhereHas('marketingPerson', fn($sub) => $sub->where('name', 'like', "%$search%"));
            });
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Marketing Person filter
        if ($request->filled('marketing_id')) {
            $query->where('marketing_person_id', $request->marketing_id);
        }

        // Month filter
        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->month);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        // Get distinct years for filter dropdown
        $years = InvoiceTransaction::selectRaw('YEAR(transaction_date) as year')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

        // Get transactions ordered by date
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10);

        // Pass clients and marketing persons for filter dropdowns
        $clients = Client::all();
        $marketingPersons = User::whereHas('role', function ($q) {
                            $q->where('slug', 'marketing_person');
                        })->get(['id', 'user_code', 'name']);

        return view('superadmin.accounts.transactions.index', compact('transactions', 'clients', 'marketingPersons', 'years'));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        $pdf = Pdf::loadView('superadmin.accounts.transactions.pdf_export', compact('transactions'));
        return $pdf->download('invoice_transactions.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        // Build human-friendly filters array similar to PDF export
        $filters = [];
        $qs = $request->except(['_token','page']);
        foreach($qs as $k => $v){
            if(is_null($v) || $v === '') continue;
            switch($k){
                case 'search':
                    $filters['Search'] = $v; break;
                case 'client_id':
                    $client = Client::find($v);
                    $filters['Client'] = $client ? $client->name : $v; break;
                case 'marketing_id':
                case 'marketing_person_code':
                    $user = null;
                    if(is_numeric($v)) $user = User::find($v);
                    if(!$user) $user = User::where('user_code', $v)->first(['name','user_code']);
                    $filters['Marketing Person'] = $user ? ($user->name . ($user->user_code ? ' (' . $user->user_code . ')' : '')) : $v;
                    break;
                case 'payment_mode':
                    $filters['Payment Mode'] = ucfirst($v); break;
                case 'from_date':
                    $filters['From Date'] = $v; break;
                case 'to_date':
                    $filters['To Date'] = $v; break;
                case 'month':
                    $m = is_numeric($v) ? (int) $v : null;
                    if($m){
                        try{ $filters['Month'] = \Carbon\Carbon::create()->month($m)->format('F'); }catch(\Exception $e){ $filters['Month'] = $v; }
                    } else { $filters['Month'] = $v; }
                    break;
                case 'year':
                    $filters['Year'] = $v; break;
                case 'per_page':
                case 'perPage':
                    $filters['Rows Per Page'] = $v; break;
                default:
                    $filters[ucwords(str_replace(['_','-'], ' ', $k))] = $v;
            }
        }

        return Excel::download(new TransactionsExport($transactions, $filters), 'invoice_transactions.xlsx');
    }



    /**
     * Show form to create a new payment
     */
    public function create($id)
    {
        try {
            $invoice = Invoice::with(['relatedBooking', 'relatedBooking.marketingPerson'])->findOrFail($id);

            switch ($invoice->status) {
                case 1:
                    return redirect()->route('superadmin.invoices.index')
                                    ->with('info', 'This invoice is already fully paid.');
                case 2:
                    return redirect()->route('superadmin.invoices.index')
                                    ->with('info', 'This invoice is canceled.');
                case 3:
                    return redirect()->route('superadmin.invoices.index')
                                    ->with('info', 'This invoice is partially paid. You cannot make payment from here.');
                case 4:
                    return redirect()->route('superadmin.invoices.index')
                                    ->with('info', 'This invoice is in a pending/other state and cannot be processed here.');
            }

            $totalAmount = $invoice->total_job_order_amount * (1 - $invoice->discount_percent / 100);

            return view('superadmin.cashPayments.create', compact('invoice', 'totalAmount'));
        } catch (\Exception $e) {
            Log::error("Error fetching invoice for create payment: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Unable to load payment form.'])->withInput();
        }
    }

    /**
     * Store a new payment (full payment form)
     */
    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            
            $createdBy = null; 
            if (Auth::guard('web')->check()) {
                $createdBy = Auth::guard('web')->id();   
            }
            
            // TDS entry
            $tds = InvoiceTds::firstOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'client_id'           => $validated['client_id'],
                    'marketing_person_id' => $validated['marketing_person_id'],
                    'tds_percentage'      => $validated['tds_percentage'],
                    'amount_after_tds'    => $validated['amount_after_tds'],
                    'tds_amount'          => $validated['subtotal_amount'] * $validated['tds_percentage'] / 100,
                    'created_by'          => $createdBy,
                ]
            );

            // Payment transaction
            InvoiceTransaction::create([
                'invoice_id'          => $invoice->id,
                'client_id'           => $validated['client_id'],
                'marketing_person_id' => $validated['marketing_person_id'],
                'payment_mode'        => $validated['payment_mode'],
                'transaction_date'    => $validated['transaction_date'],
                'amount_received'     => $validated['amount_received'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes'               => $validated['notes'] ?? null,
                'created_by'          => $createdBy,
            ]);

            // Update invoice status
            $totalReceived = InvoiceTransaction::where('invoice_id', $invoice->id)->sum('amount_received');
            $amountAfterTds = $tds->amount_after_tds;
           

            if ($totalReceived == 0) {
                $invoice->status = 0; // Pending
            } elseif ($totalReceived < $amountAfterTds) {
                $invoice->status = 3; // Partial
            } elseif ($totalReceived >= $amountAfterTds) {
                $invoice->status = 1; // Paid
            }

            $invoice->save();

            DB::commit();

            return redirect()->route('superadmin.invoices.index', ['type' => $invoice->type])
                             ->with('success', 'Payment saved successfully and invoice status updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing payment: {$e->getMessage()}", [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Something went wrong while saving the payment.'])->withInput();
        }
    }

    /**
     * Show repay (partial payment) form
     */
    public function repay($id)
    {   
        try {
            $invoice = Invoice::with(['client', 'marketingPerson', 'relatedBooking', 'tdsTransaction'])->findOrFail($id);

            $totalReceived = InvoiceTransaction::where('invoice_id', $invoice->id)->sum('amount_received');
            $tdsTransaction = $invoice->tdsTransaction;
            $tdsPercentage = $tdsTransaction->tds_percentage ?? 0;
            $amountAfterTds = $tdsTransaction->amount_after_tds ?? 0;
            $totalDueAmount = $amountAfterTds - $totalReceived;

            $payment_info = (object)[
                'invoice_id'               => $invoice->id,
                'client_id'                => $invoice->client_id,
                'marketing_person_id'      => $invoice->marketingPerson->user_code,
                'client'                   => $invoice->client->name ?? 'N/A',
                'marketing_person'         => $invoice->marketingPerson->name ?? 'N/A',
                'letter_no'                => $invoice->relatedBooking->reference_no ?? 'N/A',
                'invoice_no'               => $invoice->invoice_no,
                'invoice_date'             => $invoice->created_at?->format('Y-m-d'),
                'total_invoice_amount'     => $invoice->total_amount,
                'tds_percentage'           => $tdsPercentage,
                'payable_amount_after_tds' => $amountAfterTds,
                'total_amount_received'    => $totalReceived,
                'total_due_amount'         => $totalDueAmount,
                'total_paid_amount'        => $totalReceived,
            ];

            return view('superadmin.cashPayments.repay', compact('payment_info'));
        } catch (\Exception $e) {
            Log::error("Error fetching repay form: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Unable to load repay form.'])->withInput();
        }
    }

    /**
     * Store partial/repay payment
     */
    public function storeRepay(Request $request, $invoice_id)
    {
    

        $validated = $request->validate([
            'payment_mode' => 'required|string',
            'transaction_date' => 'required|date',
            'amount_received' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'transaction_reference' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with('tdsTransaction')->findOrFail($invoice_id);
            $createdBy = null; 
            
            if (Auth::guard('web')->check()) {
                $createdBy = Auth::guard('web')->id();   
            }

            // Create payment transaction
            InvoiceTransaction::create([
                'invoice_id'            => $invoice->id,
                'client_id'             => $invoice->client_id,
                'marketing_person_id'   => $invoice->marketing_user_code,
                'payment_mode'          => $validated['payment_mode'],
                'transaction_date'      => $validated['transaction_date'],
                'amount_received'       => $validated['amount_received'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'created_by'            => $createdBy,
            ]);

            // Update invoice status
            $totalReceived = InvoiceTransaction::where('invoice_id', $invoice->id)->sum('amount_received');
            $amountAfterTds = $invoice->tdsTransaction->amount_after_tds ?? 0;

            if ($request->has('isSettled') && $request->isSettled == 1) {
                $invoice->status = 4; // Settled
            } else {
                if ($totalReceived == 0) {
                    $invoice->status = 0; // Pending
                } elseif ($totalReceived < $amountAfterTds) {
                    $invoice->status = 3; // Partial
                } elseif ($totalReceived >= $amountAfterTds) {
                    $invoice->status = 1; // Paid
                }
            }

            $invoice->save();
            DB::commit();

            return redirect()->route('superadmin.invoices.index', ['type' => $invoice->type])
                             ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing repay payment: {$e->getMessage()}", [
                'invoice_id' => $invoice_id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Something went wrong while recording the payment.'])->withInput();
        }
    }  
    
    
    public function destroyInvoiceTransactions($invoice_id)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['tdsTransaction', 'transactions'])->findOrFail($invoice_id);

            // Delete all related invoice transactions
            if ($invoice->transactions()->exists()) {
                $invoice->transactions()->delete();
            }

            // Delete related TDS entry if exists
            if ($invoice->tdsTransaction) {
                $invoice->tdsTransaction->delete();
            }

            // Reset invoice status to 0 (Pending) since all payments are removed
            $invoice->status = 0;
            $invoice->save();

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error deleting transactions for invoice {$invoice_id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

}
