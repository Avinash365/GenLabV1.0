<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ReportEditorFile;  


class ReportingController extends Controller
{
    /**
     * Show Received Reports page with optional Job Order search.
     */
    public function received(Request $request)
    {
        $job = trim((string) $request->get('job'));

        $baseQuery = BookingItem::query()->with(['booking', 'analyst', 'receivedBy']);

        $header = null;
        if ($job !== '') {
            // Find first matching item to determine its booking/reference
            $firstItem = (clone $baseQuery)
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $b = $firstItem->booking;
                // Build header data
                $header = [ 
                    'id'               => $b->id,       
                    'job_card_no'      => $firstItem->job_order_no,
                    'client_name'      => $b->client_name,
                    'job_order_date'   => optional($b->job_order_date)->format('Y-m-d'),
                    'issue_date'       => optional($firstItem->issue_date)->format('Y-m-d'),
                    'reference_no'     => $b->reference_no,
                    'sample_description'=> $firstItem->sample_description,
                    'name_of_work'     => $b->client_address,
                    'issued_to'        => $b->report_issue_to,
                    'ms'               => $b->m_s,
                ];

                // Show all items for the same booking/reference
                $items = $b->items()->with(['booking', 'analyst', 'reports','receivedBy'])->latest('id')->paginate(20)->withQueryString();
                $reports = ReportEditorFile::latest()->get();

                return view('superadmin.reporting.received', compact('items', 'job', 'header', 'reports'));
            }
        }

        // Default: no auto-listing; show empty when no search or not found
        $items = BookingItem::query()->whereRaw('1=0')->paginate(20)->withQueryString();
        return view('superadmin.reporting.received', compact('items', 'job', 'header'));
    }

    /**
     * Pendings: show items that have been received but not yet issued (issue_date null).
     * Supports optional search by job order no (search), and month/year filters based on received_at.
     */
    public function pendings(Request $request)
    {    
        $search = trim((string) $request->get('search'));
        $month = $request->has('month') ? (int) $request->get('month') : null;
        $year = $request->has('year') ? (int) $request->get('year') : null;
        $overdue = $request->boolean('overdue');
        $today = now()->toDateString();
        $departmentId = $request->get('department');
        $marketing = $request->get('marketing'); // user_code of marketing person
        $mode = $request->get('mode', 'job'); // job | reference
        if (!in_array($mode, ['job','reference'], true)) { $mode = 'job'; }
        if ($month !== null && ($month < 1 || $month > 12)) { $month = null; }
        if ($year !== null && ($year < 2000 || $year > 2100)) { $year = null; }
        $departments = \App\Models\Department::orderBy('name')->get(['id','name']);
        $marketingPersons = \App\Models\User::whereHas('marketingBookings')
            ->orderBy('name')
            ->get(['id','name','user_code']);

        if ($mode === 'reference') {
            // Aggregate by booking (reference_no) where at least one pending/overdue item
            $bookingQuery = \App\Models\NewBooking::query()->withCount(['items as pending_items_count' => function($q) use ($overdue, $today) {
                if ($overdue) {
                    $q->whereNull('issue_date')->whereNotNull('lab_expected_date')->whereDate('lab_expected_date', '<', $today);
                } else {
                    $q->where(function($qq){ $qq->whereNull('received_at')->orWhereNull('issue_date'); });
                }
            }])->with(['items' => function($q) use ($overdue, $today){
                if ($overdue) {
                    $q->whereNull('issue_date')->whereNotNull('lab_expected_date')->whereDate('lab_expected_date', '<', $today);
                } else {
                    $q->where(function($qq){ $qq->whereNull('received_at')->orWhereNull('issue_date'); });
                }
            }]);
            if ($departmentId) { $bookingQuery->where('department_id', $departmentId); }
            if ($marketing) { $bookingQuery->where('marketing_id', $marketing); }
            if ($search !== '') {
                $bookingQuery->where(function($qq) use ($search) {
                    $qq->where('client_name','like',"%{$search}%")
                        ->orWhere('reference_no','like',"%{$search}%");
                });
            }
            if (!$overdue) {
                if ($month) { $bookingQuery->whereHas('items', function($qi) use ($month){ $qi->whereMonth('received_at',$month); }); }
                if ($year) { $bookingQuery->whereHas('items', function($qi) use ($year){ $qi->whereYear('received_at',$year); }); }
            }
            $bookingQuery->having('pending_items_count','>',0)->latest('id');
            $bookings = $bookingQuery->paginate(20)->withQueryString();
            $items = collect();
            return view('superadmin.reporting.pendings', compact('items','bookings','departments','departmentId','mode','marketingPersons','marketing'));
        } else {
            $q = BookingItem::query()->with(['booking']);
            if ($overdue) {
                $q->whereNull('issue_date')
                  ->whereNotNull('lab_expected_date')
                  ->whereDate('lab_expected_date','<', $today);
            } else {
                // Pending items: either not received yet OR issue date not set
                $q->where(function($qq){ $qq->whereNull('received_at')->orWhereNull('issue_date'); });
            }
            if ($departmentId) {
                $q->whereHas('booking', function($b) use ($departmentId) { $b->where('department_id', $departmentId); });
            }
            if ($marketing) {
                $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
            }
            if ($search !== '') {
                $q->where(function($qq) use ($search) {
                    $qq->where('job_order_no', 'like', "%{$search}%")
                       ->orWhere('sample_description', 'like', "%{$search}%")
                       ->orWhere('particulars', 'like', "%{$search}%");
                });
            }
            if (!$overdue) {
                if ($month) { $q->whereMonth('received_at', $month); }
                if ($year) { $q->whereYear('received_at', $year); }
            }
            $q->latest('id');
            $items = $q->paginate(20)->withQueryString();
            $bookings = collect();
            return view('superadmin.reporting.pendings', compact('items','bookings','departments','departmentId','mode','marketingPersons','marketing'));
        }
    }

    protected function buildPendingsQuery(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $month = $request->has('month') ? (int) $request->get('month') : null;
        $year = $request->has('year') ? (int) $request->get('year') : null;
        $departmentId = $request->get('department');
        $marketing = $request->get('marketing');
        $overdue = $request->boolean('overdue');
        $today = now()->toDateString();

        $q = BookingItem::query()->with(['booking']);
        if ($overdue) {
            $q->whereNull('issue_date')
              ->whereNotNull('lab_expected_date')
              ->whereDate('lab_expected_date','<', $today);
        } else {
            // Pending when not received OR issue date not set
            $q->where(function($qq){ $qq->whereNull('received_at')->orWhereNull('issue_date'); });
        }
        if ($departmentId) {
            $q->whereHas('booking', function($b) use ($departmentId) { $b->where('department_id', $departmentId); });
        }
        if ($marketing) {
            $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
        }
        if ($search !== '') {
            $q->where(function($qq) use ($search) {
                $qq->where('job_order_no', 'like', "%{$search}%")
                   ->orWhere('sample_description', 'like', "%{$search}%")
                   ->orWhere('sample_quality', 'like', "%{$search}%")
                   ->orWhere('particulars', 'like', "%{$search}%")
                   ->orWhereHas('booking', function($bq) use ($search) {
                        $bq->where('client_name', 'like', "%{$search}%");
                   });
            });
        }
        if (!$overdue) {
            if ($month) { $q->whereMonth('received_at', $month); }
            if ($year) { $q->whereYear('received_at', $year); }
        }
        return $q;
    }

    public function pendingsExportPdf(Request $request)
    {
        $items = $this->buildPendingsQuery($request)->latest('id')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('superadmin.reporting.pendings_pdf', [
            'items' => $items,
            'search' => $request->get('search'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'department' => $request->get('department'),
        ])->setPaper('a4','landscape');
        return $pdf->stream('pending_reports.pdf');
    }

    public function pendingsExportExcel(Request $request)
    {
        $items = $this->buildPendingsQuery($request)->latest('id')->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PendingItemsExport($items), 'pending_reports.xlsx');
    }

    /**
     * Receive a single report item (assign to current user).
     */
    public function receiveOne(Request $request, BookingItem $item)
    {
        // Validate optional issue_date
        $data = $request->validate([
            'issue_date' => ['nullable', 'date'],
        ]);

        // Only set receiver to a front-end user (users table) to satisfy FK
        $receiverId = auth('web')->check() ? auth('web')->id() : null; // FK constraint
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $item->received_by_name = $receiverName;
        $item->received_at = now();
        if (Schema::hasColumn('booking_items', 'received_by_id')) {
            $item->received_by_id = $receiverId;
        }
        if (array_key_exists('issue_date', $data)) {
            $item->issue_date = $data['issue_date'];
        }
        $item->save();
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'received_by' => $item->received_by_name ?? optional($item->receivedBy)->name ?? $receiverName,
                'received_at' => optional($item->received_at)->toIso8601String(),
                'id' => $item->id,
                'receiver_name' => $receiverName,
                'issue_date' => optional($item->issue_date)->format('Y-m-d'),
            ]);
        }
        return back()->with('status', 'Report received');
    }

    /**
     * Receive all filtered reports.
     */
    public function receiveAll(Request $request)
    {
        $job = trim((string) $request->get('job'));

        // If job is provided, try to scope to that booking's items
        if ($job !== '') {
            $firstItem = BookingItem::with('booking')
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $receiverId = auth('web')->check() ? auth('web')->id() : null;
                $receiverName = auth('web')->check()
                    ? optional(auth('web')->user())->name
                    : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
                $update = [
                    'received_by_name' => $receiverName,
                    'received_at'    => now(),
                ];
                if (Schema::hasColumn('booking_items', 'received_by_id')) {
                    $update['received_by_id'] = $receiverId;
                }
                $firstItem->booking->items()->update($update);
                if ($request->wantsJson()) {
                    return response()->json(['ok' => true, 'scope' => 'booking', 'booking_id' => $firstItem->booking->id, 'receiver_name' => $receiverName, 'received_at' => now()->toIso8601String()]);
                }
                return back()->with('status', 'All matching reports marked as received');
            }
        }

        // Fallback: mark all items as received (use sparingly)
        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $update = [
            'received_by_name' => $receiverName,
            'received_at' => now()
        ];
        if (Schema::hasColumn('booking_items', 'received_by_id')) {
            $update['received_by_id'] = $receiverId;
        }
        BookingItem::query()->update($update);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'scope' => 'all', 'receiver_name' => $receiverName, 'received_at' => now()->toIso8601String()]);
        }

        return back()->with('status', 'All matching reports marked as received');
    }


    /**
     * Submit Issue Dates in bulk for received items.
     */
    public function submitAll(Request $request)
    {
        $payload = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:booking_items,id']
        ]);

        DB::transaction(function () use ($payload) {
            foreach ($payload['items'] as $row) {
                $item = BookingItem::find($row['id']);
                if (!$item) continue;
                // Ensure it's marked received by someone
                if (!$item->received_at) {
                    $receiverId = auth('web')->check() ? auth('web')->id() : null;
                    $receiverName = auth('web')->check()
                        ? optional(auth('web')->user())->name
                        : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
                    $item->received_by_name = $receiverName;
                    if (Schema::hasColumn('booking_items', 'received_by_id')) {
                        $item->received_by_id = $receiverId;
                    }
                    $item->received_at = now();
                }
                $item->issue_date = $row['issue_date'] ?? $item->issue_date;
                $item->save();
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Issue Dates submitted');
    }

    /**
     * Generate Report page: similar to received but with format selection.
     */
    public function generate(Request $request)
    {
        $job = trim((string) $request->get('job'));
        $baseQuery = BookingItem::query()->with(['booking']);
        $header = null;
        $items = collect();
        if ($job !== '') {
            $firstItem = (clone $baseQuery)
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();
            if ($firstItem && $firstItem->booking) {
                $b = $firstItem->booking;
                $header = [
                    'job_card_no'       => $firstItem->job_order_no,
                    'client_name'       => $b->client_name,
                    'job_order_date'    => optional($b->job_order_date)->format('Y-m-d'),
                    'issue_date'        => optional($firstItem->issue_date)->format('Y-m-d'),
                    'reference_no'      => $b->reference_no,
                    'sample_description'=> $firstItem->sample_description,
                    'name_of_work'      => $b->client_address,
                    'issued_to'         => $b->report_issue_to,
                    'ms'                => $b->m_s,
                ];
                $items = $b->items()->with('booking')->latest('id')->paginate(20)->withQueryString();
            }
        }

        // Fetch available report formats for dropdown
        $formats = \App\Models\ReportFormat::orderBy('format_name')->get(['id','format_name']);
        if (!$items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $items = BookingItem::query()->whereRaw('1=0')->paginate(20)->withQueryString();
        }
        return view('superadmin.reporting.generate', compact('items','job','header','formats'));
    }

    /**
     * Report Dispatch page: UI mirrors Received with minor changes.
     */
    public function dispatch(Request $request)
    {
    $job = trim((string) $request->get('job'));
    $month = $request->has('month') ? (int) $request->get('month') : null;
    $year = $request->has('year') ? (int) $request->get('year') : null;
    $status = $request->get('status'); // 'in-account' | 'dispatched'
    if (!in_array($status, ['in-account','dispatched'], true)) { $status = 'in-account'; }
        if ($month !== null && ($month < 1 || $month > 12)) { $month = null; }
        if ($year !== null && ($year < 2000 || $year > 2100)) { $year = null; }

        $baseQuery = BookingItem::query()->with(['booking', 'analyst', 'receivedBy']);

        // Quick list of Job Order Nos that are In Account but not yet Dispatched
        $readyJobs = BookingItem::query()
            ->whereNotNull('account_received_at')
            ->whereNull('dispatched_at')
            ->select('job_order_no')
            ->distinct()
            ->orderBy('job_order_no', 'desc')
            ->limit(50)
            ->pluck('job_order_no');

        $header = null;
        if ($job !== '') {
            $firstItem = (clone $baseQuery)
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $b = $firstItem->booking;
                $header = [
                    'job_card_no'       => $firstItem->job_order_no,
                    'client_name'       => $b->client_name,
                    'job_order_date'    => optional($b->job_order_date)->format('Y-m-d'),
                    'issue_date'        => optional($firstItem->issue_date)->format('Y-m-d'),
                    'reference_no'      => $b->reference_no,
                    'sample_description'=> $firstItem->sample_description,
                    'name_of_work'      => $b->client_address,
                    'issued_to'         => $b->report_issue_to,
                    'ms'                => $b->contractor_name,
                ];

                $items = $b->items()->with(['booking', 'analyst', 'receivedBy'])->latest('id')->paginate(20)->withQueryString();

                return view('superadmin.reporting.dispatch', compact('items', 'job', 'header', 'readyJobs', 'month', 'year', 'status'));
            }
        }

        // Default view: show items filtered by status, with optional month/year
        $q = BookingItem::query()->with(['booking', 'analyst', 'receivedBy']);
        if ($status === 'dispatched') {
            $q->whereNotNull('dispatched_at');
            if ($month) { $q->whereMonth('dispatched_at', $month); }
            if ($year) { $q->whereYear('dispatched_at', $year); }
        } else { // in-account
            $q->whereNotNull('account_received_at')->whereNull('dispatched_at');
            if ($month) { $q->whereMonth('account_received_at', $month); }
            if ($year) { $q->whereYear('account_received_at', $year); }
        }
        $q->latest('id');
        $items = $q->paginate(20)->withQueryString();
        return view('superadmin.reporting.dispatch', compact('items', 'job', 'header', 'readyJobs', 'month', 'year', 'status'));
    }

    /**
     * Mark a single item as dispatched (separate from received).
     */
    public function dispatchOne(Request $request, \App\Models\BookingItem $item)
    {
        $meta = $request->validate([
            'dispatch_by' => ['required','string','max:100'],
            'dispatch_person_name' => ['required','string','max:150'],
            'dispatch_assignment_no' => ['required','string','max:100'],
            'dispatch_comment' => ['nullable','string','max:2000'],
        ]);
        $dispatcherId = auth('web')->check() ? auth('web')->id() : null;
        $dispatcherName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $item->dispatched_at = now();
        if (Schema::hasColumn('booking_items', 'dispatched_by_id')) {
            $item->dispatched_by_id = $dispatcherId;
        }
    $item->dispatched_by_name = $dispatcherName;
    foreach ($meta as $k => $v) { $item->{$k} = $v; }
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'dispatched_at' => optional($item->dispatched_at)->toIso8601String(),
                'dispatcher_name' => $dispatcherName,
                'id' => $item->id,
                'meta' => $meta,
            ]);
        }
        return back()->with('status', 'Report dispatched');
    }

    /**
     * Bulk dispatch selected items.
     */
    public function dispatchBulk(Request $request)
    {
        $payload = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:booking_items,id'],
            'meta.dispatch_by' => ['required','string','max:100'],
            'meta.dispatch_person_name' => ['required','string','max:150'],
            'meta.dispatch_assignment_no' => ['required','string','max:100'],
            'meta.dispatch_comment' => ['nullable','string','max:2000'],
        ]);
        $dispatcherId = auth('web')->check() ? auth('web')->id() : null;
        $dispatcherName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $update = [
            'dispatched_at' => now(),
            'dispatched_by_name' => $dispatcherName,
        ] + (Schema::hasColumn('booking_items', 'dispatched_by_id') ? ['dispatched_by_id' => $dispatcherId] : []);
        if (!empty($payload['meta'])) {
            foreach ($payload['meta'] as $k => $v) { $update[$k] = $v; }
        }
        \App\Models\BookingItem::whereIn('id', $payload['ids'])->update($update);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Selected reports dispatched');
    }

    /**
     * Mark a single item as received by Accounts (separate display state for Dispatch UI).
     */
    public function accountReceiveOne(Request $request, \App\Models\BookingItem $item)
    {
        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $item->account_received_at = now();
        if (Schema::hasColumn('booking_items', 'account_received_by_id')) {
            $item->account_received_by_id = $receiverId;
        }
        $item->account_received_by_name = $receiverName;
        $item->save();
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'account_received_at' => optional($item->account_received_at)->toIso8601String(), 'receiver_name' => $receiverName]);
        }
        return back()->with('status', 'Report marked In Account');
    }

    /**
     * Bulk Accounts receive.
     */
    public function accountReceiveBulk(Request $request)
    {
        $payload = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:booking_items,id'],
        ]);
        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        \App\Models\BookingItem::whereIn('id', $payload['ids'])->update([
            'account_received_at' => now(),
            'account_received_by_name' => $receiverName,
        ] + (Schema::hasColumn('booking_items', 'account_received_by_id') ? ['account_received_by_id' => $receiverId] : []));
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Selected reports marked In Account');
    } 

    // Assign a report file to a booking item 

    public function assignReport(Request $request, BookingItem $item)
    {
        $request->validate([
            'report_id' => 'required|exists:report_editor_files,id',
        ]);

        $report = ReportEditorFile::find($request->report_id);

        $item->reports()->detach();

        $item->reports()->attach($request->report_id, [
            'booking_id' => $item->new_booking_id
        ]);

        $reportNo = $report->report_no;

        return back()->with('success', "Report: {$reportNo} assigned successfully.");
    }

}
