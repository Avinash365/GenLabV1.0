<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankController extends Controller
{
    public function create()
    {
        return view('superadmin.cheques.bank-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'cheque_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $path = $request->file('cheque_image')->store('cheques', 'public');

        $bank = Bank::create([
            'bank_name' => $validated['bank_name'],
            'cheque_image_path' => $path,
        ]);

        return redirect()
            ->route('superadmin.cheque-templates.editor', $bank->id)
            ->with('success', 'Bank created. Configure cheque alignment.');
    }

    public function destroy(Bank $bank)
    {
        // Delete stored cheque image if exists
        if ($bank->cheque_image_path && \Storage::disk('public')->exists($bank->cheque_image_path)) {
            \Storage::disk('public')->delete($bank->cheque_image_path);
        }

        // Delete related templates
        $bank->templates()->delete();

        // Delete bank
        $bank->delete();

        return back()->with('success', 'Bank and its templates deleted successfully.');
    }
}
