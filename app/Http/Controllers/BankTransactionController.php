<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BankStatementImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;


use App\Models\{Client, User, NewBooking, Invoice}; 



class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        // Start query
        $query = BankTransaction::query();

        // Include trashed if filtering softdeleted
        if ($request->filled('status') && $request->status == 'softdeleted') {
            $query->onlyTrashed();
        }

        // Search filter across multiple columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('closing_balance', 'like', "%{$search}%")
                ->orWhere('withdrawal', 'like', "%{$search}%")
                ->orWhere('deposit', 'like', "%{$search}%")
                ->orWhere('marketing_person', 'like', "%{$search}%")
                ->orWhere('transaction_remarks', 'like', "%{$search}%")
                ->orWhere('chq_ref_no', 'like', "%{$search}%");
            });
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        // Filter by status for normal rows
        if ($request->filled('status') && $request->status != 'softdeleted') {
            if ($request->status == 'credit') {
                $query->where('deposit', '>', 0);
            } elseif ($request->status == 'debit') {
                $query->where('withdrawal', '>', 0);
            }
        }

        // Filter by soft-deleted date if provided
        if ($request->filled('deleted_date') && $request->status == 'softdeleted') {
            $query->whereDate('deleted_at', $request->deleted_date);
        }

        // Get marketing persons
        $marketingPersons = User::whereHas('role', function ($q) {
            $q->where('slug', 'marketing_person');
        })->orderBy('name')->get();

        // Prepare years for the filter dropdown (10 before & 10 after current year)
        $currentYear = now()->year;
        $years = range($currentYear - 10, $currentYear + 10);
        $years = array_reverse($years); // optional: show latest year first

        // Get paginated results
        $transactions = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();

        return view('bankTransactions.upload', compact('transactions', 'years', 'marketingPersons'));
    }





    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx',
        ]);

        try {
            Excel::import(new BankStatementImport, $request->file('file'));
            return redirect()->back()->with('success', 'Bank statement imported successfully!');

        } catch (QueryException $e) {
            // Handle database errors (like foreign key or duplicate entries)
            return redirect()->back()->with('error', 'Duplicate entry or invalid reference detected. Import skipped for conflicting rows.');
        } catch (\Exception $e) {
            // Handle all other exceptions
            return redirect()->back()->with('error', 'An unexpected error occurred: ');
        }
    } 

    // Add or update note

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note'             => 'nullable|string|max:500',
            'marketing_person_id' => 'nullable|string|max:255',
            'client_ids'       => 'nullable|array',
            'client_ids.*'     => 'exists:clients,id',
            'invoice_nos'      => 'nullable|array',
            'invoice_nos.*'    => 'string',
            'ref_nos'          => 'nullable|array',
            'ref_nos.*'        => 'string',
        ]);

    
        $transaction = BankTransaction::withTrashed()->findOrFail($id);

        // Update main transaction fields
        $transaction->update([
            'note'             => $request->note,
            'marketing_person' => $request->marketing_person_id ?? null,
        ]);

        // Sync clients pivot table via Eloquent
        if ($request->filled('client_ids')) {
            $transaction->clients()->sync($request->client_ids);
        }

        // Sync invoices pivot table manually
        if ($request->filled('invoice_nos')) {
            $invoiceNos = $request->invoice_nos;

            // 1. Delete old invoice links not in request
            DB::table('bank_transaction_invoice')
                ->where('bank_transaction_id', $transaction->id)
                ->whereNotIn('invoice_no', $invoiceNos)
                ->delete();

            // 2. Insert new invoice links (ignore duplicates)
            $existingInvoices = DB::table('bank_transaction_invoice')
                ->where('bank_transaction_id', $transaction->id)
                ->pluck('invoice_no')
                ->toArray();

            $newInvoices = array_diff($invoiceNos, $existingInvoices);

            $insertData = [];
            foreach ($newInvoices as $invNo) {
                $insertData[] = [
                    'bank_transaction_id' => $transaction->id,
                    'invoice_no'          => $invNo,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }

            if (!empty($insertData)) {
                DB::table('bank_transaction_invoice')->insert($insertData);
            }
        }

        // Sync refs pivot table manually
        if ($request->filled('ref_nos')) {
            $refNos = $request->ref_nos;

            // 1. Delete old refs not in request
            DB::table('bank_transaction_ref')
                ->where('bank_transaction_id', $transaction->id)
                ->whereNotIn('ref_no', $refNos)
                ->delete();

            // 2. Insert new refs (ignore duplicates)
            $existingRefs = DB::table('bank_transaction_ref')
                ->where('bank_transaction_id', $transaction->id)
                ->pluck('ref_no')
                ->toArray();

            $newRefs = array_diff($refNos, $existingRefs);

            $insertRefs = [];
            foreach ($newRefs as $refNo) {
                $insertRefs[] = [
                    'bank_transaction_id' => $transaction->id,
                    'ref_no'              => $refNo,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }

            if (!empty($insertRefs)) {
                DB::table('bank_transaction_ref')->insert($insertRefs);
            }
        }

        return redirect()->back()->with('success', 'Note updated successfully!');
    }




    // Soft delete or undo
    public function softDeleteOrUndo($id)
    {
        $transaction = BankTransaction::withTrashed()->findOrFail($id);

        if ($transaction->trashed()) {
            $transaction->restore();
            return redirect()->back()->with('success', 'Transaction restored successfully!');
        } else {
            $transaction->delete();
            return redirect()->back()->with('success', 'Transaction send to suspense successfully!');
        }
    }

}
