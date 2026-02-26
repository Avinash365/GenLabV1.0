<?php

namespace App\Http\Controllers\SuperAdmin\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportReportController extends Controller
{
    public function index(Request $request)
    {
        // Ensure default view shows current month/year when not specified
        if (!$request->query('month')) {
            $request->query->set('month', now()->month);
        }
        if (!$request->query('year')) {
            $request->query->set('year', now()->year);
        }

        $query = BookingItem::with(['booking']);

        // Marketing filter (booking.marketing_id stores user_code)
        if ($marketing = $request->query('marketing')) {
            $query->whereHas('booking', function ($q) use ($marketing) {
                $q->where('marketing_id', $marketing);
            });
        }

        // payment option filter
        if ($po = $request->query('payment_option')) {
            $query->whereHas('booking', function ($q) use ($po) {
                $q->where('payment_option', $po);
            });
        }

        // Month / Year filtering (use booking.created_at by default)
        if ($month = $request->query('month')) {
            $query->whereMonth('created_at', $month);
        }
        if ($year = $request->query('year')) {
            $query->whereYear('created_at', $year);
        }

        // search across multiple fields
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', "%{$search}%")
                  ->orWhere('sample_description', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($b) use ($search) {
                      $b->where('client_name', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = intval($request->query('perPage', 25));
        $items = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Marketing persons list (role containing 'market')
        $marketingPersons = User::whereHas('role', function ($q) {
            $q->where('role_name', 'like', '%market%');
        })->select('id', 'name', 'user_code')->get();

        return view('superadmin.accounts.export_report', [
            'items' => $items,
            'marketingPersons' => $marketingPersons,
        ]);
    }

    /** Export as PDF using DOMPDF */
    public function exportPdf(Request $request)
    {
        $rows = $this->buildExportQuery($request)->get();
        $filters = $this->gatherFilters($request);
        $totalAmount = $rows->sum(function($r){ return isset($r->amount) ? floatval($r->amount) : 0; });

        $pdf = Pdf::loadView('superadmin.accounts.export_report_pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'totalAmount' => $totalAmount,
        ])->setPaper('a4', 'landscape');
        $filename = $this->buildExportFilename($request, 'pdf');
        return $pdf->download($filename);
    }

    /** Export as Excel-compatible CSV */
    public function exportExcel(Request $request)
    {
        $rows = $this->buildExportQuery($request)->get();
        $filters = $this->gatherFilters($request);
        $totalAmount = $rows->sum(function($r){ return isset($r->amount) ? floatval($r->amount) : 0; });

        $filename = $this->buildExportFilename($request, 'csv');
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $filters, $totalAmount) {
            $out = fopen('php://output', 'w');

            // Write applied filters as header rows
            foreach ($filters as $k => $v) {
                fputcsv($out, ["Filter: {$k}", $v]);
            }
            // Blank line separator
            fputcsv($out, []);

            // Column header (match the on-screen table)
            fputcsv($out, ['Job Order No','Client Name','Reference No','Sample Description','Status','Amount','Payment Status','Payment Option']);

            foreach ($rows as $r) {
                $booking = $r->booking;
                // Build sample description similar to blade (include details if present)
                $sampleDesc = $r->sample_description ?? '-';
                if (!empty($r->sample_details)) $sampleDesc .= ' - ' . $r->sample_details;

                // Build payment label like the blade view
                $po = $booking->payment_option ?? null;
                $poLabel = '-';
                if ($po === 'bill') {
                    $billNo = null;
                    if (!empty($booking) && !empty($booking->generatedInvoice) && !empty($booking->generatedInvoice->invoice_no)) {
                        $billNo = $booking->generatedInvoice->invoice_no;
                    } elseif (!empty($booking) && !empty($booking->bill_no)) {
                        $billNo = $booking->bill_no;
                    }
                    $poLabel = 'Bill' . ($billNo ? ('/' . $billNo) : '');
                } elseif ($po === 'old_bill') $poLabel = 'Old Bill';
                elseif ($po === 'without_bill') $poLabel = 'Without Bill';
                elseif ($po) $poLabel = ucfirst($po);

                // Payment state (prefer invoice.status if present, map numeric codes)
                $paymentStateLabel = '-';
                if (!empty($booking->generatedInvoice) && isset($booking->generatedInvoice->status)) {
                    $invStatus = $booking->generatedInvoice->status;
                    if (is_numeric($invStatus) || ctype_digit((string)$invStatus)) {
                        switch ((int)$invStatus) {
                            case 1: $paymentStateLabel = 'Paid'; break;
                            case 2: $paymentStateLabel = 'Canceled'; break;
                            default: $paymentStateLabel = 'Unpaid';
                        }
                    } else {
                        $paymentStateLabel = ucfirst((string)$invStatus);
                    }
                } else {
                    $paymentState = $booking->payment_status ?? null;
                    $paymentStateLabel = $paymentState ? ucfirst($paymentState) : '-';
                }

                fputcsv($out, [
                    $r->job_order_no,
                    $booking->client_name ?? '-',
                    $booking->reference_no ?? '-',
                    $sampleDesc,
                    $r->status ?? '-',
                    isset($r->amount) ? number_format($r->amount,2) : '-',
                    $paymentStateLabel,
                    $poLabel,
                ]);
            }

            // Total row: place total in Amount column (6th)
            fputcsv($out, []);
            fputcsv($out, ['Total','','','', '', number_format($totalAmount,2), '', '']);

            fclose($out);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Build a friendly export filename: "{Marketing Name} report {Mon} {Year}.{ext}"
     */
    protected function buildExportFilename(Request $request, $ext = 'pdf')
    {
        $marketing = $request->query('marketing');
        $month = $request->query('month') ?: now()->month;
        $year = $request->query('year') ?: now()->year;

        $marketingLabel = 'All';
        if ($marketing) {
            $user = User::where('user_code', $marketing)->first();
            $marketingLabel = $user ? $user->name : $marketing;
        }

        // month name (shorten to Jan, Feb etc)
        try {
            $monthName = \Carbon\Carbon::create()->month((int)$month)->format('M');
        } catch (\Exception $e) {
            $monthName = (string)$month;
        }

        $label = sprintf('%s report %s %s', $marketingLabel, $monthName, $year);

        // sanitize for filename: replace problematic chars and collapse spaces
        $safe = preg_replace('/[^A-Za-z0-9 _-]/', '', $label);
        $safe = preg_replace('/\s+/', '_', trim($safe));

        return $safe . '.' . ltrim($ext, '.');
    }

    protected function gatherFilters(Request $request)
    {
        $filters = [];
        if ($search = $request->query('search')) $filters['Search'] = $search;
        if ($marketing = $request->query('marketing')) {
            $user = User::where('user_code', $marketing)->first();
            $filters['Marketing'] = $user ? ($user->user_code . ' - ' . $user->name) : $marketing;
        }
        if ($month = $request->query('month')) $filters['Month'] = \Carbon\Carbon::create()->month((int) $month)->format('F');
        if ($year = $request->query('year')) $filters['Year'] = $year;
        if ($po = $request->query('payment_option')) {
            $map = ['bill' => 'Bill', 'old_bill' => 'Old Bill', 'without_bill' => 'Without Bill'];
            $filters['Billing'] = $map[$po] ?? ucfirst($po);
        }
        return $filters;
    }

    protected function buildExportQuery(Request $request)
    {
        $query = BookingItem::with(['booking.generatedInvoice']);

        if ($marketing = $request->query('marketing')) {
            $query->whereHas('booking', function ($q) use ($marketing) {
                $q->where('marketing_id', $marketing);
            });
        }

        if ($po = $request->query('payment_option')) {
            $query->whereHas('booking', function ($q) use ($po) {
                $q->where('payment_option', $po);
            });
        }

        if ($month = $request->query('month')) {
            $query->whereMonth('created_at', $month);
        }
        if ($year = $request->query('year')) {
            $query->whereYear('created_at', $year);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('job_order_no', 'like', "%{$search}%")
                  ->orWhere('sample_description', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($b) use ($search) {
                      $b->where('client_name', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderByDesc('created_at');
    }
}
