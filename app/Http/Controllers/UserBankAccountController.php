<?php

namespace App\Http\Controllers;

use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBankAccountController extends Controller
{
    /**
     * Display all bank accounts
     */

    public function manage()
    {
        $accounts = UserBankAccount::with('creator')->latest()->get();

        return view('superadmin.bankAccounts.index', compact('accounts'));
    }

    public function index()
    {
        $accounts = UserBankAccount::with('creator')->latest()->get();

        
        return response()->json([
            'status' => true,
            'data' => $accounts
        ]); 
    }

    /**
     * Store new bank account
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:50|unique:user_bank_accounts,account_no',
            'account_holder_name' => 'required|string|max:255',
        ]);

        

        $account = UserBankAccount::create([
            'bank_name' => $request->bank_name,
            'account_no' => $request->account_no,
            'account_holder_name' => $request->account_holder_name,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success','Created successfully');

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Bank account created successfully',
        //     'data' => $account
        // ], 201);
    }

    /**
     * Show single bank account (for edit)
     */
    public function show($id)
    {
        $account = UserBankAccount::with('creator')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $account
        ]);
    }

    /**
     * Update bank account
     */
    public function update(Request $request, $id)
    {
        $account = UserBankAccount::findOrFail($id);

        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:50|unique:user_bank_accounts,account_no,' . $account->id,
            'account_holder_name' => 'required|string|max:255',
        ]);

        $account->update([
            'bank_name' => $request->bank_name,
            'account_no' => $request->account_no,
            'account_holder_name' => $request->account_holder_name,
        ]);


        // return response()->json([
        //     'status' => true,
        //     'message' => 'Bank account updated successfully',
        //     'data' => $account
        // ]);


        return redirect()->back()->with('success','Update successfully');
    }
    
    /**
     * Delete bank account
     */
    public function destroy($id)
    {
        $account = UserBankAccount::findOrFail($id);
        $account->delete();

        return redirect()->back()->with('success','Deleted successfully');

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Bank account deleted successfully'
        // ]);
    }
}
