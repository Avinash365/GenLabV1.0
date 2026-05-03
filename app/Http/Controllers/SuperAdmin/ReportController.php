<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewBooking;
use App\Models\Department;
use App\Models\User;
use App\Services\GetUserActiveDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected GetUserActiveDepartment $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

        $this->middleware('permission:report.view'); 
    }

    /**
     * Gather booking ids that have reports either via DB pivot or via storage folders
     */
    private function gatherReportBookingIds(): array
    {
        $pivotIds = DB::table('booking_item_report')->distinct()->pluck('booking_id')->toArray();

        $storageIds = [];
        $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
        NewBooking::whereNotNull('reference_no')->select('id','reference_no')->chunk(200, function($rows) use (&$storageIds, $allowed) {
            foreach ($rows as $r) {
                try {
                    $ref = trim((string) ($r->reference_no ?? ''));
                    if ($ref === '') continue;
                    $safe = preg_replace('/[^A-Za-z0-9_\-]/', '-', $ref) ?: 'unknown';
                    $dir = "public/letters/{$safe}";
                    if (!\Illuminate\Support\Facades\Storage::exists($dir)) continue;
                    $files = \Illuminate\Support\Facades\Storage::files($dir);
                    $files = array_filter($files, function($p) use ($allowed) {
                        $bname = basename($p);
                        if ($bname === '_meta.json' || str_starts_with($bname, '_')) return false;
                        $ext = strtolower(pathinfo($bname, PATHINFO_EXTENSION));
                        return $ext && in_array($ext, $allowed, true);
                    });
                    if (count($files) > 0) $storageIds[] = $r->id;
                } catch (\Throwable $e) {
                    // ignore and continue
                }
            }
        });

        return array_unique(array_merge($pivotIds, $storageIds));
    }

    public function index(Request $request, $departmentId = null)
    {
        
        $departments = $this->departmentService->getDepartment();
        $deptParam = $request->query('department', $departmentId);
        $department = $deptParam ? Department::find($deptParam) : null;

                $ids = $this->gatherReportBookingIds();
                if (empty($ids)) { $ids = [0]; }

                $query = NewBooking::with(['items', 'marketingPerson', 'generatedInvoice'])
                    ->whereIn('new_bookings.id', $ids);

                // Add last_report_at (latest uploaded report timestamp from booking_item_report)
                $query->select('new_bookings.*')
                            ->selectSub(function($q){
                                    $q->from('booking_item_report')->selectRaw('MAX(created_at)')
                                        ->whereColumn('booking_item_report.booking_id', 'new_bookings.id');
                            }, 'last_report_at');

                // Order by most recent report upload first, fallback to job_order_date
                $query->orderByRaw("COALESCE(last_report_at, new_bookings.job_order_date) DESC");

        if ($department) {
            $query->where('department_id', $department->id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($sub) use ($q){
                $sub->where('client_name', 'like', "%{$q}%")
                    ->orWhere('reference_no', 'like', "%{$q}%");
            });
        }
        if ($request->filled('marketing')) {
            $query->where('marketing_id', $request->marketing);
        }
        // Bill generated filter: 'generated' | 'not_generated'
        if ($request->filled('bill')) {
            if ($request->bill === 'generated') {
                $query->whereHas('generatedInvoice');
            } elseif ($request->bill === 'not_generated') {
                $query->whereDoesntHave('generatedInvoice');
            }
        }
        // Dispatch filter: 'dispatched' | 'not_dispatched' (based on booking items)
        if ($request->filled('dispatch')) {
            if ($request->dispatch === 'dispatched') {
                $query->whereHas('items', function($q){ $q->whereNotNull('dispatched_at'); });
            } elseif ($request->dispatch === 'not_dispatched') {
                $query->whereHas('items', function($q){ $q->whereNull('dispatched_at'); });
            }
        }
        if ($request->filled('month')) {
            $query->whereMonth('job_order_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('job_order_date', $request->year);
        }

        $bookings = $query->latest()->paginate($request->perPage ?: 25);

        // compute reports_count for each booking
        // Prefer files uploaded via Received Reports (storage/public/letters/<key>), fallback to booking_item_report pivot count
        $bookings->getCollection()->transform(function($b){
            $lettersCount = 0;
            try {
                $ref = trim((string) ($b->reference_no ?? ''));
                if ($ref !== '') {
                    $safe = preg_replace('/[^A-Za-z0-9_\-]/', '-', $ref) ?: 'unknown';
                    $dir = "public/letters/{$safe}";
                    if (Storage::exists($dir)) {
                        $files = Storage::files($dir);
                        // filter allowed extensions and ignore meta/hidden files
                        $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
                        $files = array_filter($files, function($p) use ($allowed) {
                            $bname = basename($p);
                            if ($bname === '_meta.json' || str_starts_with($bname, '_')) return false;
                            $ext = strtolower(pathinfo($bname, PATHINFO_EXTENSION));
                            return $ext && in_array($ext, $allowed, true);
                        });
                        $lettersCount = count($files);
                    }
                }
            } catch (\Throwable $e) {
                $lettersCount = 0;
            }

            $pivotCount = (int) DB::table('booking_item_report')->where('booking_id', $b->id)->count();
            // prefer letters count when present else pivot count
            $b->reports_count = $lettersCount > 0 ? $lettersCount : $pivotCount;
            return $b;
        });

        // Merge storage-based latest upload timestamps into last_report_at so ordering reflects Received uploads
        $bookings->getCollection()->transform(function($b){
            try {
                $current = null;
                if (!empty($b->last_report_at)) {
                    try { $current = \Carbon\Carbon::parse($b->last_report_at); } catch (\Throwable $e) { $current = null; }
                }
                $ref = trim((string) ($b->reference_no ?? ''));
                if ($ref !== '') {
                    $safe = preg_replace('/[^A-Za-z0-9_\\-]/', '-', $ref) ?: 'unknown';
                    $dir = "public/letters/{$safe}";
                    if (Storage::exists($dir)) {
                        $metaPath = $dir.'/_meta.json';
                        $meta = [];
                        if (Storage::exists($metaPath)) {
                            $raw = Storage::get($metaPath);
                            $parsed = @json_decode($raw, true);
                            if (is_array($parsed)) $meta = $parsed;
                        }
                        $files = Storage::files($dir);
                        foreach ($files as $p) {
                            $bname = basename($p);
                            if ($bname === '_meta.json' || str_starts_with($bname, '_')) continue;
                            $uploadedAt = null;
                            if (isset($meta[$bname]['uploaded_at'])) {
                                $uploadedAt = $meta[$bname]['uploaded_at'];
                            } else {
                                $ts = Storage::lastModified($p);
                                $uploadedAt = $ts ? date('Y-m-d H:i:s', $ts) : null;
                            }
                            if ($uploadedAt) {
                                try {
                                    $dt = \Carbon\Carbon::parse($uploadedAt);
                                    if (!$current || $dt->gt($current)) $current = $dt;
                                } catch (\Throwable $e) {}
                            }
                        }
                    }
                }
                if ($current) $b->last_report_at = $current->toDateTimeString();
            } catch (\Throwable $e) {
                // ignore
            }
            return $b;
        });

        // Sort current page collection by merged last_report_at desc so recent uploads appear on top
        $sorted = $bookings->getCollection()->sortByDesc(function($item){
            return $item->last_report_at ? strtotime($item->last_report_at) : 0;
        })->values();
        $bookings->setCollection($sorted);

        $marketingPersons = User::whereHas('role', function($q) {
            $q->where('slug', 'marketing_person');
        })->orderBy('name')->get(['name','user_code']);

        return view('superadmin.report.report', compact('departments', 'department', 'bookings', 'marketingPersons'));
    }

    public function exportPdf(Request $request)
    {
        $ids = $this->gatherReportBookingIds();
        if (empty($ids)) { return redirect()->route('superadmin.report.index')->with('success', 'No reports to export.'); }

        $query = NewBooking::with(['marketingPerson'])->whereIn('new_bookings.id', $ids);

        // apply same filters as index
        if ($request->filled('department')) $query->where('department_id', $request->department);
        if ($request->filled('search')) $query->where(function($q) use ($request){ $q->where('client_name','like','%'.$request->search.'%')->orWhere('reference_no','like','%'.$request->search.'%'); });
        if ($request->filled('marketing')) $query->where('marketing_id', $request->marketing);
        if ($request->filled('month')) $query->whereMonth('job_order_date', $request->month);
        if ($request->filled('year')) $query->whereYear('job_order_date', $request->year);
        if ($request->filled('bill')) {
            if ($request->bill === 'generated') $query->whereHas('generatedInvoice');
            if ($request->bill === 'not_generated') $query->whereDoesntHave('generatedInvoice');
        }
        if ($request->filled('dispatch')) {
            if ($request->dispatch === 'dispatched') $query->whereHas('items', fn($q)=> $q->whereNotNull('dispatched_at'));
            if ($request->dispatch === 'not_dispatched') $query->whereHas('items', fn($q)=> $q->whereNull('dispatched_at'));
        }

        $bookings = $query->orderBy('client_name')->get();

        // compute reports_count and last upload time for header info
        $bookings->transform(function($b){
            $lettersCount = 0; $last = null;
            $ref = trim((string) ($b->reference_no ?? ''));
            if ($ref !== '') {
                $safe = preg_replace('/[^A-Za-z0-9_\\-]/', '-', $ref) ?: 'unknown';
                $dir = "public/letters/{$safe}";
                if (Storage::exists($dir)) {
                    $files = Storage::files($dir);
                    $files = array_filter($files, function($p){ $bname = basename($p); if ($bname === '_meta.json' || str_starts_with($bname,'_')) return false; return true; });
                    $lettersCount = count($files);
                    $metaPath = $dir.'/_meta.json';
                    $meta = [];
                    if (Storage::exists($metaPath)) { $raw = Storage::get($metaPath); $parsed = @json_decode($raw, true); if (is_array($parsed)) $meta = $parsed; }
                    foreach ($files as $p) {
                        $bname = basename($p);
                        $uploaded = $meta[$bname]['uploaded_at'] ?? null;
                        if (!$uploaded) { $ts = Storage::lastModified($p); $uploaded = $ts ? date('Y-m-d H:i:s', $ts) : null; }
                        if ($uploaded && (!$last || strtotime($uploaded) > strtotime($last))) $last = $uploaded;
                    }
                }
            }
            $b->reports_count = $lettersCount;
            $b->last_report_at = $last;
            return $b;
        });

        $filters = $request->only(['department','search','month','year','marketing','bill','dispatch']);

        $view = view('superadmin.report.export_pdf', compact('bookings','filters'))->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) || class_exists('PDF')) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('superadmin.report.export_pdf', compact('bookings','filters'));
                return $pdf->download('reports.pdf');
            } catch (\Throwable $e) {
                // fallback to HTML download
            }
        }

        return response($view, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="reports.html"'
        ]);
    }

    public function exportExcel(Request $request)
    {
        $ids = $this->gatherReportBookingIds();
        if (empty($ids)) { return redirect()->route('superadmin.report.index')->with('success', 'No reports to export.'); }

        $query = NewBooking::with(['marketingPerson'])->whereIn('new_bookings.id', $ids);
        if ($request->filled('department')) $query->where('department_id', $request->department);
        if ($request->filled('search')) $query->where(function($q) use ($request){ $q->where('client_name','like','%'.$request->search.'%')->orWhere('reference_no','like','%'.$request->search.'%'); });
        if ($request->filled('marketing')) $query->where('marketing_id', $request->marketing);
        if ($request->filled('month')) $query->whereMonth('job_order_date', $request->month);
        if ($request->filled('year')) $query->whereYear('job_order_date', $request->year);
        if ($request->filled('bill')) {
            if ($request->bill === 'generated') $query->whereHas('generatedInvoice');
            if ($request->bill === 'not_generated') $query->whereDoesntHave('generatedInvoice');
        }
        if ($request->filled('dispatch')) {
            if ($request->dispatch === 'dispatched') $query->whereHas('items', fn($q)=> $q->whereNotNull('dispatched_at'));
            if ($request->dispatch === 'not_dispatched') $query->whereHas('items', fn($q)=> $q->whereNull('dispatched_at'));
        }

        $bookings = $query->orderBy('client_name')->get();

        // CSV generation with filters in header
        $filters = $request->only(['department','search','month','year','marketing','bill','dispatch']);

        $filename = 'reports.csv';
        $callback = function() use ($bookings, $filters) {
            $out = fopen('php://output', 'w');
            // print filters
            foreach ($filters as $k => $v) {
                if ($v === null || $v === '') continue;
                fputcsv($out, [ucfirst($k), $v]);
            }
            fputcsv($out, []);
            // header
            fputcsv($out, ['Client Name','Reference No','Marketing Person','Reports Count','Last Report']);
            foreach ($bookings as $b) {
                $reportsCount = 0; $last = '';
                $ref = trim((string) ($b->reference_no ?? ''));
                if ($ref !== '') {
                    $safe = preg_replace('/[^A-Za-z0-9_\\-]/', '-', $ref) ?: 'unknown';
                    $dir = "public/letters/{$safe}";
                    if (Storage::exists($dir)) {
                        $files = Storage::files($dir);
                        $files = array_filter($files, function($p){ $bname = basename($p); if ($bname === '_meta.json' || str_starts_with($bname,'_')) return false; return true; });
                        $reportsCount = count($files);
                        $metaPath = $dir.'/_meta.json';
                        $meta = [];
                        if (Storage::exists($metaPath)) { $raw = Storage::get($metaPath); $parsed = @json_decode($raw, true); if (is_array($parsed)) $meta = $parsed; }
                        foreach ($files as $p) {
                            $bname = basename($p);
                            $uploaded = $meta[$bname]['uploaded_at'] ?? null;
                            if (!$uploaded) { $ts = Storage::lastModified($p); $uploaded = $ts ? date('Y-m-d H:i:s', $ts) : null; }
                            if ($uploaded && (!$last || strtotime($uploaded) > strtotime($last))) $last = $uploaded;
                        }
                    }
                }
                fputcsv($out, [$b->client_name, $b->reference_no, optional($b->marketingPerson)->name, $reportsCount, $last]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function reports(Request $request, $bookingId)
    {
        // First, try to reuse the same 'letters' storage used by the Received page
        // (this ensures uploader, pages and uploaded_at match the Received UI)
        $booking = NewBooking::find($bookingId);
        $letters = [];
        if ($booking && !empty($booking->reference_no)) {
            try {
                $rlController = new \App\Http\Controllers\SuperAdmin\ReportingLettersController();
                $fakeReq = new Request(['job' => $booking->reference_no]);
                $resp = $rlController->index($fakeReq);
                $data = $resp instanceof \Illuminate\Http\JsonResponse ? $resp->getData(true) : (is_array($resp) ? $resp : []);
                $letters = $data['letters'] ?? [];
            } catch (\Throwable $e) {
                $letters = [];
            }
        }

        if (!empty($letters)) {
            $rows = collect($letters)->map(function($l) {
                $obj = new \stdClass();
                $obj->report_description = $l['original_name'] ?? $l['name'] ?? null;
                $obj->file_path = $l['download_url'] ?? $l['url'] ?? $l['encoded_url'] ?? null;
                $obj->report_no = null;
                $obj->ult_r_no = null;
                $obj->pages = $l['pages'] ?? null;
                $obj->uploader_name = $l['uploader_name'] ?? ($l['uploaded_by'] ?? null);
                $obj->pdf_path = (isset($l['filename']) && strtolower(pathinfo($l['filename'], PATHINFO_EXTENSION)) === 'pdf') ? ($l['download_url'] ?? $l['url']) : null;
                $obj->generated_report_path = null;
                $obj->uploaded_at = $l['uploaded_at'] ?? null;
                return $obj;
            });

            return view('superadmin.report.booking_reports', ['rows' => $rows]);
        }

        // Fallback: original DB-based approach (preserve previous behavior)
        $rows = DB::table('booking_item_report as bir')
            ->where('bir.booking_id', $bookingId)
            ->leftJoin('report_editor_files as ref', 'ref.id', '=', 'bir.report_editor_file_id')
            ->select('bir.*', 'ref.report_description', 'ref.file_path', 'ref.report_no')
            ->orderBy('bir.created_at', 'desc')
            ->get();

        // attempt to enrich rows with uploader name and pages where possible
        $hasUploaderOnPivot = \Illuminate\Support\Facades\Schema::hasColumn('booking_item_report', 'uploaded_by');
        $hasUploaderOnFiles = \Illuminate\Support\Facades\Schema::hasColumn('report_editor_files', 'uploaded_by');

        $userIds = [];
        foreach ($rows as $r) {
            $r->pages = null;
            if (!empty($r->ult_r_no) && is_numeric($r->ult_r_no)) {
                $r->pages = (int) $r->ult_r_no;
            } elseif (!empty($r->report_no) && is_numeric($r->report_no)) {
                $r->pages = (int) $r->report_no;
            }
            $r->uploader_id = null;
            if ($hasUploaderOnPivot && property_exists($r, 'uploaded_by') && $r->uploaded_by) {
                $r->uploader_id = $r->uploaded_by;
            } elseif ($hasUploaderOnFiles && property_exists($r, 'uploaded_by') && $r->uploaded_by) {
                $r->uploader_id = $r->uploaded_by;
            }
            if ($r->uploader_id) $userIds[] = $r->uploader_id;
        }

        $users = [];
        if (!empty($userIds)) {
            $users = \App\Models\User::whereIn('id', array_unique($userIds))->get(['id','name'])->keyBy('id');
        }

        foreach ($rows as $r) {
            $r->uploader_name = $r->uploader_id && isset($users[$r->uploader_id]) ? $users[$r->uploader_id]->name : null;
            $r->uploaded_at = $r->created_at ?? null;
            if (!$r->uploader_name) {
                $possiblePaths = [];
                if (!empty($r->file_path)) $possiblePaths[] = $r->file_path;
                if (!empty($r->pdf_path)) $possiblePaths[] = $r->pdf_path;
                if (!empty($r->generated_report_path)) $possiblePaths[] = $r->generated_report_path;
                $foundUploaderId = null;
                foreach ($possiblePaths as $p) {
                    $base = basename($p);
                    $doc = DB::table('documents')->where('file_path', $p)->first();
                    if (!$doc) {
                        $doc = DB::table('documents')->where('file_path', 'like', "%{$base}")->first();
                    }
                    if ($doc && isset($doc->uploaded_by)) { $foundUploaderId = $doc->uploaded_by; break; }
                    $imp = DB::table('important_letters')->where('file_path', $p)->first();
                    if (!$imp) {
                        $imp = DB::table('important_letters')->where('file_path', 'like', "%{$base}")->first();
                    }
                    if ($imp && isset($imp->uploaded_by)) { $foundUploaderId = $imp->uploaded_by; break; }
                }
                if ($foundUploaderId) {
                    $u = \App\Models\User::find($foundUploaderId, ['id','name']);
                    if ($u) {
                        $r->uploader_name = $u->name;
                    }
                }
            }
        }

        return view('superadmin.report.booking_reports', ['rows' => $rows]);
    }
}
