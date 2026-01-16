<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingQuotationController extends Controller
{
    /**
     * Display a listing of the quotations for marketing panel.
     */
    public function index(Request $request)
    {
        $marketingPersons = $this->getMarketingPersons();
        $query = $this->buildQuery($request);
        $quotations = $query->paginate(10)->withQueryString();

        return view('superadmin.marketing.accounts.quotation.index', compact(
            'quotations',
            'marketingPersons'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $quotations = $query->get();

        $pdf = Pdf::loadView('superadmin.marketing.accounts.quotation.pdf', compact('quotations'));
        return $pdf->download('quotations.pdf');
    }

     public function exportExcel(Request $request)
    {
        $query = $this->buildQuery($request);
        $quotations = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quotations.csv"',
        ];

        $callback = function () use ($quotations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Quotation No', 'Client Name', 'Client GSTIN', 'Total Amount', 'Quotation Date', 'Bill Issue To']);

            foreach ($quotations as $row) {
                fputcsv($file, [
                    $row->quotation_no,
                    $row->client_name,
                    $row->client_gstin,
                    $row->payable_amount,
                    $row->quotation_date,
                    $row->bill_issue_to
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    private function getMarketingPersons()
    {
        $marketingPersons = User::whereHas('role', function ($q) {
            $q->where('slug', 'marketing_person');
        })->get(['id', 'user_code', 'name']);

        foreach ($marketingPersons as $person) {
            $person->label = $person->user_code . ' - ' . $person->name;
        }
        return $marketingPersons;
    }

    private function buildQuery(Request $request)
    {
        $query = Quotation::with('generatedBy');

        /** ------------------------------
         * AUTH & MARKETING CONTEXT
         * ------------------------------ */
        $authUser = $request->user();
        $isMarketing = false;

        if ($authUser && isset($authUser->role)) {
            $roleName = is_object($authUser->role)
                ? ($authUser->role->role_name ?? $authUser->role->name ?? null)
                : $authUser->role;

            $isMarketing = $roleName && stripos($roleName, 'market') !== false;
        }

        // Force marketing context or allow passing marketing person code
        $marketingCode = $request->marketing
            ?? $request->user_code
            ?? ($isMarketing ? ($authUser->user_code ?? null) : null);

        // Auto filter for marketing users
        if ($marketingCode) {
            $query->where('marketing_person_code', $marketingCode);
        }
        
        // Removed explicit marketing_person filter to enforce personalization logic above

        /** ------------------------------
         * CLIENT FILTER
         * ------------------------------ */
        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', "%{$request->client_name}%");
        }

        /** ------------------------------
         * SEARCH FILTER
         * ------------------------------ */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                // Ensure marketingPerson relationship is joined if searching by name
                // Or just assume local search for marketing person name via blade data-search
                // But here we search DB columns:
                $q->where('quotation_no', 'like', "{$search}%")
                  ->orWhere('client_name', 'like', "{$search}%");
            });
        }

        /** ------------------------------
         * MONTH & YEAR FILTER
         * ------------------------------ */
        if ($request->filled('month') || $request->filled('year')) {
            $query->when($request->month, function ($q, $month) {
                $q->whereMonth('quotation_date', $month);
            })->when($request->year, function ($q, $year) {
                $q->whereYear('quotation_date', $year);
            });
        }

        $query->orderBy('quotation_no', 'desc');

        return $query;
    }
}
