<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PaymentSetting;



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

        $bookingInfo = $data['booking_info'] ?? [];

        
        $invoice = [
            'invoice_no'       => $bookingInfo['invoice_no'] ?? $this->generateInvoiceNo(),
            'invoice_date'     => $bookingInfo['invoice_date'] ?? now()->format('d-m-Y'),
            'ref_no'           => $bookingInfo['reference_no'] ?? '',
            'ref_date'         => $bookingInfo['letter_date'] ?? '22-04-2003',
            'name_of_work'     => $bookingInfo['name_of_work'] ?? '',
            'bill_issue_to'    => $bookingInfo['bill_issue_to'] ?? '',
            'client_name'      => $bookingInfo['client_name'] ?? '',
            'marketing_person' => $bookingInfo['marketing_person'] ?? '',
            'client_gstin'     => $bookingInfo['client_gstin'] ?? '',
            'sac_code'         => '998346', 
            'address'          => $bookingInfo['address'], 
        ];

        $paymentSetting = PaymentSetting::latest()->first();
        $bankDetails = [
                    'instructions'       => $paymentSetting->instructions ?? '',
                    'bank_name'          => $paymentSetting->bank_name ?? '',
                    'account_no'         => $paymentSetting->account_no ?? '',
                    'branch_name'        => $paymentSetting->branch ?? '',
                    'branch_holder_name' => $paymentSetting->branch_holder_name ?? '',
                    'ifsc_code'          => $paymentSetting->ifsc_code ?? '',
                    'pan_code'           => $paymentSetting->pan_code ?? '',
                    'pan_no'             => $paymentSetting->pan_no ?? '',
                    'gstin'              => $paymentSetting->gstin ?? '',
                    'upi'                => $paymentSetting->upi ?? '',
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

    public function generateInvoiceNo($prefix = 'ITL/25-26/', $start = 1001)
    {
        // Get the last invoice record
        $lastInvoice = \DB::table('invoices')
            ->where('invoice_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && isset($lastInvoice->invoice_no)) {
            // Extract numeric part at the end
            preg_match('/(\d+)$/', $lastInvoice->invoice_no, $matches);

            if (isset($matches[1])) {
                $lastNumber = $matches[1];
                $nextNumber = bcadd($lastNumber, '1'); // increment
            } else {
                $nextNumber = $start;
            }
        } else {
            $nextNumber = $start;
        }

        return $prefix . $nextNumber;
    }

}
