<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FileUploadService;

use App\Models\SiteSetting;


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

        $pdf = Pdf::loadView($view, compact('invoiceData', 'companyName'))->setPaper('A4');

        $pdf->output();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $fontMetrics = new \Dompdf\FontMetrics($canvas, $pdf->getDomPDF()->getOptions());
        $canvas->page_text(500, 80, "Page {PAGE_NUM} of {PAGE_COUNT}", $fontMetrics->getFont('Arial', 'normal'), 10);

        return $pdf->stream($filename);
    }
}
