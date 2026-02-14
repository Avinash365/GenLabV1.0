<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class BookingItemsExport implements FromCollection
{
    protected $items;
    protected $filters = [];

    public function __construct($items, $filters = [])
    {
        $this->items = $items;
        $this->filters = $filters;
    }

    public function collection()
    {
        $rows = [];

        // Add filter header rows
        if (!empty($this->filters)) {
            $rows[] = ['Applied Filters:'];
            $rows[] = ['Search', $this->filters['search'] ?? ''];
            $rows[] = ['Month', $this->filters['month'] ?? ''];
            $rows[] = ['Year', $this->filters['year'] ?? ''];
            $rows[] = ['Department', $this->filters['department'] ?? ''];
            $rows[] = ['Marketing', $this->filters['marketing'] ?? ''];
            $rows[] = ['Payment Option', $this->filters['payment_option'] ?? ''];
            $rows[] = ['Use Created At', !empty($this->filters['use_created_at']) ? 'Yes' : 'No'];
            $rows[] = []; // blank row before headings
        }

        // Headings row
        $rows[] = ['#','Job Order No','Client Name','Sample Description','Sample Quality','Particulars','Expected Date','Amount'];

        // Data rows
        foreach ($this->items->values() as $i => $it) {
            $expected = '';
            if (!empty($it->lab_expected_date)) {
                try {
                    $expected = \Carbon\Carbon::parse($it->lab_expected_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $expected = (string) $it->lab_expected_date;
                }
            }

            $rows[] = [
                $i + 1,
                $it->job_order_no,
                $it->booking->client_name ?? '-',
                $it->sample_description,
                $it->sample_quality,
                $it->particulars,
                $expected,
                $it->amount,
            ];
        }

        return collect($rows);
    }
}
