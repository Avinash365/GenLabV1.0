<?php

namespace App\Http\Controllers\Accounts;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\NumberToWordsService;
use Illuminate\Support\Facades\DB; 


use App\Models\SiteSetting;

class QuotationController extends Controller
{
    /**
     * Display a listing of the quotations.
     * 
     */

    protected $numberToWordsService; 
    public function __construct(NumberToWordsService $numberToWordsService )
    {
        $this->numberToWordsService = $numberToWordsService; 

    }

    public function index()
    {
        $quotations = Quotation::with('generatedBy')->latest()->paginate(20);
        return view('superadmin.accounts.quotation.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $marketingUsers = User::whereHas('role', fn($q) => $q->where('slug', 'marketing_person'))->get();
        return view('superadmin.accounts.quotation.create', compact('marketingUsers'));
    }

    /**
     * Store a newly created quotation in storage.
     */

    public function store(Request $request)
    {
        $letterhead = $request->input('letterhead');

        // Validate request
        $request->validate([
            'quotation_no' => 'required|unique:quotations,quotation_no',
            'quotation_date' => 'required|date',
            'marketing_user_id' => 'required|exists:users,id',
            'quotation_data' => 'required|json',
        ]);

        DB::beginTransaction(); // Start transaction

        try {
            $quotationData = json_decode($request->quotation_data, true);
            $marketingUser = User::findOrFail($request->marketing_user_id);

            // Filter out empty items
            $items = array_filter($quotationData['items'], function($item) {
                return !empty(trim($item['description'])) 
                    && !empty(trim($item['qty'])) 
                    && !empty(trim($item['rate']));
            });

            // Create the quotation
            Quotation::create([
                'quotation_no' => $request->quotation_no,
                'quotation_date' => $request->quotation_date,
                'client_name' => $quotationData['client_name'] ?: null,
                'client_gstin' => $quotationData['client_gstin'] ?: null,
                'name_of_work' => $quotationData['name_of_work'] ?: null,
                'bill_issue_to' => $quotationData['bill_issue_to'] ?: null, // preserves line breaks
                'marketing_person_code' => $marketingUser->user_code,
                'generated_by' => Auth::id(),
                'items' => $items, // only non-empty items
                'total_amount' => $quotationData['totals']['total_amount'] ?: 0,
                'discount_percent' => $quotationData['totals']['discount_percent'] ?: 0,
                'discount_amount' => $quotationData['totals']['discount_amount'] ?: 0,
                'after_discount' => $quotationData['totals']['after_discount'] ?: 0,
                'cgst_percent' => $quotationData['totals']['cgst_percent'] ?: 0,
                'cgst_amount' => $quotationData['totals']['cgst_amount'] ?: 0,
                'sgst_percent' => $quotationData['totals']['sgst_percent'] ?: 0,
                'sgst_amount' => $quotationData['totals']['sgst_amount'] ?: 0,
                'igst_percent' => $quotationData['totals']['igst_percent'] ?: 0,
                'igst_amount' => $quotationData['totals']['igst_amount'] ?: 0,
                'round_off' => $quotationData['totals']['round_off'] ?: 0,
                'payable_amount' => $quotationData['totals']['payable_amount'] ?: 0,
                'letterhead' => $letterhead
            ]);

            DB::commit(); // Commit transaction
            return redirect()->back()->with('success', 'Quotation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if any error occurs
            Log::error('Quotation Store Error: '.$e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Something went wrong while creating the quotation.');
        }
    }
    
    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        $marketingUsers = User::whereHas('role', fn($q) => $q->where('slug', 'marketing_person'))->get();
        return view('superadmin.accounts.quotation.edit', compact('quotation', 'marketingUsers'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $letterhead = $request->input('letterhead');

        $request->validate([
            'marketing_user_id' => 'required|exists:users,id',
            'quotation_data' => 'required|json',
        ]);

        DB::beginTransaction(); // Start transaction

        try {
            $quotationData = json_decode($request->quotation_data, true);
            $marketingUser = User::findOrFail($request->marketing_user_id);

            // Filter out empty items
            $items = array_filter($quotationData['items'], function($item) {
                return !empty(trim($item['description'])) 
                    && !empty(trim($item['qty'])) 
                    && !empty(trim($item['rate']));
            });

            $quotation->update([
                'marketing_person_code' => $marketingUser->user_code,
                'items' => $items, // only non-empty items
                'total_amount' => $quotationData['totals']['total_amount'] ?: 0,
                'discount_percent' => $quotationData['totals']['discount_percent'] ?: 0,
                'discount_amount' => $quotationData['totals']['discount_amount'] ?: 0,
                'after_discount' => $quotationData['totals']['after_discount'] ?: 0,
                'cgst_percent' => $quotationData['totals']['cgst_percent'] ?: 0,
                'cgst_amount' => $quotationData['totals']['cgst_amount'] ?: 0,
                'sgst_percent' => $quotationData['totals']['sgst_percent'] ?: 0,
                'sgst_amount' => $quotationData['totals']['sgst_amount'] ?: 0,
                'igst_percent' => $quotationData['totals']['igst_percent'] ?: 0,
                'igst_amount' => $quotationData['totals']['igst_amount'] ?: 0,
                'round_off' => $quotationData['totals']['round_off'] ?: 0,
                'payable_amount' => $quotationData['totals']['payable_amount'] ?: 0,
                'bill_issue_to' => $quotationData['bill_issue_to'] ?: null, // preserve newlines
                'letterhead' => $letterhead
            ]);

            DB::commit(); // Commit transaction
            return redirect()->back()->with('success', 'Quotation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if any error occurs
            Log::error('Quotation Update Error: '.$e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Something went wrong while updating the quotation.');
        }
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation)
    {
        try {
            $quotation->delete();
            return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Quotation Delete Error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while deleting the quotation.');
        }
    }


    public function generateQuotations($id){ 
        $quotation = Quotation::with('generatedBy')->findOrFail($id);
        $items = $quotation->items ?? [];
        $companyName = SiteSetting::value('company_name'); 

        $WordAmout = $this->numberToWordsService->convert($quotation->payable_amount); 
        $pdf = Pdf::loadView('superadmin.accounts.quotation.quotation_pdf', [
            'quotation' => $quotation,
            'items'     => $quotation->items, 
            'WordAmout' => $WordAmout, 
            'companyName' =>$companyName
        ]);
    
        return $pdf->stream('quotation_'.$quotation->id.'.pdf');
    }

}
