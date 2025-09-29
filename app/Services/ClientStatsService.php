<?php

namespace App\Services;

use App\Models\NewBooking;
use App\Models\{Invoice,InvoiceTds, InvoiceTransaction} ;
use App\Models\TdsPayment;
use App\Models\CashLetterPayment;
use Illuminate\Support\Facades\DB;

class ClientStatsService
{
    public function calculate(int $clientId, array $filters): array
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
        $bookingQuery = NewBooking::where('client_id', $clientId)
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
            ->where('client_id', $clientId)
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'invoices.created_at'));

        $invoiceStats = Invoice::query()
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'invoices.created_at'))
            ->leftJoin(DB::raw("
                (SELECT invoice_id, SUM(amount_received) as total_received
                FROM invoice_transactions
                GROUP BY invoice_id) as it
            "), 'invoices.id', '=', 'it.invoice_id')
            ->leftJoin(DB::raw("
                (SELECT invoice_id, SUM(amount_after_tds) as total_after_tds
                FROM invoice_tds
                GROUP BY invoice_id) as tds
            "), 'invoices.id', '=', 'tds.invoice_id')
            ->select(
                'type',
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('COALESCE(SUM(invoices.total_amount),0) as total_amount'),
                DB::raw('COALESCE(SUM(invoices.gst_amount),0) as total_gst'),

                // Unpaid
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 0 THEN invoices.total_amount ELSE 0 END),0) as total_unpaid_amount"),
                DB::raw("SUM(CASE WHEN invoices.status = 0 THEN 1 ELSE 0 END) as unpaid_invoice_count"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 0 THEN it.total_received ELSE 0 END),0) as unpaid_received_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 0 THEN tds.total_after_tds ELSE 0 END),0) as unpaid_amount_after_tds"),

                // Paid
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 1 THEN invoices.total_amount ELSE 0 END),0) as total_paid_amount"),
                DB::raw("SUM(CASE WHEN invoices.status = 1 THEN 1 ELSE 0 END) as paid_invoice_count"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 1 THEN it.total_received ELSE 0 END),0) as paid_received_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 1 THEN tds.total_after_tds ELSE 0 END),0) as paid_amount_after_tds"),

                // Canceled
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 2 THEN invoices.total_amount ELSE 0 END),0) as canceled_amount"),
                DB::raw("SUM(CASE WHEN invoices.status = 2 THEN 1 ELSE 0 END) as canceled_invoice_count"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 2 THEN it.total_received ELSE 0 END),0) as canceled_received_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 2 THEN tds.total_after_tds ELSE 0 END),0) as canceled_amount_after_tds"),

                // Partial
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 3 THEN invoices.total_amount ELSE 0 END),0) as total_partial_amount"),
                DB::raw("SUM(CASE WHEN invoices.status = 3 THEN 1 ELSE 0 END) as partial_invoice_count"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 3 THEN it.total_received ELSE 0 END),0) as partial_received_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 3 THEN tds.total_after_tds ELSE 0 END),0) as partial_amount_after_tds"),

                // Settled
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 4 THEN invoices.total_amount ELSE 0 END),0) as total_settled_amount"),
                DB::raw("SUM(CASE WHEN invoices.status = 4 THEN 1 ELSE 0 END) as settled_invoice_count"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 4 THEN it.total_received ELSE 0 END),0) as settled_received_amount"),
                DB::raw("COALESCE(SUM(CASE WHEN invoices.status = 4 THEN tds.total_after_tds ELSE 0 END),0) as settled_amount_after_tds")
            )
            ->groupBy('type')
            ->get()
            ->keyBy('type'); 

        /** -------------------------
         *  Transaction Stats (with TDS)
         * ------------------------ */
        $tdsSumQuery = InvoiceTds::query()
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                ->when($year, fn($q) => $q->whereYear('created_at', $year))
                ->selectRaw("COALESCE(SUM(tds_amount),0) as totalTds, COALESCE(SUM(amount_after_tds),0) as totalAmountAfterTds")
                ->first();

        $transactionSumQuery = InvoiceTransaction::query()
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                ->when($year, fn($q) => $q->whereYear('created_at', $year))
                ->selectRaw("COALESCE(SUM(amount_received),0) as totalReceivedAmount, COUNT(*) as totalTransactions")
                ->first();


        /** -------------------------
         *  Not Generated Invoices
         * ------------------------ */
        $notGeneratedInvoicesQuery = NewBooking::where('client_id', $clientId)
            ->where('payment_option', 'bill')
            ->whereDoesntHave('generatedInvoice')
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'new_bookings.created_at'));

        $notGeneratedInvoices = $notGeneratedInvoicesQuery->count();
        $totalNotGeneratedInvoicesAmount = $notGeneratedInvoicesQuery
            ->join('booking_items as bi', 'new_bookings.id', '=', 'bi.new_booking_id')
            ->sum('bi.amount');
        

        /** -------------------------
         *  Cash Payments Stats
         * ------------------------ */
        $cashQuery = CashLetterPayment::where('client_id', $clientId)
            ->when($month || $year, fn($q) => $applyDateFilter($q, 'cash_letter_payments.created_at'));

       $cashStats = $cashQuery->selectRaw("
            COUNT(*) as totalTransactions,

                SUM(CASE WHEN transaction_status = 0 THEN JSON_LENGTH(booking_ids) ELSE 0 END) as pendingBookingIds,
                SUM(CASE WHEN transaction_status = 1 THEN JSON_LENGTH(booking_ids) ELSE 0 END) as partialBookingIds,
                SUM(CASE WHEN transaction_status = 2 THEN JSON_LENGTH(booking_ids) ELSE 0 END) as paidBookingIds,
                SUM(CASE WHEN transaction_status = 3 THEN JSON_LENGTH(booking_ids) ELSE 0 END) as settledBookingIds,

                SUM(CASE WHEN transaction_status = 0 THEN 1 ELSE 0 END) as pendingCount,
                SUM(CASE WHEN transaction_status = 0 THEN total_amount ELSE 0 END) as pendingAmount,

                SUM(CASE WHEN transaction_status = 1 THEN 1 ELSE 0 END) as partialCount,
                SUM(CASE WHEN transaction_status = 1 THEN amount_received ELSE 0 END) as partialAmount,
                SUM(CASE WHEN transaction_status = 1 THEN total_amount ELSE 0 END) as totalPartialAmount,

                SUM(CASE WHEN transaction_status = 2 THEN 1 ELSE 0 END) as paidCount,
                SUM(CASE WHEN transaction_status = 2 THEN amount_received ELSE 0 END) as paidAmount,

                SUM(CASE WHEN transaction_status = 3 THEN 1 ELSE 0 END) as settledCount,
                SUM(CASE WHEN transaction_status = 3 THEN amount_received ELSE 0 END) as settledAmount,
                SUM(CASE WHEN transaction_status = 3 THEN total_amount ELSE 0 END) as totalSettledAmount,

                SUM(total_amount) as totalTransactionCashLetterAmount,
                SUM(amount_received) as totalRecivedAmount
        ")->first();

        $cashUnpaidLetters = ($bookingStats->withoutBillBookings ?? 0) - ($cashStats->settledBookingIds ?? 0) - ($cashStats->partialBookingIds ?? 0) - ($cashStats->paidBookingIds ?? 0);
        $totalCashUnpaidAmounts = ($bookingStats->totalWithoutBillBookings ?? 0) - ($cashStats->totalTransactionCashLetterAmount ?? 0);

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

            'partialTaxInvoices' => (int) ($invoiceStats['tax_invoice']->partial_invoice_count ?? 0), 
            'totalPartialTaxInvoiceAmount' => (float) (($invoiceStats['tax_invoice']->partial_amount_after_tds ?? 0)-($invoiceStats['tax_invoice']->partial_received_amount ?? 0)), 

            'settledTaxInvoices' => (int) ($invoiceStats['tax_invoice']->settled_invoice_count ?? 0), 
            'totalSettledTaxInvoicesAmount' => (float) (($invoiceStats['tax_invoice']->settled_amount_after_tds ?? 0)-($invoiceStats['tax_invoice']->settled_received_amount ?? 0)),

            'paidPiInvoices' => (int) ($invoiceStats['proforma_invoice']->paid_invoice_count ?? 0),
            'totalPaidPIAmount' => (float) ($invoiceStats['proforma_invoice']->total_paid_amount ?? 0),

            'unpaidInvoices' => (int) ($invoiceStats['tax_invoice']->unpaid_invoice_count ?? 0),
            'totalUnpaidInvoiceAmount' => (float) ($invoiceStats['tax_invoice']->total_unpaid_amount ?? 0),

            'canceledGeneratedInvoices' => (int) ($invoiceStats['tax_invoice']->canceled_invoice_count ?? 0),
            'totalcanceledGeneratedInvoicesAmount' => (float) ($invoiceStats['tax_invoice']->canceled_amount ?? 0), 

            // Not generated invoices
            'notGeneratedInvoices' => (int) $notGeneratedInvoices,
            'totalNotGeneratedInvoicesAmount' => (float) $totalNotGeneratedInvoicesAmount,

            // Transactions 
            'transactions' => (int) $transactionSumQuery->totalTransactions ?? 0, 
            'totalTransactionsAmount' => (float) $transactionSumQuery->totalReceivedAmount ?? 0, 
            'tdsAmount'  => (float) $tdsSumQuery->totalTds ?? 0, 

            // Cash Payments
            'cashPaidLetters' => (int) ($cashStats->paidBookingIds ?? 0),
            'totalCashPaidLettersAmount' => (float) ($cashStats->paidAmount ?? 0),

            'cashPartialLetters' => (int) ($cashStats->partialBookingIds ?? 0), 
            'totalcashPartialLettersAmount' => (float)($cashStats->partialAmount ?? 0),
            'totalDueAmount'     => (float)($cashStats->totalPartialAmount ?? 0) - ($cashStats->partialAmount ?? 0), 

            'cashSettledLetters' => (int)($cashStats->settledBookingIds ?? 0), 
            'totalCashSettledLettersAmount' => (float)($cashStats->settledAmount ?? 0),  
            'totalSettledAmount'  => (float)(($cashStats->totalSettledAmount ?? 0) - ($cashStats->settledAmount ?? 0)), 

            'cashUnpaidLetters' => (int) $cashUnpaidLetters,
            'totalCashUnpaidAmounts' => (float) $totalCashUnpaidAmounts,
        ];
    }
}
