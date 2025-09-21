<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\ChequeTemplate;
use Illuminate\Http\Request;
use App\Models\Cheque;

class ChequeTemplateController extends Controller
{
    public function editor(Bank $bank, Request $request)
    {
        $fields = ['payee_name', 'date', 'amount_number', 'amount_words'];
        $templates = $bank->templates()->get()->keyBy('field_name');
        $cheque = null;
        if ($request->filled('cheque')) {
            $cheque = Cheque::find($request->query('cheque'));
        }
        return view('superadmin.cheques.cheque-editor', compact('bank', 'fields', 'templates', 'cheque'));
    }

    public function store(Request $request, Bank $bank)
    {
        $data = $request->validate([
            'positions' => ['required', 'array'],
            'positions.*.field_name' => ['required', 'string'],
            'positions.*.top' => ['required', 'integer', 'min:0'],
            'positions.*.left' => ['required', 'integer', 'min:0'],
            'positions.*.font_size' => ['nullable', 'integer', 'min:8', 'max:72'],
            'positions.*.letter_spacing' => ['nullable', 'numeric'],
            'delete_fields' => ['nullable','array'],
            'delete_fields.*' => ['string'],
        ]);

        foreach ($data['positions'] as $item) {
            ChequeTemplate::updateOrCreate(
                ['bank_id' => $bank->id, 'field_name' => $item['field_name']],
                [
                    'top' => $item['top'],
                    'left' => $item['left'],
                    'font_size' => $item['font_size'] ?? 14,
                    'letter_spacing' => $item['letter_spacing'] ?? null,
                ]
            );
        }

        if (!empty($data['delete_fields'])) {
            ChequeTemplate::where('bank_id', $bank->id)
                ->whereIn('field_name', $data['delete_fields'])
                ->delete();
        }

        return response()->json(['status' => 'ok']);
    }

    public function fetch(Bank $bank)
    {
    $templates = $bank->templates()->get(['field_name', 'top', 'left', 'font_size', 'letter_spacing']);
        return response()->json($templates);
    }
}
