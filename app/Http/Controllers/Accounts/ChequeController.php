<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cheque;
use App\Models\Bank;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'issued'); // default 'issued'

        $paymentOption = strtolower((string) $request->query('payment_option', ''));
        if (in_array($paymentOption, ['issued','received'], true)) {
            $status = $paymentOption;
        }

        $q = trim((string) $request->query('q', ''));
        $client = trim((string) $request->query('client', ''));
        $month = $request->integer('month');
        $year = $request->integer('year');
        $dateColumn = $status === 'received' ? 'received_cheque_date' : 'date';

        $cheques = Cheque::query()
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($q !== '', function($qb) use ($q){
                $qb->where(function($sub) use ($q){
                    $sub->where('cheque_no','like',"%$q%")
                        ->orWhere('bank','like',"%$q%")
                        ->orWhere('payee_name','like',"%$q%")
                        ->orWhere('purpose','like',"%$q%")
                        ->orWhere('handed_over_to','like',"%$q%")
                        ->orWhere('received_party_name','like',"%$q%")
                        ->orWhere('received_note','like',"%$q%");
                });
            })
            ->when($client !== '', function($qb) use ($client){
                $qb->where(function($sub) use ($client){
                    $sub->where('payee_name','like',"%$client%")
                        ->orWhere('received_party_name','like',"%$client%");
                });
            })
            ->when($month, fn($qb) => $qb->whereMonth($dateColumn, $month))
            ->when($year, fn($qb) => $qb->whereYear($dateColumn, $year))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $filters = compact('q','client','month','year','paymentOption');

        $templateBanks = Bank::query()
            ->whereHas('templates')
            ->withMax('templates as latest_template_at', 'updated_at')
            ->orderByDesc('latest_template_at')
            ->orderBy('bank_name')
            ->get(['id','bank_name']);

        return view('superadmin.accounts.cheques.index', compact('cheques','status','filters','templateBanks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank' => ['nullable','string','max:191'],
            'cheque_no' => ['required','string','max:191'],
            'payee_name' => ['required','string','max:191'],
            'date' => ['nullable','date'],
            'purpose' => ['nullable','string','max:255'],
            'handed_over_to' => ['nullable','string','max:191'],
            'amount' => ['required','numeric','min:0'],
            'amount_in_words' => ['nullable','string','max:255'],
        ]);

        $data['status'] = 'issued';
        Cheque::create($data);

        return redirect()->route('superadmin.cheques.index')
            ->with('success', 'Cheque issued successfully.');
    }

    public function receive(Request $request, Cheque $cheque)
    {
        $data = $request->validate([
            'received_party_name' => ['required','string','max:191'],
            'received_cheque_date' => ['required','date'],
            'received_amount' => ['required','numeric','min:0'],
            'received_copy' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:5120'],
            'received_note' => ['nullable','string','max:1000'],
        ]);

        if ($request->hasFile('received_copy')) {
            $path = $request->file('received_copy')->store('cheque_copies', 'public');
            $data['received_copy_path'] = $path;
        }

        $data['status'] = 'received';
        $cheque->update($data);

        return back()->with('success', 'Cheque received details saved.');
    }

    public function toggleDeposit(Cheque $cheque)
    {
        $cheque->deposit_status = ! $cheque->deposit_status;
        $cheque->save();
        return back()->with('success', 'Deposit status updated.');
    }

    public function storeReceived(Request $request)
    {
        $data = $request->validate([
            'received_party_name' => ['required','string','max:191'],
            'received_cheque_date' => ['required','date'],
            'received_amount' => ['required','numeric','min:0'],
            'received_copy' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:5120'],
            'received_note' => ['nullable','string','max:1000'],
        ]);

        if ($request->hasFile('received_copy')) {
            $data['received_copy_path'] = $request->file('received_copy')->store('cheque_copies', 'public');
        }

        $data['status'] = 'received';
        $data['amount'] = $data['received_amount'];
        $data['date'] = $data['received_cheque_date'];

    // Satisfy non-nullable base columns
    $data['cheque_no'] = 'RCV-' . now()->format('YmdHis');
    $data['payee_name'] = $data['received_party_name'];

    Cheque::create($data);

        return redirect()->route('superadmin.cheques.index', ['status' => 'received'])
            ->with('success', 'Received cheque saved.');
    }

    public function edit(Cheque $cheque)
    {
        return view('superadmin.accounts.cheques.edit', compact('cheque'));
    }

    public function update(Request $request, Cheque $cheque)
    {
        $data = $request->validate([
            'bank' => ['nullable','string','max:191'],
            'cheque_no' => ['required','string','max:191'],
            'payee_name' => ['required','string','max:191'],
            'date' => ['nullable','date'],
            'purpose' => ['nullable','string','max:255'],
            'handed_over_to' => ['nullable','string','max:191'],
            'amount' => ['required','numeric','min:0'],
            'amount_in_words' => ['nullable','string','max:255'],
        ]);

        $cheque->update($data);
        return redirect()->route('superadmin.cheques.index')->with('success','Cheque updated.');
    }

    public function destroy(Cheque $cheque)
    {
        $cheque->delete();
        return redirect()->route('superadmin.cheques.index')->with('success','Cheque deleted.');
    }

    public function printPreview(Cheque $cheque)
    {
        $bankName = trim((string)$cheque->bank);
        $lower = strtolower($bankName);
        $bank = \App\Models\Bank::query()
            ->whereRaw('LOWER(bank_name) = ?', [$lower])
            ->orWhereRaw('LOWER(bank_name) LIKE ?', [$lower.' %'])
            ->orWhereRaw('LOWER(bank_name) LIKE ?', ['% '.$lower])
            ->first();

        $templates = collect();
        if ($bank) {
            $templates = $bank->templates()->get()->keyBy('field_name');
        }

        return view('superadmin.accounts.cheques.print-preview', [
            'cheque' => $cheque,
            'bank' => $bank,
            'templates' => $templates,
        ]);
    }
}
