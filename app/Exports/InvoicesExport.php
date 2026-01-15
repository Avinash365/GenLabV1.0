<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $invoices;

    public function __construct($invoices)
    {
        $this->invoices = $invoices instanceof Collection ? $invoices : collect($invoices);
    }

    public function collection()
    {
        return $this->invoices;
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Reference No',
            'Client Name',
            'Assigned Client',
            'GST Amount',
            'Total Amount',
            'Invoice Date',
            'Payment Status'
        ];
    }

    public function map($invoice): array
    {
        $statusLabels = [
            '1' => 'Paid',
            '0' => 'Unpaid',
            '2' => 'Cancel',
            '3' => 'Partial',
            '4' => 'Settle',
        ];

        return [
            $invoice->invoice_no,
            $invoice->relatedBooking->reference_no ?? 'N/A',
            $invoice->relatedBooking->client_name ?? $invoice->client_name ?? 'N/A',
            $invoice->relatedBooking->client->name ?? 'N/A',
            (float) $invoice->gst_amount,
            (float) $invoice->total_amount,
            optional($invoice->invoice_date)->format('d-m-Y') ?? (($invoice->letter_date) ? \Carbon\Carbon::parse($invoice->letter_date)->format('d-m-Y') : '-'),
            $statusLabels[$invoice->status] ?? $invoice->status ?? 'Unpaid',
        ];
    }
}
