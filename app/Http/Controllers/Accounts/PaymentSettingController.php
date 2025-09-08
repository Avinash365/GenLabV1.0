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
        $settings = PaymentSetting::with(['creator', 'updater'])->get();
        return view('payment_settings.index', compact('settings'));
    }

    // Show create form
    public function create()
    {
        return view('payment_settings.create');
    }

    // Store a new payment setting
    public function store(Request $request)
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

            $validated['created_by'] = Auth::id();
            $validated['updated_by'] = Auth::id();

            PaymentSetting::create($validated);

            return redirect()
                ->route('payment-settings.index')
                ->with('success', 'Payment setting created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors('Failed to create payment setting: ' . $e->getMessage());
        }
    }

    // Show single setting
    public function show(PaymentSetting $paymentSetting)
    {
        return view('payment_settings.show', compact('paymentSetting'));
    }

    // Show edit form
    public function edit(PaymentSetting $paymentSetting)
    {
        return view('payment_settings.edit', compact('paymentSetting'));
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
                ->route('payment-settings.index')
                ->with('success', 'Payment setting updated successfully.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('payment-settings.index')->withErrors('Payment setting not found.');
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
