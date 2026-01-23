<?php

namespace App\Http\Controllers\Superadmin\Hr;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExportController extends Controller
{
    public function downloadMonthly(Request $request)
    {
        $request->validate([
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        try {
            $month = Carbon::createFromFormat('Y-m', $request->query('month'));
        } catch (\Exception $e) {
            abort(400, 'Invalid month format');
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $holidaysInMonth = Holiday::where('status', 'active')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })->toArray();

        $employees = Employee::with('user')->get();

        $filename = 'attendance-summary-' . $month->format('Y-m') . '.csv';

        $response = new StreamedResponse(function () use ($employees, $start, $end, $holidaysInMonth) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['employee_code','employee_name','total_attendance','total_leave','total_holidays','total_worked']);

            foreach ($employees as $emp) {
                // Attendance calculation
                $records = AttendanceRecord::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
                    ->get();

                $attendanceTotal = 0.0;
                foreach ($records as $r) {
                    switch ($r->status) {
                        case AttendanceRecord::STATUS_PRESENT:
                        case AttendanceRecord::STATUS_WORK_FROM_HOME:
                            $attendanceTotal += 1;
                            break;
                        case AttendanceRecord::STATUS_HALF_DAY:
                            $attendanceTotal += 0.5;
                            break;
                        default:
                            break;
                    }
                }

                // Leave calculation (approved leaves overlapping month). Use user_id mapping where possible.
                $leaveTotal = 0;
                if ($emp->user_id) {
                    $leaves = Leave::where('user_id', $emp->user_id)
                        ->where('status', 'Approved')
                        ->whereDate('from_date', '<=', $end)
                        ->whereDate('to_date', '>=', $start)
                        ->get();

                    foreach ($leaves as $leave) {
                        $from = Carbon::parse($leave->from_date)->max($start);
                        $to = Carbon::parse($leave->to_date)->min($end);
                        $days = $from->diffInDays($to) + 1;
                        $leaveTotal += $days;
                    }
                }

                // Holidays that fall in the month. These are company-wide and count equally for all employees.
                $holidayCount = count($holidaysInMonth);

                // Total worked is attendance plus holidays (leave shown separately)
                $totalWorked = $attendanceTotal + $holidayCount - $leaveTotal;

                fputcsv($handle, [
                    $emp->employee_code ?? '',
                    $emp->getFullNameAttribute(),
                    $attendanceTotal,
                    $leaveTotal,
                    $holidayCount,
                    $totalWorked,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
