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
                optional($it->booking)->client_name,
                $it->sample_description,
                $it->sample_quality,
                $it->particulars,
                $it->status,
            ];
        });
    }
    public function headings(): array
    {
        return ['#','Job Order No','Client Name','Sample Description','Sample Quality','Particulars','Status'];
    }
}
