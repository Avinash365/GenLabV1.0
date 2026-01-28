<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class PurchaseBillsExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $bills;
    protected $filters = [];

    public function __construct(...$args)
    {
        $this->bills = $args[0] ?? collect();
        $this->bills = $this->bills instanceof Collection ? $this->bills : collect($this->bills);
        $this->filters = $args[1] ?? [];
    }

    public function collection()
    {
        return $this->bills;
    }

    public function map($b): array
    {
        return [
            $b->description,
            $b->party,
            (float) $b->amount,
            $b->purchased_by,
            $b->gst_type,
            $b->purchase_date ? \Carbon\Carbon::parse($b->purchase_date)->format('d-m-Y') : '',
            $b->bill_upload ? 'Uploaded' : 'Not Uploaded'
        ];
    }

    public function headings(): array
    {
        return [
            'Description',
            'Party',
            'Amount',
            'Purchased By',
            'GST Type',
            'Purchase Date',
            'Bill'
        ];
    }

    public function startCell(): string
    {
        $fcount = count($this->filters ?: []);
        $startRow = $fcount + 3;
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
                $sheet->getStyle('A'.$headerRow.':G'.$headerRow)->getFont()->setBold(true);
            }
        ];
    }
}
