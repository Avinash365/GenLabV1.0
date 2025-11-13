<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeavesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $leaves;

    public function __construct($leaves)
    {
        $this->leaves = collect($leaves);
    }

    public function collection(): Collection
    {
        return $this->leaves;
    }

    public function headings(): array
    {
        return [
            '#',
            'Employee',
            'Employee Email',
            'Leave Type',
            'Day Type',
            'Days/Hours',
            'Formatted Duration',
            'From Date',
            'To Date',
            'Status',
            'Applied On',
            'Approved At',
            'Approved By',
            'Admin Comments',
        ];
    }

    public function map($leave): array
    {
        $employee = $leave->employee_name ?? optional($leave->user)->name;
        $email = optional($leave->user)->email;

        $status = $leave->status ? ucfirst(strtolower($leave->status)) : '-';
        $dayType = $leave->day_type ? ucfirst(strtolower($leave->day_type)) : '-';

        return [
            $leave->id,
            $employee ?: '-',
            $email ?: '-',
            $leave->leave_type,
            $dayType,
            $leave->days_hours,
            $leave->days_hours_formatted ?? $leave->days_hours,
            optional($leave->from_date)->format('d-m-Y'),
            optional($leave->to_date)->format('d-m-Y'),
            $status,
            optional($leave->created_at)->format('d-m-Y H:i'),
            optional($leave->approved_at)->format('d-m-Y H:i'),
            optional($leave->approver)->name ?: '-',
            $leave->admin_comments ?? '-',
        ];
    }
}
