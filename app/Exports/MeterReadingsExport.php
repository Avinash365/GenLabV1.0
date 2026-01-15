<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class MeterReadingsExport implements FromCollection, WithHeadings, WithMapping
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
}
