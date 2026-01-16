<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions->values()->map(function($t, $i) {
            return [
                $i + 1,
                $t->invoice->invoice_no ?? 'N/A',
                $t->client->name ?? 'N/A',
                $t->marketingPerson->name ?? 'N/A',
                number_format($t->amount_received, 2),
                ucfirst($t->payment_mode),
                $t->transaction_date ? \Carbon\Carbon::parse($t->transaction_date)->format('d-m-Y') : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Invoice No',
            'Client Name',
            'Marketing Person',
            'Amount Received',
            'Payment Mode',
            'Transaction Date',
        ];
    }
}
