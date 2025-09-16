<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentSettingController extends Controller
{
    // List all payment settings
    public function index()
    {
        $bank =PaymentSetting::first(); 

        return view('bankDetails.index', compact('bank'));
    }

    
    // Show create form
    public function create()
    {
        return view('payment_settings.create');
    }

    // Store a new payment setting
    public function store(Request $request)
    {   
        $bankId = (int) $request->input('bank_id');

        if ($bankId !== 0) {
           
            $paymentSetting = PaymentSetting::find($bankId);

            if ($paymentSetting) {
                
                return $this->update($request, $paymentSetting);
            } else {
                return redirect()->back()->withErrors('Something went wrong, please try again.');
            }
        }

        try {
            $validated = $request->validate([
                'instructions'        => 'nullable|string',
                'bank_name'           => 'nullable|string|max:255',
                'account_no'          => 'nullable|string|max:255',
                'branch'              => 'nullable|string|max:255',
                'branch_holder_name'  => 'nullable|string|max:255',
                'ifsc_code'           => 'nullable|string|max:255',
                'pan_code'            => 'nullable|string|max:255',
                'pan_no'              => 'nullable|string|max:255',
                'gstin'               => 'nullable|string|max:255',
                'upi'                 => 'nullable|string|max:255',
            ]);

            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();

            PaymentSetting::create($validated);
            
            $bank =PaymentSetting::first(); 

            return view('bankDetails.index', compact('bank')); 
            

        } catch (\Exception $e) {
            return back()->withErrors('Failed to create payment setting: ' . $e->getMessage());
        }
    }

   

    // Update setting
    public function update(Request $request, PaymentSetting $paymentSetting)
    {
        try { 

            $validated = $request->validate([
                'instructions'        => 'nullable|string',
                'bank_name'           => 'nullable|string|max:255',
                'account_no'          => 'nullable|string|max:255',
                'branch'              => 'nullable|string|max:255',
                'branch_holder_name'  => 'nullable|string|max:255',
                'ifsc_code'           => 'nullable|string|max:255',
                'pan_code'            => 'nullable|string|max:255',
                'pan_no'              => 'nullable|string|max:255',
                'gstin'               => 'nullable|string|max:255',
                'upi'                 => 'nullable|string|max:255',
            ]);
           
            $validated['updated_by'] = Auth::id();

            $paymentSetting->update($validated);

            return redirect()
                ->back()
                ->with('success', 'Payment setting updated successfully.');

        } catch (ModelNotFoundException $e) {
            return redirect()->back()->withErrors('Payment setting not found.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update payment setting: ' . $e->getMessage());
        }
    }

    // Delete setting
    public function destroy(PaymentSetting $paymentSetting)
    {
        try {
            $paymentSetting->delete();

            return redirect()
                ->route('payment-settings.index')
                ->with('success', 'Payment setting deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete payment setting: ' . $e->getMessage());
        }
    }
}
