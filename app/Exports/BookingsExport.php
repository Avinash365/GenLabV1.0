<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingsExport implements FromCollection, WithHeadings
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
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
}
