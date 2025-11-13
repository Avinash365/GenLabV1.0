<?php

namespace App\Http\Controllers\SuperAdmin\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $activeEmployees = $this->safeCount(fn () => Employee::query()
            ->where('employment_status', 'active')
            ->count());

        $employeesOnApprovedLeaveToday = $this->safeCount(fn () => Leave::query()
            ->where('status', 'Approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->count());

        $pendingLeaveRequests = $this->safeGet(fn () => Leave::query()
            ->with('user')
            ->where('status', 'Applied')
            ->orderBy('from_date')
            ->limit(10)
            ->get());

        $departmentOptions = $this->safeGet(fn () => Employee::query()
            ->select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department'));

        $employeeOptions = $this->safeGet(fn () => Employee::query()
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'employee_code', 'first_name', 'last_name', 'department']));

        $recentAttendanceRecords = $this->safeGet(fn () => AttendanceRecord::query()
            ->with('employee')
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get());

        $metrics = [
            'present' => max($activeEmployees - $employeesOnApprovedLeaveToday, 0),
            'onLeave' => $employeesOnApprovedLeaveToday,
            'late' => 0,
            'missingLogs' => 0,
        ];

        return view('superadmin.hr.attendance.index', [
            'todayLabel' => $today->format('d M Y'),
            'defaultAttendanceDate' => $today->toDateString(),
            'metrics' => $metrics,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'departmentOptions' => $departmentOptions,
            'employeeOptions' => $employeeOptions,
            'attendanceStatusLabels' => AttendanceRecord::statusLabels(),
            'recentAttendanceRecords' => $recentAttendanceRecords,
        ]);
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validateWithBag('manualAttendance', [
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(AttendanceRecord::statusValues())],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after_or_equal:check_in'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendanceDate = Carbon::parse($validated['attendance_date'])->startOfDay();
        $employeeId = (int) $validated['employee_id'];

        $checkInAt = $this->combineDateAndTime($attendanceDate, $validated['check_in'] ?? null);
        $checkOutAt = $this->combineDateAndTime($attendanceDate, $validated['check_out'] ?? null);

        $creatorId = $this->resolveCreatorId();

        try {
            $record = DB::transaction(function () use ($employeeId, $attendanceDate, $validated, $checkInAt, $checkOutAt, $creatorId) {
                $attributes = [
                    'status' => $validated['status'],
                    'check_in_at' => $checkInAt,
                    'check_out_at' => $checkOutAt,
                    'notes' => $validated['notes'] ?? null,
                    'source' => AttendanceRecord::SOURCE_MANUAL,
                ];

                if ($creatorId) {
                    $attributes['created_by'] = $creatorId;
                }

                $attendance = AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'attendance_date' => $attendanceDate->toDateString(),
                    ],
                    $attributes
                );

                if ($attendance->source !== AttendanceRecord::SOURCE_MANUAL) {
                    $attendance->source = AttendanceRecord::SOURCE_MANUAL;
                    $attendance->save();
                }

                return $attendance;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to save manual attendance record', [
                'employee_id' => $employeeId,
                'attendance_date' => $attendanceDate->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('superadmin.hr.attendance.index')
                ->withErrors(['general' => 'Unable to save attendance. Please try again.'], 'manualAttendance')
                ->withInput();
        }

        $employee = Employee::find($employeeId);
        $employeeName = $employee?->full_name ?? 'employee';
        $formattedDate = $attendanceDate->format('d M Y');

        $message = $record->wasRecentlyCreated
            ? "Attendance recorded for {$employeeName} on {$formattedDate}."
            : "Attendance updated for {$employeeName} on {$formattedDate}.";

        return redirect()
            ->route('superadmin.hr.attendance.index')
            ->with('manualAttendanceSuccess', $message);
    }

    public function importBiometric(Request $request)
    {
        $validated = $request->validateWithBag('biometricImport', [
            'biometric_file' => ['required', 'file', 'mimes:csv,txt'],
            'default_status' => ['nullable', Rule::in(AttendanceRecord::statusValues())],
        ]);

        $defaultStatus = $validated['default_status'] ?? AttendanceRecord::STATUS_PRESENT;
        $file = $validated['biometric_file'];
        $path = $file->getRealPath();
    $creatorId = $this->resolveCreatorId();

        if (!$path || !is_readable($path)) {
            return redirect()
                ->route('superadmin.hr.attendance.index')
                ->withErrors(['biometric_file' => 'Unable to read the uploaded file.'], 'biometricImport');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return redirect()
                ->route('superadmin.hr.attendance.index')
                ->withErrors(['biometric_file' => 'Unable to process the uploaded file.'], 'biometricImport');
        }

        $header = null;
        $processed = $created = $updated = $skippedManual = 0;
        $missingEmployees = [];
        $invalidRows = [];
        $lineNumber = 0;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if ($lineNumber === 1) {
                    $header = array_map(fn ($value) => Str::slug((string) $value, '_'), $this->sanitizeRowValues($row));
                    continue;
                }

                $rowValues = $this->sanitizeRowValues($row);

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                if (!$header) {
                    $invalidRows[] = ['line' => $lineNumber, 'reason' => 'Missing header row.'];
                    continue;
                }

                if (count($rowValues) < count($header)) {
                    $rowValues = array_pad($rowValues, count($header), null);
                }

                $rowAssoc = array_combine($header, array_slice($rowValues, 0, count($header)));

                $employeeCode = $rowAssoc['employee_code'] ?? $rowAssoc['code'] ?? null;
                $attendanceDateInput = $rowAssoc['attendance_date'] ?? $rowAssoc['date'] ?? null;

                if (!$employeeCode || !$attendanceDateInput) {
                    $invalidRows[] = ['line' => $lineNumber, 'reason' => 'Missing employee code or date.'];
                    continue;
                }

                $attendanceDate = $this->parseDate($attendanceDateInput);

                if (!$attendanceDate) {
                    $invalidRows[] = ['line' => $lineNumber, 'reason' => 'Unable to parse attendance date.'];
                    continue;
                }

                $employee = Employee::query()
                    ->where('employee_code', $employeeCode)
                    ->first();

                if (!$employee) {
                    $missingEmployees[] = $employeeCode;
                    continue;
                }

                $checkInRaw = $rowAssoc['check_in'] ?? $rowAssoc['checkin'] ?? $rowAssoc['in_time'] ?? null;
                $checkOutRaw = $rowAssoc['check_out'] ?? $rowAssoc['checkout'] ?? $rowAssoc['out_time'] ?? null;
                $statusRaw = $rowAssoc['status'] ?? $rowAssoc['attendance_status'] ?? null;

                $status = $this->normalizeStatus($statusRaw, $defaultStatus);
                $checkInAt = $this->combineDateAndTime($attendanceDate, $checkInRaw);
                $checkOutAt = $this->combineDateAndTime($attendanceDate, $checkOutRaw);

                try {
                    $attendance = AttendanceRecord::firstOrNew([
                        'employee_id' => $employee->id,
                        'attendance_date' => $attendanceDate->toDateString(),
                    ]);

                    if ($attendance->exists && $attendance->source === AttendanceRecord::SOURCE_MANUAL) {
                        $skippedManual++;
                        continue;
                    }

                    $attendance->status = $status;
                    $attendance->check_in_at = $checkInAt;
                    $attendance->check_out_at = $checkOutAt;
                    $attendance->source = AttendanceRecord::SOURCE_BIOMETRIC;
                    $attendance->notes = $rowAssoc['notes'] ?? $attendance->notes;

                    $metadata = $attendance->metadata ?? [];
                    $metadata['biometric_payload'] = $rowAssoc;
                    $metadata['imported_at'] = now()->toIso8601String();
                    $attendance->metadata = $metadata;

                    if ($creatorId && !$attendance->created_by) {
                        $attendance->created_by = $creatorId;
                    }

                    $wasExisting = $attendance->exists;
                    $attendance->save();

                    $processed++;

                    if ($attendance->wasRecentlyCreated && !$wasExisting) {
                        $created++;
                    } elseif ($wasExisting) {
                        $updated++;
                    } else {
                        $created++;
                    }
                } catch (\Throwable $rowException) {
                    Log::error('Failed to import biometric attendance row', [
                        'line' => $lineNumber,
                        'row' => $rowAssoc,
                        'error' => $rowException->getMessage(),
                    ]);

                    $invalidRows[] = ['line' => $lineNumber, 'reason' => 'Unexpected error while saving row.'];
                }
            }
        } finally {
            fclose($handle);
        }

        $missingEmployees = array_values(array_unique(array_filter($missingEmployees)));
        $invalidRows = array_slice($invalidRows, 0, 20);

        $summary = [
            'processed_rows' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped_manual' => $skippedManual,
            'missing_employees' => array_slice($missingEmployees, 0, 20),
            'invalid_rows' => $invalidRows,
        ];

        if (count($missingEmployees) > count($summary['missing_employees'])) {
            $summary['missing_employees_more'] = count($missingEmployees) - count($summary['missing_employees']);
        }

        $message = $processed > 0
            ? "Processed {$processed} attendance rows from biometric upload."
            : 'No attendance rows were processed from the biometric upload.';

        return redirect()
            ->route('superadmin.hr.attendance.index')
            ->with('biometricImportSuccess', $message)
            ->with('biometricImportSummary', $summary);
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

    protected function resolveCreatorId(): ?int
    {
        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            return $user->getKey();
        }

        return null;
    }

    protected function combineDateAndTime(Carbon $date, ?string $time): ?Carbon
    {
        if (!$time || trim($time) === '') {
            return null;
        }

        try {
            return Carbon::parse($date->toDateString().' '.$time);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function normalizeStatus(?string $status, string $fallback): string
    {
        if (!$status || trim($status) === '') {
            return $fallback;
        }

        $normalized = Str::snake(strtolower(trim($status)));

        if (in_array($normalized, AttendanceRecord::statusValues(), true)) {
            return $normalized;
        }

        return match ($normalized) {
            'leave' => AttendanceRecord::STATUS_ON_LEAVE,
            'wfh', 'workfromhome' => AttendanceRecord::STATUS_WORK_FROM_HOME,
            'holiday' => AttendanceRecord::STATUS_HOLIDAY,
            default => $fallback,
        };
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (!$value || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function sanitizeRowValues(array $row): array
    {
        return array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
