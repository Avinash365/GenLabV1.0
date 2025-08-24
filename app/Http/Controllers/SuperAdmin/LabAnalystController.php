<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LabReportOverride;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LabAnalystController extends Controller
{
    private string $reportDir;

    public function __construct()
    {
        $this->reportDir = base_path('Reportfrmt');
    }

    public function index()
    {
        $files = $this->getReportFiles();
        return view('superadmin.labanalysts.index', compact('files'));
    }

    public function view(Request $request)
    {
        $encoded = $request->query('f');
        $file = $this->decodeFilename($encoded);
        $files = $this->getReportFiles();
        if (!$file || !in_array($file, $files, true)) {
            // If invalid or missing, show picker only
            return view('superadmin.labanalysts.index', compact('files'));
        }
        $title = trim(pathinfo($file, PATHINFO_FILENAME));
        $title = preg_replace('/[_-]+/', ' ', $title);
        return view('superadmin.labanalysts.show', [
            'title' => $title,
            'encoded' => $encoded,
            'file' => $file,
            'reportHtml' => '',
            'reference_no' => $request->query('reference_no'),
            'job_card_no' => $request->query('job_card_no'),
        ]);
    }

    public function render(Request $request): Response
    {
        $encoded = $request->query('f');
        $file = $this->decodeFilename($encoded);
        if (!$file) {
            abort(404);
        }

        $fullPath = realpath($this->reportDir . DIRECTORY_SEPARATOR . $file);
        $base = realpath($this->reportDir);
        if (!$fullPath || strncmp($fullPath, $base, strlen($base)) !== 0 || !is_file($fullPath)) {
            abort(404);
        }

        // Optionally make the editable fields available to included files
        $Date_of_Start_of_Analysis = $request->query('start_date');
        $Date_of_Completion_of_Analysis = $request->query('completion_date');
        $Results = $request->query('results');
        $Conformity = $request->query('conformity');
        // Pass through legacy expected GET param for data binding
        if ($request->filled('job_card_no')) {
            $_GET['JOB_CARD_NO'] = $request->query('job_card_no');
        }
        // If a reference number (or derived from JOB_CARD_NO) is supplied and an override exists, inject those values
        $ref = $request->query('reference_no');
        if (!$ref && $request->filled('job_card_no')) {
            try {
                $ref = DB::table('nonulr')->where('JOB_CARD_NO', $request->query('job_card_no'))->value('ULR_NO');
            } catch (\Throwable $e) {
                $ref = null;
            }
        }
        if ($ref) {
            $ov = LabReportOverride::where('format', $file)->where('reference_no', $ref)->first();
            if ($ov) {
                $Date_of_Start_of_Analysis = $Date_of_Start_of_Analysis ?: $ov->start_date;
                $Date_of_Completion_of_Analysis = $Date_of_Completion_of_Analysis ?: $ov->completion_date;
                $Results = $Results ?: $ov->results;
                $Conformity = $Conformity ?: $ov->conformity;
            }
        }

        // Make relative requires (e.g., 'function.php') work and bypass legacy session gate
        $cwd = getcwd();
        $dir = dirname($fullPath);
        chdir($dir);
        if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['login'] = true;

        ob_start();
        try {
            include $fullPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            abort(500, 'Error rendering report: ' . $e->getMessage());
        }
        // Restore working directory
        if ($cwd) {
            @chdir($cwd);
        }
        $html = ob_get_clean();
        $isDownload = $request->boolean('download');
        // For AAC_BLOCK, inject only Results/Conformity so dates and headers come from DB
        if (strcasecmp($file, 'AAC_BLOCK.legacy.php') === 0) {
            $html = $this->injectEditableFields($html, null, null, $Results, $Conformity, true);
        } else {
            $html = $this->transformHtml($html, $Date_of_Start_of_Analysis, $Date_of_Completion_of_Analysis, $Results, $Conformity, $file, $isDownload);
        }

        // Inject inline editors for preview (not for downloads)
        if (!$isDownload) {
            $ref = $request->query('reference_no');
            $job = $request->query('job_card_no');
            $html = $this->injectInlineEditors($html, $encoded, $ref, $job, $file, $Results, $Conformity);
        }

        $response = response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-Report-File', $file);

        if ($request->boolean('download')) {
            // Serve as Word document and force download to avoid inline handlers blocking
            $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($file, PATHINFO_FILENAME)) ?: 'Report';
            // Remove any legacy headers set inside included templates
            if (function_exists('header_remove')) {
                @header_remove('Content-Type');
                @header_remove('content-type');
                @header_remove('Content-Disposition');
            }
            $response->header('Content-Type', 'application/msword');
            $response->header('Content-Disposition', 'attachment; filename="' . $safeName . '.doc"');
        }
        else {
            // Remove any headers set by legacy files that would force download
            if (function_exists('header_remove')) {
                @header_remove('Content-Type');
                @header_remove('content-type');
                @header_remove('Content-Disposition');
            }
            $response->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }

    public function pdf(Request $request)
    {
        // Validate and locate file
        $encoded = $request->query('f');
        $file = $this->decodeFilename($encoded);
        if (!$file) abort(404);
        $fullPath = realpath($this->reportDir . DIRECTORY_SEPARATOR . $file);
        $base = realpath($this->reportDir);
        if (!$fullPath || strncmp($fullPath, $base, strlen($base)) !== 0 || !is_file($fullPath)) {
            abort(404);
        }

        // Resolve overrides by reference/job card
        $ref = $request->query('reference_no');
        if (!$ref && $request->filled('job_card_no')) {
            try { $ref = DB::table('nonulr')->where('JOB_CARD_NO', $request->query('job_card_no'))->value('ULR_NO'); } catch (\Throwable $e) { $ref = null; }
        }
        $ov = null;
        if ($ref) {
            $ov = LabReportOverride::where('format', $file)->where('reference_no', $ref)->first();
        }
        $Date_of_Start_of_Analysis = $ov->start_date ?? $request->query('start_date');
        $Date_of_Completion_of_Analysis = $ov->completion_date ?? $request->query('completion_date');
        $Results = $ov->results ?? $request->query('results');
        $Conformity = $ov->conformity ?? $request->query('conformity');

        // Prepare legacy environment
        if ($request->filled('job_card_no')) {
            $_GET['JOB_CARD_NO'] = $request->query('job_card_no');
        }
        if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['login'] = true;
        $cwd = getcwd();
        chdir(dirname($fullPath));

        ob_start();
        try {
            include $fullPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            // Fallback to static HTML (no PHP) for PDF to avoid 500s
            $html = $this->staticHtmlFromFile($fullPath);
            if ($cwd) @chdir($cwd);
            // Ensure wrapper
            if (stripos($html, '<html') === false) {
                $html = '<html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
            }
            // Continue to PDF generation with fallback
            $body = $this->extractBody($html);
            $pdfCss = <<<CSS
<style>
@page { margin: 10mm 8mm; }
* { overflow: visible !important; }
html, body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
img { max-width: 100% !important; height: auto !important; }
table { width: 100% !important; max-width: 100% !important; border-collapse: collapse; }
th, td { word-wrap: break-word; white-space: normal; }
table, tr { page-break-inside: auto; }
tr, td, th { page-break-inside: avoid; page-break-after: auto; }
.page-header, #gl-save-bar { display: none !important; }
</style>
CSS;
            $htmlForPdf = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $pdfCss . $body;
            $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 96,
                'defaultFont' => 'DejaVu Sans',
            ])->setPaper('a4', 'landscape')->loadHTML($htmlForPdf);
            $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($file, PATHINFO_FILENAME)) ?: 'Report';
            return $pdf->download($safeName . '.pdf');
        }
        if ($cwd) @chdir($cwd);
        $html = ob_get_clean();

        // Transform to include overrides where appropriate
        if (strcasecmp($file, 'AAC_BLOCK.legacy.php') === 0) {
            $html = $this->injectEditableFields($html, null, null, $Results, $Conformity, true);
        } else {
            // Treat as download to avoid injecting inline editors or preview tweaks
            $html = $this->transformHtml($html, $Date_of_Start_of_Analysis, $Date_of_Completion_of_Analysis, $Results, $Conformity, $file, true);
        }

        $body = $this->extractBody($html);
    $pdfCss = <<<CSS
<style>
@page { margin: 10mm 8mm; }
* { overflow: visible !important; }
html, body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
img { max-width: 100% !important; height: auto !important; }
table { width: 100% !important; max-width: 100% !important; border-collapse: collapse; }
th, td { word-wrap: break-word; white-space: normal; }
table, tr { page-break-inside: auto; }
tr, td, th { page-break-inside: avoid; page-break-after: auto; }
.page-header, #gl-save-bar { display: none !important; }
</style>
CSS;
    $htmlForPdf = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . $pdfCss . $body;
    $pdf = Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 96,
                'defaultFont' => 'DejaVu Sans',
            ])
        ->setPaper('a4', 'landscape')
            ->loadHTML($htmlForPdf);
        if (method_exists($pdf, 'setBasePath')) {
            $pdf->setBasePath(public_path());
        }
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($file, PATHINFO_FILENAME)) ?: 'Report';
        return $pdf->download($safeName . '.pdf');
    }

    private function transformHtml(string $html, ?string $start, ?string $completion, ?string $results, ?string $conformity, string $file, bool $isDownload): string
    {
        // For exact legacy output (AAC_BLOCK.legacy.php) or when downloading, do not alter HTML
        if (strcasecmp($file, 'AAC_BLOCK.legacy.php') === 0 || $isDownload) {
            return $html;
        }
        $safe = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($start) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Date of Start of Analysis\s*<\/th>\s*<td[^>]*>[^<]*<\/td>\s*<td[^>]*>)(.*?)(<\/td>)/is',
                '$1' . $safe($start) . '$3',
                $html,
                2 // appears twice across pages
            ) ?? $html;
        }

        if ($completion) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Date of Completion of Analysis\s*<\/th>\s*<td[^>]*>[^<]*<\/td>\s*<td[^>]*>)(.*?)(<\/td>)/is',
                '$1' . $safe($completion) . '$3',
                $html,
                2
            ) ?? $html;
        }

        // Optional simple fill for Results/Conformity: if header row contains these labels,
        // fill the first empty cell under each as a quick preview.
        if ($results) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Results\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(\s*<\/td>)/is',
                '$1$2' . $safe($results) . '$3',
                $html,
                1
            ) ?? $html;
        }
        if ($conformity) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Conformity\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(\s*<\/td>)/is',
                '$1$2' . $safe($conformity) . '$3',
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }

    public function preview(Request $request): Response
    {
        $encoded = $request->query('f');
        $file = $this->decodeFilename($encoded);
        if (!$file) abort(404);

        $fullPath = realpath($this->reportDir . DIRECTORY_SEPARATOR . $file);
        $base = realpath($this->reportDir);
        if (!$fullPath || strncmp($fullPath, $base, strlen($base)) !== 0 || !is_file($fullPath)) {
            abort(404);
        }

        // For AAC_BLOCK.legacy.php, render static HTML without executing PHP for guaranteed visibility
        if (strcasecmp($file, 'AAC_BLOCK.legacy.php') === 0) {
            // Execute legacy template with DB-backed function.php and show inline (no Word headers)
            if ($request->filled('job_card_no')) {
                $_GET['JOB_CARD_NO'] = $request->query('job_card_no');
            }
            if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $_SESSION['login'] = true;
            $cwd = getcwd();
            chdir(dirname($fullPath));
            ob_start();
            try {
                include $fullPath;
            } catch (\Throwable $e) {
                // Fall back to static HTML if execution fails
                $fallback = $this->staticHtmlFromFile($fullPath);
                if ($cwd) @chdir($cwd);
                $note = '<div style="background:#fff3cd;border:1px solid #ffeeba;padding:8px 12px;margin:8px 0;color:#856404;">Preview fallback used. Provide a valid Job Card No. to load data.</div>';
                return response($note . $fallback)->header('Content-Type', 'text/html; charset=UTF-8');
            }
            if ($cwd) @chdir($cwd);
            $html = ob_get_clean();
            // Remove any preamble before <html>
            $html = preg_replace('/^.*?<html/s', '<html', $html) ?? $html;

            // Merge saved overrides (by reference_no or derived from JOB_CARD_NO)
            $ov = null;
            $ref = $request->query('reference_no');
            if (!$ref && $request->filled('job_card_no')) {
                try { $ref = DB::table('nonulr')->where('JOB_CARD_NO', $request->query('job_card_no'))->value('ULR_NO'); } catch (\Throwable $e) { $ref = null; }
            }
            if ($ref) {
                $ov = LabReportOverride::where('format', $file)->where('reference_no', $ref)->first();
            }
            $results = $request->query('results') ?: ($ov->results ?? null);
            $conformity = $request->query('conformity') ?: ($ov->conformity ?? null);

            // Inject only Results and Conformity (dates remain from DB)
            $html = $this->injectEditableFields($html, null, null, $results, $conformity, true);
            // Add inline editors for in-report editing
            $html = $this->injectInlineEditors($html, $encoded, $request->query('reference_no'), $request->query('job_card_no'), $file, $results, $conformity);
            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        // Prepare environment similar to render()
        $Date_of_Start_of_Analysis = $request->query('start_date');
        $Date_of_Completion_of_Analysis = $request->query('completion_date');
        $Results = $request->query('results');
        $Conformity = $request->query('conformity');
        if ($request->filled('job_card_no')) {
            $_GET['JOB_CARD_NO'] = $request->query('job_card_no');
        }
        if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['login'] = true;

        // Execute template but strip legacy headers for preview
        $cwd = getcwd();
        chdir(dirname($fullPath));
        ob_start();
        try {
            include $fullPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            $fallback = $this->staticHtmlFromFile($fullPath);
            // Remove legacy headers if present and return graceful fallback
            $fallback = preg_replace('/^.*?<html/s', '<html', $fallback) ?? $fallback;
            return response($fallback)->header('Content-Type', 'text/html; charset=UTF-8');
        }
        if ($cwd) @chdir($cwd);
        $html = ob_get_clean();

        // Remove legacy headers if they got buffered into output accidentally
        $html = preg_replace('/^.*?<html/s', '<html', $html) ?? $html;

    // Add inline editors for in-report editing
    $html = $this->injectInlineEditors($html, $encoded, $request->query('reference_no'), $request->query('job_card_no'), $file, $Results, $Conformity);
    return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function staticHtmlFromFile(string $path): string
    {
        $content = @file_get_contents($path) ?: '';
        // Remove PHP blocks entirely
        $content = preg_replace('/<\?php[\s\S]*?\?>/i', '', $content) ?? $content;
        $content = preg_replace('/<\?=[\s\S]*?\?>/i', '', $content) ?? $content;
        // Keep only from first <html or <!DOCTYPE
        if (preg_match('/<!DOCTYPE|<html/i', $content, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            $content = substr($content, $pos);
        }
        // Ensure valid content wrapper
        if (stripos($content, '<html') === false) {
            $content = '<html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>';
        }
        return $content;
    }

    private function extractBody(string $html): string
    {
        if (preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $m)) {
            return $m[1];
        }
        // Fallback: remove everything before first <table> to reduce duplicated headers
        $pos = stripos($html, '<table');
        if ($pos !== false) {
            return substr($html, $pos);
        }
        return $html;
    }

    private function injectEditableFields(string $html, ?string $start, ?string $completion, ?string $results, ?string $conformity, bool $skipDates = false): string
    {
        $safe = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (!$skipDates && $start) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Date of Start of Analysis\s*<\/th>\s*<td[^>]*>[^<]*<\/td>\s*<td[^>]*>)(.*?)(<\/td>)/is',
                '$1' . $safe($start) . '$3',
                $html,
                2
            ) ?? $html;
        }
        if (!$skipDates && $completion) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Date of Completion of Analysis\s*<\/th>\s*<td[^>]*>[^<]*<\/td>\s*<td[^>]*>)(.*?)(<\/td>)/is',
                '$1' . $safe($completion) . '$3',
                $html,
                2
            ) ?? $html;
        }
        if ($results) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Results\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(\s*<\/td>)/is',
                '$1$2' . $safe($results) . '$3',
                $html,
                1
            ) ?? $html;
        }
        if ($conformity) {
            $html = preg_replace(
                '/(<th[^>]*>\s*Conformity\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(\s*<\/td>)/is',
                '$1$2' . $safe($conformity) . '$3',
                $html,
                1
            ) ?? $html;
        }
        return $html;
    }

        private function injectInlineEditors(string $html, ?string $encoded, ?string $referenceNo, ?string $jobCardNo, string $file, ?string $results, ?string $conformity): string
        {
                // Wrap Results cell with a contenteditable div
                $resultsEditor = function($match) use ($results) {
                        $content = $results !== null && $results !== '' ? htmlspecialchars($results, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : trim($match[3]);
                        return $match[1] . '<div id="gl-results-editor" contenteditable="true" style="min-height:2.5em; outline:1px dashed #0d6efd; padding:6px;">' . $content . '</div>' . $match[4];
                };
                $html = preg_replace_callback(
                        '/(<th[^>]*>\s*Results\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(.*?)(<\/td>)/is',
                        $resultsEditor,
                        $html,
                        1
                ) ?? $html;

                // Wrap Conformity cell with a contenteditable div
                $conformityEditor = function($match) use ($conformity) {
                        $content = $conformity !== null && $conformity !== '' ? htmlspecialchars($conformity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : trim($match[3]);
                        return $match[1] . '<div id="gl-conformity-editor" contenteditable="true" style="min-height:2.5em; outline:1px dashed #198754; padding:6px;">' . $content . '</div>' . $match[4];
                };
                $html = preg_replace_callback(
                        '/(<th[^>]*>\s*Conformity\s*<\/th>.*?<tr>.*?)(<td[^>]*>)(.*?)(<\/td>)/is',
                        $conformityEditor,
                        $html,
                        1
                ) ?? $html;

                // Add floating save button and script
                $saveUrl = route('superadmin.labanalysts.save');
                $csrf = csrf_token();
                $encoded = $encoded ?: '';
                $referenceNo = $referenceNo ?: '';
                $jobCardNo = $jobCardNo ?: '';

                $injected = <<<HTML
<style>
#gl-save-bar{position:fixed;bottom:16px;right:16px;z-index:2147483647;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.15);padding:10px 12px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
#gl-save-bar button{background:#0d6efd;color:#fff;border:0;border-radius:6px;padding:6px 10px;cursor:pointer}
#gl-save-msg{margin-left:8px;color:#198754}
</style>
<div id="gl-save-bar">
    <button id="gl-save-btn" type="button">Save Changes</button>
    <span id="gl-save-msg"></span>
</div>
<script>
    (function(){
        const btn = document.getElementById('gl-save-btn');
        if(!btn) return;
        const save = async () => {
            const resultsEl = document.getElementById('gl-results-editor');
            const conformityEl = document.getElementById('gl-conformity-editor');
            const payload = {
                f: '$encoded',
                reference_no: '$referenceNo',
                job_card_no: '$jobCardNo',
                results: resultsEl ? resultsEl.innerText.trim() : '',
                conformity: conformityEl ? conformityEl.innerText.trim() : ''
            };
            try{
                const res = await fetch('$saveUrl', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'$csrf'},
                    body: JSON.stringify(payload)
                });
                const msg = document.getElementById('gl-save-msg');
                if(res.ok){ msg.textContent = 'Saved'; setTimeout(()=>msg.textContent='', 2000); }
                else { const t = await res.text(); msg.textContent = 'Save failed'; console.error(t); }
            }catch(err){ console.error(err); }
        };
        btn.addEventListener('click', save);
    })();
</script>
HTML;

                // Append before closing body or at end
                if (stripos($html, '</body>') !== false) {
                        $html = preg_replace('/<\/body>/i', $injected . '</body>', $html, 1) ?? ($html . $injected);
                } else {
                        $html .= $injected;
                }
                return $html;
        }

    public function save(Request $request): Response
    {
        $data = $request->validate([
            'f' => 'required|string',
            'reference_no' => 'nullable|string',
            'job_card_no' => 'nullable|string',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'results' => 'nullable|string',
            'conformity' => 'nullable|string',
        ]);
        $file = $this->decodeFilename($data['f']);
        if (!$file) abort(422, 'Invalid format');

        // Derive reference number from DB if not provided, prefer ULR_NO
        if (empty($data['reference_no']) && !empty($data['job_card_no'])) {
            try {
                $row = DB::table('nonulr')->where('JOB_CARD_NO', $data['job_card_no'])->first();
                if ($row && isset($row->ULR_NO)) {
                    $data['reference_no'] = (string)$row->ULR_NO;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if (empty($data['reference_no'])) {
            abort(422, 'Reference No. is required');
        }

    $ov = LabReportOverride::updateOrCreate(
            ['format' => $file, 'reference_no' => $data['reference_no']],
            [
                'start_date' => $data['start_date'] ?? null,
                'completion_date' => $data['completion_date'] ?? null,
                'results' => $data['results'] ?? null,
                'conformity' => $data['conformity'] ?? null,
                'updated_by' => Auth::id(),
            ]
        );
        if (!$ov->created_by) {
            $ov->created_by = Auth::id();
            $ov->save();
        }
        return response()->json(['status' => 'ok']);
    }

    private function getReportFiles(): array
    {
        if (!is_dir($this->reportDir)) {
            return [];
        }
        $paths = File::files($this->reportDir);
        $files = [];
        foreach ($paths as $path) {
            $name = $path->getFilename();
            if (preg_match('/\.php$/i', $name)) {
                $files[] = $name;
            }
        }
        sort($files, SORT_NATURAL | SORT_FLAG_CASE);
        return $files;
    }

    private function encodeFilename(string $filename): string
    {
        return rtrim(strtr(base64_encode($filename), '+/', '-_'), '=');
    }

    private function decodeFilename(?string $encoded): ?string
    {
        if (!$encoded) return null;
        $remainder = strlen($encoded) % 4;
        if ($remainder) {
            $encoded .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($decoded === false) return null;
        return $decoded;
    }
}
