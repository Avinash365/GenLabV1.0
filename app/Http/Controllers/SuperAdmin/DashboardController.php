<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\BookingItem;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PurchaseBill;
use App\Models\InvoiceTransaction;
use App\Models\MarketingExpense;
use App\Models\NewBooking;
use App\Models\Leave;
use App\Models\Product;
use App\Models\ProductStockEntry;
use App\Models\Client;
use App\Models\InvoiceTds;
use App\Models\CashLetterPayment;
use App\Models\Approval;
use App\Services\GetUserActiveDepartment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    protected GetUserActiveDepartment $departmentService;

    protected array $chartPalette = [
        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
        '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab',
    ];

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index()
    {
        $activeDepartments = $this->departmentService->getDepartment();

        // If the user is being redirected immediately after login we avoid running
        // the heavy dashboard computations synchronously. The login flow appends
        // ?post_login=1 so we can return a lightweight page and let the frontend
        // fetch the heavy widgets via AJAX.
        $req = request();

        // Optional: enable query logging for a single request by appending
        // ?debug_queries=1 (development only). This will write SQL events to the
        // application log to help identify slow/duplicate queries during dashboard load.
        if ($req->query('debug_queries')) {
            DB::listen(function ($query) {
                Log::info('dashboard_query', ['sql' => $query->sql, 'bindings' => $query->bindings, 'time' => $query->time]);
            });
        }

        // GST Overview Data
        $salesGst = Cache::remember('dashboard:sales_gst', 300, function() {
            return \App\Models\Invoice::sum('gst_amount');
        });

        $purchaseGst = Cache::remember('dashboard:purchase_gst', 300, function() {
            return \App\Models\PurchaseBill::sum('gst_amount');
        });

        $totalGst = $salesGst - $purchaseGst;

        if ($req->query('post_login')) {
            $minimal = [
                'salesGst' => $salesGst,
                'purchaseGst' => $purchaseGst,
                'totalGst' => $totalGst,
                'departments' => $activeDepartments,
                // Prefer cached snapshots so small important widgets render immediately after login.
                'analystWorkloadAll' => Cache::get('dashboard:analyst:all', []),
                'analystWorkload30' => Cache::get('dashboard:analyst:30', []),
                'analystWorkload90' => Cache::get('dashboard:analyst:90', []),
                'analystWorkload180' => Cache::get('dashboard:analyst:180', []),
                'analystWorkload365' => Cache::get('dashboard:analyst:365', []),
                'overdueAll' => Cache::get('dashboard:overdue:all', 0),
                'overdue30' => Cache::get('dashboard:overdue:30', 0),
                'overdue90' => Cache::get('dashboard:overdue:90', 0),
                'overdue180' => Cache::get('dashboard:overdue:180', 0),
                'overdue365' => Cache::get('dashboard:overdue:365', 0),
                'lowStockItems' => Cache::get('dashboard:lowStockItems', collect()),
                'bookingsByDepartment' => Cache::get('dashboard:bookings_by_dept:' . Carbon::today()->toDateString(), []),
                'bookingsByMarketing' => Cache::get('dashboard:bookings_by_marketing:' . Carbon::today()->toDateString(), []),
            ];

            if (\Auth::guard('admin')->check()) {
                return view('superadmin.dashboard', $minimal);
            }

            $user = Auth::guard('web')->user();
            if (!$user) {
                abort(403, 'Unauthorized');
            }

            $departmentName = optional($user->employee)->department;

            return view('superadmin.departments.default', array_merge($minimal, [
                'departmentName' => $departmentName,
                'department' => null,
                'user' => $user,
                'payload' => [],
            ]));
        }

        // Fetch Analyst Workload using case-insensitive join to users.user_code so
        // `lab_analysis_code` like "LAb032" maps to the correct user name where present.
        // Provide full lists (all lab analysts) for the widget. The chart can handle many items,
        // but we still provide 30/90 filters as well.
        $analystWorkloadAll = Cache::remember('dashboard:analyst:all', 300, fn() => $this->getAllAnalystWorkload(null));
        $analystWorkload30 = Cache::remember('dashboard:analyst:30', 300, fn() => $this->getAllAnalystWorkload(30));
        $analystWorkload90 = Cache::remember('dashboard:analyst:90', 300, fn() => $this->getAllAnalystWorkload(90));
        $analystWorkload180 = Cache::remember('dashboard:analyst:180', 300, fn() => $this->getAllAnalystWorkload(180));
        $analystWorkload365 = Cache::remember('dashboard:analyst:365', 300, fn() => $this->getAllAnalystWorkload(365));

        // Fetch Low Stock Inventory (Items with Total Stock < 10)
        $lowStockItems = Cache::remember('dashboard:lowStockItems', 300, function () {
            return \App\Models\ProductStockEntry::select('product_code', \DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('product_code')
                ->having('total_qty', '<', 10)
                ->with('product')
                ->limit(5)
                ->get();
        });

        // Bookings by Department: return both booking counts and summed amount per department
        $bookingsByDeptRaw = Cache::remember('dashboard:bookings_by_dept:' . Carbon::today()->toDateString(), 300, function () {
            return \App\Models\NewBooking::whereNotNull('department_id')
            ->whereNull('new_bookings.deleted_at')
            ->join('departments', 'new_bookings.department_id', '=', 'departments.id')
            ->where('departments.is_active', 1)
            ->leftJoin('booking_items', function($join){
                $join->on('booking_items.new_booking_id', '=', 'new_bookings.id')
                     ->whereNull('booking_items.deleted_at');
            })
            ->selectRaw('departments.name as department, COUNT(DISTINCT new_bookings.id) as total, COALESCE(SUM(booking_items.amount), 0) as amount')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        });

        $bookingsByDepartment = [];
        foreach ($bookingsByDeptRaw as $row) {
            $bookingsByDepartment[$row->department] = [
                'total' => (int) ($row->total ?? 0),
                'amount' => (float) ($row->amount ?? 0),
            ];
        }

        // Bookings by Marketing Person (snapshot) - used as server-side fallback
        $bookingsByMarketingRaw = Cache::remember('dashboard:bookings_by_marketing:' . Carbon::today()->toDateString(), 300, function () {
            return \App\Models\NewBooking::whereNotNull('marketing_id')
                ->whereNull('new_bookings.deleted_at')
                ->leftJoin('users', function($join){
                    $join->on('new_bookings.marketing_id', '=', 'users.user_code');
                })
                ->leftJoin('booking_items', function($join){
                    $join->on('booking_items.new_booking_id', '=', 'new_bookings.id')
                         ->whereNull('booking_items.deleted_at');
                })
                ->selectRaw('COALESCE(users.name, new_bookings.marketing_id) as marketing, COUNT(DISTINCT new_bookings.id) as total, COALESCE(SUM(booking_items.amount), 0) as amount')
                ->groupBy('marketing')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        });

        $bookingsByMarketing = [];
        foreach ($bookingsByMarketingRaw as $row) {
            $key = (string) ($row->marketing ?? 'Unknown');
            $bookingsByMarketing[$key] = ['total' => (int) ($row->total ?? 0), 'amount' => (float) ($row->amount ?? 0)];
        }

        if (\Auth::guard('admin')->check()) {
            return view('superadmin.dashboard', [
                'salesGst' => $salesGst,
                'purchaseGst' => $purchaseGst,
                'totalGst' => $totalGst,
                'departments' => $activeDepartments,
                'analystWorkloadAll' => $analystWorkloadAll,
                'analystWorkload30' => $analystWorkload30,
                'analystWorkload90' => $analystWorkload90,
                'analystWorkload180' => $analystWorkload180,
                'analystWorkload365' => $analystWorkload365,
                'overdueAll' => Cache::remember('dashboard:overdue:all', 300, fn() => \App\Models\BookingItem::whereDate('lab_expected_date', '<', Carbon::today())->count()),
                'overdue30' => Cache::remember('dashboard:overdue:30', 300, fn() => \App\Models\BookingItem::whereDate('lab_expected_date', '<', Carbon::today())->whereBetween('created_at', [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()])->count()),
                'overdue90' => Cache::remember('dashboard:overdue:90', 300, fn() => \App\Models\BookingItem::whereDate('lab_expected_date', '<', Carbon::today())->whereBetween('created_at', [Carbon::now()->subDays(89)->startOfDay(), Carbon::now()->endOfDay()])->count()),
                'overdue180' => Cache::remember('dashboard:overdue:180', 300, fn() => \App\Models\BookingItem::whereDate('lab_expected_date', '<', Carbon::today())->whereBetween('created_at', [Carbon::now()->subDays(179)->startOfDay(), Carbon::now()->endOfDay()])->count()),
                'overdue365' => Cache::remember('dashboard:overdue:365', 300, fn() => \App\Models\BookingItem::whereDate('lab_expected_date', '<', Carbon::today())->whereBetween('created_at', [Carbon::now()->subDays(364)->startOfDay(), Carbon::now()->endOfDay()])->count()),
                'lowStockItems'   => $lowStockItems,
                'bookingsByDepartment' => $bookingsByDepartment,
                'bookingsByMarketing' => $bookingsByMarketing,
            ]);
        }

        $user = Auth::guard('web')->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $primaryDepartmentName = optional($user->employee)->department;
        $role = $user->role;

        $identifier = $primaryDepartmentName
            ?? ($role?->slug)
            ?? ($role?->role_name);

        $departmentSlug = $this->normalizeDepartmentSlug($identifier);
        $departmentModel = $primaryDepartmentName
            ? Department::where('name', $primaryDepartmentName)->first()
            : null;

        $departmentName = $primaryDepartmentName
            ?? ($role?->role_name ?? $identifier);

        $payloadCacheKey = "dashboard:payload:" . ($departmentSlug ?? 'default') . ":user:" . $user->id;

        // Optimize for accountant: return empty payload initially and load via AJAX
        if ($departmentSlug === 'accountant') {
            $payload = [];
        } else {
            $payload = Cache::remember($payloadCacheKey, 600, function () use ($departmentSlug, $user, $departmentModel) {
                return $this->buildDepartmentPayload($departmentSlug, $user, $departmentModel);
            });
        }

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
                    'analystWorkloadAll' => $analystWorkloadAll,
                        'analystWorkload30' => $analystWorkload30,
                        'analystWorkload90' => $analystWorkload90,
                        'analystWorkload180' => $analystWorkload180,
                        'analystWorkload365' => $analystWorkload365,
                        'overdueAll' => $overdueAll ?? 0,
                        'overdue30' => $overdue30 ?? 0,
                        'overdue90' => $overdue90 ?? 0,
                        'overdue180' => $overdue180 ?? 0,
                        'overdue365' => $overdue365 ?? 0,
                        'lowStockItems' => $lowStockItems ?? [],
                        'bookingsByDepartment' => $bookingsByDepartment ?? [],
                        'bookingsByMarketing' => $bookingsByMarketing ?? [],
                ]);
            }
        }

        return view('superadmin.departments.default', [
            'departments' => $activeDepartments,
            'departmentName' => $departmentName,
            'department' => $departmentModel,
            'user' => $user,
            'payload' => $payload,
            'analystWorkloadAll' => $analystWorkloadAll,
            'analystWorkload30' => $analystWorkload30,
            'analystWorkload90' => $analystWorkload90,
            'analystWorkload180' => $analystWorkload180,
            'analystWorkload365' => $analystWorkload365,
            'overdueAll' => $overdueAll ?? 0,
            'overdue30' => $overdue30 ?? 0,
            'overdue90' => $overdue90 ?? 0,
            'overdue180' => $overdue180 ?? 0,
            'overdue365' => $overdue365 ?? 0,
            'lowStockItems' => $lowStockItems ?? [],
            'bookingsByDepartment' => $bookingsByDepartment ?? [],
            'bookingsByMarketing' => $bookingsByMarketing ?? [],
        ]);
    }

    public function invoicePaymentChart(Request $request)
    {
        $range = (string) $request->query('range', '1Y');

        // Align the start/end boundaries to the bucket granularity so the series includes
        // the most recent bucket (e.g., current month for 1Y/6M).
        $now = Carbon::now();
        [$start, $end] = match ($range) {
            '1D' => [$now->copy()->subHours(23)->startOfHour(), $now->copy()->endOfHour()],
            '1W' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '1M' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            '3M' => [$now->copy()->subWeeks(11)->startOfWeek(), $now->copy()->endOfWeek()],
            '6M' => [$now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->subMonths(11)->startOfMonth(), $now->copy()->endOfMonth()],
        };

        // Use created_at for grouping/filtering so the dashboard reflects when invoices
        // were generated/updated in the system.
        $dateExpr = 'invoice_date';

        $groupExpr = match ($range) {
            '1D' => 'DATE_FORMAT(invoice_date, "%Y-%m-%d %H")',
            '1W', '1M' => 'DATE_FORMAT(invoice_date, "%Y-%m-%d")',
            '3M' => 'DATE_FORMAT(invoice_date, "%x-W%v")',
            '6M', '1Y' => 'DATE_FORMAT(invoice_date, "%Y-%m")',
            default => 'DATE_FORMAT(invoice_date, "%Y-%m")',
        };

        // Expected invoices: exclude cancelled (status=2)
        $invoiceRaw = Invoice::selectRaw($groupExpr . ' as k, COALESCE(SUM(CASE WHEN status != 2 THEN total_amount ELSE 0 END), 0) as amount')
            ->whereBetween('invoice_date', [$start, $end])
            ->groupBy('k')
            ->orderBy('k')
            ->pluck('amount', 'k');

        // Payment done: treat Paid(1) and Settled(4) as fully done.
        $paymentRaw = Invoice::selectRaw($groupExpr . ' as k, COALESCE(SUM(CASE WHEN status IN (1, 4) THEN total_amount ELSE 0 END), 0) as amount')
            ->whereBetween('invoice_date', [$start, $end])
            ->groupBy('k')
            ->orderBy('k')
            ->pluck('amount', 'k');

        $labels = [];
        $invoiceSeries = [];
        $paymentSeries = [];

        if ($range === '1D') {
            $cursor = $start->copy();
            $limit = 24;
            for ($i = 0; $i < $limit; $i++) {
                $key = $cursor->format('Y-m-d H');
                $labels[] = $cursor->format('ha');
                $invoiceSeries[] = (float) ($invoiceRaw[$key] ?? 0);
                $paymentSeries[] = (float) ($paymentRaw[$key] ?? 0);
                $cursor->addHour();
            }
        } elseif ($range === '1W' || $range === '1M') {
            $days = $range === '1W' ? 7 : 30;
            $cursor = $start->copy();
            for ($i = 0; $i < $days; $i++) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $range === '1W' ? $cursor->format('D') : $cursor->format('d');
                $invoiceSeries[] = (float) ($invoiceRaw[$key] ?? 0);
                $paymentSeries[] = (float) ($paymentRaw[$key] ?? 0);
                $cursor->addDay();
            }
        } elseif ($range === '3M') {
            $cursor = $start->copy();
            for ($i = 0; $i < 12; $i++) {
                $key = $cursor->format('o-\WW');
                $labels[] = 'W' . $cursor->format('W');
                $invoiceSeries[] = (float) ($invoiceRaw[$key] ?? 0);
                $paymentSeries[] = (float) ($paymentRaw[$key] ?? 0);
                $cursor->addWeek();
            }
        } else {
            $months = $range === '6M' ? 6 : 12;
            $cursor = $start->copy();
            for ($i = 0; $i < $months; $i++) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M');
                $invoiceSeries[] = (float) ($invoiceRaw[$key] ?? 0);
                $paymentSeries[] = (float) ($paymentRaw[$key] ?? 0);
                $cursor->addMonth();
            }
        }

        $expectedTotal = (float) Invoice::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 2)
            ->sum('total_amount');

        $paidTotal = (float) Invoice::whereBetween('created_at', [$start, $end])
            ->whereIn('status', [1, 4])
            ->sum('total_amount');

        $outstandingTotal = max(0.0, $expectedTotal - $paidTotal);

        return response()->json([
            'range' => $range,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            'labels' => $labels,
            'invoice' => $invoiceSeries,
            'payment' => $paymentSeries,
            'totals' => [
                // For the dashboard, show remaining expected amount after subtracting paid.
                'invoice' => $outstandingTotal,
                'payment' => $paidTotal,
                // Optional reference values (safe to ignore on frontend).
                'expected' => $expectedTotal,
                'outstanding' => $outstandingTotal,
            ],
        ]);
    }

    /**
     * Return department-specific dashboard payload for AJAX requests.
     * Renders the metrics/charts/quick-links partials server-side and returns
     * them as HTML so the client can replace placeholders without re-running
     * expensive computations on the initial page render.
     */
    public function payload(Request $request)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $primaryDepartmentName = optional($user->employee)->department;
        $role = $user->role;

        $identifier = $primaryDepartmentName
            ?? ($role?->slug)
            ?? ($role?->role_name);

        $departmentSlug = $this->normalizeDepartmentSlug($identifier);
        $departmentModel = $primaryDepartmentName
            ? Department::where('name', $primaryDepartmentName)->first()
            : null;

        $payload = $this->buildDepartmentPayload($departmentSlug, $user, $departmentModel);

        $metricsHtml = view('superadmin.departments.partials.metrics', ['metrics' => $payload['metrics'] ?? []])->render();
        $chartsHtml = view('superadmin.departments.partials.charts', ['charts' => $payload['charts'] ?? []])->render();
        $quickLinksHtml = view('superadmin.departments.partials.quick-links', ['quickLinks' => $payload['quick_links'] ?? []])->render();

        $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');

        return response()->json([
            'metric_lookup' => $metricLookup,
            'metrics_html' => $metricsHtml,
            'charts_html' => $chartsHtml,
            'quick_links_html' => $quickLinksHtml,
            'followups' => [
                'bookings_created_today' => $metricLookup->get('Bookings Created Today', 0),
                'on_hold' => $metricLookup->get('On Hold', 0),
                'awaiting_invoice' => $metricLookup->get('Awaiting Invoice', 0),
                'insight' => $payload['insights']['message'] ?? null,
            ],
        ]);
    }

    /**
     * Return booking counts per day for the last N days (default 30)
     */
    public function bookingTrendChart(Request $request)
    {
        $daysParam = $request->query('days', '30');
        $end = Carbon::today();

        // Support explicit 'all' which will fetch from earliest booking up to today.
        if (is_string($daysParam) && strtolower($daysParam) === 'all') {
            $earliest = NewBooking::whereNull('deleted_at')->min('created_at');
            $start = $earliest ? Carbon::parse($earliest)->startOfDay() : $end->copy()->subYears(1)->startOfDay();
            $days = $start->diffInDays($end) + 1;
            // cap to reasonable limit to avoid huge payloads
            $days = min($days, 730);
            $start = $end->copy()->subDays($days - 1)->startOfDay();
        } else {
            $days = (int) $daysParam;
            $days = max(1, min(365, $days));
            $start = Carbon::today()->subDays($days - 1)->startOfDay();
        }

        $raw = NewBooking::whereNull('deleted_at')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $days; $i++) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d M');
            $data[] = (int) ($raw[$key] ?? 0);
            $cursor->addDay();
        }

        return response()->json([
            'days' => $days,
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Return bookings by department with counts and amounts for the given days or 'all'.
     */
    public function bookingsByDepartmentChart(Request $request)
    {
        $daysParam = $request->query('days', '30');
        $end = Carbon::today();

        if (is_string($daysParam) && strtolower($daysParam) === 'all') {
            // earliest booking date
            $earliest = NewBooking::whereNull('deleted_at')->min('created_at');
            $start = $earliest ? Carbon::parse($earliest)->startOfDay() : $end->copy()->subYears(1)->startOfDay();
        } else {
            $days = (int) $daysParam;
            $days = max(1, min(365, $days));
            $start = Carbon::today()->subDays($days - 1)->startOfDay();
        }

        $rows = NewBooking::whereNotNull('department_id')
            ->whereNull('new_bookings.deleted_at')
            ->join('departments', 'new_bookings.department_id', '=', 'departments.id')
            ->where('departments.is_active', 1)
            ->leftJoin('booking_items', function($join){
                $join->on('booking_items.new_booking_id', '=', 'new_bookings.id')
                     ->whereNull('booking_items.deleted_at');
            })
            ->whereBetween('new_bookings.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('departments.name as department, COUNT(DISTINCT new_bookings.id) as total, COALESCE(SUM(booking_items.amount), 0) as amount')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->department] = ['total' => (int) $r->total, 'amount' => (float) $r->amount];
        }

        return response()->json(['start' => $start->toDateString(), 'end' => $end->toDateString(), 'data' => $out]);
    }

    /**
     * Return bookings grouped by marketing person (counts + amounts) for the given days or 'all'.
     */
    public function bookingsByMarketingChart(Request $request)
    {
        $daysParam = $request->query('days', '30');
        $end = Carbon::today();

        if (is_string($daysParam) && strtolower($daysParam) === 'all') {
            $earliest = NewBooking::whereNull('deleted_at')->min('created_at');
            $start = $earliest ? Carbon::parse($earliest)->startOfDay() : $end->copy()->subYears(1)->startOfDay();
        } else {
            $days = (int) $daysParam;
            $days = max(1, min(365, $days));
            $start = Carbon::today()->subDays($days - 1)->startOfDay();
        }

        $rows = NewBooking::whereNotNull('marketing_id')
            ->whereNull('new_bookings.deleted_at')
            ->leftJoin('users', function ($join) {
                $join->on('new_bookings.marketing_id', '=', 'users.user_code');
            })
            ->leftJoin('booking_items', function($join){
                $join->on('booking_items.new_booking_id', '=', 'new_bookings.id')
                     ->whereNull('booking_items.deleted_at');
            })
            ->whereBetween('new_bookings.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('COALESCE(users.name, new_bookings.marketing_id) as marketing, COUNT(DISTINCT new_bookings.id) as total, COALESCE(SUM(booking_items.amount), 0) as amount')
            ->groupBy('marketing')
            ->orderByDesc('total')
            ->limit(30)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->marketing] = ['total' => (int) $r->total, 'amount' => (float) $r->amount];
        }

        return response()->json(['start' => $start->toDateString(), 'end' => $end->toDateString(), 'data' => $out]);
    }

    /**
     * Helper: get analyst workload grouped by analyst name (case-insensitive user_code match)
     * @param int|null $days  Number of days (null => all time)
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getAnalystWorkload(?int $days = null, int $limit = 10)
    {
        $now = Carbon::now();

        $query = DB::table('booking_items')
            ->leftJoin('users', function ($join) {
                // case-insensitive join: lower(users.user_code) = lower(booking_items.lab_analysis_code)
                $join->on(DB::raw('LOWER(users.user_code)'), '=', DB::raw('LOWER(booking_items.lab_analysis_code)'));
            })
            ->select(DB::raw('COALESCE(users.name, booking_items.lab_analysis_code) as name'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('booking_items.lab_analysis_code');

        if ($days && $days > 0) {
            $start = $now->copy()->subDays($days - 1)->startOfDay();
            $end = $now->endOfDay();
            $query->whereBetween('booking_items.created_at', [$start, $end]);
        }

        // Select both the raw lab_analysis_code and the joined user name explicitly
        $rows = $query->select(DB::raw('booking_items.lab_analysis_code as code'), DB::raw('users.name as name'), DB::raw('COUNT(*) as count'))
            ->groupBy('booking_items.lab_analysis_code', 'users.name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        // Map: prefer user name when present, otherwise show the raw code (trimmed)
        return $rows->map(function ($r) {
            $code = is_string($r->code) ? trim($r->code) : null;
            $display = $r->name ? $r->name : ($code ?: 'Unknown');
            return [
                'name' => $display,
                'code' => $code,
                'count' => (int) $r->count,
            ];
        });
    }

    /**
     * Return counts for every user that has the Lab Analyst role.
     * Includes users with zero bookings.
     * @param int|null $days
     * @return \Illuminate\Support\Collection
     */
    protected function getAllAnalystWorkload(?int $days = null)
    {
        // Find lab analyst role id (fallback to 1)
        $labRoleId = DB::table('roles')->where('slug', 'lab_analyst')->value('id') ?? 1;

        $now = Carbon::now();

        $start = null;
        $end = null;
        if ($days && $days > 0) {
            $start = $now->copy()->subDays($days - 1)->startOfDay();
            $end = $now->endOfDay();
        }

        // Use subqueries per-user to avoid accidental duplication from joins.
        // This counts booking_items where the analyst is referenced either by user_code
        // or by the analyst's name stored in the `status` column. It also computes
        // overdue items where lab_expected_date is before today.

        $dateFilter = '';
        if ($start && $end) {
            $s = $start->format('Y-m-d H:i:s');
            $e = $end->format('Y-m-d H:i:s');
            $dateFilter = "AND booking_items.created_at BETWEEN '{$s}' AND '{$e}'";
        }

        $rows = DB::table('users')
            ->where('users.role_id', $labRoleId)
            ->select(
                'users.id',
                'users.name',
                'users.user_code',
                DB::raw("(
                    SELECT COUNT(*) FROM booking_items
                    WHERE booking_items.deleted_at IS NULL
                      AND (
                            LOWER(TRIM(booking_items.lab_analysis_code)) = LOWER(TRIM(users.user_code))
                         OR LOWER(TRIM(booking_items.status)) = LOWER(TRIM(users.name))
                      )
                      {$dateFilter}
                ) as total_count"),
                DB::raw("(
                    SELECT COUNT(*) FROM booking_items
                    WHERE booking_items.deleted_at IS NULL
                      AND (
                            LOWER(TRIM(booking_items.lab_analysis_code)) = LOWER(TRIM(users.user_code))
                         OR LOWER(TRIM(booking_items.status)) = LOWER(TRIM(users.name))
                      )
                      AND booking_items.lab_expected_date < CURDATE()
                      {$dateFilter}
                ) as overdue_count")
            )
            ->orderByDesc('total_count')
            ->get();

        return $rows->map(function ($r) {
            return [
                'name' => $r->name ?: trim($r->user_code) ?: 'Unknown',
                'code' => trim($r->user_code ?? ''),
                'count' => (int) ($r->total_count ?? 0),
                'overdue' => (int) ($r->overdue_count ?? 0),
            ];
        });
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
            'hr-manager' => 'hr',
            'hr-executive' => 'hr',
            'hr-team' => 'hr',
            'lab' => 'lab',
            'laboratory' => 'lab',
            'quality-lab' => 'lab',
            'lab-analyst' => 'lab',
            'lab-analysts' => 'lab',
            'accounts' => 'accountant',
            'account' => 'accountant',
            'accounting' => 'accountant',
            'finance' => 'accountant',
            'accountant' => 'accountant',
            'accounts-manager' => 'accountant',
            'accounts-team' => 'accountant',
            'account-manager' => 'accountant',
            'computer-operator' => 'computer-operator',
            'computer-operations' => 'computer-operator',
            'data-entry' => 'computer-operator',
            'operations' => 'computer-operator',
            'computer-incharge' => 'computer-incharge',
            'computer-in-charge' => 'computer-incharge',
            'it-coordinator' => 'computer-incharge',
            'it-incharge' => 'computer-incharge',
            'marketing' => 'marketing',
            'marketing-person' => 'marketing',
            'marketing-team' => 'marketing',
            'marketing-personnel' => 'marketing',
            'tech-manager' => 'tech-manager',
            'tech-head' => 'tech-manager',
            'technology-head' => 'tech-manager',
            'technology-manager' => 'tech-manager',
            'quality-manager' => 'quality-manager',
            'quality-head' => 'quality-manager',
            'qa-manager' => 'quality-manager',
            'general-manager' => 'general-manager',
            'gm' => 'general-manager',
            'gm-office' => 'general-manager',
            'general-management' => 'general-manager',
            'receptionist' => 'receptionist',
            'front-office' => 'receptionist',
            'front-office-executive' => 'receptionist',
            'frontdesk' => 'receptionist',
            'office-coordinator' => 'office-coordinator',
            'coordinator' => 'office-coordinator',
            'office-admin' => 'office-coordinator',
            'office-administrator' => 'office-coordinator',
            'employee' => 'employee',
            'employees' => 'employee',
            'staff' => 'employee',
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
            'computer-incharge' => $this->buildComputerInchargePayload($user),
            'tech-manager' => $this->buildTechManagerPayload($user),
            'quality-manager' => $this->buildQualityManagerPayload($user),
            'general-manager' => $this->buildGeneralManagerPayload(),
            'receptionist' => $this->buildReceptionistPayload(),
            'office-coordinator' => $this->buildOfficeCoordinatorPayload(),
            'employee' => $this->buildEmployeePayload($user),
            default => $this->buildGenericPayload($department, $user),
        };
    }

    protected function chartColors(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $palette = $this->chartPalette;
        if ($count <= count($palette)) {
            return array_slice($palette, 0, $count);
        }

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
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

        $cacheKey = "marketing_payload:{$userCode}";

        return Cache::remember($cacheKey, 60, function () use ($user, $userCode) {
            $bookingQuery = NewBooking::where('marketing_id', $userCode);
            $expenseBaseQuery = MarketingExpense::where('marketing_person_code', $userCode);

            $pendingExpenses = (clone $expenseBaseQuery)->where('status', 'pending')->count();
            $approvedExpenseAmount = (clone $expenseBaseQuery)->where('status', 'approved')->sum('approved_amount');
            $rejectedExpenses = (clone $expenseBaseQuery)->where('status', 'rejected')->count();
            $expensesThisMonth = (clone $expenseBaseQuery)
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->sum('amount');

            $trendStart = Carbon::now()->subMonths(5)->startOfMonth();
            $trendEnd = Carbon::now()->endOfMonth();

            $expenseTrendRaw = (clone $expenseBaseQuery)
                ->whereBetween('created_at', [$trendStart, $trendEnd])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COALESCE(SUM(amount), 0) as total')
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');

            $expenseTrendLabels = [];
            $expenseTrendData = [];
            for ($i = 0; $i < 6; $i++) {
                $month = $trendStart->copy()->addMonths($i);
                $key = $month->format('Y-m');
                $expenseTrendLabels[] = $month->format('M Y');
                $expenseTrendData[] = (float) ($expenseTrendRaw[$key] ?? 0);
            }

            $statusBreakdown = (clone $expenseBaseQuery)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->get();

            $statusLabels = [];
            $statusData = [];
            foreach ($statusBreakdown as $row) {
                $label = $row->status ? Str::title(str_replace(['_', '-'], ' ', $row->status)) : 'Unspecified';
                $statusLabels[] = $label;
                $statusData[] = (int) $row->total;
            }
            if (empty($statusLabels)) {
                $statusLabels = ['No Data'];
                $statusData = [0];
            }

            $rangeStart = Carbon::today()->subDays(6);
            $rangeEnd = Carbon::today();

            $bookingTrendRaw = (clone $bookingQuery)
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('total', 'day');

            $bookingTrendLabels = [];
            $bookingTrendData = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $rangeStart->copy()->addDays($i);
                $key = $day->format('Y-m-d');
                $bookingTrendLabels[] = $day->format('d M');
                $bookingTrendData[] = (int) ($bookingTrendRaw[$key] ?? 0);
            }

            // --- Per-marketing-person detailed stats (for profile section) ---
            $bookingStats = DB::table('new_bookings')
                ->leftJoin('booking_items', function($join) {
                    $join->on('new_bookings.id', '=', 'booking_items.new_booking_id')
                         ->whereNull('booking_items.deleted_at');
                })
                ->where('new_bookings.marketing_id', $userCode)
                ->whereNull('new_bookings.deleted_at')
                ->selectRaw('COUNT(DISTINCT new_bookings.id) as total_bookings')
                ->selectRaw('COALESCE(SUM(booking_items.amount), 0) as total_booking_amount')
                ->selectRaw("COUNT(DISTINCT CASE WHEN new_bookings.payment_option = 'bill' THEN new_bookings.id END) as bill_bookings")
                ->selectRaw("COALESCE(SUM(CASE WHEN new_bookings.payment_option = 'bill' THEN booking_items.amount ELSE 0 END), 0) as bill_amount")
                ->selectRaw("COUNT(DISTINCT CASE WHEN new_bookings.payment_option <> 'bill' OR new_bookings.payment_option IS NULL THEN new_bookings.id END) as without_bill_bookings")
                ->selectRaw("COALESCE(SUM(CASE WHEN new_bookings.payment_option <> 'bill' OR new_bookings.payment_option IS NULL THEN booking_items.amount ELSE 0 END), 0) as without_bill_amount")
                ->first();

            $totalBookings = (int) ($bookingStats?->total_bookings ?? 0);
            $totalBookingAmount = (float) ($bookingStats?->total_booking_amount ?? 0);
            $billBookings = (int) ($bookingStats?->bill_bookings ?? 0);
            $totalBillBookingAmount = (float) ($bookingStats?->bill_amount ?? 0);
            $withoutBillBookings = (int) ($bookingStats?->without_bill_bookings ?? 0);
            $totalWithoutBillBookings = (float) ($bookingStats?->without_bill_amount ?? 0);

            $notGeneratedStats = DB::table('new_bookings')
                ->leftJoin('booking_items', function($join) {
                    $join->on('new_bookings.id', '=', 'booking_items.new_booking_id')
                         ->whereNull('booking_items.deleted_at');
                })
                ->leftJoin('invoices', 'new_bookings.id', '=', 'invoices.new_booking_id')
                ->where('new_bookings.marketing_id', $userCode)
                ->whereNull('new_bookings.deleted_at')
                ->whereNull('invoices.id')
                ->selectRaw('COUNT(DISTINCT new_bookings.id) as total')
                ->selectRaw('COALESCE(SUM(booking_items.amount), 0) as amount')
                ->first();

            $notGeneratedInvoicesCount = (int) ($notGeneratedStats?->total ?? 0);
            $totalNotGeneratedInvoicesAmount = (float) ($notGeneratedStats?->amount ?? 0);

            $invoiceBase = Invoice::where('marketing_user_code', $userCode);
            $unpaidInvoiceStats = (clone $invoiceBase)
                ->whereDoesntHave('transactions')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(total_amount), 0) as amount')
                ->first();
            $unpaidInvoices = (int) ($unpaidInvoiceStats?->total ?? 0);
            $totalUnpaidInvoiceAmount = (float) ($unpaidInvoiceStats?->amount ?? 0);
            $paidInvoicesAmount = (float) (clone $invoiceBase)->whereHas('transactions')->sum('total_amount');

            $transactionsSummary = InvoiceTransaction::where('marketing_person_id', $userCode)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(amount_received), 0) as amount')
                ->first();
            $transactionsCount = (int) ($transactionsSummary?->total ?? 0);
            $totalTransactionsAmount = (float) ($transactionsSummary?->amount ?? 0);

            $tdsAmount = (float) InvoiceTds::where('marketing_person_id', $userCode)->sum('tds_amount');

            $cashPaymentSummary = CashLetterPayment::where('marketing_person_id', $userCode)
                ->selectRaw('SUM(CASE WHEN transaction_status = 2 THEN 1 ELSE 0 END) as paid_letters')
                ->selectRaw('COALESCE(SUM(CASE WHEN transaction_status = 2 THEN amount_received ELSE 0 END), 0) as paid_amount')
                ->selectRaw('SUM(CASE WHEN transaction_status = 0 THEN 1 ELSE 0 END) as unpaid_letters')
                ->selectRaw('COALESCE(SUM(CASE WHEN transaction_status = 0 THEN amount_received ELSE 0 END), 0) as unpaid_amount')
                ->selectRaw('SUM(CASE WHEN transaction_status = 1 THEN 1 ELSE 0 END) as partial_letters')
                ->selectRaw('COALESCE(SUM(CASE WHEN transaction_status = 1 THEN amount_received ELSE 0 END), 0) as partial_amount')
                ->selectRaw('SUM(CASE WHEN transaction_status = 3 THEN 1 ELSE 0 END) as settled_letters')
                ->selectRaw('COALESCE(SUM(CASE WHEN transaction_status = 3 THEN amount_received ELSE 0 END), 0) as settled_amount')
                ->first();

            $cashPaidLetters = (int) ($cashPaymentSummary?->paid_letters ?? 0);
            $totalCashPaidLettersAmount = (float) ($cashPaymentSummary?->paid_amount ?? 0);
            $cashUnpaidLetters = (int) ($cashPaymentSummary?->unpaid_letters ?? 0);
            $totalCashUnpaidAmounts = (float) ($cashPaymentSummary?->unpaid_amount ?? 0);
            $cashPartialLetters = (int) ($cashPaymentSummary?->partial_letters ?? 0);
            $totalDueAmount = (float) ($cashPaymentSummary?->partial_amount ?? 0);
            $cashSettledLetters = (int) ($cashPaymentSummary?->settled_letters ?? 0);
            $totalSettledAmount = (float) ($cashPaymentSummary?->settled_amount ?? 0);

            $clientsCount = (clone $bookingQuery)
                ->whereNotNull('client_id')
                ->distinct()
                ->count('client_id');

            $stats = [
                'transactions' => $transactionsCount,
                'totalTransactionsAmount' => (float) $totalTransactionsAmount,
                'totalBookings' => $totalBookings,
                'totalBookingAmount' => (float) $totalBookingAmount,
                'billBookings' => $billBookings,
                'totalBillBookingAmount' => (float) $totalBillBookingAmount,
                'withoutBillBookings' => $withoutBillBookings,
                'totalWithoutBillBookings' => (float) $totalWithoutBillBookings,
                'notGeneratedInvoices' => $notGeneratedInvoicesCount,
                'totalNotGeneratedInvoicesAmount' => (float) $totalNotGeneratedInvoicesAmount,
                'unpaidInvoices' => $unpaidInvoices,
                'totalUnpaidInvoiceAmount' => (float) $totalUnpaidInvoiceAmount,
                'totalPaidInvoiceAmount' => (float) $paidInvoicesAmount,
                'tdsAmount' => (float) $tdsAmount,
                'cashPaidLetters' => $cashPaidLetters,
                'totalCashPaidLettersAmount' => (float) $totalCashPaidLettersAmount,
                'cashUnpaidLetters' => $cashUnpaidLetters,
                'totalCashUnpaidAmounts' => (float) $totalCashUnpaidAmounts,
                'cashPartialLetters' => $cashPartialLetters,
                'totalDueAmount' => (float) $totalDueAmount,
                'cashSettledLetters' => $cashSettledLetters,
                'totalSettledAmount' => (float) $totalSettledAmount,
                'allClients' => $clientsCount,
            ];

            $primaryColor = $this->chartColors(1)[0] ?? '#4e79a7';
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
                // include marketingPerson and detailed stats for profile partial
                'marketingPerson' => $user,
                'stats' => $stats,
                'quick_links' => $this->marketingQuickLinks(),
                'insights' => [
                    'message' => 'Track expense submissions and follow up on pending approvals to keep campaigns moving.',
                ],
                'charts' => [
                    [
                        'id' => 'marketingExpenseTrend',
                        'type' => 'line',
                        'title' => 'Expense Submissions (Last 6 Months)',
                        'labels' => $expenseTrendLabels,
                        'datasets' => [[
                            'label' => 'Amount (₹)',
                            'data' => $expenseTrendData,
                            'borderColor' => $primaryColor,
                            'backgroundColor' => 'rgba(78,121,167,0.15)',
                            'tension' => 0.35,
                            'fill' => true,
                        ]],
                        'height' => 320,
                    ],
                    [
                        'id' => 'marketingExpenseStatus',
                        'type' => 'doughnut',
                        'title' => 'Expense Status Mix',
                        'labels' => $statusLabels,
                        'datasets' => [[
                            'label' => 'Expenses',
                            'data' => $statusData,
                            'backgroundColor' => $this->chartColors(count($statusLabels)),
                        ]],
                        'height' => 280,
                    ],
                    [
                        'id' => 'marketingBookingTrend',
                        'type' => 'bar',
                        'title' => 'Bookings Captured (Last 7 Days)',
                        'labels' => $bookingTrendLabels,
                        'datasets' => [[
                            'label' => 'Bookings',
                            'data' => $bookingTrendData,
                            'backgroundColor' => $this->chartColors(7),
                        ]],
                        'height' => 320,
                    ],
                ],
            ];
        });
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

        $attendanceCounts = AttendanceRecord::select('status', DB::raw('COUNT(*) as total'))
            ->whereDate('attendance_date', $today)
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusLabels = AttendanceRecord::statusLabels();
        $attendanceChartLabels = [];
        $attendanceChartData = [];

        foreach ($statusLabels as $status => $label) {
            $attendanceChartLabels[] = $label;
            $attendanceChartData[] = (int) ($attendanceCounts[$status] ?? 0);
        }

        $hireStart = Carbon::now()->subMonths(5)->startOfMonth();
        $hireEnd = Carbon::now()->endOfMonth();

        $hireTrendRaw = Employee::whereNotNull('date_of_joining')
            ->whereBetween('date_of_joining', [$hireStart, $hireEnd])
            ->selectRaw('DATE_FORMAT(date_of_joining, "%Y-%m") as ym, COUNT(*) as total')
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $hireLabels = [];
        $hireData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $hireStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $hireLabels[] = $month->format('M Y');
            $hireData[] = (int) ($hireTrendRaw[$key] ?? 0);
        }

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
            'charts' => [
                [
                    'id' => 'hrAttendanceStatus',
                    'type' => 'doughnut',
                    'title' => 'Attendance Status Today',
                    'labels' => $attendanceChartLabels,
                    'datasets' => [[
                        'label' => 'Employees',
                        'data' => $attendanceChartData,
                        'backgroundColor' => $this->chartColors(count($attendanceChartLabels)),
                    ]],
                    'height' => 280,
                ],
                [
                    'id' => 'hrHiringTrend',
                    'type' => 'bar',
                    'title' => 'New Hires (Last 6 Months)',
                    'labels' => $hireLabels,
                    'datasets' => [[
                        'label' => 'New Hires',
                        'data' => $hireData,
                        'backgroundColor' => $this->chartColors(6),
                    ]],
                    'height' => 320,
                ],
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

        $dueRangeStart = Carbon::today();
        $dueRangeEnd = Carbon::today()->addDays(6);

        $dueCounts = BookingItem::where('lab_analysis_code', $userCode)
            ->whereNotNull('lab_expected_date')
            ->whereBetween('lab_expected_date', [$dueRangeStart, $dueRangeEnd])
            ->selectRaw('DATE(lab_expected_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $completedCounts = BookingItem::where('lab_analysis_code', $userCode)
            ->whereNotNull('issue_date')
            ->whereBetween('issue_date', [$dueRangeStart, $dueRangeEnd])
            ->selectRaw('DATE(issue_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $pipelineLabels = [];
        $pipelineDue = [];
        $pipelineDone = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $dueRangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $pipelineLabels[] = $day->format('d M');
            $pipelineDue[] = (int) ($dueCounts[$key] ?? 0);
            $pipelineDone[] = (int) ($completedCounts[$key] ?? 0);
        }

        $pipelineColors = $this->chartColors(2);

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
            'charts' => [
                [
                    'id' => 'labPipelineTrend',
                    'type' => 'line',
                    'title' => 'Sample Pipeline (Next 7 Days)',
                    'labels' => $pipelineLabels,
                    'datasets' => [
                        [
                            'label' => 'Due',
                            'data' => $pipelineDue,
                            'borderColor' => $pipelineColors[0] ?? '#4e79a7',
                            'backgroundColor' => 'rgba(78,121,167,0.15)',
                            'tension' => 0.4,
                            'fill' => true,
                        ],
                        [
                            'label' => 'Completed',
                            'data' => $pipelineDone,
                            'borderColor' => $pipelineColors[1] ?? '#f28e2b',
                            'backgroundColor' => 'rgba(242,142,43,0.15)',
                            'tension' => 0.4,
                            'fill' => true,
                        ],
                    ],
                    'height' => 320,
                ],
                [
                    'id' => 'labStatusSplit',
                    'type' => 'polarArea',
                    'title' => 'Workload Distribution',
                    'labels' => ['Assigned', 'Completed', 'Pending Reports', 'Due Today'],
                    'datasets' => [[
                        'label' => 'Samples',
                        'data' => [
                            (int) $assignedSamples,
                            (int) $completedReports,
                            (int) $pendingReports,
                            (int) $dueToday,
                        ],
                        'backgroundColor' => $this->chartColors(4),
                    ]],
                    'height' => 280,
                    'options' => [
                        'scales' => ['r' => ['beginAtZero' => true]],
                    ],
                ],
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

        $seriesStart = Carbon::now()->subMonths(5)->startOfMonth();
        $seriesEnd = Carbon::now()->endOfMonth();

        $raisedRaw = Invoice::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COALESCE(SUM(total_amount), 0) as amount')
            ->whereBetween('created_at', [$seriesStart, $seriesEnd])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('amount', 'ym');

        $collectedRaw = InvoiceTransaction::selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as ym, COALESCE(SUM(amount_received), 0) as amount')
            ->whereBetween('transaction_date', [$seriesStart, $seriesEnd])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('amount', 'ym');

        $monthLabels = [];
        $raisedData = [];
        $collectedData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $seriesStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $monthLabels[] = $month->format('M Y');
            $raisedData[] = (float) ($raisedRaw[$key] ?? 0);
            $collectedData[] = (float) ($collectedRaw[$key] ?? 0);
        }

        $lineColors = $this->chartColors(2);

        $paymentBreakdown = InvoiceTransaction::select('payment_mode', DB::raw('COALESCE(SUM(amount_received), 0) as total'))
            ->groupBy('payment_mode')
            ->pluck('total', 'payment_mode');

        $paymentLabels = [];
        $paymentData = [];
        foreach ($paymentBreakdown as $mode => $total) {
            $paymentLabels[] = Str::title(str_replace(['_', '-'], ' ', $mode ?: 'Other'));
            $paymentData[] = (float) $total;
        }

        if (empty($paymentLabels)) {
            $paymentLabels = ['No Data'];
            $paymentData = [0];
        }

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
            'charts' => [
                [
                    'id' => 'accountRevenueTrend',
                    'type' => 'line',
                    'title' => 'Invoices Raised vs Collections',
                    'labels' => $monthLabels,
                    'datasets' => [
                        [
                            'label' => 'Raised (₹)',
                            'data' => $raisedData,
                            'borderColor' => $lineColors[0] ?? '#4e79a7',
                            'backgroundColor' => 'rgba(78,121,167,0.15)',
                            'tension' => 0.3,
                            'fill' => true,
                        ],
                        [
                            'label' => 'Collected (₹)',
                            'data' => $collectedData,
                            'borderColor' => $lineColors[1] ?? '#f28e2b',
                            'backgroundColor' => 'rgba(242,142,43,0.15)',
                            'tension' => 0.3,
                            'fill' => true,
                        ],
                    ],
                    'height' => 320,
                ],
                [
                    'id' => 'accountPaymentBreakdown',
                    'type' => 'doughnut',
                    'title' => 'Payment Mode Distribution',
                    'labels' => $paymentLabels,
                    'datasets' => [[
                        'label' => 'Amount (₹)',
                        'data' => $paymentData,
                        'backgroundColor' => $this->chartColors(count($paymentLabels)),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildTechManagerPayload($user): array
    {
        $monthRange = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        $seriesStart = Carbon::now()->subMonths(5)->startOfMonth();
        $seriesEnd = Carbon::now()->endOfMonth();
        $today = Carbon::today();

        $totalProducts = Product::count();
        $productsThisMonth = Product::whereBetween('created_at', $monthRange)->count();
        $documentsThisMonth = Document::whereBetween('created_at', $monthRange)->count();
        $pendingApprovals = Approval::whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])->count();
        $overdueApprovals = Approval::whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $productTrendRaw = Product::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
            ->whereBetween('created_at', [$seriesStart, $seriesEnd])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $trendLabels = [];
        $trendData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $seriesStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendData[] = (int) ($productTrendRaw[$key] ?? 0);
        }

        $approvalStatusRaw = Approval::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $approvalLabels = [];
        $approvalData = [];
        foreach ($approvalStatusRaw as $row) {
            $label = $row->status ? Str::title(str_replace(['_', '-'], ' ', $row->status)) : 'Unspecified';
            $approvalLabels[] = $label;
            $approvalData[] = (int) $row->total;
        }

        if (empty($approvalLabels)) {
            $approvalLabels = ['No Data'];
            $approvalData = [0];
        }

        $primaryColor = $this->chartColors(1)[0] ?? '#4e79a7';

        return [
            'metrics' => [
                [
                    'label' => 'Active Products',
                    'value' => $totalProducts,
                    'icon' => 'ti ti-cpu',
                    'type' => 'primary',
                ],
                [
                    'label' => 'New Products (MTD)',
                    'value' => $productsThisMonth,
                    'icon' => 'ti ti-rocket',
                    'type' => 'info',
                ],
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'icon' => 'ti ti-clipboard-list',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Overdue Approvals',
                    'value' => $overdueApprovals,
                    'icon' => 'ti ti-alert-triangle',
                    'type' => 'danger',
                ],
                [
                    'label' => 'Documents Uploaded (MTD)',
                    'value' => $documentsThisMonth,
                    'icon' => 'ti ti-folder',
                    'type' => 'success',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Create Product',
                    'url' => route('superadmin.products.addProduct'),
                    'icon' => 'ti ti-tools',
                ],
                [
                    'label' => 'Tech Documents',
                    'url' => route('superadmin.documents.index'),
                    'icon' => 'ti ti-folders',
                ],
                [
                    'label' => 'Approval Board',
                    'url' => route('superadmin.approvals.index'),
                    'icon' => 'ti ti-checkup-list',
                ],
                [
                    'label' => 'Product Categories',
                    'url' => route('superadmin.categories.index'),
                    'icon' => 'ti ti-category',
                ],
            ],
            'insights' => [
                'message' => 'Review overdue approvals and align documentation with the latest product rollouts.',
            ],
            'charts' => [
                [
                    'id' => 'techProductTrend',
                    'type' => 'bar',
                    'title' => 'Product Additions (Last 6 Months)',
                    'labels' => $trendLabels,
                    'datasets' => [[
                        'label' => 'New Products',
                        'data' => $trendData,
                        'backgroundColor' => $primaryColor,
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'techApprovalStatus',
                    'type' => 'doughnut',
                    'title' => 'Approval Status Mix',
                    'labels' => $approvalLabels,
                    'datasets' => [[
                        'label' => 'Approvals',
                        'data' => $approvalData,
                        'backgroundColor' => $this->chartColors(count($approvalLabels)),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildQualityManagerPayload($user): array
    {
        $today = Carbon::today();
        $rangeStart = $today->copy();
        $rangeEnd = $today->copy()->addDays(6);
        $weekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];

        $pendingSamples = BookingItem::whereNull('issue_date')->count();
        $dueToday = BookingItem::whereNull('issue_date')
            ->whereNotNull('lab_expected_date')
            ->whereDate('lab_expected_date', $today)
            ->count();
        $overdueSamples = BookingItem::whereNull('issue_date')
            ->whereNotNull('lab_expected_date')
            ->whereDate('lab_expected_date', '<', $today)
            ->count();

        $reportsIssuedWeek = DB::table('booking_item_report')->whereBetween('created_at', $weekRange)->count();
        if (DB::getSchemaBuilder()->hasTable('booking_item_report_28day')) {
            $reportsIssuedWeek += DB::table('booking_item_report_28day')->whereBetween('created_at', $weekRange)->count();
        }

        $pipelineDueRaw = BookingItem::whereNull('issue_date')
            ->whereNotNull('lab_expected_date')
            ->whereBetween('lab_expected_date', [$rangeStart, $rangeEnd])
            ->selectRaw('DATE(lab_expected_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $pipelineCompletedRaw = BookingItem::whereNotNull('issue_date')
            ->whereBetween('issue_date', [$rangeStart, $rangeEnd])
            ->selectRaw('DATE(issue_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $pipelineLabels = [];
        $pipelineDue = [];
        $pipelineCompleted = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $rangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $pipelineLabels[] = $day->format('d M');
            $pipelineDue[] = (int) ($pipelineDueRaw[$key] ?? 0);
            $pipelineCompleted[] = (int) ($pipelineCompletedRaw[$key] ?? 0);
        }

        $issuedSamples = BookingItem::whereNotNull('issue_date')->count();
        $awaitingReceipt = BookingItem::whereNull('received_at')->count();

        $statusLabels = ['Awaiting Issue', 'Issued', 'Awaiting Receipt'];
        $statusData = [
            (int) $pendingSamples,
            (int) $issuedSamples,
            (int) $awaitingReceipt,
        ];

        $lineColors = $this->chartColors(2);

        return [
            'metrics' => [
                [
                    'label' => 'Pending Samples',
                    'value' => $pendingSamples,
                    'icon' => 'ti ti-flask',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Due Today',
                    'value' => $dueToday,
                    'icon' => 'ti ti-clock-hour-3',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Overdue Samples',
                    'value' => $overdueSamples,
                    'icon' => 'ti ti-alert-triangle',
                    'type' => 'danger',
                ],
                [
                    'label' => 'Reports Issued (This Week)',
                    'value' => $reportsIssuedWeek,
                    'icon' => 'ti ti-report',
                    'type' => 'success',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Lab Schedule',
                    'url' => route('superadmin.labanalysts.index'),
                    'icon' => 'ti ti-flask-2',
                ],
                [
                    'label' => 'Pending Dispatch',
                    'url' => route('superadmin.reporting.pendings'),
                    'icon' => 'ti ti-truck',
                ],
                [
                    'label' => 'Generate Reports',
                    'url' => route('superadmin.reporting.generate'),
                    'icon' => 'ti ti-printer',
                ],
                [
                    'label' => 'Calibrations',
                    'url' => route('superadmin.calibrations.index'),
                    'icon' => 'ti ti-adjustments',
                ],
            ],
            'insights' => [
                'message' => 'Balance pending workload with upcoming commitments to keep lab throughput steady.',
            ],
            'charts' => [
                [
                    'id' => 'qualityPipeline',
                    'type' => 'line',
                    'title' => 'Pipeline (Next 7 Days)',
                    'labels' => $pipelineLabels,
                    'datasets' => [
                        [
                            'label' => 'Due',
                            'data' => $pipelineDue,
                            'borderColor' => $lineColors[0] ?? '#4e79a7',
                            'backgroundColor' => 'rgba(78,121,167,0.15)',
                            'tension' => 0.3,
                            'fill' => true,
                        ],
                        [
                            'label' => 'Completed',
                            'data' => $pipelineCompleted,
                            'borderColor' => $lineColors[1] ?? '#f28e2b',
                            'backgroundColor' => 'rgba(242,142,43,0.15)',
                            'tension' => 0.3,
                            'fill' => true,
                        ],
                    ],
                    'height' => 320,
                ],
                [
                    'id' => 'qualityStatusSplit',
                    'type' => 'polarArea',
                    'title' => 'Process Status',
                    'labels' => $statusLabels,
                    'datasets' => [[
                        'label' => 'Samples',
                        'data' => $statusData,
                        'backgroundColor' => $this->chartColors(count($statusLabels)),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildComputerInchargePayload($user): array
    {
        $today = Carbon::today();
        $rangeStart = Carbon::today()->subDays(6);
        $rangeEnd = Carbon::today();

        $documentsToday = Document::whereDate('created_at', $today)->count();
        $totalDocuments = Document::count();
        $pendingApprovals = Approval::whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])->count();
        $unassignedSamples = BookingItem::whereNull('lab_analysis_code')->count();

        $docTypeRaw = Document::selectRaw('COALESCE(NULLIF(type, ""), "General") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $docLabels = [];
        $docData = [];
        foreach ($docTypeRaw as $row) {
            $docLabels[] = Str::title(str_replace(['_', '-'], ' ', $row->label));
            $docData[] = (int) $row->total;
        }
        if (empty($docLabels)) {
            $docLabels = ['General'];
            $docData = [0];
        }

        $bookingTrendRaw = NewBooking::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $trendLabels = [];
        $trendData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $rangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $trendLabels[] = $day->format('d M');
            $trendData[] = (int) ($bookingTrendRaw[$key] ?? 0);
        }

        return [
            'metrics' => [
                [
                    'label' => 'Documents Today',
                    'value' => $documentsToday,
                    'icon' => 'ti ti-file-upload',
                    'type' => 'info',
                ],
                [
                    'label' => 'Repository Size',
                    'value' => $totalDocuments,
                    'icon' => 'ti ti-database',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'icon' => 'ti ti-clipboard-check',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Unassigned Samples',
                    'value' => $unassignedSamples,
                    'icon' => 'ti ti-alert-square-rounded',
                    'type' => 'danger',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Document Library',
                    'url' => route('superadmin.documents.index'),
                    'icon' => 'ti ti-folders',
                ],
                [
                    'label' => 'Approval Queue',
                    'url' => route('superadmin.approvals.index'),
                    'icon' => 'ti ti-checkup-list',
                ],
                [
                    'label' => 'Store Inventory',
                    'url' => route('superadmin.store.Store'),
                    'icon' => 'ti ti-building-store',
                ],
                [
                    'label' => 'Issue Tracker',
                    'url' => route('superadmin.issue.Issue'),
                    'icon' => 'ti ti-clipboard-warning',
                ],
            ],
            'insights' => [
                'message' => 'Keep repositories organised and assign pending samples to maintain throughput.',
            ],
            'charts' => [
                [
                    'id' => 'computerDocumentTypes',
                    'type' => 'doughnut',
                    'title' => 'Document Distribution',
                    'labels' => $docLabels,
                    'datasets' => [[
                        'label' => 'Documents',
                        'data' => $docData,
                        'backgroundColor' => $this->chartColors(count($docLabels)),
                    ]],
                    'height' => 280,
                ],
                [
                    'id' => 'computerBookingTrend',
                    'type' => 'line',
                    'title' => 'Bookings Created (Last 7 Days)',
                    'labels' => $trendLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $trendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                    'height' => 320,
                ],
            ],
        ];
    }

    protected function buildGeneralManagerPayload(): array
    {
        $monthRange = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        $seriesStart = Carbon::now()->subMonths(5)->startOfMonth();
        $seriesEnd = Carbon::now()->endOfMonth();

        $revenueMonth = Invoice::whereBetween('created_at', $monthRange)->sum('total_amount');
        $collectionsMonth = InvoiceTransaction::whereBetween('transaction_date', $monthRange)->sum('amount_received');
        $newClientsMonth = Client::whereBetween('created_at', $monthRange)->count();
        $bookingsMonth = NewBooking::whereBetween('created_at', $monthRange)->count();

        $revenueTrendRaw = Invoice::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COALESCE(SUM(total_amount), 0) as total')
            ->whereBetween('created_at', [$seriesStart, $seriesEnd])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $trendLabels = [];
        $trendData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $seriesStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendData[] = (float) ($revenueTrendRaw[$key] ?? 0);
        }

        $departmentBreakdown = Department::select('departments.name', DB::raw('COUNT(new_bookings.id) as total'))
            ->leftJoin('new_bookings', 'departments.id', '=', 'new_bookings.department_id')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $departmentLabels = [];
        $departmentData = [];
        foreach ($departmentBreakdown as $row) {
            $departmentLabels[] = $row->name ?? 'Unassigned';
            $departmentData[] = (int) $row->total;
        }
        if (empty($departmentLabels)) {
            $departmentLabels = ['Unassigned'];
            $departmentData = [0];
        }

        return [
            'metrics' => [
                [
                    'label' => 'Revenue (MTD)',
                    'value' => number_format((float) $revenueMonth, 2),
                    'icon' => 'ti ti-currency-rupee',
                    'type' => 'success',
                ],
                [
                    'label' => 'Collections (MTD)',
                    'value' => number_format((float) $collectionsMonth, 2),
                    'icon' => 'ti ti-wallet',
                    'type' => 'primary',
                ],
                [
                    'label' => 'New Clients (MTD)',
                    'value' => $newClientsMonth,
                    'icon' => 'ti ti-user-plus',
                    'type' => 'info',
                ],
                [
                    'label' => 'Bookings (MTD)',
                    'value' => $bookingsMonth,
                    'icon' => 'ti ti-notebook',
                    'type' => 'warning',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Executive Dashboard',
                    'url' => route('superadmin.dashboard.index'),
                    'icon' => 'ti ti-dashboard',
                ],
                [
                    'label' => 'Invoice Register',
                    'url' => route('superadmin.invoices.index'),
                    'icon' => 'ti ti-file-invoice',
                ],
                [
                    'label' => 'Bookings Overview',
                    'url' => route('superadmin.showbooking.showBooking'),
                    'icon' => 'ti ti-calendar-stats',
                ],
                [
                    'label' => 'People Directory',
                    'url' => route('superadmin.employees.index'),
                    'icon' => 'ti ti-users',
                ],
            ],
            'insights' => [
                'message' => 'Compare booking momentum with realised collections to steer weekly priorities.',
            ],
            'charts' => [
                [
                    'id' => 'gmRevenueTrend',
                    'type' => 'line',
                    'title' => 'Revenue Trend (6M)',
                    'labels' => $trendLabels,
                    'datasets' => [[
                        'label' => 'Revenue (₹)',
                        'data' => $trendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'tension' => 0.3,
                        'fill' => true,
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'gmDepartmentContribution',
                    'type' => 'bar',
                    'title' => 'Bookings by Department',
                    'labels' => $departmentLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $departmentData,
                        'backgroundColor' => $this->chartColors(count($departmentLabels)),
                    ]],
                    'height' => 320,
                ],
            ],
        ];
    }

    protected function buildReceptionistPayload(): array
    {
        $today = Carbon::today();
        $rangeStart = $today->copy()->subDays(6);
        $rangeEnd = $today->copy();
        $weekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];

        $bookingsToday = NewBooking::whereDate('created_at', $today)->count();
        $visitsToday = NewBooking::whereDate('job_order_date', $today)->count();
        $holdBookings = NewBooking::where('hold_status', true)->count();
        $newClientsWeek = Client::whereBetween('created_at', $weekRange)->count();
        $upcomingVisits = NewBooking::whereNotNull('job_order_date')
            ->whereBetween('job_order_date', [$today, $today->copy()->addDays(3)])
            ->count();

        $bookingTrendRaw = NewBooking::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $trendLabels = [];
        $trendData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $rangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $trendLabels[] = $day->format('d M');
            $trendData[] = (int) ($bookingTrendRaw[$key] ?? 0);
        }

        $totalBookings = NewBooking::count();
        $activeBookings = max($totalBookings - $holdBookings, 0);

        return [
            'metrics' => [
                [
                    'label' => 'Bookings Today',
                    'value' => $bookingsToday,
                    'icon' => 'ti ti-calendar-plus',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Visits Today',
                    'value' => $visitsToday,
                    'icon' => 'ti ti-building',
                    'type' => 'info',
                ],
                [
                    'label' => 'Upcoming 3-Day Visits',
                    'value' => $upcomingVisits,
                    'icon' => 'ti ti-alarm',
                    'type' => 'warning',
                ],
                [
                    'label' => 'New Clients (This Week)',
                    'value' => $newClientsWeek,
                    'icon' => 'ti ti-user-plus',
                    'type' => 'success',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Create Booking',
                    'url' => route('superadmin.bookings.newbooking'),
                    'icon' => 'ti ti-calendar-plus',
                ],
                [
                    'label' => 'Booking Register',
                    'url' => route('superadmin.showbooking.showBooking'),
                    'icon' => 'ti ti-table',
                ],
                [
                    'label' => 'Client Directory',
                    'url' => route('superadmin.clients.index'),
                    'icon' => 'ti ti-users',
                ],
                [
                    'label' => 'Document Inbox',
                    'url' => route('superadmin.documents.index'),
                    'icon' => 'ti ti-inbox',
                ],
            ],
            'insights' => [
                'message' => 'Prioritise visitor scheduling and keep client handovers smooth for the week.',
            ],
            'charts' => [
                [
                    'id' => 'receptionBookingsTrend',
                    'type' => 'line',
                    'title' => 'Bookings (Last 7 Days)',
                    'labels' => $trendLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $trendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'tension' => 0.3,
                        'fill' => true,
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'receptionStatusSplit',
                    'type' => 'doughnut',
                    'title' => 'Booking Status Mix',
                    'labels' => ['Active', 'On Hold'],
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => [(int) $activeBookings, (int) $holdBookings],
                        'backgroundColor' => $this->chartColors(2),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildOfficeCoordinatorPayload(): array
    {
        $weekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $rangeStart = Carbon::today()->subDays(6);
        $rangeEnd = Carbon::today();

        $pendingApprovals = Approval::whereRaw('LOWER(COALESCE(status, "")) = ?', ['pending'])->count();
        $approvalsDueThisWeek = Approval::whereNotNull('due_date')
            ->whereBetween('due_date', $weekRange)
            ->count();
        $documentsThisWeek = Document::whereBetween('created_at', $weekRange)->count();
        $pendingLeaves = Leave::where('status', 'Applied')->count();

        $approvalStatusRaw = Approval::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();
        $approvalLabels = [];
        $approvalData = [];
        foreach ($approvalStatusRaw as $row) {
            $label = $row->status ? Str::title(str_replace(['_', '-'], ' ', $row->status)) : 'Unspecified';
            $approvalLabels[] = $label;
            $approvalData[] = (int) $row->total;
        }
        if (empty($approvalLabels)) {
            $approvalLabels = ['No Data'];
            $approvalData = [0];
        }

        $leaveStatusRaw = Leave::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();
        $leaveLabels = [];
        $leaveData = [];
        foreach ($leaveStatusRaw as $row) {
            $label = $row->status ? Str::title(str_replace(['_', '-'], ' ', $row->status)) : 'Unspecified';
            $leaveLabels[] = $label;
            $leaveData[] = (int) $row->total;
        }
        if (empty($leaveLabels)) {
            $leaveLabels = ['No Data'];
            $leaveData = [0];
        }

        $documentTrendRaw = Document::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');
        $documentTrendLabels = [];
        $documentTrendData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $rangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $documentTrendLabels[] = $day->format('d M');
            $documentTrendData[] = (int) ($documentTrendRaw[$key] ?? 0);
        }

        return [
            'metrics' => [
                [
                    'label' => 'Pending Approvals',
                    'value' => $pendingApprovals,
                    'icon' => 'ti ti-checkup-list',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Approvals Due (This Week)',
                    'value' => $approvalsDueThisWeek,
                    'icon' => 'ti ti-calendar-event',
                    'type' => 'danger',
                ],
                [
                    'label' => 'Documents Logged (This Week)',
                    'value' => $documentsThisWeek,
                    'icon' => 'ti ti-files',
                    'type' => 'info',
                ],
                [
                    'label' => 'Pending Leaves',
                    'value' => $pendingLeaves,
                    'icon' => 'ti ti-plane-departure',
                    'type' => 'primary',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'Approval Console',
                    'url' => route('superadmin.approvals.index'),
                    'icon' => 'ti ti-clipboard-check',
                ],
                [
                    'label' => 'Document Center',
                    'url' => route('superadmin.documents.index'),
                    'icon' => 'ti ti-folders',
                ],
                [
                    'label' => 'Leave Board',
                    'url' => route('superadmin.leave.Leave'),
                    'icon' => 'ti ti-calendar-stats',
                ],
                [
                    'label' => 'Employee Directory',
                    'url' => route('superadmin.employees.index'),
                    'icon' => 'ti ti-users',
                ],
            ],
            'insights' => [
                'message' => 'Keep approvals flowing and monitor leave balance to avoid last-minute escalations.',
            ],
            'charts' => [
                [
                    'id' => 'officeApprovalStatus',
                    'type' => 'bar',
                    'title' => 'Approval Status Overview',
                    'labels' => $approvalLabels,
                    'datasets' => [[
                        'label' => 'Approvals',
                        'data' => $approvalData,
                        'backgroundColor' => $this->chartColors(count($approvalLabels)),
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'officeLeaveStatus',
                    'type' => 'doughnut',
                    'title' => 'Leave Status Split',
                    'labels' => $leaveLabels,
                    'datasets' => [[
                        'label' => 'Leaves',
                        'data' => $leaveData,
                        'backgroundColor' => $this->chartColors(count($leaveLabels)),
                    ]],
                    'height' => 280,
                ],
                [
                    'id' => 'officeDocumentTrend',
                    'type' => 'line',
                    'title' => 'Documents Logged (7 Days)',
                    'labels' => $documentTrendLabels,
                    'datasets' => [[
                        'label' => 'Documents',
                        'data' => $documentTrendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                    'height' => 320,
                ],
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
        $totalBookings = (clone $bookingBaseQuery)->count();

        $trendStart = Carbon::today()->subDays(6);
        $trendEnd = Carbon::today();

        $bookingTrendRaw = (clone $bookingBaseQuery)
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $bookingTrendLabels = [];
        $bookingTrendData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $trendStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $bookingTrendLabels[] = $day->format('d M');
            $bookingTrendData[] = (int) ($bookingTrendRaw[$key] ?? 0);
        }

        $releasedBookings = max($totalBookings - $onHold, 0);
        $invoicedBookings = max($totalBookings - $invoicesPending, 0);

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
            'charts' => [
                [
                    'id' => 'operatorBookingTrend',
                    'type' => 'line',
                    'title' => 'Bookings Entered (Last 7 Days)',
                    'labels' => $bookingTrendLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $bookingTrendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'operatorHoldSplit',
                    'type' => 'doughnut',
                    'title' => 'Hold vs Released',
                    'labels' => ['On Hold', 'Released'],
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => [(int) $onHold, (int) $releasedBookings],
                        'backgroundColor' => $this->chartColors(2),
                    ]],
                    'height' => 280,
                ],
                [
                    'id' => 'operatorInvoiceStatus',
                    'type' => 'polarArea',
                    'title' => 'Invoice Progress',
                    'labels' => ['Awaiting Invoice', 'Invoiced'],
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => [(int) $invoicesPending, (int) $invoicedBookings],
                        'backgroundColor' => $this->chartColors(2),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildEmployeePayload($user): array
    {
        $employee = $user->employee;

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $pendingLeaves = Leave::where('user_id', $user->id)->where('status', 'Applied')->count();
        $approvedLeaves = Leave::where('user_id', $user->id)->where('status', 'Approved')->count();
        $rejectedLeaves = Leave::where('user_id', $user->id)->where('status', 'Rejected')->count();

        $presentDays = $employee
            ? AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                ->where('status', AttendanceRecord::STATUS_PRESENT)
                ->count()
            : 0;

        $absentDays = $employee
            ? AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                ->where('status', AttendanceRecord::STATUS_ABSENT)
                ->count()
            : 0;

        $attendanceRangeStart = Carbon::today()->subDays(6);
        $attendanceRangeEnd = Carbon::today();

        $attendanceRecords = $employee
            ? AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$attendanceRangeStart, $attendanceRangeEnd])
                ->get()
                ->keyBy(fn ($record) => $record->attendance_date->format('Y-m-d'))
            : collect();

        $attendanceLabels = [];
        $presentSeries = [];
        $leaveSeries = [];
        $absentSeries = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $attendanceRangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $attendanceLabels[] = $day->format('d M');
            $status = optional($attendanceRecords->get($key))->status;

            $presentSeries[] = $status === AttendanceRecord::STATUS_PRESENT ? 1 : 0;
            $leaveSeries[] = $status === AttendanceRecord::STATUS_ON_LEAVE ? 1 : 0;
            $absentSeries[] = $status === AttendanceRecord::STATUS_ABSENT ? 1 : 0;
        }

        $leaveBreakdown = Leave::where('user_id', $user->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $leaveLabels = [];
        $leaveData = [];
        foreach (['Applied', 'Approved', 'Rejected'] as $statusLabel) {
            $leaveLabels[] = $statusLabel;
            $leaveData[] = (int) ($leaveBreakdown[strtoupper($statusLabel)] ?? $leaveBreakdown[$statusLabel] ?? 0);
        }

        return [
            'metrics' => [
                [
                    'label' => 'Pending Leave Requests',
                    'value' => $pendingLeaves,
                    'icon' => 'ti ti-clock-hour-4',
                    'type' => 'warning',
                ],
                [
                    'label' => 'Approved Leaves',
                    'value' => $approvedLeaves,
                    'icon' => 'ti ti-circle-check',
                    'type' => 'success',
                ],
                [
                    'label' => 'Present Days (MTD)',
                    'value' => $presentDays,
                    'icon' => 'ti ti-calendar-check',
                    'type' => 'primary',
                ],
                [
                    'label' => 'Absent Days (MTD)',
                    'value' => $absentDays,
                    'icon' => 'ti ti-alert-circle',
                    'type' => 'danger',
                ],
            ],
            'quick_links' => [
                [
                    'label' => 'My Leave Requests',
                    'url' => route('superadmin.leave.Leave'),
                    'icon' => 'ti ti-calendar-event',
                ],
                [
                    'label' => 'Attendance Summary',
                    'url' => route('superadmin.hr.attendance.index'),
                    'icon' => 'ti ti-calendar-stats',
                ],
                [
                    'label' => 'Employee Directory',
                    'url' => route('superadmin.employees.index'),
                    'icon' => 'ti ti-address-book',
                ],
            ],
            'insights' => [
                'message' => 'Review your attendance pattern and keep an eye on leave approvals.',
            ],
            'charts' => [
                [
                    'id' => 'employeeAttendanceWeek',
                    'type' => 'bar',
                    'title' => 'Attendance (Last 7 Days)',
                    'labels' => $attendanceLabels,
                    'datasets' => [
                        [
                            'label' => 'Present',
                            'data' => $presentSeries,
                            'backgroundColor' => $this->chartColors(3)[0] ?? '#4e79a7',
                        ],
                        [
                            'label' => 'On Leave',
                            'data' => $leaveSeries,
                            'backgroundColor' => $this->chartColors(3)[1] ?? '#f28e2b',
                        ],
                        [
                            'label' => 'Absent',
                            'data' => $absentSeries,
                            'backgroundColor' => $this->chartColors(3)[2] ?? '#e15759',
                        ],
                    ],
                    'options' => [
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                                'ticks' => ['stepSize' => 1],
                            ],
                        ],
                        'plugins' => [
                            'legend' => ['position' => 'top'],
                        ],
                    ],
                ],
                [
                    'id' => 'employeeLeaveSummary',
                    'type' => 'doughnut',
                    'title' => 'Leave Summary',
                    'labels' => $leaveLabels,
                    'datasets' => [[
                        'label' => 'Requests',
                        'data' => $leaveData,
                        'backgroundColor' => $this->chartColors(count($leaveLabels)),
                    ]],
                    'height' => 280,
                ],
            ],
        ];
    }

    protected function buildGenericPayload(?Department $department, $user): array
    {
        $departmentLabel = $department?->name ?? optional($user->employee)->department ?? 'Team';
        $weekRange = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];

        $trendStart = Carbon::now()->subMonths(5)->startOfMonth();
        $trendEnd = Carbon::now()->endOfMonth();

        $bookingsTrendRaw = NewBooking::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $trendLabels = [];
        $trendData = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $trendStart->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendData[] = (int) ($bookingsTrendRaw[$key] ?? 0);
        }

        $departmentDistribution = Department::select('departments.name', DB::raw('COUNT(new_bookings.id) as total'))
            ->leftJoin('new_bookings', 'departments.id', '=', 'new_bookings.department_id')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $departmentLabels = [];
        $departmentData = [];
        foreach ($departmentDistribution as $row) {
            $departmentLabels[] = $row->name ?? 'Unassigned';
            $departmentData[] = (int) $row->total;
        }
        if (empty($departmentLabels)) {
            $departmentLabels = ['No Data'];
            $departmentData = [0];
        }

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
            'charts' => [
                [
                    'id' => 'genericBookingsTrend',
                    'type' => 'line',
                    'title' => 'Bookings Trend (6M)',
                    'labels' => $trendLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $trendData,
                        'borderColor' => $this->chartColors(1)[0] ?? '#4e79a7',
                        'backgroundColor' => 'rgba(78,121,167,0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                    'height' => 320,
                ],
                [
                    'id' => 'genericDepartmentSplit',
                    'type' => 'bar',
                    'title' => 'Bookings by Department',
                    'labels' => $departmentLabels,
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => $departmentData,
                        'backgroundColor' => $this->chartColors(count($departmentLabels)),
                    ]],
                    'height' => 320,
                ],
            ],
        ];
    }

    public function accountsInvoicesChart()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        // Use the `status` column to compute current totals across the invoices table.
        // This ensures the dashboard reflects the current status values in the DB.
        $paid = Invoice::whereIn('status', [1, 4])->count();
        $unpaid = Invoice::where('status', 0)->count();
        $cancel = Invoice::where('status', 2)->count();

        // Overdue: unpaid invoices with invoice_date before start of this month
        $overdue = Invoice::where('invoice_date', '<', $startOfMonth)
            ->where('status', 0)
            ->count();

        return response()->json([
            'paid' => $paid,
            'unpaid' => $unpaid,
            'cancel' => $cancel,
            'overdue' => $overdue,
        ]);
    }

    public function gstOverview(Request $request)
    {
        $daysParam = $request->query('days', '30'); 
        
        $cacheKey = 'dashboard:gst:' . $daysParam;
        
        $data = Cache::remember($cacheKey, 300, function() use ($daysParam) {
            $querySales = \App\Models\Invoice::query();
            $queryPurchase = \App\Models\PurchaseBill::query();

            // purchase_date, invoice_date
            if (strtoupper($daysParam) !== 'ALL') {
                $days = (int)$daysParam;
                $startDate = Carbon::now()->subDays($days)->startOfDay();
                
                $querySales->where('invoice_date', '>=', $startDate);
                $queryPurchase->where('purchase_date', '>=', $startDate);
            }

            $salesGst = (float) $querySales->sum('gst_amount');
            $purchaseGst = (float) $queryPurchase->sum('gst_amount');
            $totalGst = $salesGst - $purchaseGst;
            
            return [
                'salesGst' => $salesGst,
                'purchaseGst' => $purchaseGst,
                'totalGst' => $totalGst,
            ];
        });

        return response()->json($data);
    }
}
