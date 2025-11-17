<?php

namespace App\Traits;

use App\Models\MarketingExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

trait HandlesMarketingExpenses
{
    protected function buildListingFromQuery(Builder $query, bool $groupPersonal = false, int $perPage = 15): array
    {
        if ($groupPersonal) {
            $collection = $query->get();
            $submitted = $collection->filter(fn ($expense) => (bool) $expense->submitted_for_approval)->values();
            $summaries = $this->buildPersonalMonthlySummaries($submitted);
            $paginator = $this->paginateCollection($summaries, $perPage);
            $paginator->appends(request()->query());

            return [
                'expenses' => $paginator,
                'totals'   => $this->calculateTotals($submitted),
                'raw'      => $collection,
            ];
        }

        $forTotals = (clone $query)->get();
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'expenses' => $paginator,
            'totals'   => $this->calculateTotals($forTotals),
            'raw'      => $forTotals,
        ];
    }

    protected function calculateTotals(Collection $expenses): array
    {
        $total = 0.0;
        $approved = 0.0;

        foreach ($expenses as $expense) {
            $amount = (float) ($expense->amount ?? 0);
            $total += $amount;

            $approvedAmount = (float) ($expense->approved_amount ?? 0);
            $status = data_get($expense, 'status');
            if ($status === 'approved') {
                $approvedAmount = $amount;
            }

            $approved += $approvedAmount;
        }

        return [
            'total_expenses' => $total,
            'approved'       => $approved,
            'due'            => max(0, $total - $approved),
        ];
    }

    protected function paginateCollection(Collection $items, int $perPage = 15): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $slice = $items->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    protected function buildPersonalMonthlySummaries(Collection $expenses): Collection
    {
        if ($expenses->isEmpty()) {
            return collect();
        }

        return $expenses
            ->groupBy(function ($expense) {
                if (!empty($expense->approval_summary_path)) {
                    return 'summary:' . $expense->approval_summary_path;
                }
                $created = optional($expense->created_at)->format('Y-m');
                return 'period:' . $created;
            })
            ->map(function ($group) {
                $first = $group->sortBy('created_at')->first();

                $summary = new MarketingExpense();
                $summary->exists = false;
                $summary->id = $first?->id;
                $summary->section = 'personal';
                $firstPersonName = $group->map(function ($expense) {
                        if ($expense->relationLoaded('marketingPerson') && $expense->marketingPerson) {
                            return $expense->marketingPerson->name;
                        }
                        return $expense->person_name;
                    })->filter()->first();
                $summary->person_name = $firstPersonName ?: 'Personal Expenses';
                $summary->setAttribute('aggregate_ids', $group->pluck('id')->all());
                $summary->amount = (float) $group->sum('amount');
                $summary->approved_amount = (float) $group->sum('approved_amount');
                $summary->from_date = $group->min('from_date');
                $summary->to_date = $group->max('to_date');
                $summary->created_at = $group->max('created_at');
                $summary->approval_summary_path = $group->pluck('approval_summary_path')->filter()->first();
                $summary->submitted_for_approval = true;
                $summary->status = $this->resolveAggregateStatus($group);
                $summary->setAttribute('receipt_paths', $group->pluck('file_path')->filter()->unique()->values()->all());

                $summary->setRelation('marketingPerson', $group->first()?->marketingPerson);
                $approverExpense = $group->filter(fn ($item) => $item->approved_by)->sortByDesc('approved_at')->first();
                $summary->setRelation('approver', $approverExpense?->approver);

                $periodStart = optional($summary->from_date)->format('M Y');
                $periodEnd   = optional($summary->to_date)->format('M Y');
                if ($periodStart && $periodEnd && $periodStart !== $periodEnd) {
                    $periodLabel = optional($summary->from_date)->format('d M Y') . ' - ' . optional($summary->to_date)->format('d M Y');
                } elseif ($periodStart) {
                    $periodLabel = $periodStart;
                } else {
                    $periodLabel = null;
                }

                $summary->setAttribute('personal_period_label', $periodLabel);

                return $summary;
            })
            ->sortByDesc(function ($expense) {
                return $expense->created_at ?? now();
            })
            ->values();
    }

    protected function generatePersonalSummaryDocument(Collection $expenses, Carbon $period, string $summaryPath): string
    {
        $summaryHtml = view('superadmin.marketing.expenses.export_pdf', [
            'expenses' => $expenses,
            'section'  => 'personal',
            'title'    => 'Personal Expenses - ' . $period->format('F Y'),
        ])->render();

        $tempDir = storage_path('app/temp/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf(['format' => 'A4', 'tempDir' => $tempDir]);
        $mpdf->WriteHTML($summaryHtml);

        $receiptPaths = $expenses->pluck('file_path')->filter()->unique()->values();
        foreach ($receiptPaths as $receiptPath) {
            if (!Storage::disk('public')->exists($receiptPath)) {
                continue;
            }

            try {
                $absolutePath = Storage::disk('public')->path($receiptPath);
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

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
                    $mpdf->AddPage();
                    $type = $extension === 'jpg' ? 'jpeg' : $extension;
                    $mpdf->Image($absolutePath, 10, 10, 190, 0, strtoupper($type));
                }
            } catch (\Throwable $th) {
                continue;
            }
        }

        $pdfOutput = $mpdf->Output('', 'S');
        Storage::disk('public')->put($summaryPath, $pdfOutput);

        return $pdfOutput;
    }

    protected function refreshPersonalSummaryForExpense(MarketingExpense $expense): ?MarketingExpense
    {
        if (!$expense->submitted_for_approval) {
            return null;
        }

        $summaryPath = $expense->approval_summary_path;

        if ($summaryPath) {
            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->where('section', 'personal')
                ->where('approval_summary_path', $summaryPath)
                ->orderBy('created_at')
                ->get();
        } else {
            $createdAt = $expense->created_at instanceof Carbon
                ? $expense->created_at->copy()
                : ($expense->created_at ? Carbon::parse($expense->created_at) : Carbon::now());

            $period = Carbon::create($createdAt->year, $createdAt->month, 1);

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->where('section', 'personal')
                ->whereYear('created_at', $period->year)
                ->whereMonth('created_at', $period->month)
                ->orderBy('created_at')
                ->get();
        }

        if ($groupExpenses->isEmpty()) {
            return null;
        }

        $expenseIds = $groupExpenses->pluck('id')->all();
        $firstCreated = optional($groupExpenses->first())->created_at;
        $periodBase = $firstCreated instanceof Carbon ? $firstCreated->copy() : ($firstCreated ? Carbon::parse($firstCreated) : Carbon::now());
        $period = Carbon::create($periodBase->year, $periodBase->month, 1);
        $summaryPath = $summaryPath ?: $groupExpenses->pluck('approval_summary_path')->filter()->first();

        if (!$summaryPath) {
            $summaryFilename = sprintf('personal-expenses-%s-%s.pdf', $period->format('Y_m'), Str::lower(Str::random(6)));
            $summaryPath = 'marketing_expenses/' . $summaryFilename;

            MarketingExpense::whereIn('id', $expenseIds)->update([
                'approval_summary_path' => $summaryPath,
                'submitted_for_approval' => true,
            ]);

            $groupExpenses = MarketingExpense::with(['marketingPerson', 'approver'])
                ->whereIn('id', $expenseIds)
                ->orderBy('created_at')
                ->get();
        }

        $this->generatePersonalSummaryDocument($groupExpenses, $period, $summaryPath);

        $summary = $this->buildPersonalMonthlySummaries($groupExpenses)->first();

        if ($summary && $summary->approval_summary_path) {
            $summary->approval_summary_path = $groupExpenses->pluck('approval_summary_path')->filter()->first();
        }
        if ($summary) {
            $summary->approval_summary_path = $summaryPath;
        }

        return $summary;
    }

    protected function resolveAggregateStatus(Collection $expenses): string
    {
        if ($expenses->contains(fn ($expense) => $expense->status === 'rejected')) {
            return 'rejected';
        }
        if ($expenses->every(fn ($expense) => $expense->status === 'approved')) {
            return 'approved';
        }
        return 'pending';
    }

    protected function buildExportQuery(Request|array $request, string $section, ?string $statusOverride = null): Builder
    {
        $status = $statusOverride ?? $this->getFilterValue($request, 'status', 'all');
        $search = $this->getFilterValue($request, 'search');
        $month  = $this->getFilterValue($request, 'month');
        $year   = $this->getFilterValue($request, 'year');

        $query = MarketingExpense::with(['marketingPerson', 'approver'])
            ->where('section', $section)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('marketingPerson', function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('user_code', 'like', "%{$search}%");
                        })
                        ->orWhere('person_name', 'like', "%{$search}%")
                        ->orWhere('marketing_person_code', 'like', "%{$search}%");
                });
            })
            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->latest();

        return $query;
    }

    protected function resolvePersonCode(string $input): ?string
    {
        $value = trim($input);
        if ($value === '') {
            return null;
        }

        $candidates = [];
        if (preg_match('/\(([^)]+)\)$/', $value, $matches)) {
            $candidates[] = trim($matches[1]);
        }
        $candidates[] = $value;
        $candidates[] = preg_replace('/\(([^)]+)\)$/', '', $value);

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            $user = User::where('user_code', $candidate)->first();
            if ($user) {
                return $user->user_code;
            }
        }

        $lower = mb_strtolower($value);
        $user = User::whereRaw('LOWER(name) = ?', [$lower])->first();
        if ($user) {
            return $user->user_code;
        }

        $user = User::where('name', 'like', $value)->first();
        if ($user) {
            return $user->user_code;
        }

        return null;
    }

    protected function getFilterValue(Request|array $source, string $key, $default = null)
    {
        if ($source instanceof Request) {
            return $source->input($key, $default);
        }

        return $source[$key] ?? $default;
    }
}
