<?php

namespace App\Http\Controllers\SuperAdmin\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    protected const STANDARD_FULL_DAY_MINUTES = 8 * 60;
    protected const STANDARD_HALF_DAY_MINUTES = 4 * 60;
    protected const DEFAULT_MONTHLY_MINUTES = 8 * 60 * 22; // 22 working days assumption
    protected const MAX_TRACKABLE_MINUTES_PER_DAY = 16 * 60;

    public function index(Request $request): View
    {
        [$month, $year] = $this->resolvePeriod($request);

        /** @var PayrollCycle $cycle */
        $cycle = PayrollCycle::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['status' => PayrollCycle::STATUS_DRAFT]
        );

        $this->syncCycleEntries($cycle);

        $cycle->load(['entries.employee' => function ($query) {
            $query->select(
                'id',
                'first_name',
                'last_name',
                'department',
                'employment_status',
                'user_id',
                'ctc'
            );
        }]);

        $entries = $cycle->entries->sortBy(fn (PayrollEntry $entry) => $entry->employee->first_name ?? '');

        $cycleTotals = [
            'gross' => $entries->sum('gross_amount'),
            'leave_deductions' => $entries->sum('leave_deductions'),
            'other_deductions' => $entries->sum('other_deductions'),
            'net' => $entries->sum('net_amount'),
            'paid' => $entries->where('status', PayrollEntry::STATUS_PAID)->sum('net_amount'),
        ];

        $stats = $this->buildDashboardStats($month, $year, $cycle, $entries);

        $recentChanges = $cycle->entries()
            ->with('employee:id,first_name,last_name')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $availableCycles = PayrollCycle::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('superadmin.hr.payroll.index', [
            'cycle' => $cycle,
            'entries' => $entries,
            'stats' => $stats,
            'cycleTotals' => $cycleTotals,
            'recentChanges' => $recentChanges,
            'availableCycles' => $availableCycles,
            'selectedPeriod' => sprintf('%04d-%02d', $year, $month),
            'cycleStatusOptions' => PayrollCycle::statusOptions(),
            'entryStatusOptions' => PayrollEntry::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        [$year, $month] = array_map('intval', explode('-', $data['period']));

        $cycle = PayrollCycle::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['status' => PayrollCycle::STATUS_DRAFT]
        );

        $this->syncCycleEntries($cycle);

        return Redirect::route('superadmin.hr.payroll.index', [
            'month' => $month,
            'year' => $year,
        ])->with('success', 'Payroll cycle ready. You can now review and export the sheet.');
    }

    public function refresh(PayrollCycle $cycle): RedirectResponse
    {
        $this->syncCycleEntries($cycle, true);

        return Redirect::back()->with('success', 'Payroll entries refreshed from employee data.');
    }

    public function updateStatus(Request $request, PayrollCycle $cycle): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(PayrollCycle::statusOptions()))],
            'notes' => ['nullable', 'string'],
        ]);

        $cycle->fill($data);

        if ($data['status'] === PayrollCycle::STATUS_READY && $cycle->processed_at === null) {
            $cycle->processed_at = now();
        }

        if (in_array($data['status'], [PayrollCycle::STATUS_SENT, PayrollCycle::STATUS_PAID], true) && $cycle->locked_at === null) {
            $cycle->locked_at = now();
        }

        $cycle->save();

        return Redirect::back()->with('success', 'Payroll cycle status updated.');
    }

    public function updateEntry(Request $request, PayrollEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_keys(PayrollEntry::statusOptions()))],
            'remarks' => ['nullable', 'string', 'max:500'],
            'payout_due_date' => ['nullable', 'date'],
            'payout_released_at' => ['nullable', 'date'],
        ]);

        $entry->other_deductions = $data['other_deductions'] ?? 0;
        $entry->status = $data['status'];
        $entry->remarks = $data['remarks'] ?? null;
        $entry->payout_due_date = $data['payout_due_date'] ?? null;
        $entry->payout_released_at = isset($data['payout_released_at'])
            ? Carbon::parse($data['payout_released_at'])->endOfDay()
            : null;

        $entry->net_amount = max($entry->gross_amount - $entry->leave_deductions - $entry->other_deductions, 0);
        $entry->save();

        $this->updateCycleTotals($entry->cycle);

        return Redirect::back()->with('success', 'Payroll entry updated.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_ids' => ['required', 'array', 'min:1'],
            'entry_ids.*' => ['integer', 'distinct', 'exists:payroll_entries,id'],
            'status' => ['required', 'in:'.implode(',', array_keys(PayrollEntry::statusOptions()))],
        ]);

        $entries = PayrollEntry::query()
            ->whereIn('id', $data['entry_ids'])
            ->get();

        if ($entries->isEmpty()) {
            return Redirect::back()->with('error', 'No payroll entries selected.');
        }

        $cycleIds = $entries->pluck('payroll_cycle_id')->unique()->all();

        DB::transaction(function () use ($entries, $data) {
            foreach ($entries as $entry) {
                $entry->status = $data['status'];
                $entry->save();
            }
        });

        PayrollCycle::query()
            ->whereIn('id', $cycleIds)
            ->get()
            ->each(fn (PayrollCycle $cycle) => $this->updateCycleTotals($cycle));

        return Redirect::back()->with('success', sprintf('Status updated for %d payroll entr%s.', $entries->count(), $entries->count() === 1 ? 'y' : 'ies'));
    }

    public function downloadBankCsv(PayrollCycle $cycle): StreamedResponse
    {
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

        $fileName = sprintf('payroll-bank-%s-%s.csv', $cycle->year, str_pad((string) $cycle->month, 2, '0', STR_PAD_LEFT));

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
                    $employee?->bank_account_number ?? '—',
                    $employee?->bank_ifsc ?? '—',
                    $employee?->bank_name ?? '—',
                    number_format($netAmount, 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total', '', '', '', number_format($totalNet, 2, '.', '')]);

            fclose($handle);
        }, $fileName, $headers);
    }

    public function download(PayrollCycle $cycle): StreamedResponse
    {
        $cycle->load(['entries.employee' => fn ($query) => $query->select('id', 'first_name', 'last_name', 'department', 'ctc')]);

        $fileName = sprintf('payroll-%s-%s.csv', $cycle->year, str_pad((string) $cycle->month, 2, '0', STR_PAD_LEFT));

        $headers = ['Content-Type' => 'text/csv'];

        return response()->streamDownload(function () use ($cycle) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Employee Name',
                'Department',
                'Base Gross',
                'Overtime Amount',
                'Leave Hours Taken',
                'Leave Hours Charged',
                'Leave Deduction Amount',
                'Other Deductions',
                'Total Deductions',
                'Net Amount',
                'Worked Hours',
                'Expected Hours',
                'Overtime Hours',
                'Status',
            ]);

            foreach ($cycle->entries as $entry) {
                $employee = $entry->employee;

                $meta = is_array($entry->meta) ? $entry->meta : [];
                $attendanceMeta = $meta['attendance'] ?? [];
                $leaveMeta = $meta['leave_policy'] ?? [];

                $baseGross = $attendanceMeta['base_gross'] ?? $entry->gross_amount;
                $overtimeAmount = max($attendanceMeta['overtime_amount'] ?? 0, 0);
                $leaveHours = $leaveMeta['total_hours'] ?? 0;
                $leaveChargedHours = $leaveMeta['deductible_hours'] ?? 0;
                $leaveDeductionAmount = round($entry->leave_deductions, 2);
                $otherDeductions = round($entry->other_deductions, 2);
                $shortfallAmount = round(max($attendanceMeta['shortfall_amount'] ?? 0, 0), 2);
                $workedHours = $attendanceMeta['worked_hours'] ?? 0;
                $expectedHours = $attendanceMeta['expected_hours'] ?? 0;
                $overtimeHours = $attendanceMeta['overtime_hours'] ?? (($attendanceMeta['overtime_minutes'] ?? 0) / 60);
                $totalDeductions = round($leaveDeductionAmount + $otherDeductions, 2);

                fputcsv($handle, [
                    $employee ? ($employee->first_name.' '.$employee->last_name) : 'Employee',
                    $employee?->department ?? '—',
                    number_format($baseGross, 2, '.', ''),
                    number_format($overtimeAmount, 2, '.', ''),
                    number_format($leaveHours, 2, '.', ''),
                    number_format($leaveChargedHours, 2, '.', ''),
                    number_format($leaveDeductionAmount, 2, '.', ''),
                    number_format($otherDeductions, 2, '.', ''),
                    number_format($totalDeductions, 2, '.', ''),
                    number_format($entry->net_amount, 2, '.', ''),
                    number_format($workedHours, 2, '.', ''),
                    number_format($expectedHours, 2, '.', ''),
                    number_format($overtimeHours, 2, '.', ''),
                    PayrollEntry::statusOptions()[$entry->status] ?? ucfirst($entry->status),
                ]);
            }

            fclose($handle);
        }, $fileName, $headers);
    }

    protected function resolvePeriod(Request $request): array
    {
        if ($request->filled('period')) {
            try {
                $period = Carbon::createFromFormat('Y-m', $request->string('period')->value());
                return [(int) $period->month, (int) $period->year];
            } catch (\Throwable $e) {
                // Fall back to current month if parsing fails
            }
        }

        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        return [$month, $year];
    }

    protected function syncCycleEntries(PayrollCycle $cycle, bool $forceRefresh = false): void
    {
        $startOfMonth = Carbon::create($cycle->year, $cycle->month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $activeEmployees = Employee::query()
            ->where('employment_status', 'active')
            ->get();

        DB::transaction(function () use ($cycle, $activeEmployees, $startOfMonth, $endOfMonth, $forceRefresh) {
            foreach ($activeEmployees as $employee) {
                /** @var PayrollEntry $entry */
                $entry = PayrollEntry::firstOrNew([
                    'payroll_cycle_id' => $cycle->id,
                    'employee_id' => $employee->id,
                ]);

                if ($entry->other_deductions === null) {
                    $entry->other_deductions = 0;
                }

                $monthlyGross = $this->calculateMonthlyGross($employee);
                $attendanceAdjustments = $this->calculateAttendanceAdjustments($employee, $startOfMonth, $endOfMonth, $monthlyGross);
                $leaveBreakdown = null;
                $leaveDeduction = $this->calculateLeaveDeduction($employee, $startOfMonth, $endOfMonth, $monthlyGross, $leaveBreakdown);

                $entry->gross_amount = $monthlyGross;
                $entry->leave_deductions = $leaveDeduction;

                if (!$entry->exists) {
                    $entry->status = PayrollEntry::STATUS_PENDING;
                }

                $entry->net_amount = max($entry->gross_amount - $entry->leave_deductions - $entry->other_deductions, 0);

                $meta = is_array($entry->meta) ? $entry->meta : [];
                $meta['attendance'] = [
                    'base_gross' => round($monthlyGross, 2),
                    'overtime_amount' => round($attendanceAdjustments['overtime_amount'], 2),
                    'overtime_hours' => round($attendanceAdjustments['overtime_minutes'] / 60, 2),
                    'shortfall_amount' => round($attendanceAdjustments['shortfall_amount'], 2),
                    'shortfall_hours' => round($attendanceAdjustments['shortfall_minutes'] / 60, 2),
                    'worked_hours' => round($attendanceAdjustments['worked_minutes'] / 60, 2),
                    'expected_hours' => round($attendanceAdjustments['expected_minutes'] / 60, 2),
                    'hourly_rate' => round($attendanceAdjustments['hourly_rate'], 2),
                    'records' => $attendanceAdjustments['record_count'],
                    'synced_at' => now()->toIso8601String(),
                ];

                if (is_array($leaveBreakdown)) {
                    $meta['leave_policy'] = $leaveBreakdown;
                }
                $entry->meta = $meta;

                $entry->save();
            }

            PayrollEntry::query()
                ->where('payroll_cycle_id', $cycle->id)
                ->whereNotIn('employee_id', $activeEmployees->pluck('id'))
                ->delete();
        });

        $this->updateCycleTotals($cycle->refresh());
    }

    protected function calculateAttendanceAdjustments(Employee $employee, Carbon $start, Carbon $end, float $monthlyGross): array
    {
        $records = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $expectedMinutes = 0;
        $workedMinutes = 0;
        $overtimeMinutes = 0;
        $shortfallMinutes = 0;

        foreach ($records as $record) {
            $dailyExpected = $this->expectedMinutesForStatus($record->status);
            $expectedMinutes += $dailyExpected;

            $dailyWorked = $this->workedMinutesForRecord($record, $dailyExpected);
            $workedMinutes += $dailyWorked;

            if ($dailyExpected > 0) {
                if ($dailyWorked > $dailyExpected) {
                    $overtimeMinutes += $dailyWorked - $dailyExpected;
                } elseif ($dailyWorked < $dailyExpected) {
                    $shortfallMinutes += $dailyExpected - $dailyWorked;
                }
            } elseif ($dailyWorked > 0) {
                $overtimeMinutes += $dailyWorked;
            }
        }

        $denominatorHours = $expectedMinutes > 0
            ? $expectedMinutes / 60
            : self::DEFAULT_MONTHLY_MINUTES / 60;

        $hourlyRate = $denominatorHours > 0 ? $monthlyGross / $denominatorHours : 0.0;

        $overtimeAmount = round(($overtimeMinutes / 60) * $hourlyRate, 2);
        $shortfallAmount = round(($shortfallMinutes / 60) * $hourlyRate, 2);

        return [
            'expected_minutes' => $expectedMinutes,
            'worked_minutes' => $workedMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'shortfall_minutes' => $shortfallMinutes,
            'overtime_amount' => $overtimeAmount,
            'shortfall_amount' => $shortfallAmount,
            'hourly_rate' => $hourlyRate,
            'record_count' => $records->count(),
        ];
    }

    protected function expectedMinutesForStatus(?string $status): int
    {
        return match ($status) {
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_WORK_FROM_HOME => self::STANDARD_FULL_DAY_MINUTES,
            AttendanceRecord::STATUS_HALF_DAY => self::STANDARD_HALF_DAY_MINUTES,
            default => 0,
        };
    }

    protected function workedMinutesForRecord(AttendanceRecord $record, int $expectedMinutes): int
    {
        $checkIn = $record->check_in_at;
        $checkOut = $record->check_out_at;

        if ($checkIn && $checkOut && $checkOut->greaterThan($checkIn)) {
            $minutes = $checkOut->diffInMinutes($checkIn);
            $minutes = max($minutes, 0);

            return (int) min($minutes, self::MAX_TRACKABLE_MINUTES_PER_DAY);
        }

        if ($expectedMinutes > 0 && in_array($record->status, [
            AttendanceRecord::STATUS_PRESENT,
            AttendanceRecord::STATUS_WORK_FROM_HOME,
            AttendanceRecord::STATUS_HALF_DAY,
        ], true)) {
            return $expectedMinutes;
        }

        return 0;
    }

    protected function calculateMonthlyGross(Employee $employee): float
    {
        $ctc = $employee->ctc ?? 0;

        if ($ctc <= 0) {
            return 0;
        }

        return round($ctc / 12, 2);
    }

    protected function calculateLeaveDeduction(Employee $employee, Carbon $start, Carbon $end, float $monthlyGross, ?array &$breakdown = null): float
    {
        $breakdownRequested = func_num_args() >= 5;

        if (!$employee->user_id || $monthlyGross <= 0) {
            if ($breakdownRequested) {
                $breakdown = [
                    'total_hours' => 0.0,
                    'deductible_hours' => 0.0,
                    'free_hours' => 12.0,
                    'hourly_rate' => 0.0,
                    'deduction_amount' => 0.0,
                ];
            }
            return 0.0;
        }

        $leaves = Leave::query()
            ->where('user_id', $employee->user_id)
            ->where('status', 'Approved')
            ->whereDate('from_date', '<=', $end)
            ->whereDate('to_date', '>=', $start)
            ->get();

        if ($leaves->isEmpty()) {
            if ($breakdownRequested) {
                $breakdown = [
                    'total_hours' => 0.0,
                    'deductible_hours' => 0.0,
                    'free_hours' => 12.0,
                    'hourly_rate' => round(($monthlyGross / 30) / 8, 2),
                    'deduction_amount' => 0.0,
                ];
            }
            return 0.0;
        }

        $perDayRate = $monthlyGross / 30;
        $hourlyRate = $perDayRate / 8;

        $totalLeaveHours = 0.0;

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->from_date)->max($start);
            $leaveEnd = Carbon::parse($leave->to_date)->min($end);

            if ($leaveStart->gt($leaveEnd)) {
                continue;
            }

            $spanDays = $leaveStart->diffInDays($leaveEnd) + 1;

            if ($leave->day_type === 'Hours') {
                $hours = max((float) ($leave->days_hours ?? 0), 0);
                $totalLeaveHours += $hours;
                continue;
            }

            if ($leave->day_type === 'Half Day') {
                $totalLeaveHours += $spanDays * 4;
            } else {
                $totalLeaveHours += $spanDays * 8;
            }
        }

        $freeHours = 12.0;
        $deductibleHours = max($totalLeaveHours - $freeHours, 0.0);
        $deductionAmount = round($hourlyRate * $deductibleHours, 2);

        if ($breakdownRequested) {
            $breakdown = [
                'total_hours' => round($totalLeaveHours, 2),
                'deductible_hours' => round($deductibleHours, 2),
                'free_hours' => $freeHours,
                'hourly_rate' => round($hourlyRate, 2),
                'deduction_amount' => $deductionAmount,
            ];
        }

        return $deductionAmount;
    }

    protected function updateCycleTotals(PayrollCycle $cycle): void
    {
        $totals = $cycle->entries()
            ->selectRaw('SUM(gross_amount) as gross_total, SUM(leave_deductions + other_deductions) as deduction_total, SUM(net_amount) as net_total')
            ->first();

        $cycle->gross_total = (float) ($totals->gross_total ?? 0);
        $cycle->deduction_total = (float) ($totals->deduction_total ?? 0);
        $cycle->net_total = (float) ($totals->net_total ?? 0);
        $cycle->save();
    }

    protected function buildDashboardStats(int $month, int $year, PayrollCycle $cycle, EloquentCollection $entries): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::create($year, $month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $activeEmployees = $this->safeCount(fn () => Employee::query()->where('employment_status', 'active')->count());
        $pendingLeaveApprovals = $this->safeCount(fn () => Leave::query()->where('status', 'Applied')->count());
        $approvedLeavesThisMonth = $this->safeCount(fn () => Leave::query()->where('status', 'Approved')->whereBetween('from_date', [$startOfMonth, $endOfMonth])->count());
        $employeesMissingBankDetails = $this->safeCount(fn () => Employee::query()
            ->where('employment_status', 'active')
            ->where(static function ($query) {
                $query->whereNull('bank_account_number')
                    ->orWhere('bank_account_number', '')
                    ->orWhereNull('bank_ifsc')
                    ->orWhere('bank_ifsc', '');
            })
            ->count());

        $upcomingAnniversaries = $this->safeGet(fn () => Employee::query()
            ->whereNotNull('date_of_joining')
            ->get(['id', 'first_name', 'last_name', 'date_of_joining']))
            ->filter(fn (Employee $employee) => $employee->date_of_joining !== null)
            ->sortBy(function (Employee $employee) use ($today) {
                $anniversary = $employee->date_of_joining->setYear($today->year);
                if ($anniversary->isPast()) {
                    $anniversary = $anniversary->copy()->addYear();
                }

                return $anniversary->diffInDays($today);
            })
            ->take(5);

        $recentBankingIssues = $this->safeGet(fn () => Employee::query()
            ->where('employment_status', 'active')
            ->where(static function ($query) {
                $query->whereNull('bank_account_number')
                    ->orWhere('bank_account_number', '')
                    ->orWhereNull('bank_ifsc')
                    ->orWhere('bank_ifsc', '');
            })
            ->orderBy('first_name')
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'department', 'bank_account_number', 'bank_ifsc']));

        $entryStatusBreakdown = [
            'pending' => $entries->where('status', PayrollEntry::STATUS_PENDING)->count(),
            'reviewed' => $entries->where('status', PayrollEntry::STATUS_REVIEWED)->count(),
            'approved' => $entries->where('status', PayrollEntry::STATUS_APPROVED)->count(),
            'paid' => $entries->where('status', PayrollEntry::STATUS_PAID)->count(),
        ];

        return [
            'activeEmployees' => $activeEmployees,
            'pendingLeaveApprovals' => $pendingLeaveApprovals,
            'approvedLeavesThisMonth' => $approvedLeavesThisMonth,
            'employeesMissingBankDetails' => $employeesMissingBankDetails,
            'readyToProcess' => max($entries->count() - $entryStatusBreakdown['pending'], 0),
            'entryStatusBreakdown' => $entryStatusBreakdown,
            'upcomingAnniversaries' => $upcomingAnniversaries,
            'recentBankingIssues' => $recentBankingIssues,
        ];
    }

    protected function safeCount(callable $callback): int
    {
        try {
            return (int) $callback();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function safeGet(callable $callback): Collection
    {
        try {
            $result = $callback();
            return $result instanceof Collection ? $result : collect($result);
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
