<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $employee = null;

        if ($user instanceof User) {
            $user->loadMissing('employee');
            $employee = $user->employee;
        } elseif (method_exists($user, 'employee')) {
            try {
                $user->loadMissing('employee');
                $employee = $user->employee;
            } catch (\Throwable $e) {
                $employee = null;
            }
        }

        $periodInput = $request->string('period')->trim()->value();

        [$selectedPeriod, $startOfPeriod, $endOfPeriod, $periodLabel] = $this->resolveAttendancePeriod($periodInput, $employee?->id);

    $attendanceRecords = collect();
        $attendanceBreakdown = collect();
        $attendanceTotals = [
            'worked_days' => 0,
            'half_days' => 0,
            'leave_days' => 0,
            'absent_days' => 0,
            'non_working_days' => 0,
        ];
        $leaveRecords = collect();

        if ($employee) {
            $attendanceQuery = AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('attendance_date')
                ->orderByDesc('id');

            if ($selectedPeriod !== 'all') {
                $attendanceQuery->whereBetween('attendance_date', [
                    $startOfPeriod->toDateString(),
                    $endOfPeriod->toDateString(),
                ]);
            }

            /** @var LengthAwarePaginator $attendanceRecords */
            $attendanceRecords = $attendanceQuery->paginate(20)->withQueryString();

            $attendanceSummaryQuery = AttendanceRecord::query()
                ->where('employee_id', $employee->id);

            if ($selectedPeriod !== 'all') {
                $attendanceSummaryQuery->whereBetween('attendance_date', [
                    $startOfPeriod->toDateString(),
                    $endOfPeriod->toDateString(),
                ]);
            }

            $attendanceSummary = $attendanceSummaryQuery
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $attendanceBreakdown = collect(AttendanceRecord::statusLabels())
                ->map(fn (string $label, string $status) => [
                    'status' => $status,
                    'label' => $label,
                    'count' => (int) ($attendanceSummary[$status] ?? 0),
                ])
                ->values();

            $attendanceTotals = [
                'worked_days' => ($attendanceSummary[AttendanceRecord::STATUS_PRESENT] ?? 0)
                    + ($attendanceSummary[AttendanceRecord::STATUS_WORK_FROM_HOME] ?? 0),
                'half_days' => $attendanceSummary[AttendanceRecord::STATUS_HALF_DAY] ?? 0,
                'leave_days' => $attendanceSummary[AttendanceRecord::STATUS_ON_LEAVE] ?? 0,
                'absent_days' => $attendanceSummary[AttendanceRecord::STATUS_ABSENT] ?? 0,
                'non_working_days' => ($attendanceSummary[AttendanceRecord::STATUS_WEEKEND] ?? 0)
                    + ($attendanceSummary[AttendanceRecord::STATUS_HOLIDAY] ?? 0),
            ];

            if ($user->getKey()) {
                $leaveQuery = Leave::query()
                    ->where('user_id', $user->getKey())
                    ->orderByDesc('from_date');

                if ($selectedPeriod !== 'all') {
                    $leaveQuery->where(function ($query) use ($startOfPeriod, $endOfPeriod) {
                        $query->whereBetween('from_date', [$startOfPeriod, $endOfPeriod])
                            ->orWhereBetween('to_date', [$startOfPeriod, $endOfPeriod])
                            ->orWhere(function ($subQuery) use ($startOfPeriod, $endOfPeriod) {
                                $subQuery->where('from_date', '<=', $startOfPeriod)
                                    ->where('to_date', '>=', $endOfPeriod);
                            });
                    });
                }

                $leaveRecords = $leaveQuery->get();
            }
        }

        return view('superadmin.profile.index', [
            'user' => $user,
            'employee' => $employee,
            'attendanceRecords' => $attendanceRecords,
            'attendanceBreakdown' => $attendanceBreakdown,
            'attendanceTotals' => $attendanceTotals,
            'attendancePeriodOptions' => $this->buildAttendancePeriods(),
            'selectedAttendancePeriod' => $selectedPeriod,
            'attendancePeriodLabel' => $periodLabel,
            'leaveRecords' => $leaveRecords,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique($user->getTable(),'email')->ignore($user->getKey(), $user->getKeyName())
            ],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // Handle avatar upload to storage/app/public/avatars/{id}.{ext}
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowed = ['jpg','jpeg','png','webp'];
            // Remove any previous avatar files for this user
            foreach ($allowed as $e) {
                Storage::disk('public')->delete("avatars/{$user->id}.{$e}");
            }
            if (!in_array($ext, $allowed)) {
                $ext = 'jpg';
            }
            $file->storeAs('avatars', $user->id . '.' . $ext, 'public');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('superadmin.profile')->with('success', 'Profile updated successfully.');
    }

    protected function resolveAttendancePeriod(?string $period, ?int $employeeId): array
    {
        if ($period === 'all') {
            $bounds = null;

            if ($employeeId) {
                $bounds = AttendanceRecord::query()
                    ->where('employee_id', $employeeId)
                    ->selectRaw('MIN(attendance_date) as min_date, MAX(attendance_date) as max_date')
                    ->first();
            }

            $start = $bounds?->min_date ? Carbon::parse($bounds->min_date)->startOfDay() : now()->copy()->startOfMonth();
            $end = $bounds?->max_date ? Carbon::parse($bounds->max_date)->endOfDay() : now()->copy()->endOfMonth();

            return ['all', $start, $end, 'All Time'];
        }

        try {
            $periodDate = $period
                ? Carbon::createFromFormat('Y-m', $period)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable $e) {
            $periodDate = now()->startOfMonth();
        }

        return [
            $periodDate->format('Y-m'),
            $periodDate->copy()->startOfMonth(),
            $periodDate->copy()->endOfMonth(),
            $periodDate->format('F Y'),
        ];
    }

    protected function buildAttendancePeriods(): Collection
    {
        $now = now()->startOfMonth();

        return collect(range(0, 11))->map(function (int $offset) use ($now) {
            $period = $now->copy()->subMonthsNoOverflow($offset);

            return [
                'value' => $period->format('Y-m'),
                'label' => $period->format('F Y'),
            ];
        });
    }
}
