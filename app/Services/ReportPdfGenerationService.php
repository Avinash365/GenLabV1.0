<?php

namespace App\Services;

use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;

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
        // Calculate extra margin based on line breaks
        $extraMargin = 0;
        if (isset($headerData['line_breaks']['total_n'])) {  
            $pix = 5; 
            $extraMargin = $pix * (int) $headerData['line_breaks']['total_n'];
        }  

        // Initialize mPDF with dynamic margins
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 110 + $extraMargin,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15
        ]);
        
        // Prepare header HTML with page numbers
        $headerHtml = view('Reportfrmt.tableHadder', $headerData)->render();

        $pageNumOffset = 65+$extraMargin; // in mm
        $headerHtmlWithPageNum = $headerHtml . '
            <div style="text-align: right; font-size: 10pt; margin-top:' .'-'. $pageNumOffset . 'mm;">
                Page {PAGENO} of {nbpg}
            </div>
        '; 

        $this->mpdf->SetHTMLHeader($headerHtmlWithPageNum);

        // Loop through HTML files and write to PDF
        foreach ($htmlPaths as $index => $path) {
            if (!Storage::disk('public')->exists($path)) {
                continue;
            }

            $html = Storage::disk('public')->get($path);

            // Add border styles to tables
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

        // Ensure PDF folder exists
        $pdfFolder = storage_path('app/public/generatedReports');
        if (!file_exists($pdfFolder)) {
            mkdir($pdfFolder, 0755, true);
        }

        // Generate unique PDF name if none provided
        if (!$outputName) {
            $outputName = time() . '_report.pdf';
        }

        $pdfFilePath = $pdfFolder . '/' . basename($outputName);

        // Save PDF to disk
        $this->mpdf->Output($pdfFilePath, 'F');

        // Return relative path for storage
        return 'generatedReports/' . basename($outputName);
    }
}
