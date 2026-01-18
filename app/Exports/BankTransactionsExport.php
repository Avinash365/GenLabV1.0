<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class BankTransactionsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function map($transaction): array
    {
        return [
            $transaction->tran_id,
            $transaction->value_date ? \Carbon\Carbon::parse($transaction->value_date)->format('d M Y') : '',
            $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d M Y') : '',
            $transaction->transaction_remarks,
            $transaction->chq_ref_no,
            $transaction->withdrawal > 0 ? $transaction->withdrawal : '',
            $transaction->deposit > 0 ? $transaction->deposit : '',
            $transaction->closing_balance,
            $transaction->note,
        ];
    }

    public function headings(): array
    {
        return [
            'Tran ID',
            'Value Date',
            'Txn Date',
            'Remarks',
            'Chq/Ref',
            'Withdrawal',
            'Deposit',
            'Balance',
            'Note',
        ];
    }
}
