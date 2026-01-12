<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingItemsExport implements FromCollection, WithHeadings
{
    protected $items;
    public function __construct($items) { $this->items = $items; }
    public function collection()
    {
        return $this->items->values()->map(function($it, $i) {
            return [
                $i+1,
                $it->job_order_no,
                // Client Name removed
                $it->sample_description,
                $it->sample_quality,
                $it->particulars,
                $it->lab_expected_date ? $it->lab_expected_date->format('Y-m-d') : '-',
                $it->updated_at ? $it->updated_at->format('Y-m-d H:i') : '-',
                $it->status,
            ];
        });
    }
    public function headings(): array
    {
        return ['#','Job Order No','Sample Description','Sample Quality','Particulars','Lab Expected Date','Updated At','Status'];
    }
}
