<?php

namespace App\Services;

use App\Models\NewBooking;
use App\Models\Invoice;
use App\Models\TdsPayment;
use App\Models\CashLetterPayment;
use Illuminate\Support\Facades\DB;

class MarketingPersonStatsService
{
    public function calculate(string $userCode, array $filters): array
    {
        $month = $filters['month'] ?? null;
        $year  = $filters['year'] ?? null;

        // Helper for month/year filter with table prefix
        $applyDateFilter = function ($query, $column) use ($month, $year) {
            if ($month) {
                $query->whereMonth($column, $month);
            }
            if ($year) {
                $query->whereYear($column, $year);
            }
            return $query;
        };

        /** -------------------------
         *  Bookings Stats
         * ------------------------ */
        $bookingQuery = NewBooking::where('marketing_id', $userCode)
            ->whereNull('deleted_at')
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'new_bookings.created_at'));

        $bookingStats = DB::table(DB::raw("({$bookingQuery->toSql()}) as nb"))
            ->mergeBindings($bookingQuery->getQuery())
            ->leftJoin(DB::raw('(
                SELECT 
                    new_booking_id, 
                    SUM(amount) as total_item_amount,
                    COUNT(*) as total_items
                FROM booking_items
                WHERE deleted_at IS NULL
                GROUP BY new_booking_id
            ) as bi'), 'nb.id', '=', 'bi.new_booking_id')
            ->selectRaw("
                COUNT(nb.id) as totalBookings,
                COALESCE(SUM(bi.total_item_amount),0) as totalBookingAmount,
                SUM(CASE WHEN nb.payment_option = 'bill' THEN bi.total_item_amount ELSE 0 END) as totalBillBookingAmount,
                SUM(CASE WHEN nb.payment_option = 'without_bill' THEN bi.total_item_amount ELSE 0 END) as totalWithoutBillBookings,
                SUM(CASE WHEN nb.payment_option = 'bill' THEN 1 ELSE 0 END) as billBookings,
                SUM(CASE WHEN nb.payment_option = 'without_bill' THEN 1 ELSE 0 END) as withoutBillBookings
            ")
            ->first();

        /** -------------------------
         *  Invoice Stats
         * ------------------------ */
        $invoiceQuery = Invoice::query()
            ->when($userCode, fn($q) => $q->where('marketing_user_code', $userCode))
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'invoices.created_at'));

        $invoiceStats = $invoiceQuery
            ->select(
                'type',
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('COALESCE(SUM(total_amount),0) as total_amount'),
                DB::raw('COALESCE(SUM(gst_amount),0) as total_gst'),
                DB::raw('COALESCE(SUM(CASE WHEN status = 1 THEN total_amount ELSE 0 END),0) as total_paid_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END),0) as paid_invoice_count'), 
                DB::raw('COALESCE(SUM(CASE WHEN status = 2 THEN total_amount ELSE 0 END),0) as canceled_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END),0) as canceled_invoice_count')
            )
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        /** -------------------------
         *  Not Generated Invoices
         * ------------------------ */
        $notGeneratedInvoicesQuery = NewBooking::where('marketing_id', $userCode)
            ->where('payment_option', 'bill')
            ->whereDoesntHave('generatedInvoice')
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'new_bookings.created_at'));

        $notGeneratedInvoices = $notGeneratedInvoicesQuery->count();
        $totalNotGeneratedInvoicesAmount = $notGeneratedInvoicesQuery
            ->join('booking_items as bi', 'new_bookings.id', '=', 'bi.new_booking_id')
            ->sum('bi.amount');

        /** -------------------------
         *  Paid Invoice Amount
         * ------------------------ */
        $totalPaidInvoiceAmount = (clone $invoiceQuery)->where('status', 1)->sum('total_amount');

        /** -------------------------
         *  TDS Stats
         * ------------------------ */
        $tdsStats = TdsPayment::select(
                'marketing_person_id',
                DB::raw('COUNT(*) as total_payments'),
                DB::raw('COALESCE(SUM(subtotal_amount * tds_percentage / 100), 0) as total_tds_amount')
            )
            ->where('marketing_person_id', $userCode)
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'tds_payments.created_at'))
            ->groupBy('marketing_person_id')
            ->first();

        /** -------------------------
         *  Cash Payments Stats
         * ------------------------ */
        $cashQuery = CashLetterPayment::where('marketing_person_id', $userCode)
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'cash_letter_payments.created_at'));

        $cashStats = $cashQuery->selectRaw("
            COUNT(*) as totalTransactions, 
            SUM(CASE WHEN transaction_status = 2 THEN 1 ELSE 0 END) as cashPaidLetters,
            SUM(CASE WHEN transaction_status = 2 THEN amount_received ELSE 0 END) as totalCashPaidLettersAmounts,
            SUM(CASE WHEN total_amount != amount_received THEN 1 ELSE 0 END) as cashDefaulter,
            SUM(CASE WHEN transaction_status = 1 THEN amount_received ELSE 0 END) as totalDefaulterAmount, 
            SUM(total_amount) as totalTransactionCashLetterAmount, 
            SUM(amount_received) as totalRecivedAmount
        ")->first();

     

        $cashUnpaidLetters = ($bookingStats->withoutBillBookings ?? 0) - ($cashStats->totalTransactions ?? 0);
        $totalCashUnpaidAmounts = ($bookingStats->totalWithoutBillBookings ?? 0) - ($cashStats->totalTransactionCashLetterAmount ?? 0) ;

        /** -------------------------
         *  Final Stats Response
         * ------------------------ */
        return [
            // Bookings
            'totalBookings' => (int) ($bookingStats->totalBookings ?? 0),
            'billBookings' => (int) ($bookingStats->billBookings ?? 0),
            'withoutBillBookings' => (int) ($bookingStats->withoutBillBookings ?? 0),
            'totalBookingAmount' => (float) ($bookingStats->totalBookingAmount ?? 0),
            'totalBillBookingAmount' => (float) ($bookingStats->totalBillBookingAmount ?? 0),
            'totalWithoutBillBookings' => (float) ($bookingStats->totalWithoutBillBookings ?? 0),

            // Invoices
            'GeneratedInvoices' => (int) ($invoiceStats['tax_invoice']->invoice_count ?? 0),
            'GeneratedPIs' => (int) ($invoiceStats['proforma_invoice']->invoice_count ?? 0),
            'totalInvoiceAmount' => (float) ($invoiceStats['tax_invoice']->total_amount ?? 0),
            'totalPIAmount' => (float) ($invoiceStats['proforma_invoice']->total_amount ?? 0),
            'paidInvoices' => (int) ($invoiceStats['tax_invoice']->paid_invoice_count ?? 0),
            'totalPaidInvoiceAmount' => (float) ($invoiceStats['tax_invoice']->total_paid_amount ?? 0),
            'paidPiInvoices' => (int) ($invoiceStats['proforma_invoice']->paid_invoice_count ?? 0),
            'totalPaidPIAmount' => (float) ($invoiceStats['proforma_invoice']->total_paid_amount ?? 0),
            'unpaidInvoices' => (int) (($invoiceStats['tax_invoice']->invoice_count ?? 0) - ($invoiceStats['tax_invoice']->paid_invoice_count ?? 0) - (($invoiceStats['tax_invoice']->canceled_invoice_count ?? 0))),
            'totalUnpaidInvoiceAmount' => (float) (($invoiceStats['tax_invoice']->total_amount ?? 0) - ($invoiceStats['tax_invoice']->total_paid_amount ?? 0)-($invoiceStats['tax_invoice']->canceled_amount ?? 0)),

            'canceledGeneratedInvoices' => (int)($invoiceStats['tax_invoice']->canceled_invoice_count ?? 0),
            'totalcanceledGeneratedInvoicesAmount' => (int)($invoiceStats['tax_invoice']->canceled_amount ?? 0), 

            // Not generated invoices
            'notGeneratedInvoices' => (int) $notGeneratedInvoices,
            'totalNotGeneratedInvoicesAmount' => (float) $totalNotGeneratedInvoicesAmount,

            // TDS
            'invoiceTransactions' => (int) ($tdsStats->total_payments ?? 0),
            'totalTdsAmount' => (float) ($tdsStats->total_tds_amount ?? 0),

            // Cash Payments
            'cashPaidLetters' => (int) ($cashStats->cashPaidLetters ?? 0),
            'totalCashPaidLettersAmounts' => (float) ($cashStats->totalCashPaidLettersAmounts ?? 0),
            'cashUnpaidLetters' => (int) $cashUnpaidLetters,
            'totalCashUnpaidAmounts' => (float) $totalCashUnpaidAmounts,
            'cashDefaulter' => (int) ($cashStats->cashDefaulter ?? 0),
            'totalDefaulterAmount' => (float) ($cashStats->totalTransactionCashLetterAmount ?? 0) - ($cashStats->totalRecivedAmount ?? 0),
        ];
    }
}
