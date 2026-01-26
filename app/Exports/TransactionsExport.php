<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionsExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $transactions;
    protected $filters = [];

    

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

    // Support constructor with optional filters
    public function __construct(...$args)
    {
        $this->transactions = $args[0] ?? collect();
        $this->filters = $args[1] ?? [];
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

    // Place the collection (headings) start row below the filters
    public function startCell(): string
    {
        $fcount = count($this->filters ?: []);
        $startRow = $fcount + 3; // 1: title, fcount: filters, +1 blank
        return 'A' . $startRow;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $row = 1;
                if(!empty($this->filters)){
                    $sheet->setCellValue('A'.$row, 'Applied Filters:');
                    $row++;
                    foreach($this->filters as $k => $v){
                        $sheet->setCellValue('A'.$row, $k . ': ' . $v);
                        $row++;
                    }
                    // leave one blank row after filters
                    $row++;
                }
                // Optionally style the header row (where headings will be written)
                $headerRow = $row;
                $sheet->getStyle('A'.$headerRow.':G'.$headerRow)->getFont()->setBold(true);
            }
        ];
    }
}
