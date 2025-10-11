<?php

namespace App\Services;

use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPdfGenerationService
{
    protected $mpdf;

    public function __construct()
    {
        // Default mpdf instance
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 100,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15
        ]);
    }

    /**
     * Generate PDF from HTML files, save to disk, and return path
     */
    public function generateFromHtmlFiles(array $htmlPaths, array $headerData = [], string $outputName = null)
    {
        // Calculate dynamic top margin
        $extraMargin = 0;
        if (
            (!isset($headerData['include_header']) || $headerData['include_header'] == 1) 
            && isset($headerData['line_breaks']['total_n'])
        ) {
            $pix = 5;
            $extraMargin = 50 + $pix * (int) $headerData['line_breaks']['total_n'];
        }

        // Create mPDF instance with updated margin
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 70 + $extraMargin,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15
        ]);

        // Generate QR Code for report verification
        $booking_item_id = $headerData['booking_item_id'] ?? 1;
        $reportUrl = route('varification.view', ['no' => $booking_item_id]);

        $qrCodeBase64 = base64_encode(
            QrCode::format('svg')->size(60)->margin(0)->errorCorrection('H')->generate($reportUrl)
        );

        $headerData['qr_code_svg'] = 'data:image/svg+xml;base64,' . $qrCodeBase64;

        //  Render header view (includes QR + Page No now)
        $headerHtml = view('Reportfrmt.tableHadder', $headerData)->render();
        $this->mpdf->SetHTMLHeader($headerHtml);

        // Loop through HTML files
        foreach ($htmlPaths as $index => $path) {
            if (!Storage::disk('public')->exists($path)) {
                continue;
            }

            $html = Storage::disk('public')->get($path);

            // Add table borders dynamically
            $html = preg_replace_callback('/<table(.*?)>/', function ($matches) {
                $tableTag = $matches[0];
                if (strpos($tableTag, 'style=') !== false) {
                    $tableTag = preg_replace(
                        '/style="(.*?)"/',
                        'style="$1; border:1px solid black; border-collapse: collapse; margin-top: 20px;"',
                        $tableTag
                    );
                } else {
                    $tableTag = str_replace(
                        '<table',
                        '<table style="border:1px solid black; border-collapse: collapse; margin-top: 20px;"',
                        $tableTag
                    );
                }

                if (strpos($tableTag, 'border=') === false) {
                    $tableTag = str_replace('<table', '<table border="1"', $tableTag);
                }

                return $tableTag;
            }, $html);

            $this->mpdf->WriteHTML($html);

            if ($index < count($htmlPaths) - 1) {
                $this->mpdf->AddPage();
            }
        }

        // Ensure directory exists
        $pdfFolder = storage_path('app/public/generatedReports');
        if (!file_exists($pdfFolder)) {
            mkdir($pdfFolder, 0755, true);
        }

        // Output filename
        if (!$outputName) {
            $outputName = time() . '_report.pdf';
        }

        $pdfFilePath = $pdfFolder . '/' . basename($outputName);

        // Save PDF to disk
        $this->mpdf->Output($pdfFilePath, 'F');

        return 'generatedReports/' . basename($outputName);
    }
}
