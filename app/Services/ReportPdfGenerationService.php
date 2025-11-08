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
        // Default mPDF configuration
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 100,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15
        ]);
    }

   
    public function generateFromHtmlFiles(array $htmlPaths, array $headerData = [], string $outputName = null, bool $isTemp = false)
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

        // Create new Mpdf instance
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 70 + $extraMargin,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15
        ]);

        // Generate QR Code
        $booking_item_id = $headerData['booking_item_id'] ?? 1;
        $reportUrl = route('varification.view', ['no' => $booking_item_id]);

        $qrCodeBase64 = base64_encode(
            QrCode::format('svg')->size(60)->margin(0)->errorCorrection('H')->generate($reportUrl)
        );

        $headerData['qr_code_svg'] = 'data:image/svg+xml;base64,' . $qrCodeBase64;

        // Render header view
        $headerHtml = view('Reportfrmt.tableHadder', $headerData)->render();
        $this->mpdf->SetHTMLHeader($headerHtml);

        // Merge all HTML content
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

        // Decide storage folder
        $folderName = $isTemp ? 'tempReports' : 'generatedReports';
        $pdfFolder = storage_path("app/public/{$folderName}");

        if (!file_exists($pdfFolder)) {
            mkdir($pdfFolder, 0755, true);
        }

        // Clean old temporary files (older than 10 minutes)
        if ($isTemp) {
            $this->cleanOldTempFiles($pdfFolder, 10);
        }

        // File name
        if (!$outputName) {
            $outputName = time() . '_report.pdf';
        }

        $pdfFilePath = $pdfFolder . '/' . basename($outputName);

        // Save the file
        $this->mpdf->Output($pdfFilePath, 'F');

        // Return relative public path
        return "{$folderName}/" . basename($outputName);
    }

    /**
     * Clean up old temporary files (older than given minutes)
     */
    protected function cleanOldTempFiles($folder, $minutes = 1)
    {
        if (!file_exists($folder)) {
            return;
        }

        $files = glob($folder . '/*.pdf');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = ($now - filemtime($file)) / 60; // minutes
                if ($fileAge > $minutes) {
                    @unlink($file);
                }
            }
        }
    }
}
