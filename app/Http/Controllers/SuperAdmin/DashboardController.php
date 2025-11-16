<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\BookingItem;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use App\Models\MarketingExpense;
use App\Models\NewBooking;
use App\Models\Leave;
use App\Services\GetUserActiveDepartment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    protected GetUserActiveDepartment $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index()
    {
        $activeDepartments = $this->departmentService->getDepartment();

        if (Auth::guard('admin')->check()) {
            return view('superadmin.dashboard', [
                'departments' => $activeDepartments,
            ]);
        }

        $user = Auth::guard('web')->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $departmentName = optional($user->employee)->department;
        $departmentSlug = $this->normalizeDepartmentSlug($departmentName);
        $departmentModel = $departmentName
            ? Department::where('name', $departmentName)->first()
            : null;

        $payload = $this->buildDepartmentPayload($departmentSlug, $user, $departmentModel);

        $candidateViews = array_filter([
            $departmentSlug ? "superadmin.departments.{$departmentSlug}.dashboard" : null,
            $departmentSlug ? "superadmin.departments.{$departmentSlug}" : null,
        ]);

        foreach ($candidateViews as $viewName) {
            if (view()->exists($viewName)) {
                return view($viewName, [
                    'departments' => $activeDepartments,
                    'departmentName' => $departmentName,
                    'department' => $departmentModel,
                    'user' => $user,
                    'payload' => $payload,
                ]);
            }
        }

        return view('superadmin.departments.default', [
            'departments' => $activeDepartments,
            'departmentName' => $departmentName,
            'department' => $departmentModel,
            'user' => $user,
            'payload' => $payload,
        ]);
    }

    protected function normalizeDepartmentSlug(?string $departmentName): ?string
    {
        if (!$departmentName) {
            return null;
        }

        $slug = Str::slug($departmentName);

        $aliases = [
            'human-resources' => 'hr',
            'human-resource' => 'hr',
            'hr' => 'hr',
            'lab' => 'lab',
            'laboratory' => 'lab',
            'quality-lab' => 'lab',
            'accounts' => 'accountant',
            'account' => 'accountant',
            'accounting' => 'accountant',
            'finance' => 'accountant',
            'accountant' => 'accountant',
            'computer-operator' => 'computer-operator',
            'computer-operations' => 'computer-operator',
            'data-entry' => 'computer-operator',
            'operations' => 'computer-operator',
            'marketing' => 'marketing',
        ];

        return $aliases[$slug] ?? $slug;
    }

    protected function buildDepartmentPayload(?string $departmentSlug, $user, ?Department $department): array
    {
        return match ($departmentSlug) {
            'marketing' => $this->buildMarketingPayload($user),
            'hr' => $this->buildHrPayload(),
            'lab' => $this->buildLabPayload($user),
            'accountant' => $this->buildAccountantPayload(),
            'computer-operator' => $this->buildComputerOperatorPayload($user),
            default => $this->buildGenericPayload($department, $user),
        };
    }

    protected function buildMarketingPayload($user): array
    {
        $userCode = $user->user_code;

        if (!$userCode) {
            return [
                'metrics' => [],
                'quick_links' => $this->marketingQuickLinks(),
            ];
        }

        $bookingQuery = NewBooking::where('marketing_id', $userCode);
        $expenseBaseQuery = MarketingExpense::where('marketing_person_code', $userCode);

        $pendingExpenses = (clone $expenseBaseQuery)->where('status', 'pending')->count();
        $approvedExpenseAmount = (clone $expenseBaseQuery)->where('status', 'approved')->sum('approved_amount');
        $rejectedExpenses = (clone $expenseBaseQuery)->where('status', 'rejected')->count();
        $expensesThisMonth = (clone $expenseBaseQuery)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('amount');

        return [
            'metrics' => [
                [
                    'label' => 'Active Bookings',
                    'value' => $bookingQuery->count(),
                    'icon' => 'ti ti-briefcase',
                    'type' => 'primary',
                    'description' => 'Total bookings linked with your marketing code.',
                ],
                [
                    'label' => 'Pending Expenses',
                    'value' => $pendingExpenses,
                    'icon' => 'ti ti-report-money',
                    'type' => 'warning',
                    'description' => 'Awaiting approval in expense workflow.',
                ],
                [
                    'label' => 'Approved Amount (₹)',
                    'value' => number_format((float) $approvedExpenseAmount, 2),
                    'icon' => 'ti ti-circle-check',
                    'type' => 'success',
                    'description' => 'Total approved claims till date.',
                ],
                [
                    'label' => 'Spend This Month (₹)',
                    'value' => number_format((float) $expensesThisMonth, 2),
                    'icon' => 'ti ti-calendar-event',
                    'type' => 'info',
                    'description' => 'Expense submissions in the current month.',
                ],
                [
                    'label' => 'Rejected Requests',
                    'value' => $rejectedExpenses,
                    'icon' => 'ti ti-circle-x',
                    'type' => 'danger',
                    'description' => 'Requests needing rework or clarification.',
                ],
            ],
            'quick_links' => $this->marketingQuickLinks(),
            'insights' => [
                'message' => 'Track expense submissions and follow up on pending approvals to keep campaigns moving.',
            ],
        ];
    }

    protected function marketingQuickLinks(): array
    {
        return [
            [
                'label' => 'Submit Expense',
                'url' => route('superadmin.marketing.expenses.view'),
                'icon' => 'ti ti-cash',
            ],
            [
                'label' => 'Pending Approvals',
                'url' => route('superadmin.marketing.expenses.approved'),
                'icon' => 'ti ti-clock-hour-4',
            ],
            [
                'label' => 'Rejected Expenses',
                'url' => route('superadmin.marketing.expenses.rejected'),
                'icon' => 'ti ti-alert-circle',
            ],
            [
                'label' => 'New Booking Request',
                'url' => route('superadmin.bookings.newbooking'),
                'icon' => 'ti ti-calendar-plus',
            ],
            [
                'label' => 'View Bookings',
                'url' => route('superadmin.showbooking.showBooking'),
                'icon' => 'ti ti-layout-list',
            ],
        ];
    }

    protected function buildHrPayload(): array
    {
        $currentQuarter = [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()];
        $today = Carbon::today();

        $attendanceToday = AttendanceRecord::whereDate('attendance_date', $today);

        $presentToday = (clone $attendanceToday)->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $onLeaveToday = (clone $attendanceToday)->where('status', AttendanceRecord::STATUS_ON_LEAVE)->count();

        return [
            'metrics' => [
                [
                    'label' => 'Total Employees',
                    'value' => Employee::count(),
                    'icon' => 'ti ti-users',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Active Workforce',
                    'value' => Employee::where('employment_status', 'Active')->count(),
                    'icon' => 'ti ti-user-check',
                    'type' => 'success',
                ],
                [
                    'label' => 'New Hires (QTD)',
                    'value' => Employee::whereBetween('date_of_joining', $currentQuarter)->count(),
                    'icon' => 'ti ti-briefcase',
                    'type' => 'info',
                ],
                [
                    'label' => 'Pending Leave Requests',
                    'value' => Leave::where('status', 'Applied')->count(),
                    'icon' => 'ti ti-mail-opened',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Present Today',
                    'value' => $presentToday,
                    'icon' => 'ti ti-calendar-check',
                    'type' => 'success',
                ],
                [
                    'label' => 'On Leave Today',
                    'value' => $onLeaveToday,
                    'icon' => 'ti ti-plane-departure',
                    'type' => 'info',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Employee Directory',
                    'url' => route('superadmin.employees.index'),
                    'icon' => 'ti ti-address-book',
                ],
                [
                    'label' => 'Attendance Console',
                    'url' => route('superadmin.hr.attendance.index'),
                    'icon' => 'ti ti-calendar-stats',
                ],
                [
                    'label' => 'Leave Approvals',
                    'url' => route('superadmin.leave.Leave'),
                    'icon' => 'ti ti-calendar-event',
                ],
                [
                    'label' => 'Payroll Cycles',
                    'url' => route('superadmin.hr.payroll.index'),
                    'icon' => 'ti ti-cash-banknote',
                ],
            ],
            'insights' => [
                'message' => 'Keep an eye on attendance anomalies and approve urgent leave requests promptly.',
            ],
        ];
    }

    protected function buildLabPayload($user): array
    {
        $userCode = $user->user_code;

        if (!$userCode) {
            return [
                'metrics' => [],
                'quick_links' => $this->labQuickLinks(),
            ];
        }

        $assignmentQuery = BookingItem::where('lab_analysis_code', $userCode);
        $assignedSamples = (clone $assignmentQuery)->count();
        $pendingReports = (clone $assignmentQuery)->whereDoesntHave('reports')->count();
        $completedReports = (clone $assignmentQuery)->whereHas('reports')->count();
        $dueToday = (clone $assignmentQuery)->whereDate('lab_expected_date', Carbon::today())->count();

        return [
            'metrics' => [
                [
                    'label' => 'Assigned Samples',
                    'value' => $assignedSamples,
                    'icon' => 'ti ti-flask',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Due Today',
                    'value' => $dueToday,
                    'icon' => 'ti ti-clock-hour-4',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Pending Reports',
                    'value' => $pendingReports,
                    'icon' => 'ti ti-alert-triangle',
                    'type' => 'danger',
                ],
                [
                    'label' => 'Completed Reports',
                    'value' => $completedReports,
                    'icon' => 'ti ti-circle-check',
                    'type' => 'success',
                ],
            ],
            'quick_links' => $this->labQuickLinks(),
            'insights' => [
                'message' => 'Prioritize samples due today to keep the testing pipeline on schedule.',
            ],
        ];
    }

    protected function labQuickLinks(): array
    {
        return [
            [
                'label' => 'Worksheet Render',
                'url' => route('superadmin.labanalysts.render'),
                'icon' => 'ti ti-flask-2',
            ],
            [
                'label' => 'Generate Report',
                'url' => route('superadmin.reporting.generate'),
                'icon' => 'ti ti-report',
            ],
            [
                'label' => 'Pending Bookings',
                'url' => route('superadmin.reporting.pendings'),
                'icon' => 'ti ti-notes',
            ],
            [
                'label' => 'Report Dispatch',
                'url' => route('superadmin.reporting.dispatch'),
                'icon' => 'ti ti-truck-delivery',
            ],
        ];
    }

    protected function buildAccountantPayload(): array
    {
        $rangeMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

        $invoicesAwaitingPayment = Invoice::whereDoesntHave('transactions')->count();
        $amountCollectedThisMonth = InvoiceTransaction::whereBetween('transaction_date', $rangeMonth)->sum('amount_received');

        return [
            'metrics' => [
                [
                    'label' => 'Total Invoices',
                    'value' => Invoice::count(),
                    'icon' => 'ti ti-file-invoice',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Awaiting Payment',
                    'value' => $invoicesAwaitingPayment,
                    'icon' => 'ti ti-hourglass-high',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Collected This Month (₹)',
                    'value' => number_format((float) $amountCollectedThisMonth, 2),
                    'icon' => 'ti ti-cash-banknote',
                    'type' => 'success',
                ],
                [
                    'label' => 'Invoices Raised (MTD)',
                    'value' => Invoice::whereBetween('created_at', $rangeMonth)->count(),
                    'icon' => 'ti ti-calendar-event',
                    'type' => 'info',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Invoice Register',
                    'url' => route('superadmin.invoices.index'),
                    'icon' => 'ti ti-file-invoice',
                ],
                [
                    'label' => 'Cash Collections',
                    'url' => route('superadmin.cashPayments.index'),
                    'icon' => 'ti ti-wallet',
                ],
                [
                    'label' => 'Generate Invoice',
                    'url' => route('superadmin.bookingInvoiceStatuses.index'),
                    'icon' => 'ti ti-printer',
                ],
                [
                    'label' => 'Bank Uploads',
                    'url' => route('superadmin.bank.upload'),
                    'icon' => 'ti ti-building-bank',
                ],
            ],
            'insights' => [
                'message' => 'Follow up on outstanding invoices to improve cash flow.',
            ],
        ];
    }

    protected function buildComputerOperatorPayload($user): array
    {
        $createdByType = get_class($user);
        $bookingBaseQuery = NewBooking::where('created_by_type', $createdByType)
            ->where('created_by_id', $user->id);

        $today = Carbon::today();
        $rangeMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

        $bookingsToday = (clone $bookingBaseQuery)->whereDate('created_at', $today)->count();
        $bookingsThisMonth = (clone $bookingBaseQuery)->whereBetween('created_at', $rangeMonth)->count();
        $onHold = (clone $bookingBaseQuery)->where('hold_status', true)->count();
        $invoicesPending = (clone $bookingBaseQuery)->whereDoesntHave('generatedInvoice')->count();

        return [
            'metrics' => [
                [
                    'label' => 'Bookings Created Today',
                    'value' => $bookingsToday,
                    'icon' => 'ti ti-calendar-plus',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Bookings This Month',
                    'value' => $bookingsThisMonth,
                    'icon' => 'ti ti-calendar-stats',
                    'type' => 'info',
                ],
                [
                    'label' => 'On Hold',
                    'value' => $onHold,
                    'icon' => 'ti ti-alert-octagon',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Awaiting Invoice',
                    'value' => $invoicesPending,
                    'icon' => 'ti ti-file-alert',
                    'type' => 'danger',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Create Booking',
                    'url' => route('superadmin.bookings.newbooking'),
                    'icon' => 'ti ti-calendar-plus',
                ],
                [
                    'label' => 'View Bookings',
                    'url' => route('superadmin.showbooking.showBooking'),
                    'icon' => 'ti ti-table',
                ],
                [
                    'label' => 'Upload Documents',
                    'url' => route('superadmin.documents.index'),
                    'icon' => 'ti ti-folder',
                ],
                [
                    'label' => 'Approvals',
                    'url' => route('superadmin.approvals.index'),
                    'icon' => 'ti ti-checkup-list',
                ],
            ],
            'insights' => [
                'message' => 'Ensure bookings on hold are addressed and invoices are triggered promptly.',
            ],
        ];
    }

    protected function buildGenericPayload(?Department $department, $user): array
    {
        $departmentLabel = $department?->name ?? optional($user->employee)->department ?? 'Team';
        $weekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];

        return [
            'metrics' => [
                [
                    'label' => 'Bookings This Week',
                    'value' => NewBooking::whereBetween('created_at', $weekRange)->count(),
                    'icon' => 'ti ti-notebook',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Total Departments',
                    'value' => Department::where('is_active', 1)->count(),
                    'icon' => 'ti ti-building',
                    'type' => 'info',
                ],
                [
                    'label' => 'Pending Leaves',
                    'value' => Leave::where('status', 'Applied')->count(),
                    'icon' => 'ti ti-calendar-event',
                    'type' => 'warning',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Main Dashboard',
                    'url' => route('superadmin.dashboard.index'),
                    'icon' => 'ti ti-layout-grid',
                ],
                [
                    'label' => 'All Bookings',
                    'url' => route('superadmin.showbooking.showBooking'),
                    'icon' => 'ti ti-list-details',
                ],
                [
                    'label' => 'Departments',
                    'url' => route('superadmin.departments.index'),
                    'icon' => 'ti ti-hierarchy',
                ],
            ],
            'insights' => [
                'message' => "No dedicated dashboard configured for {$departmentLabel}. Using the unified overview instead.",
            ],
        ];
    }
}
