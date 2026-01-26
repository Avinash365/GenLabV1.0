<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class BankTransactionsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithCustomStartCell, WithEvents
{
    protected $transactions;
    protected $filters = [];

    public function __construct(...$args)
    {
        $this->transactions = $args[0] ?? collect();
        $this->transactions = $this->transactions instanceof Collection ? $this->transactions : collect($this->transactions);
        $this->filters = $args[1] ?? [];
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function map($transaction): array
    {
        // Associated clients
        $clients = $transaction->clients ?? collect();
        $clientsStr = $clients->pluck('name')->implode(', ');

        // Marketing person name (fallback to raw field)
        $marketingName = optional(\App\Models\User::find($transaction->marketing_person))->name ?? ($transaction->marketing_person ?: '');

        // Invoice and ref numbers from pivot tables
        $invoiceNos = \Illuminate\Support\Facades\DB::table('bank_transaction_invoice')->where('bank_transaction_id', $transaction->id)->pluck('invoice_no')->implode(', ');
        $refNos = \Illuminate\Support\Facades\DB::table('bank_transaction_ref')->where('bank_transaction_id', $transaction->id)->pluck('ref_no')->implode(', ');

        return [
            $transaction->tran_id,
            $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d M Y') : '',
            ($transaction->transaction_remarks ?: '') . ($transaction->note ? ' | Note: '.$transaction->note : ''),
            $clientsStr,
            $marketingName,
            $invoiceNos,
            $refNos,
            $transaction->withdrawal > 0 ? $transaction->withdrawal : '',
            $transaction->deposit > 0 ? $transaction->deposit : '',
        ];
    }

    public function headings(): array
    {
        return [
            'Tran ID',
            'Txn Date',
            'Remarks / Note',
            'Associated Client(s)',
            'Marketing Person',
            'Invoice Nos',
            'Reference Nos',
            'Withdrawal',
            'Deposit',
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
                $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getFont()->setBold(true);
            }
        ];
    }
}
