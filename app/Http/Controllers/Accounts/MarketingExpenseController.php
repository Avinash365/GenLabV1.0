<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Traits\HandlesMarketingExpenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\{User, MarketingExpense};
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MarketingExpensesExport;

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
            $marketingExpenses = $this->buildExportQuery($request, 'marketing', 'pending')->get();

            $personalQuery = $this->buildExportQuery($request, 'personal', 'pending')
                ->where('submitted_for_approval', true);

            $personalPending = $personalQuery->get();
            $personalSummaries = $this->buildPersonalMonthlySummaries($personalPending);

            $combined = $marketingExpenses
                ->concat($personalSummaries)
                ->sortByDesc(function($expense){
                    $created = $expense->created_at ?? null;
                    if($created instanceof Carbon){
                        return $created->timestamp;
                    }
                    if($created){
                        return Carbon::parse($created)->timestamp;
                    }
                    return 0;
                })
                ->values();

            $paginator = $this->paginateCollection($combined);
            $totals = $this->calculateTotals($marketingExpenses->concat($personalPending));

            return view('superadmin.marketing.expenses.approve', [
                'expenses' => $paginator,
                'totals'   => $totals,
                'status'   => 'pending',
                'section'  => $section,
            ]);
        }

        $query = $this->buildExportQuery($request, $section, 'pending');

        if($section === 'personal'){
            $query->where('submitted_for_approval', true);
        }

        $listing = $this->buildListingFromQuery($query, $section === 'personal');

        return view('superadmin.marketing.expenses.approve', [
            'expenses' => $listing['expenses'],
            'totals'   => $listing['totals'],
            'status'   => 'pending',
            'section'  => $section,
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

        $listing = $this->buildListingFromQuery(
            $this->buildExportQuery($request, 'personal', $status),
            true
        );

        $monthFilter = $request->input('month');
        $yearFilter  = $request->input('year');
        $today = now();
        $targetMonth = $monthFilter ? (int) $monthFilter : (int) $today->format('n');
        $targetYear  = $yearFilter ? (int) $yearFilter : (int) $today->format('Y');

        $dailyExpenses = MarketingExpense::where('section', 'personal')
            ->where('status', 'pending')
            ->whereYear('created_at', $targetYear)
            ->whereMonth('created_at', $targetMonth)
            ->orderByDesc('created_at')
            ->get();

        if($search = $request->input('search')){
            $dailyExpenses = $dailyExpenses->filter(function($expense) use ($search){
                $term = mb_strtolower($search);
                $description = mb_strtolower((string) $expense->description);
                $person = mb_strtolower((string) $expense->person_name);
                return str_contains($description, $term) || str_contains($person, $term);
            })->values();
        }

        return view('superadmin.personal.expenses.index', [
            'expenses'       => $listing['expenses'],
            'totals'         => $listing['totals'],
            'status'         => $status,
            'section'        => 'personal',
            'dailyExpenses'  => $dailyExpenses,
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

    public function updatePersonal(Request $request, MarketingExpense $expense)
    {
        if($expense->section !== 'personal'){
            abort(403, 'Only personal expenses can be updated through this endpoint.');
        }

        if($expense->status !== 'pending'){
            return response()->json([
                'success' => false,
                'message' => 'Approved or rejected expenses cannot be modified.',
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

        if((float) $expense->approved_amount > (float) $expense->amount){
            $expense->update(['approved_amount' => $expense->amount]);
        }

        $expense->refresh()->load(['marketingPerson','approver']);

        $summaryExpense = null;
        if($expense->submitted_for_approval){
            $summaryExpense = $this->refreshPersonalSummaryForExpense($expense);
            $expense->refresh()->load(['marketingPerson','approver']);
        }

        $rowHtml = null;
        if($summaryExpense){
            $rowHtml = view('superadmin.marketing.expenses._row', [
                'expense' => $summaryExpense,
                'isApprovalPage' => false,
                'showPerson' => false,
            ])->render();
        } else {
            $rowHtml = view('superadmin.marketing.expenses._row', [
                'expense' => $expense,
                'isApprovalPage' => false,
                'showPerson' => false,
            ])->render();
        }

        $dailyRowHtml = view('superadmin.personal.expenses._daily_row', ['expense' => $expense])->render();

        $currentAmount   = (float) $expense->amount;
        $currentApproved = (float) $expense->approved_amount;
        $currentDue      = max(0, $currentAmount - $currentApproved);

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

        if($expense->status !== 'pending'){
            return response()->json([
                'success' => false,
                'message' => 'Approved or rejected expenses cannot be deleted.',
            ], 422);
        }

        $amount   = (float) $expense->amount;
        $approved = (float) $expense->approved_amount;
        $due      = max(0, $amount - $approved);
        $wasSubmitted = (bool) $expense->submitted_for_approval;

        if($expense->file_path){
            Storage::disk('public')->delete($expense->file_path);
        }

        $expense->delete();

        return response()->json([
            'success'          => true,
            'amount'           => $amount,
            'approved_amount'  => $approved,
            'due_amount'       => $due,
            'submitted_for_approval' => $wasSubmitted,
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
            'submitted_for_approval'=> false,
        ]);

        $expense->load('marketingPerson');
        $rowHtml = null;
        $dailyRowHtml = null;

        if($section === 'personal'){
            $dailyRowHtml = view('superadmin.personal.expenses._daily_row', ['expense' => $expense])->render();

            if($expense->submitted_for_approval){
                $rowHtml = view('superadmin.marketing.expenses._row', [
                    'expense' => $expense,
                    'isApprovalPage' => false,
                    'showPerson' => false,
                ])->render();
            }
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
            'submitted_for_approval' => (bool) $expense->submitted_for_approval,
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
            'submitted_for_approval' => true,
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
