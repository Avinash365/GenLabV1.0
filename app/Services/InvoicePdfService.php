<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FileUploadService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


use App\Models\SiteSetting;
use App\Models\PaymentSetting;


class InvoicePdfService
{

    protected $fileUploadService;

     public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    
    public function generate(array $invoiceData, string $view = 'superadmin.accounts.generateInvoice.bill_pdf', string $filename = 'invoice.pdf')
    {
        // $invoiceData['invoice']['ref_no']  

        $companyName = SiteSetting::value('company_name'); 
        $amount = $invoiceData['bill']['payable_amount'] ?? '1';

        // Fetch UPI & Holder Name from DB
        $paymentSetting = PaymentSetting::latest()->first();

        $upiId = $paymentSetting->upi ?? "7739136208.etb@icici"; 
        

        $payeeName =  $paymentSetting->branch_holder_name ?? "Avinash Kumar Jha"; 
        $description = "In:10001";

        $upiLink = "upi://pay?pa={$upiId}&pn=" . urlencode($payeeName) . "&am={$amount}&cu=INR&tn=" . urlencode($description);

        $qrcode = base64_encode( QrCode::format('svg')->size(200)->generate($upiLink));


        $pdf = Pdf::loadView($view, compact('invoiceData', 'companyName', 'qrcode'))->setPaper('A4');

        $pdf->output();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $fontMetrics = new \Dompdf\FontMetrics($canvas, $pdf->getDomPDF()->getOptions());
        $canvas->page_text(500, 85, "Page {PAGE_NUM} of {PAGE_COUNT}", $fontMetrics->getFont('Arial', 'normal'), 10);

        return $pdf->stream($filename);
    } 

    public function generateQrCode(float $totalAmount, string $description = 'Invoice Payment')
    {
        $paymentSetting = PaymentSetting::latest()->first();

        $upiId = $paymentSetting->upi ?? "7739136208.etb@icici";
        $payeeName = $paymentSetting->branch_holder_name ?? "Avinash Kumar Jha";

        $upiLink = "upi://pay?pa={$upiId}&pn=" . urlencode($payeeName) . "&am={$totalAmount}&cu=INR&tn=" . urlencode($description);

        // Return base64 QR code that can be embedded in Blade templates
        return base64_encode(QrCode::format('svg')->size(200)->generate($upiLink));
    }
}
