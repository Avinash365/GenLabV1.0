<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class BookingsExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $bookings;
    protected $filters = [];

    public function __construct(...$args)
    {
        $bookings = $args[0] ?? collect();
        $this->bookings = $bookings instanceof Collection ? $bookings : collect($bookings);
        $this->filters = $args[1] ?? [];
    }

    public function collection()
    {
        return $this->bookings->values()->map(function($b, $i) {
            return [
                $i + 1,
                $b->client_name,
                $b->reference_no,
                optional($b->marketingPerson)->name,
                $b->items->count(),
                $b->job_order_date ? \Carbon\Carbon::parse($b->job_order_date)->format('Y-m-d') : '',
                optional($b->department)->name,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Client Name',
            'Reference No',
            'Marketing Person',
            'Items Count',
            'Job Order Date',
            'Department',
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
