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
}
