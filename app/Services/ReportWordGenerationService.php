<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;

class ReportWordGenerationService
{
    protected PhpWord $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
    }

    public function generateFromHtmlFiles(array $htmlPaths, string $fileName = null)
    {
        $section = $this->phpWord->addSection();

        foreach ($htmlPaths as $index => $path) {
            if (!Storage::disk('public')->exists($path)) continue;

            $html = Storage::disk('public')->get($path);

            // Clean HTML but keep inline styles
            $html = $this->sanitizeHtml($html);

            // Add full HTML content (including tables) directly
            try {
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
            } catch (\Throwable $e) {
                // fallback to plain text if parsing fails
                $section->addText(strip_tags($html));
            }

            // Add page break except for last file
            if ($index < count($htmlPaths) - 1) {
                $section->addPageBreak();
            }
        }

        if (!$fileName) {
            $fileName = 'report_' . time() . '.docx';
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    protected function sanitizeHtml(string $html): string
    {
        // Fix self-closing tags
        $html = preg_replace('/<br(\s*)>/', '<br />', $html);
        $html = preg_replace('/<hr(\s*)>/', '<hr />', $html);
        $html = preg_replace('/<img(.*?)>/', '<img$1 />', $html);

        return trim($html);
    }
}
