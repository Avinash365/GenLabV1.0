<?php

namespace App\Http\Controllers\Accounts; 

use App\Http\Controllers\Controller;
use App\Models\PurchaseBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurchaseBillController extends Controller
{
    /**
     * List purchase bills
     */

    public function __construct(){
        $this->middleware('permission:purchase_bill.view')->only('index'); 
        $this->middleware('permission:purchase_bill.create')->only('store', 'create'); 
        $this->middleware('permission:purchase_bill.edit')->only('update'); 
        $this->middleware('permission:purchase_bill.delete')->only('destroy'); 
    }

   public function index(Request $request)
{
    try {
        $query = PurchaseBill::query();

        /* --------------------
         | SEARCH FILTER
         -------------------- */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "{$search}%")
                  ->orWhere('party', 'like', "{$search}%")
                  ->orWhere('purchased_by', 'like', "{$search}%");
            });
        }

        /* --------------------
         | MONTH FILTER
         -------------------- */
        if ($request->filled('month')) {
            $query->whereMonth('purchase_date', $request->month);
        }

        /* --------------------
         | YEAR FILTER
         -------------------- */
        if ($request->filled('year')) {
            $query->whereYear('purchase_date', $request->year);
        }

        $bills = $query
            ->orderBy('purchase_date', 'desc')
            ->paginate(10)
            ->appends($request->all()); // keep filters in pagination

        return view('superadmin.accounts.PurchaseBill.index', compact('bills'));

    } catch (\Throwable $e) {
        Log::error('PurchaseBill index error', [
            'error' => $e->getMessage()
        ]);

        return back()->with('error', 'Failed to load purchase bills');
    }
}


    /**
     * Store purchase bill
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $data = $request->validate([
            'description'   => 'nullable|string|max:255',
            'party'         => 'nullable|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'purchased_by'  => 'nullable|string|max:255',
            'gst_type'      => 'required|in:GST,Non-GST',
            'purchase_date' => 'nullable|date',
            'bill_upload'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('bill_upload')) {
            $data['bill_upload'] = $request->file('bill_upload')
                ->store('purchase_bills', 'public');
        }

        PurchaseBill::create($data);

        DB::commit();

        return redirect()
            ->back()
            ->with('success', 'Purchase bill created successfully');

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('PurchaseBill store error', [
            'error' => $e->getMessage()
        ]);

        return back()
            ->withInput()
            ->with('error', 'Failed to create purchase bill');
    }
    }


    public function create(){
        return view('superadmin.accounts.PurchaseBill.create'); 
    }

    /**
     * Update purchase bill
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $bill = PurchaseBill::findOrFail($id);

            $data = $request->validate([
                'description'   => 'nullable|string|max:255',
                'party'         => 'nullable|string|max:255',
                'amount'        => 'required|numeric|min:0',
                'purchased_by'  => 'nullable|string|max:255',
                'gst_type'      => 'required|in:GST,Non-GST',
                'purchase_date' => 'nullable|date',
                'bill_upload'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            if ($request->hasFile('bill_upload')) {
                if ($bill->bill_upload && Storage::disk('public')->exists($bill->bill_upload)) {
                    Storage::disk('public')->delete($bill->bill_upload);
                }

                $data['bill_upload'] = $request->file('bill_upload')
                    ->store('purchase_bills', 'public');
            }

            $bill->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase bill updated successfully',
                'data' => $bill
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PurchaseBill update error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase bill'
            ], 500);
        }
    }

    /**
     * Delete purchase bill
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $bill = PurchaseBill::findOrFail($id);

            if ($bill->bill_upload && Storage::disk('public')->exists($bill->bill_upload)) {
                Storage::disk('public')->delete($bill->bill_upload);
            }

            $bill->delete();

            DB::commit();

            return redirect()->back()->with('success','Purchase bill deleted successfully'); 

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PurchaseBill delete error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error','Failed to delete purchase bill'); 
            
        }
    }
}
