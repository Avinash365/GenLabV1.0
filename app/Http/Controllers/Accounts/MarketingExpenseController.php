<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
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
    public function index(Request $request)
    {
        return $this->renderList($request, 'all');
    }

    public function approved(Request $request)
    {
        // Show pending expenses on the "Approve Expenses" page so they can be actioned
        return $this->renderList($request, 'pending', 'approve');
    }

    public function rejected(Request $request)
    {
        return $this->renderList($request, 'rejected');
    }

    protected function renderList(Request $request, string $status, string $view = 'index')
    {
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');
        $section = $request->input('section', 'marketing'); // marketing|office

        $query = MarketingExpense::with(['marketingPerson'])
            ->where('section', $section)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function($inner) use ($search){
                    $inner->whereHas('marketingPerson', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('user_code', 'like', "%{$search}%");
                    })
                    ->orWhere('person_name', 'like', "%{$search}%")
                    ->orWhere('marketing_person_code', 'like', "%{$search}%");
                });
            });

        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        $expenses = $query->latest()->paginate(10);

        $totals = [
            'total_expenses' => (float) $expenses->sum('amount'),
            'approved'       => (float) $expenses->sum('approved_amount'),
            'due'            => (float) $expenses->sum(function($e){ return max(0, (float)$e->amount - (float)$e->approved_amount); }),
        ];

        // Provide a small list of persons for initial dropdown population if needed
        $persons = User::whereHas('role', fn($q) => $q->where('slug', 'marketing_person'))
            ->select(['id','name','user_code'])
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Choose blade base by section for index view; approvals/rejected reuse marketing blades with section filter
        $base = ($view === 'index' && $section === 'office')
            ? 'superadmin.office.expenses'
            : 'superadmin.marketing.expenses';

        return view($base . '.' . $view, compact('expenses','totals','status','month','year','search','persons','section'));
    }

    public function office(Request $request)
    {
        // Dedicated Office Expenses view
        $request->merge(['section' => 'office']);
        return $this->renderList($request, 'all', 'index');
    }

    public function persons(Request $request)
    {
        $q = $request->get('q');
        $persons = User::whereHas('role', fn($r) => $r->where('slug','marketing_person'))
            ->when($q, fn($s) => $s->where('name', 'like', "%{$q}%")->orWhere('user_code','like', "%{$q}%"))
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
        if(!in_array($section, ['marketing','office'])){ $section = 'marketing'; }

        $expenses = $this->buildExportQuery($request, $section)->get();

        $pdf = Pdf::loadView('superadmin.marketing.expenses.export_pdf', [
            'expenses' => $expenses,
            'section'  => $section,
            'title'    => $section === 'office' ? 'Office Expenses' : 'Marketing Expenses',
        ])->setPaper('a4', 'landscape');

        $filename = sprintf('%s-expenses-%s.pdf', $section, now()->format('Ymd_His'));
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office'])){ $section = 'marketing'; }

        $query = $this->buildExportQuery($request, $section);
        $filename = sprintf('%s-expenses-%s.xlsx', $section, now()->format('Ymd_His'));

        return Excel::download(new MarketingExpensesExport($query->get()), $filename);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marketing_person_code' => 'nullable|string',
            'marketing_person_name' => 'required|string|max:255',
            'amount'                => 'required|numeric|min:0',
            'from_date'             => 'required|date',
            'to_date'               => 'required|date|after_or_equal:from_date',
            'pdf'                   => 'nullable|file|mimes:pdf|max:20480',
            'description'           => 'nullable|string|max:2000',
        ]);

        $section = $request->input('section', 'marketing');
        if(!in_array($section, ['marketing','office'])){ $section = 'marketing'; }

        $personCode = $data['marketing_person_code'] ?? null;
        $personName = trim($data['marketing_person_name']);
        if(Str::endsWith($personName, ')') && Str::contains($personName, '(')){
            $personName = trim(Str::beforeLast($personName, '('));
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
            if($section === 'office'){
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
        ]);

        $expense->load('marketingPerson');

        $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense])->render();

        return response()->json([
            'success' => true,
            'rowHtml' => $rowHtml,
            'amount'  => (float) $expense->amount,
            'approved_amount' => 0.0,
            'due_amount' => (float) $expense->amount,
        ]);
    }

    protected function buildExportQuery(Request $request, string $section)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        $month  = $request->input('month');
        $year   = $request->input('year');

        $query = MarketingExpense::with(['marketingPerson','approver'])
            ->where('section', $section)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function($inner) use ($search){
                    $inner->whereHas('marketingPerson', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('user_code', 'like', "%{$search}%");
                    })
                    ->orWhere('person_name', 'like', "%{$search}%")
                    ->orWhere('marketing_person_code', 'like', "%{$search}%");
                });
            })
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->latest();

        return $query;
    }

    public function approve(Request $request, MarketingExpense $expense)
    {
        $data = $request->validate([
            'approved_amount' => 'required|numeric|min:0|max:'.$expense->amount,
            'approval_note'   => 'nullable|string|max:2000',
        ]);

        $expense->update([
            'approved_amount' => $data['approved_amount'],
            'approval_note'   => $data['approval_note'] ?? null,
            'status'          => 'approved',
            'approved_by'     => optional(auth('admin')->user())->id ?? optional(auth('web')->user())->id,
            'approved_at'     => now(),
        ]);

    $expense->load(['marketingPerson','approver']);
    // Render row for approval list context so columns align without refresh
    $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true])->render();

        return response()->json([
            'success' => true,
            'rowHtml' => $rowHtml,
            'approved_amount' => (float) $expense->approved_amount,
            'due_amount' => max(0, (float)$expense->amount - (float)$expense->approved_amount),
        ]);
    }

    public function reject(Request $request, MarketingExpense $expense)
    {
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
    // Render row for approval list context so columns align without refresh
    $rowHtml = view('superadmin.marketing.expenses._row', ['expense' => $expense, 'isApprovalPage' => true])->render();

        return response()->json([
            'success' => true,
            'rowHtml' => $rowHtml,
            'approved_amount' => (float) $expense->approved_amount,
            'due_amount' => max(0, (float)$expense->amount - (float)$expense->approved_amount),
        ]);
    }

    protected function resolvePersonCode(string $input): ?string
    {
        $value = trim($input);
        if($value === ''){
            return null;
        }

        $candidates = [];
        if(preg_match('/\(([^)]+)\)$/', $value, $matches)){
            $candidates[] = trim($matches[1]);
        }
        $candidates[] = $value;
        $candidates[] = preg_replace('/\(([^)]+)\)$/', '', $value);

        foreach($candidates as $candidate){
            $candidate = trim((string) $candidate);
            if($candidate === ''){ continue; }
            $user = User::where('user_code', $candidate)->first();
            if($user){
                return $user->user_code;
            }
        }

        $lower = mb_strtolower($value);
        $user = User::whereRaw('LOWER(name) = ?', [$lower])->first();
        if($user){
            return $user->user_code;
        }

        $user = User::where('name', 'like', $value)->first();
        if($user){
            return $user->user_code;
        }

        return null;
    }
}
