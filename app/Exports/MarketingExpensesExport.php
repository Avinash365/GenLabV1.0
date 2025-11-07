<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MarketingExpensesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $expenses;

    public function __construct($expenses)
    {
        $this->expenses = collect($expenses);
    }

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            '#',
            'Person',
            'Person Code',
            'Section',
            'Amount',
            'Approved Amount',
            'Due Amount',
            'From Date',
            'To Date',
            'Status',
            'Approved By',
            'Uploaded At',
        ];
    }

    public function map($expense): array
    {
        $approved = (float) ($expense->approved_amount ?? 0);
        $due = max(0, (float) $expense->amount - $approved);

        return [
            $expense->id,
            $expense->marketingPerson->name ?? $expense->person_name ?? '-',
            $expense->marketing_person_code,
            ucfirst($expense->section ?? 'marketing'),
            (float) $expense->amount,
            $approved,
            $due,
            optional($expense->from_date)->format('d-m-Y'),
            optional($expense->to_date)->format('d-m-Y'),
            ucfirst($expense->status ?? 'pending'),
            $expense->approver->name ?? '-',
            optional($expense->created_at)->format('d-m-Y H:i'),
        ];
    }
}
