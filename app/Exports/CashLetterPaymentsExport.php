<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class CashLetterPaymentsExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    protected $payments;
    protected $filters = [];

    public function __construct(...$args)
    {
        $this->payments = $args[0] ?? collect();
        $this->payments = $this->payments instanceof Collection ? $this->payments : collect($this->payments);
        $this->filters = $args[1] ?? [];
    }

    public function collection()
    {
        return $this->payments;
    }

    public function map($p): array
    {
        $refs = collect(is_array($p->booking_ids) ? $p->booking_ids : ($p->booking_ids ? explode(',', $p->booking_ids) : []))
                    ->map(fn($id) => optional(\App\Models\NewBooking::find($id))->reference_no)
                    ->filter()
                    ->values()
                    ->implode(', ');

        $statusMap = ['0'=>'Pending','1'=>'Partial','2'=>'Paid','3'=>'Settled'];

        return [
            $refs,
            $p->client->name ?? 'N/A',
            $p->marketingPerson->name ?? $p->marketing_person_id,
            (float) $p->total_amount,
            (float) $p->amount_received,
            $statusMap[$p->transaction_status] ?? $p->transaction_status,
            $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d-m-Y') : ''
        ];
    }

    public function headings(): array
    {
        return [
            'Reference Nos',
            'Client',
            'Marketing Person',
            'Total Amount',
            'Received',
            'Status',
            'Created At'
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
