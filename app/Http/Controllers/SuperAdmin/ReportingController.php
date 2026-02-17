<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingItem;
use App\Models\NewBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DispatchedExport;
use App\Models\ReportEditorFile;  
use Illuminate\Support\Facades\Storage;




class ReportingController extends Controller
{
    /**
     * Show Received Reports page with optional Job Order search.
     */
    public function received(Request $request)
    {
        $job = trim((string) $request->get('job'));

        // per-page control (allow users to change pagination size)
        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        $baseQuery = BookingItem::query()->with(['booking.marketingPerson', 'analyst', 'receivedBy']);

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
                    'name_of_work'     => $b->name_of_work ?: $b->client_address,
                    'issued_to'        => $b->report_issue_to,
                    'ms'               => $b->m_s,
                    'marketing_person' => $b->marketingPerson?->name ?? $b->marketing_name ?? $b->marketing ?? null,
                ];

                // Show all items for the same booking/reference
                $items = $b->items()->with(['booking', 'analyst', 'reports','receivedBy'])->latest('id')->paginate($perPage)->withQueryString();
                $reports = ReportEditorFile::latest()->get();

                return view('superadmin.reporting.received', compact('items', 'job', 'header', 'reports'));
            }
        }

        // Default: no auto-listing; show empty when no search or not found
        $items = BookingItem::query()->whereRaw('1=0')->paginate($perPage)->withQueryString();
        return view('superadmin.reporting.received', compact('items', 'job', 'header'));
    }

    /**
     * Booking-by-letter style listing (grouped by booking, with filters).
     */
    public function viewByLetter(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $month = $request->get('month');
        $year = $request->get('year');
        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100, 250, 500], true)) {
            $perPage = 25;
        }

        $departmentId = $request->get('department');
        $marketing = $request->get('marketing');

        $departments = \App\Models\Department::orderBy('name')->get(['id','name']);
        $department = $departmentId ? $departments->firstWhere('id', (int) $departmentId) : null;

        $baseQuery = \App\Models\NewBooking::query()->with(['items']);

        if ($department) {
            $baseQuery->where('department_id', $department->id);
        }
        if ($marketing) {
            $baseQuery->where('marketing_id', $marketing);
        }
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }
        if (is_numeric($month)) {
            $baseQuery->whereMonth('created_at', (int) $month);
        }
        if (is_numeric($year)) {
            $baseQuery->whereYear('created_at', (int) $year);
        }

        // Restrict to bookings that have at least one uploaded report in Received Reports (letters storage)
        $withLettersIds = (clone $baseQuery)
            ->select(['id', 'reference_no'])
            ->get()
            ->filter(function ($booking) {
                return $this->bookingHasUploadedReport($booking->reference_no);
            })
            ->pluck('id')
            ->all();

        
        $bookings = (clone $baseQuery)
            ->whereIn('id', $withLettersIds)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // Collect all uploaded report links per booking
        $letterFiles = [];
        foreach ($bookings as $bk) {
            $letterFiles[$bk->id] = $this->uploadedReportsForReference($bk->reference_no);
        }

        return view('superadmin.reporting.view-by-letter', [
            'bookings' => $bookings,
            'departments' => $departments,
            'department' => $department,
            'letterFiles' => $letterFiles,
        ]);
    }

    /**
     * Job-order centric listing for reporting submenu.
     */
    public function viewByJobOrder(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->get('month');
        $year = $request->get('year');
        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100, 250, 500], true)) {
            $perPage = 25;
        }

        // Enforce marketing scoping: marketing users only see their own bookings
        $marketingFilter = $request->get('marketing');
        if ($this->isMarketingUser($user)) {
            $marketingFilter = $marketingFilter ?: ($user->user_code ?? null);
            if ($marketingFilter) {
                // Persist to request so pagination/export keep the filter
                $request->merge(['marketing' => $marketingFilter]);
            }
        }

        // Show only items that have a generated report (pdf_path on pivot)
        $items = BookingItem::query()
            ->with(['booking', 'reports'])
            ->whereHas('reports', function ($q) {
                $q->whereNotNull('booking_item_report.pdf_path');
            })
            ->latest('id');

        if ($search !== '') {
            $items->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', "%{$search}%")
                  ->orWhere('sample_description', 'like', "%{$search}%")
                  ->orWhere('sample_quality', 'like', "%{$search}%")
                  ->orWhere('particulars', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($b) use ($search) {
                        $b->where('client_name', 'like', "%{$search}%");
                  });
            });
        }

        if (is_numeric($month)) {
            $items->whereMonth('created_at', (int) $month);
        }
        if (is_numeric($year)) {
            $items->whereYear('created_at', (int) $year);
        }

        if (!empty($marketingFilter)) {
            $items->whereHas('booking', function ($b) use ($marketingFilter) {
                $b->where('marketing_id', $marketingFilter);
            });
        }

        $items = $items->paginate($perPage)->withQueryString();

        return view('superadmin.reporting.view-by-job-order', [
            'items' => $items,
        ]);
    }

    /**
     * Determine if the authenticated user is a marketing user.
     */
    private function isMarketingUser($user): bool
    {
        if (!$user) {
            return false;
        }

        $roleName = null;
        if (isset($user->role)) {
            if (is_object($user->role)) {
                $roleName = $user->role->role_name ?? $user->role->name ?? null;
            } else {
                $roleName = $user->role;
            }
        }

        return $roleName && stripos($roleName, 'market') !== false;
    }

    /**
     * Check if a booking reference has any uploaded report file (stored under letters directory).
     */
    private function bookingHasUploadedReport(?string $reference): bool
    {
        $key = $this->sanitizeLetterKey((string) $reference);
        if ($key === '') {
            return false;
        }

        $dir = "public/letters/{$key}";
        if (!Storage::exists($dir)) {
            return false;
        }

        foreach (Storage::files($dir) as $path) {
            $base = basename($path);
            if ($base === '_meta.json' || str_starts_with($base, '_')) {
                continue;
            }
            $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf','jpg','jpeg','png','doc','docx'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all uploaded report links for a reference.
     */
    private function uploadedReportsForReference(?string $reference): array
    {
        $key = $this->sanitizeLetterKey((string) $reference);
        if ($key === '') {
            return [];
        }

        $dir = "public/letters/{$key}";
        if (!Storage::exists($dir)) {
            return [];
        }

        // Load meta to recover original filenames and uploader info
        $meta = [];
        $metaPath = $dir.'/_meta.json';
        if (Storage::exists($metaPath)) {
            $rawMeta = json_decode(Storage::get($metaPath), true);
            if (is_array($rawMeta)) {
                $meta = $rawMeta;
            }
        }

        $links = [];
        $files = Storage::files($dir);
        // Sort by last modified desc so newest uploads appear first
        usort($files, function ($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });

        foreach ($files as $path) {
            $base = basename($path);
            if ($base === '_meta.json' || str_starts_with($base, '_')) {
                continue;
            }
            $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf','jpg','jpeg','png','doc','docx'], true)) {
                continue;
            }
            $original = $meta[$base]['original'] ?? $base;
            $links[] = [
                'url' => route('superadmin.reporting.letters.show', ['job' => $key, 'filename' => $base]),
                'name' => $original,
                'stored' => $base,
            ];
        }

        return $links;
    }

    /**
     * Sanitize reference into a storage-safe key (align with ReportingLettersController logic).
     */
    private function sanitizeLetterKey(string $input): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '-', trim($input)) ?: '';
    }

    /**
     * Pendings: show items that have been received but not yet issued (issue_date null).
     * Supports optional search by job order no (search), and month/year filters based on received_at.
     */
    public function pendings(Request $request)
    {    
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->has('month') ? (int) $request->get('month') : null;
        $year = $request->has('year') ? (int) $request->get('year') : null;
        $overdue = $request->boolean('overdue');
        // Always use current date as cutoff for overdue (lab_expected_date < today)
        $cutoff = now()->toDateString();
        $departmentId = $request->get('department');
        $marketing = $request->get('marketing'); // user_code of marketing person
        $labAnalyst = $request->get('lab_analyst'); // user_code of lab analyst
        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? null);
            if ($marketing) {
                $request->merge(['marketing' => $marketing]);
            }
        }
        $mode = $request->get('mode', 'job'); // job | reference
        if (!in_array($mode, ['job','reference'], true)) { $mode = 'job'; }
        if ($month !== null && ($month < 1 || $month > 12)) { $month = null; }
        if ($year !== null && ($year < 2000 || $year > 2100)) { $year = null; }
        $departments = \App\Models\Department::orderBy('name')->get(['id','name']);
        $marketingPersonsQuery = \App\Models\User::whereHas('marketingBookings')
            ->orderBy('name');
        if ($this->isMarketingUser($user) && $marketing) {
            $marketingPersonsQuery->where('user_code', $marketing);
        }
        $marketingPersons = $marketingPersonsQuery->get(['id','name','user_code']);

        $labAnalysts = \App\Models\User::whereHas('role', function ($q) {
                $q->where('role_name', 'like', '%lab%analyst%');
            })
            ->orderBy('user_code')
            ->orderBy('name')
            ->get(['id','name','user_code']);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100, 250, 500], true)) {
            $perPage = 25;
        }

        if ($mode === 'reference') {
            // Aggregate by booking (reference_no) where at least one pending/overdue item
            $bookingQuery = \App\Models\NewBooking::query()->withCount(['items as pending_items_count' => function($q) use ($overdue, $cutoff, $month, $year, $labAnalyst) {
                $q->where(function($sub) {
                     $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                })->where(function($sub) {
                     $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                });
                if ($overdue) {
                    $q->whereNull('issue_date')
                        ->where('lab_expected_date', '!=', '0000-00-00')
                        ->whereDate('lab_expected_date', '<', $cutoff);
                    if ($month) { $q->whereMonth('lab_expected_date', $month); }
                    if ($year) { $q->whereYear('lab_expected_date', $year); }
                } else {
                    // Pendings are items that do not have an issue date
                    $q->whereNull('issue_date');
                    if ($month) {
                        $q->where(function($qq) use ($month){
                            $qq->whereMonth('received_at', $month)->orWhereMonth('created_at', $month);
                        });
                    }
                    if ($year) {
                        $q->where(function($qq) use ($year){
                            $qq->whereYear('received_at', $year)->orWhereYear('created_at', $year);
                        });
                    }
                }

                if (!empty($labAnalyst)) {
                    $q->where('lab_analysis_code', $labAnalyst);
                }
            }])->with(['items' => function($q) use ($overdue, $cutoff, $month, $year){
                $q->where(function($sub) {
                     $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                })->where(function($sub) {
                     $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                });
                if ($overdue) {
                    $q->whereNull('issue_date')
                        ->where('lab_expected_date', '!=', '0000-00-00')
                        ->whereDate('lab_expected_date', '<', $cutoff);
                    if ($month) { $q->whereMonth('lab_expected_date', $month); }
                    if ($year) { $q->whereYear('lab_expected_date', $year); }
                } else {
                    $q->whereNull('issue_date');
                    if ($month) {
                        $q->where(function($qq) use ($month){
                            $qq->whereMonth('received_at', $month)->orWhereMonth('created_at', $month);
                        });
                    }
                    if ($year) {
                        $q->where(function($qq) use ($year){
                            $qq->whereYear('received_at', $year)->orWhereYear('created_at', $year);
                        });
                    }
                }
            }]);
            if (!empty($labAnalyst)) {
                $bookingQuery->whereHas('items', function ($qi) use ($labAnalyst) {
                    $qi->where('lab_analysis_code', $labAnalyst);
                });
            }
            if ($departmentId) { $bookingQuery->where('department_id', $departmentId); }
            if ($marketing) { $bookingQuery->where('marketing_id', $marketing); }
            if ($search !== '') {
                $bookingQuery->where(function($qq) use ($search) {
                    $qq->where('client_name','like',"%{$search}%")
                        ->orWhere('reference_no','like',"%{$search}%");
                });
            }
            // Apply month/year filters. For overdue mode filter by lab_expected_date,
            // otherwise filter by received_at OR created_at.
            if ($month) {
                if ($overdue) {
                    $bookingQuery->whereHas('items', function($qi) use ($month, $cutoff, $labAnalyst){
                        $qi->where(function($sub) {
                                $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                           })->where(function($sub) {
                                $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                           })
                           ->whereNull('issue_date')
                           ->where('lab_expected_date','!=','0000-00-00')
                           ->whereDate('lab_expected_date','<', $cutoff)
                           ->whereMonth('lab_expected_date', $month);

                        if (!empty($labAnalyst)) {
                            $qi->where('lab_analysis_code', $labAnalyst);
                        }
                    });
                } else {
                    $bookingQuery->whereHas('items', function($qi) use ($month, $labAnalyst){
                        $qi->where(function($sub) {
                                $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                           })->where(function($sub) {
                                $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                           })
                           ->where(function($qii) use ($month){
                               $qii->whereMonth('received_at', $month)
                                   ->orWhereMonth('created_at', $month);
                           });

                        if (!empty($labAnalyst)) {
                            $qi->where('lab_analysis_code', $labAnalyst);
                        }
                    });
                }
            }
            if ($year) {
                if ($overdue) {
                    $bookingQuery->whereHas('items', function($qi) use ($year, $cutoff, $labAnalyst){
                        $qi->where(function($sub) {
                                $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                           })->where(function($sub) {
                                $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                           })
                           ->whereNull('issue_date')
                           ->where('lab_expected_date','!=','0000-00-00')
                           ->whereDate('lab_expected_date','<', $cutoff)
                           ->whereYear('lab_expected_date', $year);

                        if (!empty($labAnalyst)) {
                            $qi->where('lab_analysis_code', $labAnalyst);
                        }
                    });
                } else {
                    $bookingQuery->whereHas('items', function($qi) use ($year, $labAnalyst){
                        $qi->where(function($sub) {
                                $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                           })->where(function($sub) {
                                $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                           })
                           ->where(function($qii) use ($year){
                               $qii->whereYear('received_at', $year)
                                   ->orWhereYear('created_at', $year);
                        });

                        if (!empty($labAnalyst)) {
                            $qi->where('lab_analysis_code', $labAnalyst);
                        }
                    });
                }
            }
            $bookingQuery->having('pending_items_count','>',0)->latest('id');
            $bookings = $bookingQuery->paginate($perPage)->withQueryString();
            $items = collect();
            return view('superadmin.reporting.pendings', compact('items','bookings','departments','departmentId','mode','marketingPersons','marketing','labAnalysts','labAnalyst'));
        } else {
            $q = BookingItem::query()->with(['booking']);
            $q->where(function($sub) {
                $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
            })->where(function($sub) {
                $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
            });
            if ($overdue) {
                $q->whereNull('issue_date')
                  ->where('lab_expected_date', '!=', '0000-00-00')
                  ->whereDate('lab_expected_date','<', $cutoff);
                // When overdue filtering, allow month/year to target lab_expected_date
                if ($month) { $q->whereMonth('lab_expected_date', $month); }
                if ($year) { $q->whereYear('lab_expected_date', $year); }
            } else {
                // Pending items: items without an issue date
                $q->whereNull('issue_date');
            }
            if ($departmentId) {
                $q->whereHas('booking', function($b) use ($departmentId) { $b->where('department_id', $departmentId); });
            }
            if ($marketing) {
                $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
            }

            if (!empty($labAnalyst)) {
                $q->where('lab_analysis_code', $labAnalyst);
            }
            if ($search !== '') {
                $q->where(function($qq) use ($search) {
                    $qq->where('job_order_no', 'like', "%{$search}%")
                       ->orWhere('sample_description', 'like', "%{$search}%")
                       ->orWhere('particulars', 'like', "%{$search}%");
                });
            }
            if (!$overdue) {
                if ($month) {
                    $q->where(function($qq) use ($month){
                        $qq->whereMonth('received_at', $month)
                           ->orWhereMonth('created_at', $month);
                    });
                }
                if ($year) {
                    $q->where(function($qq) use ($year){
                        $qq->whereYear('received_at', $year)
                           ->orWhereYear('created_at', $year);
                    });
                }
            }
            $q->latest('id');
            $items = $q->paginate($perPage)->withQueryString();
            $bookings = collect();
            return view('superadmin.reporting.pendings', compact('items','bookings','departments','departmentId','mode','marketingPersons','marketing','labAnalysts','labAnalyst'));
        }
    }

    protected function buildPendingsQuery(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->has('month') ? (int) $request->get('month') : null;
        $year = $request->has('year') ? (int) $request->get('year') : null;
        $departmentId = $request->get('department');
        $marketing = $request->get('marketing');
        $labAnalyst = $request->get('lab_analyst');
        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? null);
            if ($marketing) {
                $request->merge(['marketing' => $marketing]);
            }
        }
        $overdue = $request->boolean('overdue');
        // Always use current date as cutoff for overdue (lab_expected_date < today)
        $cutoff = now()->toDateString();

        $q = BookingItem::query()->with(['booking']);
        $q->where(function($sub) {
            $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
        })->where(function($sub) {
            $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
        });
        if ($overdue) {
            $q->whereNull('issue_date')
              ->where('lab_expected_date', '!=', '0000-00-00')
              ->whereDate('lab_expected_date','<', $cutoff);
        } else {
            // Pending when issue date not set
            $q->whereNull('issue_date');
        }
        if ($departmentId) {
            $q->whereHas('booking', function($b) use ($departmentId) { $b->where('department_id', $departmentId); });
        }
        if ($marketing) {
            $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
        }

        if (!empty($labAnalyst)) {
            $q->where('lab_analysis_code', $labAnalyst);
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
        // Apply month/year filters: when overdue use lab_expected_date, otherwise use received_at OR created_at
        if ($overdue) {
            if ($month) { $q->whereMonth('lab_expected_date', $month); }
            if ($year) { $q->whereYear('lab_expected_date', $year); }
        } else {
            if ($month) {
                $q->where(function($qq) use ($month){
                    $qq->whereMonth('received_at', $month)
                       ->orWhereMonth('created_at', $month);
                });
            }
            if ($year) {
                $q->where(function($qq) use ($year){
                    $qq->whereYear('received_at', $year)
                       ->orWhereYear('created_at', $year);
                });
            }
        }
        return $q;
    }

    public function pendingsExportPdf(Request $request)
    {
        $mode = $request->get('mode', 'job');
        if (!in_array($mode, ['job', 'reference'], true)) {
            $mode = 'job';
        }

        // Protect against very large exports — ask user to apply filters first
        $exportLimit = 2000; // max rows allowed for direct export
        if ($mode === 'reference') {
            $total = $this->buildPendingsBookingsQuery($request)->count();
            if ($total > $exportLimit) {
                return response()->json([
                    'error' => 'Too many records',
                    'message' => "Your current selection returns {$total} bookings. Please narrow down the results (apply month/year/department) before exporting. Maximum allowed: {$exportLimit}.",
                ], 413);
            }

            // Export all matching bookings (no pagination)
            $bookings = $this->buildPendingsBookingsQuery($request)->latest('id')->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('superadmin.reporting.pendings_reference_pdf', [
                'bookings' => $bookings,
                'search' => $request->get('search'),
                'month' => $request->get('month'),
                'year' => $request->get('year'),
                'department' => $request->get('department'),
                'marketing' => $request->get('marketing'),
                'lab_analyst' => $request->get('lab_analyst'),
                'overdue' => $request->boolean('overdue'),
            ])->setPaper('a4', 'landscape');

            $response = $pdf->download('pending_reports_by_reference.pdf');
            // Attach helpful export headers for client-side estimate
            $estimate = (int) ceil($total / 200); // rough seconds estimate (200 rows/sec)
            $response->headers->set('X-Export-Total', (string) $total);
            $response->headers->set('X-Export-EstimateSeconds', (string) max(1, $estimate));
            return $response;
        }

        $totalItems = $this->buildPendingsQuery($request)->count();
        if ($totalItems > $exportLimit) {
            return response()->json([
                'error' => 'Too many records',
                'message' => "Your current selection returns {$totalItems} items. Please narrow down the results (apply month/year/department) before exporting. Maximum allowed: {$exportLimit}.",
            ], 413);
        }

        // Export all matching items (no pagination)
        $items = $this->buildPendingsQuery($request)->latest('id')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('superadmin.reporting.pendings_pdf', [
            'items' => $items,
            'search' => $request->get('search'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'department' => $request->get('department'),
            'marketing' => $request->get('marketing'),
            'lab_analyst' => $request->get('lab_analyst'),
            'overdue' => $request->boolean('overdue'),
        ])->setPaper('a4','landscape');
        $response = $pdf->download('pending_reports_by_job.pdf');
        $estimate = (int) ceil($totalItems / 200);
        $response->headers->set('X-Export-Total', (string) $totalItems);
        $response->headers->set('X-Export-EstimateSeconds', (string) max(1, $estimate));
        return $response;
    }

    public function pendingsExportExcel(Request $request)
    {
        $mode = $request->get('mode', 'job');
        if (!in_array($mode, ['job', 'reference'], true)) {
            $mode = 'job';
        }

        $exportLimit = 2000;
        if ($mode === 'reference') {
            $total = $this->buildPendingsBookingsQuery($request)->count();
            if ($total > $exportLimit) {
                return response()->json([
                    'error' => 'Too many records',
                    'message' => "Your current selection returns {$total} bookings. Please narrow down the results (apply month/year/department) before exporting. Maximum allowed: {$exportLimit}.",
                ], 413);
            }

            $bookings = $this->buildPendingsBookingsQuery($request)->latest('id')->get();
            $response = \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PendingBookingsExport($bookings), 'pending_reports_by_reference.xlsx');
            $estimate = (int) ceil($total / 200);
            $response->headers->set('X-Export-Total', (string) $total);
            $response->headers->set('X-Export-EstimateSeconds', (string) max(1, $estimate));
            return $response;
        }

        $totalItems = $this->buildPendingsQuery($request)->count();
        if ($totalItems > $exportLimit) {
            return response()->json([
                'error' => 'Too many records',
                'message' => "Your current selection returns {$totalItems} items. Please narrow down the results (apply month/year/department) before exporting. Maximum allowed: {$exportLimit}.",
            ], 413);
        }

        $items = $this->buildPendingsQuery($request)->latest('id')->get();
        $response = \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PendingItemsExport($items), 'pending_reports_by_job.xlsx');
        $estimate = (int) ceil($totalItems / 200);
        $response->headers->set('X-Export-Total', (string) $totalItems);
        $response->headers->set('X-Export-EstimateSeconds', (string) max(1, $estimate));
        return $response;
    }

    protected function buildPendingsBookingsQuery(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->has('month') ? (int) $request->get('month') : null;
        $year = $request->has('year') ? (int) $request->get('year') : null;
        $overdue = $request->boolean('overdue');
        $cutoff = now()->toDateString();
        $departmentId = $request->get('department');
        $marketing = $request->get('marketing');
        $labAnalyst = $request->get('lab_analyst');

        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? null);
            if ($marketing) {
                $request->merge(['marketing' => $marketing]);
            }
        }

        if ($month !== null && ($month < 1 || $month > 12)) { $month = null; }
        if ($year !== null && ($year < 2000 || $year > 2100)) { $year = null; }

        $bookingQuery = \App\Models\NewBooking::query()
            ->with(['marketingPerson', 'items'])
            ->withCount(['items as pending_items_count' => function ($q) use ($overdue, $cutoff, $month, $year, $labAnalyst) {
                $q->where(function($sub) {
                    $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                })->where(function($sub) {
                    $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                });
                if ($overdue) {
                    $q->whereNull('issue_date')
                        ->where('lab_expected_date', '!=', '0000-00-00')
                        ->whereDate('lab_expected_date', '<', $cutoff);
                    if ($month) { $q->whereMonth('lab_expected_date', $month); }
                    if ($year) { $q->whereYear('lab_expected_date', $year); }
                } else {
                    $q->whereNull('issue_date');
                    if ($month) {
                        $q->where(function ($qq) use ($month) {
                            $qq->whereMonth('received_at', $month)->orWhereMonth('created_at', $month);
                        });
                    }
                    if ($year) {
                        $q->where(function ($qq) use ($year) {
                            $qq->whereYear('received_at', $year)->orWhereYear('created_at', $year);
                        });
                    }
                }

                if (!empty($labAnalyst)) {
                    $q->where('lab_analysis_code', $labAnalyst);
                }
            }]);

        if ($departmentId) {
            $bookingQuery->where('department_id', $departmentId);
        }
        if (!empty($marketing)) {
            $bookingQuery->where('marketing_id', $marketing);
        }
        if (!empty($labAnalyst)) {
            $bookingQuery->whereHas('items', function ($qi) use ($labAnalyst) {
                $qi->where('lab_analysis_code', $labAnalyst);
            });
        }
        if ($search !== '') {
            $bookingQuery->where(function ($qq) use ($search) {
                $qq->where('client_name', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($month) {
            if ($overdue) {
                $bookingQuery->whereHas('items', function ($qi) use ($month, $cutoff, $labAnalyst) {
                    $qi->where(function($sub) {
                            $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                       })->where(function($sub) {
                            $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                       })
                       ->whereNull('issue_date')
                        ->where('lab_expected_date', '!=', '0000-00-00')
                        ->whereDate('lab_expected_date', '<', $cutoff)
                        ->whereMonth('lab_expected_date', $month);
                    if (!empty($labAnalyst)) {
                        $qi->where('lab_analysis_code', $labAnalyst);
                    }
                });
            } else {
                $bookingQuery->whereHas('items', function ($qi) use ($month, $labAnalyst) {
                    $qi->where(function($sub) {
                            $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                       })->where(function($sub) {
                            $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                       })
                       ->where(function ($qii) use ($month) {
                        $qii->whereMonth('received_at', $month)->orWhereMonth('created_at', $month);
                    });
                    if (!empty($labAnalyst)) {
                        $qi->where('lab_analysis_code', $labAnalyst);
                    }
                });
            }
        }

        if ($year) {
            if ($overdue) {
                $bookingQuery->whereHas('items', function ($qi) use ($year, $cutoff, $labAnalyst) {
                    $qi->where(function($sub) {
                            $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                       })->where(function($sub) {
                            $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                       })
                       ->whereNull('issue_date')
                        ->where('lab_expected_date', '!=', '0000-00-00')
                        ->whereDate('lab_expected_date', '<', $cutoff)
                        ->whereYear('lab_expected_date', $year);
                    if (!empty($labAnalyst)) {
                        $qi->where('lab_analysis_code', $labAnalyst);
                    }
                });
            } else {
                $bookingQuery->whereHas('items', function ($qi) use ($year, $labAnalyst) {
                    $qi->where(function($sub) {
                            $sub->whereNull('cancel_reason')->orWhere('cancel_reason', '');
                       })->where(function($sub) {
                            $sub->whereNull('hold_reason')->orWhere('hold_reason', 'not like', 'CANCELED:%');
                       })
                       ->where(function ($qii) use ($year) {
                        $qii->whereYear('received_at', $year)->orWhereYear('created_at', $year);
                    });
                    if (!empty($labAnalyst)) {
                        $qi->where('lab_analysis_code', $labAnalyst);
                    }
                });
            }
        }

        return $bookingQuery->having('pending_items_count', '>', 0);
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
        if (Schema::hasColumn('booking_items', 'status')) {
            $item->status = $receiverName;
        }
        if (array_key_exists('issue_date', $data)) {
            $item->issue_date = $data['issue_date'];
            // When an issue date is set, mark status as Report Generated
            if (Schema::hasColumn('booking_items', 'status')) {
                $item->status = 'Report Generated';
            }
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

        // Prepare receiver data
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
        if (Schema::hasColumn('booking_items', 'status')) {
            $update['status'] = $receiverName;
        }

        // If explicit IDs provided, update only those (useful for current page)
        // Accept multiple forms: array from inputs named ids[], single string '1,2,3', or single id
        $ids = $request->input('ids');
        if (empty($ids)) {
            $ids = $request->input('ids[]') ?? $request->get('ids');
        }
        if (is_string($ids)) {
            // comma separated or single
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }
        if ($ids instanceof \Illuminate\Support\Collection) {
            $ids = $ids->all();
        }
        if (is_numeric($ids)) {
            $ids = [(int) $ids];
        }
        if (is_array($ids) && count($ids) > 0) {
            $validIds = array_values(array_filter(array_map('intval', $ids)));
            if (!empty($validIds)) {
                BookingItem::whereIn('id', $validIds)->update($update);
                if ($request->wantsJson()) {
                    return response()->json(['ok' => true, 'scope' => 'ids', 'count' => count($validIds), 'receiver_name' => $receiverName, 'received_at' => now()->toIso8601String()]);
                }
                return back()->with('status', 'Selected reports marked as received');
            }
        }

        // If job is provided, try to scope to that booking's items (legacy behavior)
        if ($job !== '') {
            $firstItem = BookingItem::with('booking')
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $firstItem->booking->items()->update($update);
                if ($request->wantsJson()) {
                    return response()->json(['ok' => true, 'scope' => 'booking', 'booking_id' => $firstItem->booking->id, 'receiver_name' => $receiverName, 'received_at' => now()->toIso8601String()]);
                }
                return back()->with('status', 'All matching reports marked as received');
            }
        }

        // Fallback: mark all items as received (use sparingly)
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
                    if (Schema::hasColumn('booking_items', 'status')) {
                        $item->status = $receiverName;
                    }
                }
                $item->issue_date = $row['issue_date'] ?? $item->issue_date;
                if (!empty($row['issue_date']) && Schema::hasColumn('booking_items', 'status')) {
                    $item->status = 'Report Generated';
                }
                $item->save();
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Issue Dates submitted');
    }

    public function updateHeader(Request $request, NewBooking $booking)
    {
        $data = $request->validate([
            'name_of_work' => ['sometimes', 'nullable', 'string', 'max:255'],
            'issued_to' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ms' => ['sometimes', 'nullable', 'string', 'max:255'],
        ], [], [
            'issued_to' => 'Issued To',
            'ms' => 'M/s',
        ]);

        foreach (['name_of_work', 'issued_to', 'ms'] as $key) {
            if (array_key_exists($key, $data) && is_string($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        $updates = [];
        if (array_key_exists('name_of_work', $data)) {
            $updates['name_of_work'] = $data['name_of_work'];
        }
        if (array_key_exists('issued_to', $data)) {
            $updates['report_issue_to'] = $data['issued_to'];
        }
        if (array_key_exists('ms', $data)) {
            $updates['m_s'] = $data['ms'];
        }

        if (!empty($updates)) {
            $booking->fill($updates);
            $booking->save();
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'name_of_work' => $booking->name_of_work ?: $booking->client_address,
                'issued_to' => $booking->report_issue_to,
                'ms' => $booking->m_s,
            ],
        ]);
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
                    'name_of_work'      => $b->name_of_work ?: $b->client_address,
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

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100, 250], true)) {
            $perPage = 25;
        }

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
                    'marketing_person'  => optional($b->marketingPerson)->name,
                    'sample_description'=> $firstItem->sample_description,
                    'name_of_work'      => $b->client_address,
                    'issued_to'         => $b->report_issue_to,
                    'ms'                => $b->contractor_name,
                ];

                $items = $b->items()->with(['booking.marketingPerson', 'analyst', 'receivedBy'])->latest('id')->paginate($perPage)->withQueryString();

                return view('superadmin.reporting.dispatch', compact('items', 'job', 'header', 'readyJobs', 'month', 'year', 'status'));
            }
        }

        // Default view: show items filtered by status, with optional month/year
        $q = BookingItem::query()->with(['booking.marketingPerson', 'analyst', 'receivedBy']);
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
        $items = $q->paginate($perPage)->withQueryString();
        return view('superadmin.reporting.dispatch', compact('items', 'job', 'header', 'readyJobs', 'month', 'year', 'status'));
    }

    /**
     * Dispatched reports listing (marketing-scoped).
     */
    public function dispatched(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->get('month');
        $year = $request->get('year');
        $perPage = (int) $request->get('per_page', $request->get('perPage', 25));
        if (!in_array($perPage, [25, 50, 100, 250], true)) { $perPage = 25; }

        // Enforce marketing scoping for marketing users
        $marketing = $request->get('marketing');
        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? $user->id ?? null);
            if ($marketing) { $request->merge(['marketing' => $marketing]); }
        }

        $handOver = $request->boolean('hand_over');

        if ($handOver) {
            // Only show items that were submitted (handed over to client)
            $q = BookingItem::query()->with(['booking','analyst','receivedBy'])->whereNotNull('submitted_to');
        } else {
            // Show dispatched items that have NOT yet been submitted to client
            $q = BookingItem::query()->with(['booking','analyst','receivedBy'])
                ->whereNotNull('dispatched_at')
                ->whereNull('submitted_to');
        }

        if ($search !== '') {
            $q->where(function($qq) use ($search) {
                $qq->where('job_order_no', 'like', "%{$search}%")
                   ->orWhere('sample_description', 'like', "%{$search}%")
                   ->orWhereHas('booking', function($b) use ($search) {
                        $b->where('client_name', 'like', "%{$search}%");
                   });
            });
        }

        if (is_numeric($month)) { $q->whereMonth('dispatched_at', (int)$month); }
        if (is_numeric($year)) { $q->whereYear('dispatched_at', (int)$year); }

        if (!empty($marketing)) {
            // Booking stores marketing person in `marketing_id` (user_code). Filter by that.
            $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
        }

        // Order appropriately
        if ($handOver) {
            $items = $q->latest('submitted_at')->paginate($perPage)->withQueryString();
        } else {
            $items = $q->latest('dispatched_at')->paginate($perPage)->withQueryString();
        }

        $marketingPersons = \App\Models\User::whereHas('marketingBookings')->orderBy('name')->get(['id','name','user_code']);
        $isAdmin = Auth::guard('admin')->check();
        $isEmpty = ($items->total() === 0);

        $viewPath = resource_path('views/superadmin/Dispatched Reports/dispatched.blade.php');
        if (file_exists($viewPath)) {
            return view()->file($viewPath, compact('items','search','month','year','marketingPersons','isAdmin','isEmpty'));
        }

        return view('superadmin.reporting.dispatched', compact('items','search','month','year','marketingPersons','isAdmin','isEmpty'));
    }

    public function dispatchedExportPdf(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->get('month');
        $year = $request->get('year');

        // Enforce marketing scoping for marketing users
        $marketing = $request->get('marketing');
        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? $user->id ?? null);
            if ($marketing) { $request->merge(['marketing' => $marketing]); }
        }

        $pdfHandOver = $request->boolean('hand_over');
        if ($pdfHandOver) {
            $q = BookingItem::query()->with(['booking'])->whereNotNull('submitted_to');
        } else {
            $q = BookingItem::query()->with(['booking'])->whereNotNull('dispatched_at')->whereNull('submitted_to');
        }
        if ($search !== '') {
            $q->where(function($qq) use ($search) {
                $qq->where('job_order_no', 'like', "%{$search}%")
                   ->orWhere('sample_description', 'like', "%{$search}%")
                   ->orWhereHas('booking', function($b) use ($search) {
                        $b->where('client_name', 'like', "%{$search}%");
                   });
            });
        }
        if (is_numeric($month)) { $q->whereMonth('dispatched_at', (int)$month); }
        if (is_numeric($year)) { $q->whereYear('dispatched_at', (int)$year); }
        if (!empty($marketing)) {
            $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
        }

        $items = $q->latest('dispatched_at')->get();

        $pdf = Pdf::loadView('superadmin.reporting.exports.dispatched_pdf', compact('items'));
        $filename = 'dispatched_reports_'.now()->format('Ymd_His').'.pdf';
        return $pdf->download($filename);
    }

    public function dispatchedExportExcel(Request $request)
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $search = trim((string) $request->get('search'));
        $month = $request->get('month');
        $year = $request->get('year');

        // Enforce marketing scoping for marketing users
        $marketing = $request->get('marketing');
        if ($this->isMarketingUser($user)) {
            $marketing = $marketing ?: ($user->user_code ?? $user->id ?? null);
            if ($marketing) { $request->merge(['marketing' => $marketing]); }
        }

        $excelHandOver = $request->boolean('hand_over');
        if ($excelHandOver) {
            $q = BookingItem::query()->with(['booking'])->whereNotNull('submitted_to');
        } else {
            $q = BookingItem::query()->with(['booking'])->whereNotNull('dispatched_at')->whereNull('submitted_to');
        }
        if ($search !== '') {
            $q->where(function($qq) use ($search) {
                $qq->where('job_order_no', 'like', "%{$search}%")
                   ->orWhere('sample_description', 'like', "%{$search}%")
                   ->orWhereHas('booking', function($b) use ($search) {
                        $b->where('client_name', 'like', "%{$search}%");
                   });
            });
        }
        if (is_numeric($month)) { $q->whereMonth('dispatched_at', (int)$month); }
        if (is_numeric($year)) { $q->whereYear('dispatched_at', (int)$year); }
        if (!empty($marketing)) {
            $q->whereHas('booking', function($b) use ($marketing) { $b->where('marketing_id', $marketing); });
        }

        $items = $q->latest('dispatched_at')->get();

        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                $item->job_order_no,
                $item->booking?->client_name ?? '',
                $item->sample_description,
                optional($item->dispatched_at)->format('Y-m-d H:i:s'),
                $item->dispatched_by_name ?? '',
            ];
        }

        $filename = 'dispatched_reports_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new DispatchedExport($rows), $filename);
    }

    /**
     * Record a handover to client for a single booking item.
     */
    public function handoverOne(Request $request, \App\Models\BookingItem $item)
    {
        $data = $request->validate([
            'client_name' => ['required','string','max:255'],
            'note' => ['nullable','string','max:2000'],
        ]);

        $user = Auth::guard('admin')->user() ?: Auth::user();
        $byId = $user ? $user->id : null;
        $byName = $user ? ($user->name ?? ($user->email ?? null)) : null;

        // create handover record
        $handover = \App\Models\BookingItemHandover::create([
            'booking_item_id' => $item->id,
            'client_name' => $data['client_name'],
            'note' => $data['note'] ?? null,
            'handed_over_by_id' => $byId,
            'handed_over_by_name' => $byName,
            // store explicit datetime string to avoid DB adapter casting issues
            'handed_over_at' => now()->toDateTimeString(),
        ]);

        // ensure we have fresh values from DB (some DB drivers may modify default values)
        $handover->refresh();

        // update last handed over summary fields on booking_items if columns exist
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_items', 'last_handed_over_at')) {
            $item->last_handed_over_at = now();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_items', 'last_handed_over_to')) {
            $item->last_handed_over_to = $data['client_name'];
        }
        // mark booking item status as handed over
        $item->status = 'hand over';
        $item->save();

        // compute count and last handover info to return to AJAX client
        $count = \App\Models\BookingItemHandover::where('booking_item_id', $item->id)->count();
        $last = \App\Models\BookingItemHandover::where('booking_item_id', $item->id)
            ->latest('handed_over_at')
            ->first();

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'client' => $data['client_name'],
                'at' => $handover->handed_over_at->toDateTimeString(),
                'count' => $count,
                'last' => $last ? [
                    'client' => $last->client_name,
                    'at' => optional($last->handed_over_at)->toDateTimeString(),
                    'by' => $last->handed_over_by_name,
                    'note' => $last->note,
                ] : null,
            ], 200);
        }

        return back()->with('success', 'Handed over to client: ' . $data['client_name']);
    }

    /**
     * Return handover history for a booking item (JSON).
     */
    public function handoverHistory(Request $request, \App\Models\BookingItem $item)
    {
        $history = \App\Models\BookingItemHandover::where('booking_item_id', $item->id)
            ->orderBy('handed_over_at', 'desc')
            ->get(['id','client_name','note','handed_over_by_name','handed_over_at','created_at'])
            ->map(function($h){
                return [
                    'id' => $h->id,
                    'client' => $h->client_name,
                    'note' => $h->note,
                    'by' => $h->handed_over_by_name,
                    'at' => optional($h->handed_over_at ?: $h->created_at)->toDateTimeString(),
                ];
            });

        return response()->json(['ok' => true, 'history' => $history], 200);
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
    if (Schema::hasColumn('booking_items', 'status')) {
        $item->status = 'Dispatched';
    }
    foreach ($meta as $k => $v) { $item->{$k} = $v; }
        $item->save();
        $item->refresh();

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
            // dispatch meta (existing)
            'meta.dispatch_by' => ['sometimes','string','max:100'],
            'meta.dispatch_person_name' => ['sometimes','string','max:150'],
            'meta.dispatch_assignment_no' => ['sometimes','string','max:100'],
            'meta.dispatch_comment' => ['nullable','string','max:2000'],
            // submission (hand-over) meta
            'meta.submitted_to' => ['sometimes','string','max:191'],
        ]);
        $dispatcherId = auth('web')->check() ? auth('web')->id() : null;
        $dispatcherName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $update = [];

        // If dispatch meta provided, mark dispatched
        if (!empty($payload['meta']['dispatch_person_name']) || !empty($payload['meta']['dispatch_by'])) {
            $update = array_merge($update, [
                'dispatched_at' => now(),
                'dispatched_by_name' => $dispatcherName,
            ] + (Schema::hasColumn('booking_items', 'dispatched_by_id') ? ['dispatched_by_id' => $dispatcherId] : []));
            if (Schema::hasColumn('booking_items', 'status')) {
                $update['status'] = 'Dispatched';
            }
        }

        // Apply provided meta fields directly (for dispatch_* keys)
        if (!empty($payload['meta'])) {
            foreach ($payload['meta'] as $k => $v) {
                // skip submitted_to here (handled separately)
                if ($k === 'submitted_to') continue;
                $update[$k] = $v;
            }
        }

        // If submission (hand-over) info provided, set submitted fields
        if (!empty($payload['meta']['submitted_to'])) {
            if (Schema::hasColumn('booking_items', 'submitted_to')) {
                $update['submitted_to'] = $payload['meta']['submitted_to'];
            }
            if (Schema::hasColumn('booking_items', 'submitted_at')) {
                $update['submitted_at'] = now();
            }
            if (Schema::hasColumn('booking_items', 'status')) {
                $update['status'] = 'Submitted to client';
            }
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
        $data = $request->validate([
            'storage_no' => ['nullable', 'string', 'max:100'],
            'storage_description' => ['nullable', 'string'],
        ]);

        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $item->account_received_at = now();
        if (Schema::hasColumn('booking_items', 'status')) {
            $item->status = 'In Account';
        }
        if (Schema::hasColumn('booking_items', 'account_received_by_id')) {
            $item->account_received_by_id = $receiverId;
        }
        $item->account_received_by_name = $receiverName;

        // Save storage fields if the columns exist
        if (array_key_exists('storage_no', $data) && Schema::hasColumn('booking_items', 'storage_no')) {
            $item->storage_no = $data['storage_no'];
        }
        if (array_key_exists('storage_description', $data) && Schema::hasColumn('booking_items', 'storage_description')) {
            $item->storage_description = $data['storage_description'];
        }

        $item->save();
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'account_received_at' => optional($item->account_received_at)->toIso8601String(),
                'receiver_name' => $receiverName,
                'storage_no' => $item->storage_no ?? null,
                'storage_description' => $item->storage_description ?? null,
            ]);
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
            'storage_no' => ['nullable', 'string', 'max:100'],
            'storage_description' => ['nullable', 'string'],
        ]);
        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $update = [
            'account_received_at' => now(),
            'account_received_by_name' => $receiverName,
        ];
        if (Schema::hasColumn('booking_items', 'account_received_by_id')) {
            $update['account_received_by_id'] = $receiverId;
        }
        if (Schema::hasColumn('booking_items', 'status')) {
            $update['status'] = 'In Account';
        }
        // Add storage fields when provided and column exists
        if (!empty($payload['storage_no']) && Schema::hasColumn('booking_items', 'storage_no')) {
            $update['storage_no'] = $payload['storage_no'];
        }
        if (array_key_exists('storage_description', $payload) && Schema::hasColumn('booking_items', 'storage_description')) {
            $update['storage_description'] = $payload['storage_description'];
        }
        \App\Models\BookingItem::whereIn('id', $payload['ids'])->update($update);
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
