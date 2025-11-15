<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): View
    {
        $query = Employee::query()->orderBy('first_name');

        $search = $request->string('search')->trim()->value();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_primary', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('employment_status', $status);
        }

        if ($department = $request->get('department')) {
            $query->where('department', $department);
        }

        /** @var LengthAwarePaginator $employees */
        $employees = $query->paginate(12)->withQueryString();

        $systemUsers = User::query()
            ->with(['role', 'permissions'])
            ->whereDoesntHave('employee')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('user_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $departmentOptions = Employee::query()
            ->select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('superadmin.employees.index', [
            'employees' => $employees,
            'departmentOptions' => $departmentOptions,
            'systemUsers' => $systemUsers,
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(Request $request): View
    {
        $prefillUser = null;

        if ($request->filled('user_id')) {
            $prefillUser = User::query()
                ->where('id', $request->integer('user_id'))
                ->whereDoesntHave('employee')
                ->with('role')
                ->first();
        }

        $prefillData = [];

        if ($prefillUser) {
            [$firstName, $lastName] = $this->splitName($prefillUser->name);

            $prefillData = [
                'employee_code' => $prefillUser->user_code,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'department' => optional($prefillUser->role)->role_name,
                'employment_status' => 'active',
                'user_id' => $prefillUser->id,
            ];
        }

        return view('superadmin.employees.create', [
            'managerOptions' => Employee::query()
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'userOptions' => User::query()
                ->whereDoesntHave('employee')
                ->orderBy('name')
                ->get(['id', 'name', 'user_code']),
            'prefillData' => $prefillData,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $employee = Employee::create($validated);

        return redirect()
            ->route('superadmin.employees.show', $employee)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Request $request, Employee $employee): View
    {
        $periodInput = $request->string('period')->trim()->value();
        $periodDate = $this->resolvePeriodDate($periodInput);

        $selectedPeriod = $periodInput === 'all'
            ? 'all'
            : ($periodDate?->format('Y-m') ?? now()->format('Y-m'));

        if ($selectedPeriod === 'all') {
            $dateBounds = AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->selectRaw('MIN(attendance_date) as min_date, MAX(attendance_date) as max_date')
                ->first();

            $startOfPeriod = $dateBounds?->min_date ? Carbon::parse($dateBounds->min_date)->startOfDay() : now()->startOfMonth();
            $endOfPeriod = $dateBounds?->max_date ? Carbon::parse($dateBounds->max_date)->endOfDay() : $startOfPeriod->copy()->endOfMonth();
            $periodLabel = 'All Time';
        } else {
            $periodDate = $periodDate ?? now()->startOfMonth();
            $startOfPeriod = $periodDate->copy()->startOfMonth();
            $endOfPeriod = $periodDate->copy()->endOfMonth();
            $periodLabel = $periodDate->format('F Y');
        }

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
        $attendanceRecords = $attendanceQuery->paginate(30)->withQueryString();

        $attendanceSummaryQuery = AttendanceRecord::query()
            ->where('employee_id', $employee->id);

        if ($selectedPeriod !== 'all') {
            $attendanceSummaryQuery->whereBetween('attendance_date', [
                $startOfPeriod->toDateString(),
                $endOfPeriod->toDateString(),
            ]);
        }

        /** @var Collection<string,int> $attendanceSummary */
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

        $attendancePeriodOptions = $this->buildAttendancePeriods();

        $leaveRecords = collect();

        if ($employee->user_id) {
            $leaveQuery = Leave::query()
                ->where('user_id', $employee->user_id)
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

        return view('superadmin.employees.show', [
            'employee' => $employee->load(['manager', 'user']),
            'managers' => Employee::query()->where('id', '!=', $employee->id)->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'attendanceRecords' => $attendanceRecords,
            'attendanceBreakdown' => $attendanceBreakdown,
            'attendanceTotals' => $attendanceTotals,
            'attendancePeriodOptions' => $attendancePeriodOptions,
            'selectedAttendancePeriod' => $selectedPeriod,
            'attendancePeriodLabel' => $periodLabel,
            'leaveRecords' => $leaveRecords,
        ]);
    }

    protected function resolvePeriodDate(?string $period): ?Carbon
    {
        if (!$period) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        } catch (\Throwable $e) {
            return null;
        }
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee): View
    {
        return view('superadmin.employees.edit', [
            'employee' => $employee,
            'managers' => Employee::query()->where('id', '!=', $employee->id)->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $this->validateRequest($request, $employee);

        $employee->update($validated);

        return redirect()
            ->route('superadmin.employees.show', $employee)
            ->with('success', 'Employee details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()
            ->route('superadmin.employees.index')
            ->with('success', 'Employee removed successfully.');
    }

    /**
     * Validate and sanitise the employee payload.
     *
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request, ?Employee $employee = null): array
    {
        $employeeId = $employee?->id;

        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($employeeId)],
            'employee_code' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email,'.($employeeId ?? 'NULL').',id'],
            'phone_primary' => ['nullable', 'string', 'max:30'],
            'phone_secondary' => ['nullable', 'string', 'max:30'],
            'designation' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'date_of_joining' => ['nullable', 'date'],
            'employment_status' => ['required', 'string', 'max:50'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'bio' => ['nullable', 'string'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:120'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
            'bank_swift' => ['nullable', 'string', 'max:30'],
            'ctc' => ['nullable', 'numeric', 'min:0'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:40'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $payload = Arr::except($data, ['profile_photo', 'resume']);

        if ($request->hasFile('profile_photo') && $request->file('profile_photo') instanceof UploadedFile) {
            $payload['profile_photo_path'] = $this->storeFile($request->file('profile_photo'), 'employee-photos', $employee?->profile_photo_path);
        }

        if ($request->hasFile('resume') && $request->file('resume') instanceof UploadedFile) {
            $payload['resume_path'] = $this->storeFile($request->file('resume'), 'employee-resumes', $employee?->resume_path);
        }

        return $payload;
    }

    protected function storeFile(UploadedFile $file, string $directory, ?string $existingPath = null): string
    {
        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->store($directory, 'public');
    }

    protected function splitName(string $name): array
    {
        $trimmed = trim($name);

        if ($trimmed === '') {
            return ['User', null];
        }

        $parts = preg_split('/\s+/', $trimmed, 2);

        return [$parts[0] ?? $trimmed, $parts[1] ?? null];
    }
}
