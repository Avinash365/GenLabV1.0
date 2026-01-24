<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewBooking;
use App\Models\Department;
use App\Services\GetUserActiveDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\SiteSetting;
use App\Services\NumberToWordsService;


class ShowBookingController extends Controller
{
    
    protected $departmentService;
    
    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }

        protected function buildQuery(Request $request, Department $department = null)
        {
            $search = trim($request->input('search'));
            $month  = $request->input('month');
            $year   = $request->input('year');

            $query = NewBooking::with([
                'items.reports',
                'department',
                'marketingPerson',
                'generatedInvoice',
            ]);

            // Department filter
            if ($department) {
                $query->where('department_id', $department->id);
            }

            //  Optimized Search
            $query->when(strlen($search) >= 2, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $like = '%' . $search . '%';

                    if (is_numeric($search)) {
                        $sub->orWhere('id', (int) $search);
                    }

                    $sub->orWhere('reference_no', 'like', $like)
                        ->orWhere('client_name', 'like', $like)
                        ->orWhereHas('marketingPerson', function ($mpQ) use ($like) {
                            $mpQ->where('name', 'like', $like);
                        })
                        ->orWhereHas('department', function ($deptQ) use ($like) {
                            $deptQ->where('name', 'like', $like);
                        })
                        ->orWhereHas('items', function ($itemQ) use ($like) {
                            $itemQ->where('job_order_no', 'like', $like)
                                ->orWhere('sample_description', 'like', $like)
                                ->orWhere('sample_quality', 'like', $like)
                                ->orWhere('particulars', 'like', $like)
                                ->orWhere('lab_analysis_code', 'like', $like);
                        });
                });
            });

            // Determine which date column to use for filtering
            $dateColumn = $request->boolean('use_created_at') ? 'created_at' : 'job_order_date';

            // Month filter
            if (!empty($month)) {
                $query->whereMonth($dateColumn, $month);
            }

            // Year filter
            if (!empty($year)) {
                $query->whereYear($dateColumn, $year);
            }

            // Marketing person filter
            if ($request->filled('marketing')) {
                $query->where('marketing_id', $request->input('marketing'));
            }

            return $query;
        }


        public function exportPdf(Request $request, Department $department = null)
        {
            $query = $this->buildQuery($request, $department);

            if ($request->has(['page', 'perPage'])) {
                $bookings = $query->latest()->forPage($request->input('page'), $request->input('perPage'))->get();
            } else {
                // Safety: avoid building extremely large PDFs that exhaust PHP memory.
                // Set max allowed rows via env `BOOKING_EXPORT_MAX_ROWS` (default 3000).
                $maxRows = (int) config('app.booking_export_max_rows', env('BOOKING_EXPORT_MAX_ROWS', 3000));
                $total = $query->count();

                if ($total > $maxRows) {
                    return back()->with('error', "Too many records to export as PDF ({$total}). Please narrow the filters or set BOOKING_EXPORT_MAX_ROWS in your .env (current: {$maxRows}).");
                }

                // Optional: raise memory limit for export if configured via env BOOKING_EXPORT_MEMORY_LIMIT.
                $mem = env('BOOKING_EXPORT_MEMORY_LIMIT');
                if ($mem) {
                    @ini_set('memory_limit', $mem);
                }

                $bookings = $query->latest()->get();
            }

            $pdf = Pdf::loadView('superadmin.showbooking.showbooking_pdf', [
                'bookings' => $bookings,
                'department' => $department,
                'search' => $request->input('search'),
                'month' => $request->input('month'),
                'year' => $request->input('year'),
            ])->setPaper('a4', 'landscape');

            return $pdf->stream('bookings.pdf');
        }

        public function exportExcel(Request $request, Department $department = null)
        {
            // Use the query builder and a chunked export to avoid loading all rows into memory
            $query = $this->buildQuery($request, $department)->latest();

            if ($request->has(['page', 'perPage'])) {
                $query->forPage($request->input('page'), $request->input('perPage'));
            }

            // Eager load relationships used in mapping to avoid N+1 while streaming
            $query = $query->with(['items', 'department', 'marketingPerson']);

            return Excel::download(new \App\Exports\BookingsQueryExport($query), 'bookings.xlsx');
        }
    
    public function index(Request $request, Department $department = null)
    {
        $query = $this->buildQuery($request, $department);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100])) { $perPage = 25; }
        $bookings = $query->latest()->paginate($perPage)->withQueryString();

        $departments = $this->departmentService->getDepartment();
        $marketingPersons = User::whereHas('marketingBookings')
            ->orderBy('user_code')
            ->orderBy('name')
            ->get(['id', 'name', 'user_code']);

        return view('superadmin.showbooking.showbooking', [
            'bookings' => $bookings,
            'department' => $department,
            'departments' => $departments,
            'marketingPersons' => $marketingPersons,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ]);
    }

    public function marketing(Request $request, Department $department = null)
    {
        $query = $this->buildQuery($request, $department);

        $perPage = (int) $request->get('perPage', 25);
        if (!in_array($perPage, [25, 50, 100,500])) { $perPage = 25; }
        $bookings = $query->latest()->paginate($perPage)->withQueryString();

        // Collect uploaded report files per booking (align with reporting view)
        $letterFiles = [];
        foreach ($bookings as $bk) {
            $letterFiles[$bk->id] = $this->uploadedReportsForReference($bk->reference_no);
        }

        $departments = $this->departmentService->getDepartment();

        return view('superadmin.showbooking.marketing.showbooking', [
            'bookings' => $bookings,
            'department' => $department,
            'departments' => $departments,
            'search' => $request->input('search'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'letterFiles' => $letterFiles,
        ]);
    }
    
    /**
     * Get all uploaded report links for a reference (mirrors ReportingController logic).
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
                'url' => route('superadmin.reporting.letters.show', ['job' => $reference, 'filename' => $base]),
                'name' => $original,
                'stored' => $base,
            ];
        }

        return $links;
    }

    private function sanitizeLetterKey(string $input): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '-', trim($input)) ?: '';
    }
    
    /**
     * Render a client card (HTML) for printing; uses site settings for company info.
     */
    public function clientCard($bookingId, $itemId = null)
    {
        $booking = NewBooking::with('items')->findOrFail($bookingId);
        $item = null;
        if ($itemId) {
            $item = $booking->items->firstWhere('id', $itemId);
        }

        $site = SiteSetting::first();

        // Render as PDF to match booking card output
        // compute amount in words using service
        $numberToWords = app(NumberToWordsService::class)->convert(round($booking->total_amount ?? 0));
        $amountInWords = $numberToWords ? ucfirst(trim($numberToWords)) . ' only' : '';

        $pdf = Pdf::loadView('pdf.client_card', [
            'booking' => $booking,
            'item' => $item,
            'site' => $site,
            'amount_in_words' => $amountInWords,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('client_card.pdf');
    }
}