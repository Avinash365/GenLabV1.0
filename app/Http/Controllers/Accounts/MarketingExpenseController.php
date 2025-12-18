<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Traits\HandlesMarketingExpenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\{User, MarketingExpense};
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MarketingExpensesExport;
use Mpdf\Mpdf;
use App\Models\ClearedExpense;
use Illuminate\Support\Facades\DB;

class MarketingExpenseController extends Controller
{
    use HandlesMarketingExpenses;

    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $section = 'marketing';

        $listing = $this->buildListingFromQuery(
            $this->buildExportQuery($request, $section, $status)
        );

        return view('superadmin.marketing.expenses.index', [
            'expenses' => $listing['expenses'],
            'totals'   => $listing['totals'],
            'status'   => $status,
            'section'  => $section,
        ]);
    }

    public function approved(Request $request)
    {
       
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing', 'office', 'personal'], true)){
            $section = 'marketing';
        }

        if($section === 'marketing'){
            // Per-page for pending list
            $perPage = (int) ($request->input('perPage') ?? 15);
            $marketingExpenses = $this->buildExportQuery($request, 'marketing', 'pending')->get();

            $personalQuery = $this->buildExportQuery($request, 'personal', 'pending');
            $personalPending = $personalQuery->get();

            // Show personal expenses individually in approvals (do not combine into summaries)
            $combined = $marketingExpenses
                ->concat($personalPending)
                ->sortByDesc(function($expense){
                    $created = $expense->created_at ?? null;
                    if($created instanceof Carbon){ return $created->timestamp; }
                    if($created){
                        try { return Carbon::parse($created)->timestamp; } catch (\Throwable $e) { return 0; }
                    }
                    return 0;
                })
                ->values();
            // Paginate combined collection
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $paginator = new LengthAwarePaginator(
                $combined->forPage($currentPage, $perPage),
                $combined->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $totals = $this->calculateTotals($marketingExpenses->concat($personalPending));

            // Per-page for approved list (card)
            $approvedPerPage = (int) ($request->input('approved_per_page') ?? 15);
            $approvedCardSection = 'personal';
            $selectedApprovedPerson = $request->input('marketing_person_code');
            $approvedListQuery = MarketingExpense::query()
                ->with(['marketingPerson','approver'])
                ->where('status', 'approved')
                ->whereNull('cleared_at')
                ->when($approvedCardSection === 'personal', fn($q) => $q->where('section', 'personal'))
                ->when($selectedApprovedPerson, function($q) use ($selectedApprovedPerson){
                    $mp = $selectedApprovedPerson;
                    $q->where(function($inner) use ($mp){
                        $inner->where('marketing_person_code', $mp)
                              ->orWhereHas('marketingPerson', function($m) use ($mp){
                                  $m->where('user_code', $mp)->orWhere('id', $mp);
                              });
                    });
                })
                ->orderBy('created_at', 'desc');
            $approvedList = $approvedListQuery->paginate($approvedPerPage)->withQueryString();

            // If the approved rows are requested as a partial (AJAX), return just the rows
            if ($request->ajax() && $request->input('approved_partial')) {
                return view('superadmin.marketing.expenses._approved_rows', ['approvedList' => $approvedList]);
            } 
            

            // --------------------------------    
            // SEND NOTIFICATION TO EXPENSE OWNER (MARKETING USER)
            // --------------------------------- 

            $expense = $marketingExpenses->first();    // or the specific approved expense

            if ($expense && $expense->marketingPerson) {

                $marketingUser = User::where('user_code', $expense->marketingPerson->user_code)->first();
              
                if ($marketingUser) {
                    SendMarketingNotificationJob::dispatch(
                        $marketingUser,
                        "Expense Approved",
                        "Your expense of ₹{$expense->amount} has been approved.",
                        [
                            "type"        => "expense_approved",
                            "expense_id"  => $expense->id,
                            "section"     => $expense->section,
                            "amount"      => $expense->amount,
                            "approved_by" => auth()->id(),
                            "status"      => "approved"
                        ]
                    );
                }
            } 

            return view('superadmin.marketing.expenses.approve', [
                'expenses' => $paginator,
                'totals'   => $totals,
                'status'   => 'pending',
                'section'  => $section,
                'approvedList' => $approvedList,
                'selected_per_page' => $perPage,
                'selected_approved_per_page' => $approvedPerPage,
            ]); 
        }

        $query = $this->buildExportQuery($request, $section, 'pending');

        if($section === 'personal'){
            // Personal section: show personal expenses (pending) in listings.
        }

        $listing = $this->buildListingFromQuery($query, $section === 'personal');

        // Also prepare the Approved list (card) for the view so the Blade template
        // does not run database queries while rendering.
        $mainSection = $section ?? 'marketing';
        $approvedCardSection = $mainSection === 'marketing' ? 'personal' : $request->input('approved_section', $mainSection);
        $selectedApprovedPerson = $request->input('marketing_person_code');
        $approvedList = MarketingExpense::query()
            ->with(['marketingPerson','approver'])
            ->where('status', 'approved')
            ->whereNull('cleared_at')
            ->when($approvedCardSection === 'personal', fn($q) => $q->where('section', 'personal'))
            ->when($approvedCardSection === 'office', fn($q) => $q->where('section', 'office'))
            ->when($approvedCardSection === 'marketing', fn($q) => $q->where('section', 'marketing'))
            ->when($selectedApprovedPerson, function($q) use ($selectedApprovedPerson){
                $mp = $selectedApprovedPerson;
                $q->where(function($inner) use ($mp){
                    $inner->where('marketing_person_code', $mp)
                          ->orWhereHas('marketingPerson', function($m) use ($mp){
                              $m->where('user_code', $mp)->orWhere('id', $mp);
                          });
                });
            })
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // For non-marketing sections, support perPage for main list and approvedList
        $perPage = (int) ($request->input('perPage') ?? 15);
        $approvedPerPage = (int) ($request->input('approved_per_page') ?? 15);

        // Main pending list
        $expenses = $listing['expenses'];
        if ($expenses instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // Already paginated
        } elseif ($expenses instanceof \Illuminate\Support\Collection) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $expenses = new LengthAwarePaginator(
                $expenses->forPage($currentPage, $perPage),
                $expenses->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Approved list (card)
        $approvedList = $approvedList instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $approvedList
            : (new LengthAwarePaginator(
                collect($approvedList)->forPage(LengthAwarePaginator::resolveCurrentPage(), $approvedPerPage),
                collect($approvedList)->count(),
                $approvedPerPage,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            ));

        return view('superadmin.marketing.expenses.approve', [
            'expenses' => $expenses,
            'totals'   => $listing['totals'],
            'status'   => 'pending',
            'section'  => $section,
            'approvedList' => $approvedList,
            'selected_per_page' => $perPage,
            'selected_approved_per_page' => $approvedPerPage,
        ]);
    }

    public function rejected(Request $request)
    {
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing', 'office', 'personal'], true)){
            $section = 'marketing';
        }

        $status = 'rejected';

        $listing = $this->buildListingFromQuery(
            $this->buildExportQuery($request, $section, $status)
        );

        return view('superadmin.marketing.expenses.index', [
            'expenses' => $listing['expenses'],
            'totals'   => $listing['totals'],
            'status'   => $status,
            'section'  => $section,
        ]);
    }

    public function office(Request $request)
    {
        $status = $request->input('status', 'all');
        $section = 'office';

        $listing = $this->buildListingFromQuery(
            $this->buildExportQuery($request, $section, $status)
        );

        return view('superadmin.office.expenses.index', [
            'expenses' => $listing['expenses'],
            'totals'   => $listing['totals'],
            'status'   => $status,
            'section'  => $section,
        ]);
    }

    public function personal(Request $request)
    {
        $status = $request->input('status', 'all');

        $user = auth()->user();
        // If no user is authenticated, show empty listing
        if (!$user) {
            return view('superadmin.personal.expenses.index', [
                'expenses' => collect(),
                'approvedRejected' => collect(),
                'totals' => ['total_expenses' => 0, 'approved' => 0],
                'status' => $status,
                'section' => 'personal',
                'dailyExpenses' => collect(),
            ]);
        }

        // Build base query for the logged-in user's personal expenses
        $baseQuery = $this->buildExportQuery($request, 'personal', $status)
            ->where(function($q) use ($user){
                $q->where('marketing_person_code', $user->user_code)
                  ->orWhere('person_name', $user->name);
            });

        $listing = $this->buildListingFromQuery($baseQuery, true);

        $monthFilter = $request->input('month');
        $yearFilter  = $request->input('year');
        $today = now();
        $targetMonth = $monthFilter ? (int) $monthFilter : (int) $today->format('n');
        $targetYear  = $yearFilter ? (int) $yearFilter : (int) $today->format('Y');

        // Keep personal expenses (pending, approved and rejected) in the daily uploads table
        $dailyExpensesQuery = MarketingExpense::where('section', 'personal')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereYear('created_at', $targetYear)
            ->whereMonth('created_at', $targetMonth)
            ->where(function($q) use ($user){
                $q->where('marketing_person_code', $user->user_code)
                  ->orWhere('person_name', $user->name);
            })
            ->orderByDesc('created_at');

        $dailyExpenses = $dailyExpensesQuery->get();

        if($search = $request->input('search')){
            $dailyExpenses = $dailyExpenses->filter(function($expense) use ($search){
                $term = mb_strtolower($search);
                $description = mb_strtolower((string) $expense->description);
                $person = mb_strtolower((string) $expense->person_name);
                return str_contains($description, $term) || str_contains($person, $term);
            })->values();
        }

        // Also prepare a paginator of approved/rejected personal expenses for the "Approved & Rejected" table
        // Exclude personal items that are part of submitted_for_approval summaries
        // to keep approved personal expenses visible in the daily uploads table.
        $approvedRejectedQuery = $this->buildExportQuery($request, 'personal')
            ->where(function($q) use ($user){
                $q->where('marketing_person_code', $user->user_code)
                  ->orWhere('person_name', $user->name);
            })
            ->whereIn('status', ['approved', 'rejected'])
            // Exclude items that have been attached to a summary PDF
            ->whereNull('approval_summary_path');

        // Use same per-page as main listing if available, otherwise default to 15
        $defaultPerPage = method_exists($listing['expenses'], 'perPage') ? $listing['expenses']->perPage() : 15;
        $perPage = (int) ($request->input('approved_per_page') ?? $defaultPerPage);
        $approvedRejected = $approvedRejectedQuery->paginate($perPage)->withQueryString();

        // Build 'Checked In' items from DB-backed cleared_expenses records (falls back to storage files elsewhere)
        try {
            $records = ClearedExpense::orderByDesc('created_at')->get();
        } catch (\Throwable $_) {
            $records = collect();
        }

        $checkedIn = $records->map(function($rec){
            $path = $rec->path;
            return [
                'path' => $path,
                'url' => asset('storage/' . $path),
                'filename' => $rec->filename,
                'meta' => $rec->meta ?? [],
            ];
        })->values();

        // Filter checkedIn for this user (match marketing_person_code in meta.filters or person_name/person_code in meta)
        $checkedIn = $checkedIn->filter(function($it) use ($user){
            $meta = $it['meta'] ?? [];
            // Skip checked-in items that were marked as global exports
            if (!empty($meta['hide_from_personal'])) return false;
            $filters = $meta['filters'] ?? [];

            // 1) Check explicit query filter saved
            $mp = $filters['marketing_person_code'] ?? null;
            if ($mp && (string)$mp === (string)$user->user_code) return true;

            // 2) Check explicit arrays of person codes/names saved in metadata
            $personCodes = $meta['person_codes'] ?? [];
            $personNames = $meta['person_names'] ?? [];
            if (!empty($personCodes) && in_array((string)$user->user_code, array_map('strval', $personCodes), true)) return true;
            foreach ($personNames as $pn) {
                if (!empty($pn) && stripos($pn, $user->name) !== false) return true;
            }

            // 3) Check explicit single person fields
            $personCode = $meta['person_code'] ?? null;
            $personName = $meta['person_name'] ?? null;
            if ($personCode && (string)$personCode === (string)$user->user_code) return true;
            if ($personName && stripos($personName, $user->name) !== false) return true;

                // 4) If expense_ids exist, load those expenses and check ownership
            if (!empty($meta['expense_ids']) && is_array($meta['expense_ids'])){
                $ids = array_map('intval', $meta['expense_ids']);
                $match = \App\Models\MarketingExpense::whereIn('id', $ids)
                    ->where(function($q) use ($user){
                        $q->where('marketing_person_code', $user->user_code)
                          ->orWhere('person_name', 'like', "%{$user->name}%");
                    })->exists();
                if ($match) return true;
            }

            // 5) Fallback: check filename contains user code or name
            $filename = $it['filename'] ?? '';
            if ($filename && (stripos($filename, $user->user_code) !== false || stripos($filename, $user->name) !== false)) return true;

            return false;
        })->values();

        // Remove approved daily expenses that were already cleared (match month/year & person code if available in metadata)
        $dailyExpenses = $dailyExpenses->reject(function($expense) use ($checkedIn){
            if($expense->status !== 'approved') return false;
            foreach($checkedIn as $it){
                $meta = $it['meta'] ?? [];
                // Do not remove daily expenses for global exports
                if (!empty($meta['hide_from_personal'])) continue;
                // If metadata contains explicit expense_ids, remove those exact expenses
                if(!empty($meta['expense_ids']) && is_array($meta['expense_ids'])){
                    if(in_array((int)$expense->id, array_map('intval', $meta['expense_ids']))) return true;
                }

                $filters = $meta['filters'] ?? [];
                if(!empty($filters['month']) && !empty($filters['year'])){
                    $m = (int)$filters['month'];
                    $y = (int)$filters['year'];
                    if($expense->created_at && ((int)$expense->created_at->format('n') === $m) && ((int)$expense->created_at->format('Y') === $y)){
                        // If metadata has marketing_person_code, ensure it matches expense
                        if(!empty($filters['marketing_person_code'])){
                            if((string)$filters['marketing_person_code'] === (string)$expense->marketing_person_code) return true;
                        } else {
                            return true;
                        }
                    }
                }
            }
            return false;
        })->values();

        // Ensure checkedIn items have approver_name resolved for display
        $checkedIn = $checkedIn->map(function($it){
            $meta = $it['meta'] ?? [];
            $approverName = $meta['approver_name'] ?? null;
            if (empty($approverName) && !empty($meta['approver_id'])){
                $approver = \App\Models\Admin::find($meta['approver_id']) ?? \App\Models\User::find($meta['approver_id']);
                $approverName = $approver?->name ?? null;
            }
            $meta['approver_name'] = $approverName;
            $it['meta'] = $meta;
            return $it;
        })->values();

        // Paginate checkedIn items for personal view
        $checkedInPerPage = (int) ($request->input('checkedin_per_page') ?? 15);
        $checkedInPage = (int) ($request->input('checkedin_page') ?? 1);
        $checkedInPaginator = new LengthAwarePaginator(
            collect($checkedIn)->forPage($checkedInPage, $checkedInPerPage),
            collect($checkedIn)->count(),
            $checkedInPerPage,
            $checkedInPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('superadmin.personal.expenses.index', [
            'expenses'       => $listing['expenses'],
            'approvedRejected'=> $approvedRejected,
            'totals'         => $listing['totals'],
            'status'         => $status,
            'section'        => 'personal',
            'dailyExpenses'  => $dailyExpenses,
            'checkedIn'      => $checkedInPaginator,
            'selected_checkedin_per_page' => $checkedInPerPage,
            'selected_approved_per_page' => $perPage,
        ]);
    }

    public function persons(Request $request)
    {
        return $this->marketingPersons($request);
    }

    public function marketingPersons(Request $request)
    {
        $q = $request->get('q');
        $persons = User::when($q, function($s) use ($q){
                $s->where(function($w) use ($q){
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('user_code','like',"%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['name','user_code','id']);

        return response()->json($persons);
    }

    public function officePersons(Request $request)
    {
        $q = $request->get('q');
        $persons = User::when($q, function($s) use ($q){
                $s->where(function($w) use ($q){
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('user_code','like',"%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['name','user_code','id']);
        return response()->json($persons);
    }

    public function exportPdf(Request $request)
    {
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office','personal'])){ $section = 'marketing'; }

        $expenses = $this->buildExportQuery($request, $section)->get();

        $pdf = Pdf::loadView('superadmin.marketing.expenses.export_pdf', [
            'expenses' => $expenses,
            'section'  => $section,
            'title'    => match($section){
                'office'   => 'Office Expenses',
                'personal' => 'Personal Expenses',
                default    => 'Marketing Expenses',
            },
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('%s-expenses-%s.pdf', $section, now()->format('Ymd_His'));
        return $pdf->download($filename);
    }

    /**
     * Download Approved Expenses (In Account) PDF with receipts appended.
     */
    public function inAccount(Request $request)
    {
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office','personal'])){ $section = 'marketing'; }

        // Determine which approved subsection to use
        $approvedSection = $request->input('approved_section');
        if(!$approvedSection && $section === 'marketing'){
            // on marketing page we show personal approved; default to personal
            $approvedSection = 'personal';
        }

        $query = \App\Models\MarketingExpense::with(['marketingPerson','approver'])
            ->where('status', 'approved')
            ->whereNull('cleared_at')
            ->when($approvedSection === 'personal', fn($q) => $q->where('section', 'personal'))
            ->when($approvedSection === 'office', fn($q) => $q->where('section', 'office'))
            ->when($approvedSection === 'marketing', fn($q) => $q->where('section', 'marketing'));

        if($mp = $request->input('marketing_person_code')){
            $query->where(function($inner) use ($mp){
                $inner->where('marketing_person_code', $mp)
                      ->orWhereHas('marketingPerson', function($m) use ($mp){
                          $m->where('user_code', $mp)->orWhere('id', $mp);
                      });
            });
        }

        // Apply month/year/search if provided using existing helper buildExportQuery
        // but since we already have a base query, apply year/month/search manually
        if($year = $request->input('year')){ $query->whereYear('created_at', $year); }
        if($month = $request->input('month')){ $query->whereMonth('created_at', $month); }
        if($search = $request->input('search')){
            $query->where(function($q) use ($search){
                $q->whereHas('marketingPerson', function($sub) use ($search){
                    $sub->where('name', 'like', "%{$search}%")->orWhere('user_code', 'like', "%{$search}%");
                })->orWhere('person_name', 'like', "%{$search}%")->orWhere('marketing_person_code', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderByDesc('created_at')->get();

        // If there are no approved (and not already cleared) expenses matching the filters,
        // return a JSON error for AJAX requests so the frontend won't try to download or
        // process an empty/old PDF.
        if ($expenses->isEmpty()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved expenses found for the selected filters.',
                ], 422);
            }
            // For non-AJAX requests, redirect back with a flash message
            return back()->with('error', 'No approved expenses found for the selected filters.');
        }

        $totals = [
            'total_expenses' => $expenses->sum(fn($e) => (float)($e->amount ?? 0)),
            'approved' => $expenses->sum(fn($e) => (float)(($e->approved_amount ?? 0) ?: $e->amount)),
        ];

            // Determine if all expenses belong to a single person (name)
            $personNames = collect($expenses)->map(function($e){
                if (isset($e->marketingPerson) && $e->marketingPerson) return $e->marketingPerson->name;
                return $e->person_name ?? null;
            })->filter()->unique()->values();

            $personCodes = collect($expenses)->pluck('marketing_person_code')->filter()->unique()->values();

            $singlePersonName = null;
            $singlePersonCode = null;
            if ($personNames->count() === 1) {
                $singlePersonName = $personNames->first();
                if ($personCodes->count() === 1) {
                    $singlePersonCode = $personCodes->first();
                }
            }

            // Render HTML for the table
            $html = view('superadmin.marketing.expenses.in_account_pdf', [
                'expenses' => $expenses,
                'totals' => $totals,
                'approvedSection' => $approvedSection,
                'singlePersonName' => $singlePersonName,
                'singlePersonCode' => $singlePersonCode,
            ])->render();

        $tempDir = storage_path('app/temp/mpdf');
        if (!is_dir($tempDir)) { mkdir($tempDir, 0775, true); }

        $mpdf = new Mpdf(['format' => 'A4', 'tempDir' => $tempDir]);
        $mpdf->WriteHTML($html);

        // Append receipts (images/pdf) collected from the expenses
        $receiptPaths = collect($expenses)->pluck('file_path')->filter()->unique()->values();
        foreach ($receiptPaths as $receiptPath) {
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($receiptPath)) { continue; }
            try {
                $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($receiptPath);
                $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
                if ($extension === 'pdf') {
                    $pageCount = $mpdf->SetSourceFile($absolutePath);
                    for ($page = 1; $page <= $pageCount; $page++) {
                        $template = $mpdf->ImportPage($page);
                        $mpdf->AddPage();
                        $mpdf->UseTemplate($template);
                    }
                    continue;
                }
                if (in_array($extension, ['jpg','jpeg','png','gif','bmp','webp'], true)) {
                    $mpdf->AddPage();
                    $type = $extension === 'jpg' ? 'jpeg' : $extension;
                    $mpdf->Image($absolutePath, 10, 10, 190, 0, strtoupper($type));
                }
            } catch (\Throwable $th) {
                // continue on any failure silently
                continue;
            }
        }

        // If the export contains multiple different person names, mark it clearly as a Global Expense
        if ($personNames->count() > 1) {
            $filename = sprintf('Global Expense-%s.pdf', now()->format('Ymd_His'));
        } else {
            $filename = sprintf('in-account-approved-%s.pdf', now()->format('Ymd_His'));
        }

        // Save generated PDF to storage so it can be listed in "Cleared Expenses"
        $pdfOutput = $mpdf->Output('', 'S');
        $storagePath = 'marketing_expenses/in_account/' . $filename;
        try {
            Storage::disk('public')->put($storagePath, $pdfOutput);

            // Store a small metadata file alongside the PDF for listing purposes
            $first = $expenses->first();
            $personCodes = $expenses->pluck('marketing_person_code')->filter()->unique()->values()->all();
            $personNames = $expenses->pluck('person_name')->filter()->unique()->values()->all();

            // Determine if this export is a global/all export (no marketing_person_code filter)
            $isGlobalExport = !$request->has('marketing_person_code') || empty($request->input('marketing_person_code'));

            $metadata = [
                'approved_section' => $approvedSection,
                'approved_total'   => $totals['approved'] ?? ($totals['approved'] ?? 0),
                'total_expenses'   => $totals['total_expenses'] ?? ($totals['total_expenses'] ?? 0),
                'approver_id'      => optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id,
                'created_at'       => now()->toDateTimeString(),
                'filters'          => request()->query(),
                'person_codes'     => $personCodes,
                'person_names'     => $personNames,
                'person_name'      => $first?->person_name ?? null,
                'person_code'      => $first?->marketing_person_code ?? null,
            ];

            // For non-global exports include explicit expense ids so personal pages can remove included items.
            // Also, if the export contains multiple different person names, treat it as a global export
            // and hide it from individual personal Checked In tables.
            $isMultiPerson = is_array($personNames) && count($personNames) > 1;
            if (!$isGlobalExport && !$isMultiPerson) {
                $metadata['expense_ids'] = $expenses->pluck('id')->map(fn($i) => (int)$i)->values()->all();
            } else {
                // Mark global/multi-person exports so they are not shown in personal Checked In tables
                $metadata['hide_from_personal'] = true;
            }

            Storage::disk('public')->put($storagePath . '.json', json_encode($metadata));

            // Persist cleared export into database and remove moved expense rows when possible.
            $expenseIds = $expenses->pluck('id')->filter()->unique()->values()->all();
            try {
                DB::transaction(function() use ($storagePath, $filename, $metadata, $expenseIds, $totals, $approvedSection) {
                    $record = ClearedExpense::create([
                        'filename' => $filename,
                        'path' => $storagePath,
                        'approver_id' => optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id,
                        'approved_total' => $totals['approved'] ?? 0,
                        'total_expenses' => $totals['total_expenses'] ?? 0,
                        'approved_section' => $approvedSection,
                        'person_name' => $metadata['person_name'] ?? null,
                        'person_code' => $metadata['person_code'] ?? null,
                        'meta' => $metadata,
                        'expense_ids' => !empty($expenseIds) ? $expenseIds : null,
                    ]);

                    // If explicit expense IDs exist, remove those rows from marketing_expenses table
                    if (!empty($expenseIds)) {
                        MarketingExpense::whereIn('id', $expenseIds)->delete();
                    }
                });
            } catch (\Throwable $e) {
                // ignore DB failures and fall back to existing behavior
            }

            // If the request expects JSON (AJAX), return a JSON success response so the
            // frontend can remove cleared rows from the Approved table without a full refresh.
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF saved to Cleared Expenses.',
                    'cleared_ids' => $expenseIds ?? [],
                ]);
            }

        } catch (\Throwable $e) {
            // if saving fails, continue to return the PDF directly
            return response($mpdf->Output($filename, 'S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        // Return the saved PDF as download
        $download = Storage::disk('public')->get($storagePath);
        return response($download, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show list of cleared (In Account) PDFs saved when approver generated them.
     */
    public function clearedExpenses(Request $request)
    {
        // Prefer DB-backed cleared_expenses records; fallback to storage files if DB table doesn't exist
        try {
            $records = ClearedExpense::orderByDesc('created_at')->get();
        } catch (\Throwable $_) {
            $records = collect();
        }

        $items = $records->map(function($rec){
            $path = $rec->path;
            $baseUrl = rtrim(request()->getBaseUrl(), '/');
            $host = request()->getSchemeAndHttpHost();
            $relative = $baseUrl ? $baseUrl . '/storage/' . $path : '/storage/' . $path;
            $fullUrl = $host . $relative;
            return [
                'id' => $rec->id,
                'path' => $path,
                'url' => $fullUrl,
                'filename' => $rec->filename,
                'meta' => $rec->meta ?? [],
                'approver_id' => $rec->approver_id,
                'approved_total' => (float) ($rec->approved_total ?? 0),
                'approved_section' => $rec->approved_section,
                'created_at' => $rec->created_at?->toDateTimeString() ?? ($rec->meta['created_at'] ?? null),
                'person_name' => $rec->person_name,
                'person_code' => $rec->person_code,
            ];
        })->values();

        // Resolve approver names and compute display name
        $items = $items->map(function($it){
            $approverName = $it['meta']['approver_name'] ?? null;
            if (empty($approverName) && !empty($it['approver_id'])){
                $approver = \App\Models\Admin::find($it['approver_id']) ?? \App\Models\User::find($it['approver_id']);
                $approverName = $approver?->name ?? null;
            }
            $meta = $it['meta'] ?? [];
            $personNames = $meta['person_names'] ?? [];
            $displayName = null;
            if (!empty($meta['hide_from_personal']) || (is_array($personNames) && count($personNames) > 1) || stripos($it['filename'] ?? '', 'Global Expense') !== false) {
                $displayName = 'Global Expense';
            } else {
                $displayName = $meta['person_name'] ?? $it['person_name'] ?? (is_array($personNames) && !empty($personNames[0]) ? $personNames[0] : null);
            }

            return array_merge($it, [
                'approver_name' => $approverName,
                'approved_total' => $it['approved_total'] ?? ($meta['approved_total'] ?? ($meta['total_expenses'] ?? 0)),
                'approved_section' => $it['approved_section'] ?? ($meta['approved_section'] ?? null),
                'created_at' => $it['created_at'] ?? ($meta['created_at'] ?? null),
                'display_name' => $displayName,
            ]);
        });

        // Apply filters similar to previous implementation
        $filterPerson = $request->input('marketing_person_code');
        $filterMonth = $request->input('month');
        $filterYear  = $request->input('year');

        $filtered = $items->filter(function($it) use ($filterPerson, $filterMonth, $filterYear){
            $meta = $it['meta'] ?? [];
            $metaFilters = $meta['filters'] ?? [];

            if ($filterPerson) {
                $mp = $metaFilters['marketing_person_code'] ?? null;
                $personCode = $it['person_code'] ?? ($meta['person_code'] ?? null);
                $personName = $it['person_name'] ?? ($meta['person_name'] ?? null);
                $matched = false;
                foreach ([$mp, $personCode, $personName] as $candidate) {
                    if (empty($candidate)) continue;
                    if ((string)$candidate === (string)$filterPerson) { $matched = true; break; }
                    if (stripos((string)$candidate, (string)$filterPerson) !== false) { $matched = true; break; }
                }
                if (!$matched) return false;
            }

            $metaMonth = $metaFilters['month'] ?? null;
            $metaYear  = $metaFilters['year'] ?? null;
            $createdAt = $meta['created_at'] ?? $it['created_at'] ?? null;

            if ($filterMonth) {
                if ($metaMonth !== null) {
                    if ((int)$metaMonth !== (int)$filterMonth) return false;
                } elseif ($createdAt) {
                    try { $dt = \Carbon\Carbon::parse($createdAt); if ($dt->month !== (int)$filterMonth) return false; } catch (\Throwable $e) { return false; }
                } else { return false; }
            }
            if ($filterYear) {
                if ($metaYear !== null) {
                    if ((int)$metaYear !== (int)$filterYear) return false;
                } elseif ($createdAt) {
                    try { $dt = \Carbon\Carbon::parse($createdAt); if ($dt->year !== (int)$filterYear) return false; } catch (\Throwable $e) { return false; }
                } else { return false; }
            }
            return true;
        })->values();

        $filtered = $filtered->sortByDesc(function($it){
            $dt = $it['created_at'] ?? ($it['meta']['created_at'] ?? null);
            try { return $dt ? \Carbon\Carbon::parse($dt)->timestamp : 0; } catch (\Throwable $e) { return 0; }
        })->values();

        $clearedPerPage = (int) ($request->input('cleared_per_page') ?? 15);
        $clearedPage = (int) ($request->input('page') ?? 1);
        $clearedPaginator = new LengthAwarePaginator(
            $filtered->forPage($clearedPage, $clearedPerPage),
            $filtered->count(),
            $clearedPerPage,
            $clearedPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $persons = User::orderBy('name')->get(['name','user_code']);

        return view('superadmin.accounts.cleared_expenses', [
            'items' => $clearedPaginator,
            'persons' => $persons,
            'selected_person' => $filterPerson,
            'selected_month' => $filterMonth,
            'selected_year' => $filterYear,
            'selected_cleared_per_page' => $clearedPerPage,
        ]);
    }

    public function updatePersonal(Request $request, MarketingExpense $expense)
    {
        if($expense->section !== 'personal'){
            abort(403, 'Only personal expenses can be updated through this endpoint.');
        }

        // Allow updates for pending and rejected expenses; disallow only approved ones
        if(!in_array($expense->status, ['pending', 'rejected'], true)){
            return response()->json([
                'success' => false,
                'message' => 'Approved expenses cannot be modified.',
            ], 422);
        }

        $data = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'from_date'   => 'required|date',
            'to_date'     => 'required|date|after_or_equal:from_date',
            'description' => 'nullable|string|max:2000',
            'pdf'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $previousAmount   = (float) $expense->amount;
        $previousApproved = (float) $expense->approved_amount;
        $wasRejected = ($expense->status === 'rejected');
        $previousDue      = max(0, $previousAmount - $previousApproved);

        $updatePayload = [
            'amount'      => $data['amount'],
            'from_date'   => $data['from_date'],
            'to_date'     => $data['to_date'],
            'description' => $data['description'] ?? null,
        ];

        if($request->hasFile('pdf')){
            if($expense->file_path){
                Storage::disk('public')->delete($expense->file_path);
            }
            $updatePayload['file_path'] = $request->file('pdf')->store('marketing_expenses', 'public');
        }

        $expense->update($updatePayload);

        // If this expense was previously rejected and is now edited, treat it as a new upload:
        // - reset approval fields, mark as pending, and resubmit for approval
        if ($wasRejected) {
            $expense->update([
                'status' => 'pending',
                'approved_amount' => 0,
                'approved_by' => null,
                'approved_at' => null,
                'approval_note' => null,
                'created_at' => now()->toDateTimeString(),
            ]);
        }

        if((float) $expense->approved_amount > (float) $expense->amount){
            $expense->update(['approved_amount' => $expense->amount]);
        }

        $expense->refresh()->load(['marketingPerson','approver']);

        // Render the updated expense row directly — do not consolidate into a summary.
        $rowHtml = view('superadmin.marketing.expenses._row', [
            'expense' => $expense,
            'isApprovalPage' => false,
            'showPerson' => false,
        ])->render();

        $dailyRowHtml = view('superadmin.personal.expenses._daily_row', ['expense' => $expense])->render();

        $currentAmount   = (float) $expense->amount;
        $currentApproved = (float) $expense->approved_amount;
        $currentDue      = max(0, $currentAmount - $currentApproved);

        // Refresh or rebuild any personal summary that this expense belongs to
        $summaryExpense = null;
        try {
            if (method_exists($this, 'refreshPersonalSummaryForExpense')) {
                $summaryExpense = $this->refreshPersonalSummaryForExpense($expense);
            }
        } catch (\Throwable $e) {
            $summaryExpense = null;
        }

        $summaryExpenseId = $summaryExpense?->id;
        $summaryGroupIds = $summaryExpense ? (array) ($summaryExpense->aggregate_ids ?? []) : [];

        return response()->json([
            'success'           => true,
            'rowHtml'           => $rowHtml,
            'dailyRowHtml'      => $dailyRowHtml,
            'amount'            => $currentAmount,
            'approved_amount'   => $currentApproved,
            'due_amount'        => $currentDue,
            'previous_amount'   => $previousAmount,
            'previous_approved' => $previousApproved,
            'previous_due'      => $previousDue,
            'submitted_for_approval' => (bool) $expense->submitted_for_approval,
            'status'            => $expense->status,
            'summary_expense_id' => $summaryExpenseId,
            'summary_group_ids'  => $summaryGroupIds,
        ]);
    }

    public function destroyPersonal(Request $request, MarketingExpense $expense)
    {
        if($expense->section !== 'personal'){
            abort(403, 'Only personal expenses can be deleted through this endpoint.');
        }

        // Allow deletion for pending and rejected expenses; disallow only approved ones
        if(!in_array($expense->status, ['pending', 'rejected'], true)){
            return response()->json([
                'success' => false,
                'message' => 'Approved expenses cannot be deleted.',
            ], 422);
        }

        $amount   = (float) $expense->amount;
        $approved = (float) $expense->approved_amount;
        $due      = max(0, $amount - $approved);
        // previously tracked submitted_for_approval; removed in schema

        if($expense->file_path){
            Storage::disk('public')->delete($expense->file_path);
        }

        $expense->delete();

        return response()->json([
            'success'          => true,
            'amount'           => $amount,
            'approved_amount'  => $approved,
            'due_amount'       => $due,
            'status'           => $expense->status,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office','personal'])){ $section = 'marketing'; }

        $query = $this->buildExportQuery($request, $section);
        $filename = sprintf('%s-expenses-%s.xlsx', $section, now()->format('Ymd_His'));

        return Excel::download(new MarketingExpensesExport($query->get()), $filename);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marketing_person_code' => 'nullable|string',
            'marketing_person_name' => 'nullable|string|max:255',
            'amount'                => 'required|numeric|min:0',
            'from_date'             => 'required|date',
            'to_date'               => 'required|date|after_or_equal:from_date',
            'pdf'                   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'description'           => 'nullable|string|max:2000',
        ]);

        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office','personal'])){ $section = 'marketing'; }

        $personCode = $data['marketing_person_code'] ?? null;
        $personName = trim($data['marketing_person_name'] ?? '');
        if(Str::endsWith($personName, ')') && Str::contains($personName, '(')){
            $personName = trim(Str::beforeLast($personName, '('));
        }

        $resolvedCode = $this->resolvePersonCode($personName);
        if(!$personCode && $resolvedCode){
            $personCode = $resolvedCode;
        }

        $userExists = null;
        if($personCode){
            $userExists = User::where('user_code', $personCode)->first();
        }
        if(!$personCode || !$userExists){
            $resolved = $this->resolvePersonCode($personName);
            if($resolved){
                $personCode = $resolved;
                $userExists = User::where('user_code', $resolved)->first();
            }
        }

        if(!$personCode){
            if(in_array($section, ['office','personal'])){
                $authUser = auth('admin')->user() ?: auth('web')->user();
                $guard = auth('admin')->check() ? 'admin' : 'user';
                $identifier = $authUser?->id ?: Str::random(6);
                $personCode = sprintf('%s:%s', $guard, $identifier);
                if(!$personName){
                    $personName = $authUser?->name ?: 'Office Admin';
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to identify the selected person. Please choose from the suggestions.'
                ], 422);
            }
        }

        if($userExists){
            $personName = $userExists->name;
        }

        if(!$personName){
            $personName = $userExists?->name ?? $personCode;
        }

        $path = null;
        if ($request->hasFile('pdf')) {
            $path = $request->file('pdf')->store('marketing_expenses', 'public');
        }

        $expense = MarketingExpense::create([
            'marketing_person_code' => $personCode,
            'person_name'           => $personName,
            'section'               => $section,
            'amount'                => $data['amount'],
            'from_date'             => $data['from_date'],
            'to_date'               => $data['to_date'],
            'file_path'             => $path,
            'description'           => $data['description'] ?? null,
            'status'                => 'pending',
            // Note: Previously this used 'submitted_for_approval' boolean.
            // We no longer store that flag; personal uploads remain 'pending' and
            // will appear in approvals as pending personal items.
        ]);

        $expense->load('marketingPerson');
        $rowHtml = null;
        $dailyRowHtml = null;

            if($section === 'personal'){
            $dailyRowHtml = view('superadmin.personal.expenses._daily_row', ['expense' => $expense])->render();
            // For personal uploads, also render the approval-row so it appears in Approvals
            $rowHtml = view('superadmin.marketing.expenses._row', [
                'expense' => $expense,
                'isApprovalPage' => false,
                'showPerson' => false,
            ])->render();
        } else {
            $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense])->render();
        }

        return response()->json([
            'success' => true,
            'dailyRowHtml' => $dailyRowHtml,
            'rowHtml' => $rowHtml,
            'amount'  => (float) $expense->amount,
            'approved_amount' => 0.0,
            'due_amount' => (float) $expense->amount,
            'status' => $expense->status,
        ]);
    }

    public function sendPersonalForApproval(Request $request)
    {
        $data = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer|min:2000|max:'.(int) now()->addYear()->format('Y'),
        ]);

        $month = $data['month'] ?? (int) now()->format('n');
        $year  = $data['year'] ?? (int) now()->format('Y');

        $period = Carbon::create($year, $month, 1);

        $pendingExpenses = MarketingExpense::with(['marketingPerson','approver'])
            ->where('section', 'personal')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        if($pendingExpenses->isEmpty()){
            return response()->json([
                'success' => false,
                'message' => 'No pending personal expenses found for the selected month.',
            ], 422);
        }

        $pendingIds = $pendingExpenses->pluck('id');
        $existingSummaryPaths = $pendingExpenses->pluck('approval_summary_path')->filter()->unique();
        $summaryFilename = sprintf('personal-expenses-%s-%s.pdf', $period->format('Y_m'), Str::lower(Str::random(6)));
        $summaryPath = 'marketing_expenses/'.$summaryFilename;

        MarketingExpense::whereIn('id', $pendingIds->all())->update([
            'approval_note' => 'Submitted for approval - '.$period->format('F Y'),
            'approved_by'   => null,
            'approved_at'   => null,
            'approval_summary_path'  => $summaryPath,
        ]);

        $refreshedPending = MarketingExpense::with(['marketingPerson','approver'])
            ->whereIn('id', $pendingIds->all())
            ->orderBy('created_at')
            ->get();

        $pdfOutput = $this->generatePersonalSummaryDocument($refreshedPending, $period, $summaryPath);

        foreach($existingSummaryPaths as $oldPath){
            if(!$oldPath || $oldPath === $summaryPath){
                continue;
            }

            $stillInUse = MarketingExpense::where('approval_summary_path', $oldPath)
                ->whereNotIn('id', $pendingIds->all())
                ->exists();

            if(!$stillInUse){
                Storage::disk('public')->delete($oldPath);
            }
        }

        $filename = sprintf('personal-expenses-%s.pdf', $period->format('Y_m'));

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function approve(Request $request, MarketingExpense $expense)
    {
        $groupIds = collect($request->input('group_ids', []))
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique();

        if($groupIds->isNotEmpty()){
            if(!$groupIds->contains($expense->id)){
                $groupIds->push($expense->id);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson','approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            if($groupExpenses->isEmpty()){
                return response()->json([
                    'success' => false,
                    'message' => 'Expenses not found for approval.',
                ], 404);
            }

            $remainingDue = (float) $groupExpenses->sum(function($item){
                $approved = (float) $item->approved_amount;
                return max(0, (float) $item->amount - $approved);
            });

            if($remainingDue <= 0){
                return response()->json([
                    'success' => false,
                    'message' => 'No pending amount left to approve.',
                ], 422);
            }

            $data = $request->validate([
                'approved_amount' => 'required|numeric|min:0|max:'.$remainingDue,
                'approval_note'   => 'nullable|string|max:2000',
            ]);

            $approveAmount = (float) $data['approved_amount'];
            if($approveAmount <= 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Approving amount must be greater than zero.',
                ], 422);
            }

            $note = $data['approval_note'] ?? null;
            $approverId = optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id;

            $remaining = $approveAmount;
            foreach($groupExpenses as $item){
                $pending = max(0, (float)$item->amount - (float)$item->approved_amount);
                if($pending <= 0){
                    continue;
                }

                $apply = min($pending, $remaining);
                if($apply <= 0){
                    continue;
                }

                $item->approved_amount = min((float) $item->amount, (float) $item->approved_amount + $apply);
                $item->status = 'approved';
                $item->approval_note = $note;
                $item->approved_by = $approverId;
                $item->approved_at = now();
                $item->save();

                $remaining -= $apply;
                if($remaining <= 0){
                    break;
                }
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson','approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            $pendingGroup = $groupExpenses->where('status', 'pending')->values();
            if($pendingGroup->isNotEmpty()){
                $summary = $this->buildPersonalMonthlySummaries($pendingGroup)->first();
            } else {
                $summary = $this->buildPersonalMonthlySummaries($groupExpenses)->first();
            }

            if(!$summary){
                $summary = $groupExpenses->first();
            }

            if($summary && $summary->approval_summary_path){
                $summary->approval_summary_path = $groupExpenses->pluck('approval_summary_path')->filter()->first();
            }

            $rowHtml = view('superadmin.marketing.expenses._row', [
                'expense' => $summary,
                'isApprovalPage' => true,
            ])->render();

            $displayApproved = $summary ? (($summary->status === 'approved') ? (float) $summary->amount : (float) $summary->approved_amount) : 0.0;
            $displayDue = $summary ? max(0, (float) $summary->amount - $displayApproved) : 0.0;

            return response()->json([
                'success' => true,
                'rowHtml' => $rowHtml,
                'approved_amount' => $displayApproved,
                'due_amount' => $displayDue,
                'status' => $summary?->status,
            ]);
        }

        $maxApprovable = max(0, (float)$expense->amount - (float)$expense->approved_amount);
        if($maxApprovable <= 0){
            return response()->json([
                'success' => false,
                'message' => 'No pending amount left to approve.',
            ], 422);
        }

        $data = $request->validate([
            'approved_amount' => 'required|numeric|min:0|max:'.$maxApprovable,
            'approval_note'   => 'nullable|string|max:2000',
        ]);

        $newApproved = min((float) $expense->amount, (float) $data['approved_amount']);

        $expense->update([
            'approved_amount' => $newApproved,
            'approval_note'   => $data['approval_note'] ?? null,
            'status'          => 'approved',
            'approved_by'     => optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id,
            'approved_at'     => now(),
        ]);

        $expense->load(['marketingPerson','approver']);
        $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true])->render();

        return response()->json([
            'success' => true,
            'rowHtml' => $rowHtml,
            'approved_amount' => (float) $expense->amount,
            'due_amount' => 0.0,
            'status' => $expense->status,
        ]);
    }

    public function reject(Request $request, MarketingExpense $expense)
    {
        $groupIds = collect($request->input('group_ids', []))
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique();

        if($groupIds->isNotEmpty()){
            if(!$groupIds->contains($expense->id)){
                $groupIds->push($expense->id);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson','approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            if($groupExpenses->isEmpty()){
                return response()->json([
                    'success' => false,
                    'message' => 'Expenses not found for rejection.',
                ], 404);
            }

            $data = $request->validate([
                'approval_note' => 'nullable|string|max:2000',
            ]);

            $note = $data['approval_note'] ?? null;
            $approverId = optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id;

            foreach($groupExpenses as $item){
                $item->update([
                    'status'        => 'rejected',
                    'approval_note' => $note,
                    'approved_by'   => $approverId,
                    'approved_at'   => now(),
                ]);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson','approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            $summary = $this->buildPersonalMonthlySummaries($groupExpenses)->first();

            $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $summary, 'isApprovalPage' => true])->render();

            return response()->json([
                'success' => true,
                'rowHtml' => $rowHtml,
                'approved_amount' => (float) $summary->approved_amount,
                'due_amount' => max(0, (float)$summary->amount - (float)$summary->approved_amount),
            ]);
        }

        $data = $request->validate([
            'approval_note' => 'nullable|string|max:2000',
        ]);

        $expense->update([
            'status'        => 'rejected',
            'approval_note' => $data['approval_note'] ?? null,
            'approved_by'   => optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id,
            'approved_at'   => now(),
        ]);

        $expense->load(['marketingPerson','approver']);
        $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true])->render();

        return response()->json([
            'success' => true,
            'rowHtml' => $rowHtml,
            'approved_amount' => (float) $expense->approved_amount,
            'due_amount' => max(0, (float)$expense->amount - (float)$expense->approved_amount),
        ]);
    }

}
