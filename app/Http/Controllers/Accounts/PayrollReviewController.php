<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollReviewController extends Controller
{
    public function index(Request $request): View
    {
        $availableCycles = PayrollCycle::query()
            ->where('status', PayrollCycle::STATUS_SENT)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $selectedCycle = null;
        $entries = collect();

        if ($availableCycles->isNotEmpty()) {
            $requestedCycleId = (int) $request->query('cycle_id');
            $selectedCycle = $availableCycles->firstWhere('id', $requestedCycleId) ?? $availableCycles->first();

            $selectedCycle->load(['entries.employee' => function ($query) {
                $query->select(
                    'id',
                    'first_name',
                    'last_name',
                    'department',
                    'employment_status',
                    'ctc',
                    'bank_account_number',
                    'bank_ifsc',
                    'bank_name'
                );
            }]);

            $entries = $selectedCycle->entries->sortBy(fn (PayrollEntry $entry) => $entry->employee?->first_name ?? '');
        }

        $totals = [
            'gross' => $entries->sum('gross_amount'),
            'leave_deductions' => $entries->sum('leave_deductions'),
            'other_deductions' => $entries->sum('other_deductions'),
            'net' => $entries->sum('net_amount'),
        ];

        return view('superadmin.accounts.payroll.index', [
            'availableCycles' => $availableCycles,
            'selectedCycle' => $selectedCycle,
            'entries' => $entries,
            'entryStatusLabels' => PayrollEntry::statusOptions(),
            'totals' => $totals,
        ]);
    }

    public function downloadBankCsv(PayrollCycle $cycle): StreamedResponse
    {
        abort_if($cycle->status !== PayrollCycle::STATUS_SENT, 404);

        $cycle->load(['entries.employee' => function ($query) {
            $query->select(
                'id',
                'first_name',
                'last_name',
                'bank_account_number',
                'bank_ifsc',
                'bank_name'
            );
        }]);

        $fileName = sprintf(
            'payroll-bank-%s-%s.csv',
            $cycle->year,
            str_pad((string) $cycle->month, 2, '0', STR_PAD_LEFT)
        );

        $headers = ['Content-Type' => 'text/csv'];

        return response()->streamDownload(function () use ($cycle) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Employee Name',
                'Account Number',
                'IFSC Code',
                'Bank Name',
                'Net Pay Amount',
            ]);

            $totalNet = 0;

            foreach ($cycle->entries as $entry) {
                $employee = $entry->employee;
                $netAmount = round($entry->net_amount, 2);
                $totalNet += $netAmount;

                fputcsv($handle, [
                    $employee ? ($employee->first_name.' '.$employee->last_name) : 'Employee',
                    $employee?->bank_account_number ?? '',
                    $employee?->bank_ifsc ?? '',
                    $employee?->bank_name ?? '',
                    number_format($netAmount, 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total', '', '', '', number_format($totalNet, 2, '.', '')]);

            fclose($handle);
        }, $fileName, $headers);
    }
}
