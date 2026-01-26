<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $invoices;
    protected $filters = [];
    public function __construct(...$args)
    {
        $invoices = $args[0] ?? collect();
        $this->invoices = $invoices instanceof Collection ? $invoices : collect($invoices);
        $this->filters = $args[1] ?? [];
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

    public function startCell(): string
    {
        $fcount = count($this->filters ?: []);
        $startRow = $fcount + 3; // title + filters + blank
        return 'A' . $startRow;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $sheet = $event->sheet->getDelegate();
                $row = 1;
                if(!empty($this->filters)){
                    $sheet->setCellValue('A'.$row, 'Applied Filters:');
                    $row++;
                    foreach($this->filters as $k => $v){
                        $sheet->setCellValue('A'.$row, $k . ': ' . $v);
                        $row++;
                    }
                    $row++;
                }
                $headerRow = $row;
                $sheet->getStyle('A'.$headerRow.':H'.$headerRow)->getFont()->setBold(true);
            }
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
