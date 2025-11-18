<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketingExpenseResource;
use App\Models\MarketingExpense;
use App\Models\User;
use App\Traits\HandlesMarketingExpenses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExpenseApiController extends Controller
{
    use HandlesMarketingExpenses;

    public function index(Request $request)
    {
        $data = $request->validate([
            'section' => 'nullable|in:marketing,office,personal',
            'status' => 'nullable|in:all,pending,approved,rejected',
            'search' => 'nullable|string|max:255',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:' . (int) now()->addYear()->format('Y'),
            'per_page' => 'nullable|integer|min:1|max:100',
            'group_personal' => 'nullable|boolean',
        ]);

        $section = $data['section'] ?? 'marketing';
        $status = $data['status'] ?? 'all';
        $perPage = $data['per_page'] ?? 15;
        $groupPersonal = $section === 'personal' ? ($data['group_personal'] ?? true) : false;

        $filters = [
            'status' => $status,
            'search' => $data['search'] ?? null,
            'month' => $data['month'] ?? null,
            'year' => $data['year'] ?? null,
        ];

        $query = $this->buildExportQuery($filters, $section, $status);
        $listing = $this->buildListingFromQuery($query, $groupPersonal, $perPage);

        return MarketingExpenseResource::collection($listing['expenses'])
            ->additional([
                'totals' => $listing['totals'],
                'filters' => [
                    'section' => $section,
                    'status' => $status,
                    'search' => $filters['search'],
                    'month' => $filters['month'],
                    'year' => $filters['year'],
                    'group_personal' => $groupPersonal,
                ],
            ]);
    }

    public function show(MarketingExpense $expense)
    {
        $expense->load(['marketingPerson', 'approver']);

        return new MarketingExpenseResource($expense);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section' => 'nullable|in:marketing,office,personal',
            'marketing_person_code' => 'nullable|string',
            'marketing_person_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'pdf' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
            'description' => 'nullable|string|max:2000',
        ]);

        $section = $data['section'] ?? $request->input('section', 'marketing');
        if (!in_array($section, ['marketing', 'office', 'personal'], true)) {
            $section = 'marketing';
        }

        $personCode = $data['marketing_person_code'] ?? null;
        $personName = trim($data['marketing_person_name'] ?? '');
        if (Str::endsWith($personName, ')') && Str::contains($personName, '(')) {
            $personName = trim(Str::beforeLast($personName, '('));
        }

        $resolvedCode = $this->resolvePersonCode($personName);
        if (!$personCode && $resolvedCode) {
            $personCode = $resolvedCode;
        }

        $userExists = null;
        if ($personCode) {
            $userExists = User::where('user_code', $personCode)->first();
        }
        if (!$personCode || !$userExists) {
            $resolved = $this->resolvePersonCode($personName);
            if ($resolved) {
                $personCode = $resolved;
                $userExists = User::where('user_code', $resolved)->first();
            }
        }

        if (!$personCode) {
            if (in_array($section, ['office', 'personal'], true)) {
                [$authUser, $guard] = $this->resolveAuthenticatedUser();
                $identifier = $authUser?->id ?? Str::random(6);
                $personCode = sprintf('%s:%s', $guard, $identifier);
                if (!$personName) {
                    $personName = $authUser?->name ?? ($section === 'personal' ? 'Personal User' : 'Office Admin');
                }
            } else {
                throw ValidationException::withMessages([
                    'marketing_person_code' => ['Unable to identify the selected person. Please choose from the suggestions.'],
                ]);
            }
        }

        if ($userExists) {
            $personName = $userExists->name;
        }

        if (!$personName) {
            $personName = $userExists?->name ?? $personCode;
        }

        $path = null;
        if ($request->hasFile('pdf')) {
            $path = $request->file('pdf')->store('marketing_expenses', 'public');
        }

        $expense = MarketingExpense::create([
            'marketing_person_code' => $personCode,
            'person_name' => $personName,
            'section' => $section,
            'amount' => $data['amount'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'file_path' => $path,
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'submitted_for_approval' => false,
        ]);

        $expense->load(['marketingPerson', 'approver']);

        return (new MarketingExpenseResource($expense))
            ->additional([
                'totals' => [
                    'amount' => (float) $expense->amount,
                    'approved_amount' => (float) $expense->approved_amount,
                    'due_amount' => max(0, (float) $expense->amount - (float) $expense->approved_amount),
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, MarketingExpense $expense)
    {
        if ($expense->section !== 'personal') {
            abort(403, 'Only personal expenses can be updated through this endpoint.');
        }

        if ($expense->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Approved or rejected expenses cannot be modified.'],
            ]);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'description' => 'nullable|string|max:2000',
            'pdf' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        if ($request->hasFile('pdf')) {
            if ($expense->file_path) {
                Storage::disk('public')->delete($expense->file_path);
            }
            $expense->file_path = $request->file('pdf')->store('marketing_expenses', 'public');
        }

        $expense->amount = $data['amount'];
        $expense->from_date = $data['from_date'];
        $expense->to_date = $data['to_date'];
        $expense->description = $data['description'] ?? null;
        $expense->save();

        if ((float) $expense->approved_amount > (float) $expense->amount) {
            $expense->update(['approved_amount' => $expense->amount]);
        }

        $expense->refresh()->load(['marketingPerson', 'approver']);

        $summaryExpense = null;
        if ($expense->submitted_for_approval) {
            $summaryExpense = $this->refreshPersonalSummaryForExpense($expense);
            $expense->refresh()->load(['marketingPerson', 'approver']);
        }

        $summaryPayload = $summaryExpense ? (new MarketingExpenseResource($summaryExpense))->toArray($request) : null;

        return (new MarketingExpenseResource($expense))
            ->additional([
                'summary' => $summaryPayload,
                'totals' => [
                    'amount' => (float) $expense->amount,
                    'approved_amount' => (float) $expense->approved_amount,
                    'due_amount' => max(0, (float) $expense->amount - (float) $expense->approved_amount),
                ],
            ]);
    }

    public function destroy(MarketingExpense $expense)
    {
        if ($expense->section !== 'personal') {
            abort(403, 'Only personal expenses can be deleted through this endpoint.');
        }

        if ($expense->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Approved or rejected expenses cannot be deleted.'],
            ]);
        }

        $amount = (float) $expense->amount;
        $approved = (float) $expense->approved_amount;
        $due = max(0, $amount - $approved);
        $wasSubmitted = (bool) $expense->submitted_for_approval;

        if ($expense->file_path) {
            Storage::disk('public')->delete($expense->file_path);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'totals' => [
                'amount' => $amount,
                'approved_amount' => $approved,
                'due_amount' => $due,
            ],
            'submitted_for_approval' => $wasSubmitted,
        ]);
    }

    public function sendPersonalForApproval(Request $request)
    {
        $data = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:' . (int) now()->addYear()->format('Y'),
        ]);

        $month = $data['month'] ?? (int) now()->format('n');
        $year = $data['year'] ?? (int) now()->format('Y');

        $period = Carbon::create($year, $month, 1);

        $pendingExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
            ->where('section', 'personal')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        if ($pendingExpenses->isEmpty()) {
            throw ValidationException::withMessages([
                'month' => ['No pending personal expenses found for the selected month.'],
            ]);
        }

        $pendingIds = $pendingExpenses->pluck('id');
        $existingSummaryPaths = $pendingExpenses->pluck('approval_summary_path')->filter()->unique();
        $summaryFilename = sprintf('personal-expenses-%s-%s.pdf', $period->format('Y_m'), Str::lower(Str::random(6)));
        $summaryPath = 'marketing_expenses/' . $summaryFilename;

        MarketingExpense::whereIn('id', $pendingIds->all())->update([
            'approval_note' => 'Submitted for approval - ' . $period->format('F Y'),
            'approved_by' => null,
            'approved_at' => null,
            'submitted_for_approval' => true,
            'approval_summary_path' => $summaryPath,
        ]);

        $refreshedPending = MarketingExpense::with(['marketingPerson', 'approver'])
            ->whereIn('id', $pendingIds->all())
            ->orderBy('created_at')
            ->get();

        $this->generatePersonalSummaryDocument($refreshedPending, $period, $summaryPath);

        foreach ($existingSummaryPaths as $oldPath) {
            if (!$oldPath || $oldPath === $summaryPath) {
                continue;
            }

            $stillInUse = MarketingExpense::where('approval_summary_path', $oldPath)
                ->whereNotIn('id', $pendingIds->all())
                ->exists();

            if (!$stillInUse) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $summary = $this->buildPersonalMonthlySummaries($refreshedPending)->first();
        $summaryPayload = $summary ? (new MarketingExpenseResource($summary))->toArray($request) : null;

        return response()->json([
            'success' => true,
            'summary_path' => $summaryPath,
            'download_url' => $this->publicUrl($summaryPath),
            'summary' => $summaryPayload,
            'pending_count' => $refreshedPending->count(),
        ]);
    }

    public function approve(Request $request, MarketingExpense $expense)
    {
        $groupIds = collect($request->input('group_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($groupIds->isNotEmpty()) {
            if (!$groupIds->contains($expense->id)) {
                $groupIds->push($expense->id);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            if ($groupExpenses->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expenses not found for approval.',
                ], 404);
            }

            $remainingDue = (float) $groupExpenses->sum(function ($item) {
                $approved = (float) $item->approved_amount;
                return max(0, (float) $item->amount - $approved);
            });

            if ($remainingDue <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending amount left to approve.',
                ], 422);
            }

            $data = $request->validate([
                'approved_amount' => 'required|numeric|min:0|max:' . $remainingDue,
                'approval_note' => 'nullable|string|max:2000',
            ]);

            $approveAmount = (float) $data['approved_amount'];
            if ($approveAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approving amount must be greater than zero.',
                ], 422);
            }

            $note = $data['approval_note'] ?? null;
            $approverId = $this->resolveApproverId();

            $remaining = $approveAmount;
            foreach ($groupExpenses as $item) {
                $pending = max(0, (float) $item->amount - (float) $item->approved_amount);
                if ($pending <= 0) {
                    continue;
                }

                $apply = min($pending, $remaining);
                if ($apply <= 0) {
                    continue;
                }

                $item->approved_amount = min((float) $item->amount, (float) $item->approved_amount + $apply);
                $item->status = 'approved';
                $item->approval_note = $note;
                $item->approved_by = $approverId;
                $item->approved_at = now();
                $item->save();

                $remaining -= $apply;
                if ($remaining <= 0) {
                    break;
                }
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            $pendingGroup = $groupExpenses->where('status', 'pending')->values();
            if ($pendingGroup->isNotEmpty()) {
                $summary = $this->buildPersonalMonthlySummaries($pendingGroup)->first();
            } else {
                $summary = $this->buildPersonalMonthlySummaries($groupExpenses)->first();
            }

            if (!$summary) {
                $summary = $groupExpenses->first();
            }

            $summaryPayload = $summary ? (new MarketingExpenseResource($summary))->toArray($request) : null;
            $displayApproved = $summary ? (($summary->status === 'approved') ? (float) $summary->amount : (float) $summary->approved_amount) : 0.0;
            $displayDue = $summary ? max(0, (float) $summary->amount - $displayApproved) : 0.0;

            return response()->json([
                'success' => true,
                'summary' => $summaryPayload,
                'totals' => [
                    'approved_amount' => $displayApproved,
                    'due_amount' => $displayDue,
                ],
                'group_ids' => $groupIds->all(),
            ]);
        }

        $maxApprovable = max(0, (float) $expense->amount - (float) $expense->approved_amount);
        if ($maxApprovable <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No pending amount left to approve.',
            ], 422);
        }

        $data = $request->validate([
            'approved_amount' => 'required|numeric|min:0|max:' . $maxApprovable,
            'approval_note' => 'nullable|string|max:2000',
        ]);

        $newApproved = min((float) $expense->amount, (float) $data['approved_amount']);

        $expense->update([
            'approved_amount' => $newApproved,
            'approval_note' => $data['approval_note'] ?? null,
            'status' => 'approved',
            'approved_by' => $this->resolveApproverId(),
            'approved_at' => now(),
        ]);

        $expense->load(['marketingPerson', 'approver']);

        return (new MarketingExpenseResource($expense))
            ->additional([
                'totals' => [
                    'approved_amount' => (float) $expense->amount,
                    'due_amount' => 0.0,
                ],
            ]);
    }

    public function reject(Request $request, MarketingExpense $expense)
    {
        $groupIds = collect($request->input('group_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique();

        if ($groupIds->isNotEmpty()) {
            if (!$groupIds->contains($expense->id)) {
                $groupIds->push($expense->id);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            if ($groupExpenses->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expenses not found for rejection.',
                ], 404);
            }

            $data = $request->validate([
                'approval_note' => 'nullable|string|max:2000',
            ]);

            $note = $data['approval_note'] ?? null;
            $approverId = $this->resolveApproverId();

            foreach ($groupExpenses as $item) {
                $item->update([
                    'status' => 'rejected',
                    'approval_note' => $note,
                    'approved_by' => $approverId,
                    'approved_at' => now(),
                ]);
            }

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->whereIn('id', $groupIds->all())
                ->orderBy('created_at')
                ->get();

            $summary = $this->buildPersonalMonthlySummaries($groupExpenses)->first();
            $summaryPayload = $summary ? (new MarketingExpenseResource($summary))->toArray($request) : null;
            $summaryAmount = (float) ($summary?->amount ?? 0);
            $summaryApproved = (float) ($summary?->approved_amount ?? 0);

            return response()->json([
                'success' => true,
                'summary' => $summaryPayload,
                'totals' => [
                    'approved_amount' => $summaryApproved,
                    'due_amount' => max(0, $summaryAmount - $summaryApproved),
                ],
                'group_ids' => $groupIds->all(),
            ]);
        }

        $data = $request->validate([
            'approval_note' => 'nullable|string|max:2000',
        ]);

        $expense->update([
            'status' => 'rejected',
            'approval_note' => $data['approval_note'] ?? null,
            'approved_by' => $this->resolveApproverId(),
            'approved_at' => now(),
        ]);

        $expense->load(['marketingPerson', 'approver']);

        return (new MarketingExpenseResource($expense))
            ->additional([
                'totals' => [
                    'approved_amount' => (float) $expense->approved_amount,
                    'due_amount' => max(0, (float) $expense->amount - (float) $expense->approved_amount),
                ],
            ]);
    }

    protected function resolveAuthenticatedUser(): array
    {
        $admin = auth('api_admin')->user() ?? auth('admin')->user();
        if ($admin) {
            return [$admin, 'admin'];
        }

        $user = auth('api')->user() ?? auth('web')->user();
        if ($user) {
            return [$user, 'user'];
        }

        return [null, 'user'];
    }

    protected function resolveApproverId(): ?int
    {
        return optional(auth('api_admin')->user())->id
            ?? optional(auth('admin')->user())->id
            ?? optional(auth('api')->user())->id
            ?? optional(auth('web')->user())->id;
    }

    protected function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
