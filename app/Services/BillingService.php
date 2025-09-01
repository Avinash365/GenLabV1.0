<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;


class BillingService
{
    
    protected $CGST = 9;
    protected $SGST = 9;
    protected $IGST = 18;
    protected $numberToWordsService;

    public function __construct(NumberToWordsService $numberToWordsService)
    {
        $this->numberToWordsService = $numberToWordsService;
    }

    /**
     * Generate invoice data from JSON request
     */
    public function generateInvoiceData($request)
    {
        $data = json_decode($request->invoice_data, true);
        
        // dd($data); 
        // exit; 

        $bookingInfo = $data['booking_info'] ?? [];
        
        $invoice = [
            'invoice_no'       => $bookingInfo['invoice_no'] ?? $this->generateInvoiceNo(),
            'invoice_date'     => $bookingInfo['invoice_date'] ?? now()->format('d/m/Y'),
            'ref_no'           => $bookingInfo['reference_no'] ?? '',
            'ref_date'         => $bookingInfo['letter_date'] ?? '22-04-2003',
            'name_of_work'     => $bookingInfo['name_of_work'] ?? '',
            'bill_issue_to'    => $bookingInfo['bill_issue_to'] ?? '',
            'client_name'      => $bookingInfo['client_name'] ?? '',
            'marketing_person' => $bookingInfo['marketing_person'] ?? '',
            'client_gstin'     => $bookingInfo['client_gstin'] ?? '',
            'sac_code'         => '998346', 
        ];

        // Bank info
        $bankInfo = $data['bank_info'] ?? [];
        $bankDetails = [
            'instructions' => $bankInfo['instructions'] ?? 'hhjbgfdbbdfn',
            'name'         => $bankInfo['name'] ?? 'SBI',
            'account_no'   => $bankInfo['account_no'] ?? '5346432165465',
            'branch_name'  => $bankInfo['branch_name'] ?? 'knkfnknjfg',
            'ifsc_code'    => $bankInfo['ifsc_code'] ?? '212knkvf',
            'pan_no'       => $bankInfo['pan_no'] ?? '32414645',
            'gstin'        => $bankInfo['gstin'] ?? '4642165454654',
        ];

        // Items
        $items = $data['items'] ?? [];
        $totalAmount = 0;
        foreach ($items as &$item) {
            $qty    = floatval(str_replace(',', '', $item['qty'] ?? 0));
            $rate   = floatval(str_replace(',', '', $item['rate'] ?? 0));
            $amount = $qty * $rate;
            $totalAmount += $amount;

            $item['qty']    = $qty;
            $item['rate']   = number_format($rate, 2, '.', '');
            $item['amount'] = number_format($amount, 2, '.', '');
        }

        // Totals
        $totals = $data['totals'] ?? [];
        $discountPercent = floatval($totals['discount_percent'] ?? 0);
        $discountAmount = ($totalAmount * $discountPercent) / 100;
        $afterDiscount = $totalAmount - $discountAmount;

        // GST
        $cgstPercent = floatval($totals['cgst_percent'] ?? $this->CGST);
        $sgstPercent = floatval($totals['sgst_percent'] ?? $this->SGST);
        $igstPercent = floatval($totals['igst_percent'] ?? $this->IGST);

        $cgstAmount = ($afterDiscount * $cgstPercent) / 100;
        $sgstAmount = ($afterDiscount * $sgstPercent) / 100;
        $igstAmount = ($afterDiscount * $igstPercent) / 100;

        $payableAmount = $afterDiscount + $cgstAmount + $sgstAmount + $igstAmount;

        // Round off
        $roundOff = floatval($totals['round_off'] ?? 0);
        $roundOffAmount = 0;
        if ($roundOff) {
            $roundedPayable = round($payableAmount);
            $roundOffAmount = $roundedPayable - $payableAmount;
            $payableAmount = $roundedPayable;
        }

        $bill = [
            'total_amount'           => number_format($totalAmount, 2, '.', ''),
            'discount_percent'       => $discountPercent,
            'discount_amount'        => number_format($discountAmount, 2, '.', ''),
            'after_discount_amount'  => number_format($afterDiscount, 2, '.', ''),
            'cgst_percent'           => $cgstPercent,
            'cgst_amount'            => number_format($cgstAmount, 2, '.', ''),
            'sgst_percent'           => $sgstPercent,
            'sgst_amount'            => number_format($sgstAmount, 2, '.', ''),
            'igst_percent'           => $igstPercent,
            'igst_amount'            => number_format($igstAmount, 2, '.', ''),
            'round_of'               => $totals['round_off'] ?? 0, 
            'round_off_amount'       => number_format($roundOffAmount, 2, '.', ''),
            'payable_amount'         => number_format($payableAmount, 2, '.', ''),
            'payable_amount_in_text' => "Rupees " . ucfirst($this->numberToWordsService->convert(round($payableAmount))) . " only",
        ];

        return compact('invoice', 'bankDetails', 'items', 'bill');
    }

    /**
     * Save invoice in DB
     */
    public function storeInvoice($invoiceData)
    {
        $invoiceId = DB::table('invoices')->insertGetId([
            'invoice_no'     => $invoiceData['invoice']['invoice_no'],
            'client_name'    => $invoiceData['invoice']['client_name'], 
            'marketing'      => $invoiceData['invoice']['marketing_person'], 
            'ref_no'         => $invoiceData['invoice']['ref_no'], 
            'letter_date'    => $invoiceData['inovice']['ref_date']??now()->format('Y-m-d'), 
            'issue_to'       => $invoiceData['invoice']['bill_issue_to'],
            'name_of_work'   => $invoiceData['invoice']['name_of_work'], 
            'client_gstin'   => $invoiceData['invoice']['client_gstin']??'001', 
            'sac_code'       => $invoiceData['invoice']['sac_code'], 
        ]);

        return $invoiceId;
    }

    public function generateInvoiceNo()
    {
        $lastInvoice = DB::table('invoices')->orderBy('invoice_no', 'desc')->first();
            
        if ($lastInvoice && isset($lastInvoice->invoice_no)) {
             return $lastInvoice->invoice_no + 1;
        }
        return $lastInvoice->invoice_no ?? 1000000001;
    }
}
