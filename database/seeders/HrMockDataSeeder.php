<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('employee_code')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        $employeeRole = Role::firstOrCreate(
            ['role_name' => 'Lab Analyst'],
            ['created_by' => null, 'updated_by' => null]
        );

        $approverRole = Role::firstOrCreate(
            ['role_name' => 'HR Manager'],
            ['created_by' => null, 'updated_by' => null]
        );

        $approverUser = User::updateOrCreate(
            ['user_code' => 'HR-APPROVER'],
            [
                'name' => 'HR Approver',
                'password' => Hash::make('password123'),
                'role_id' => $approverRole->id,
            ]
        );

        if (!$approverUser->email) {
            $approverUser->forceFill(['email' => 'hr.approver@example.com'])->save();
        }

        $approverUserId = $approverUser->getKey();

        $monthAnchor = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthAnchor->copy()->endOfMonth();
        $maxDay = min(30, $monthEnd->day);

        foreach ($employees as $employee) {
            $patternSeed = crc32($employee->employee_code ?? (string) $employee->getKey());
            $pattern = [
                'work_from_home' => $this->generatePatternDays($patternSeed, $maxDay, 2, 4),
                'overtime' => $this->generatePatternDays($patternSeed + 17, $maxDay, 2, 3),
                'short' => $this->generatePatternDays($patternSeed + 33, $maxDay, 1, 2),
                'absent' => $this->generatePatternDays($patternSeed + 49, $maxDay, 0, 1),
            ];

            $user = $employee->user;
            if (!$user) {
                $user = User::updateOrCreate(
                    ['user_code' => 'EMP-'.$employee->employee_code],
                    [
                        'name' => trim($employee->first_name.' '.$employee->last_name),
                        'password' => Hash::make('password123'),
                        'role_id' => $employeeRole->id,
                    ]
                );

                if (!$user->email) {
                    $mail = Str::lower(Str::slug($employee->first_name.' '.$employee->last_name, '.')).'@example.com';
                    $user->forceFill(['email' => $mail])->save();
                }

                $employee->user()->associate($user);
                $employee->save();
            }

            $employeeLeaves = $this->buildLeavePlan($employee, $patternSeed, $maxDay, $monthAnchor);

            $leaveDates = [];
            $partialLeave = [];

            foreach ($employeeLeaves as $plan) {
                if ($plan['start_offset'] >= $maxDay || $plan['end_offset'] >= $maxDay) {
                    continue;
                }

                $from = $monthAnchor->copy()->addDays($plan['start_offset']);
                $to = $monthAnchor->copy()->addDays($plan['end_offset']);

                if ($from->greaterThan($monthAnchor->copy()->addDays($maxDay - 1))) {
                    continue;
                }

                $leaveData = [
                    'user_id' => $user->getKey(),
                    'employee_name' => trim($employee->first_name.' '.$employee->last_name),
                    'leave_type' => $plan['leave_type'],
                    'from_date' => $from->toDateString(),
                    'to_date' => $to->toDateString(),
                    'day_type' => $plan['day_type'],
                    'reason' => $plan['reason'],
                    'status' => $plan['status'],
                    'approved_by' => $approverUserId,
                    'approved_at' => $from->copy()->subDay(),
                    'admin_comments' => $plan['comments'],
                ];

                if ($plan['day_type'] === 'Hours') {
                    $leaveData['days_hours'] = (int) ($plan['hours'] ?? 0);
                    $partialLeave[$from->toDateString()] = $leaveData['days_hours'];
                } else {
                    $spanDays = $from->diffInDays($to) + 1;
                    $leaveData['days_hours'] = $spanDays;

                    $cursor = $from->copy();
                    while ($cursor->lte($to)) {
                        $leaveDates[] = $cursor->toDateString();
                        $cursor->addDay();
                    }
                }

                Leave::updateOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'from_date' => $leaveData['from_date'],
                        'to_date' => $leaveData['to_date'],
                        'leave_type' => $leaveData['leave_type'],
                    ],
                    $leaveData
                );
            }

            $current = $monthAnchor->copy();
            for ($day = 0; $day < $maxDay; $day++, $current->addDay()) {
                $dateString = $current->toDateString();

                if ($current->month !== $monthAnchor->month) {
                    break;
                }

                $status = 'present';

                if (in_array($day, $pattern['absent'] ?? [], true)) {
                    $status = 'absent';
                }

                if ($current->isWeekend()) {
                    $status = 'weekend';
                }

                if (in_array($dateString, $leaveDates, true)) {
                    $status = 'on_leave';
                } elseif (array_key_exists($dateString, $partialLeave)) {
                    $status = 'half_day';
                } elseif (in_array($day, $pattern['work_from_home'], true)) {
                    $status = 'work_from_home';
                }

                $checkIn = null;
                $checkOut = null;

                if (in_array($status, ['present', 'work_from_home'], true)) {
                    $checkIn = $current->copy()->setTime(9, 45);
                    $checkOut = $current->copy()->setTime(18, 15);

                    if (in_array($day, $pattern['overtime'], true)) {
                        $checkOut = $current->copy()->setTime(20, 5);
                    }

                    if (in_array($day, $pattern['short'], true)) {
                        $checkOut = $current->copy()->setTime(16, 30);
                    }
                } elseif ($status === 'half_day') {
                    $checkIn = $current->copy()->setTime(9, 45);
                    $checkOut = $current->copy()->setTime(14, 0);
                }

                $record = AttendanceRecord::where('employee_id', $employee->getKey())
                    ->whereDate('attendance_date', $dateString)
                    ->first();

                if ($record) {
                    continue;
                }

                AttendanceRecord::create([
                    'employee_id' => $employee->getKey(),
                    'attendance_date' => $dateString,
                    'status' => $status,
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'source' => 'mock-seeder',
                    'notes' => $status === 'on_leave' ? 'On approved leave' : 'Mock attendance entry',
                    'metadata' => [
                        'generated_by' => 'HrMockDataSeeder',
                        'month' => $monthAnchor->format('Y-m'),
                    ],
                ]);
            }
        }
    }

    protected function generatePatternDays(int $seed, int $maxDay, int $min, int $max): array
    {
        $count = max($min, min($max, $maxDay > 0 ? ($seed % ($max + 1)) : 0));
        $days = [];
        for ($i = 0; $i < $count; $i++) {
            $days[] = ($seed + ($i * 7)) % max(1, $maxDay);
        }

        return array_values(array_unique(array_filter($days, static fn ($day) => $day >= 0 && $day < $maxDay)));
    }

    protected function buildLeavePlan(Employee $employee, int $seed, int $maxDay, Carbon $anchor): array
    {
        $plans = [];

        if ($maxDay <= 1) {
            return $plans;
        }

        $firstStart = ($seed % max(2, $maxDay - 2)) + 2;
        if ($firstStart < $maxDay - 1) {
            $plans[] = [
                'start_offset' => $firstStart,
                'end_offset' => $firstStart + 1,
                'leave_type' => 'Annual Leave',
                'day_type' => 'Full Day',
                'reason' => 'Planned personal time off',
                'status' => 'Approved',
                'comments' => 'Auto-generated mock record',
            ];
        }

        $hourOffset = ($seed % max(3, $maxDay - 5)) + 5;
        if ($hourOffset < $maxDay) {
            $plans[] = [
                'start_offset' => $hourOffset,
                'end_offset' => $hourOffset,
                'leave_type' => 'Casual Leave',
                'day_type' => 'Hours',
                'hours' => 3 + ($seed % 4),
                'reason' => 'Errand and short leave',
                'status' => 'Approved',
                'comments' => 'Auto-generated mock record',
            ];
        }

        if (($seed % 5) === 0 && $firstStart + 5 < $maxDay) {
            $plans[] = [
                'start_offset' => $firstStart + 5,
                'end_offset' => $firstStart + 5,
                'leave_type' => 'Sick Leave',
                'day_type' => 'Half Day',
                'reason' => 'Migraine treatment',
                'status' => 'Approved',
                'comments' => 'Auto-generated mock record',
            ];
        }

        return $plans;
    }
}
