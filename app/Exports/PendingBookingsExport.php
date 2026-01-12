<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PendingBookingsExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var \Illuminate\Support\Collection */
    protected $bookings;

    public function __construct(Collection $bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            '#',
            'Reference No',
            'Marketing Person',
            'Updated At',
            'Lab Expected Date',
            'Pending Items',
        ];
    }

    public function map($booking): array
    {
        static $i = 0;
        $i++;

        $maxDate = $booking->items->count() > 0 ? $booking->items->max('lab_expected_date') : null;

        return [
            $i,
            $booking->reference_no,
            optional($booking->marketingPerson)->name ?: '-',
            $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i') : '-',
            $maxDate ? $maxDate->format('Y-m-d') : '-',
            (int) ($booking->pending_items_count ?? 0),
        ];
    }
}
