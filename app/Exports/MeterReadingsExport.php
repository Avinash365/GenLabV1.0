<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class MeterReadingsExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $readings;

    public function __construct($readings)
    {
        $this->readings = $readings instanceof Collection ? $readings : collect($readings);
    }

    public function collection()
    {
        return $this->readings;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Marketing Person',
            'Start Reading',
            'Start Time',
            'End Reading',
            'End Time',
            'Total Reading',
        ];
    }

    public function map($reading): array
    {
        $mpName = optional($reading->user)->name ?? optional($reading->user)->user_code ?? '-';
        $desc = $reading->start_description ?: $reading->end_description;

        return [
            $desc ?? '-',
            $mpName,
            $reading->starting_reading,
            $reading->starting_at ? $reading->starting_at->format('Y-m-d H:i') : '-',
            $reading->ending_reading,
            $reading->ending_at ? $reading->ending_at->format('Y-m-d H:i') : '-',
            $reading->total_reading ?? '-',
        ];
    }

    /**
     * Append a grand total row after the data rows.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $count = $this->readings->count();
                $row = $count + 2; // headings row (1) + data rows start at 2

                // compute numeric total (ignore non-numeric values)
                $total = $this->readings->reduce(function($carry, $item){
                    $val = $item->total_reading ?? 0;
                    return $carry + (is_numeric($val) ? $val : 0);
                }, 0);

                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('F'.$row, 'Grand Total');
                $sheet->setCellValue('G'.$row, $total);
                $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
            }
        ];
    }
}
