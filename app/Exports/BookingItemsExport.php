<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingItemsExport implements FromCollection, WithHeadings
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items->values()->map(function($it, $i) {
            $expected = '';
            if (!empty($it->lab_expected_date)) {
                try {
                    $expected = \Carbon\Carbon::parse($it->lab_expected_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $expected = (string) $it->lab_expected_date;
                }
            }
            return [
                $i + 1,
                $it->job_order_no,
                $it->booking->client_name ?? '-',
                $it->sample_description,
                $it->sample_quality,
                $it->particulars,
                $expected,
                $it->amount,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Job Order No',
            'Client Name',
            'Sample Description',
            'Sample Quality',
            'Particulars',
            'Expected Date',
            'Amount',
        ];
    }
}
