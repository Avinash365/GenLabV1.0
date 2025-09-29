<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ReportFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Dompdf\Options;
use HTMLPurifier;
use HTMLPurifier_Config;
use App\Services\DocxHtmlConverter;

class ReportFormatContentController extends Controller
{
    protected function purifier(): HTMLPurifier
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.SafeIframe', true);
        $config->set('Cache.DefinitionImpl', null);
        return new HTMLPurifier($config);
    }

    public function edit(ReportFormat $reportFormat)
    {
        // If no HTML stored yet, attempt on-demand conversion from original file
        if((!$reportFormat->body_html || trim($reportFormat->body_html)==='') && $reportFormat->stored_file_name){
            $ext = strtolower(pathinfo($reportFormat->stored_file_name, PATHINFO_EXTENSION));
            if(in_array($ext, ['docx','doc','odt'])){
                try {
                    $relative = 'report-formats/'.$reportFormat->stored_file_name;
                    // Primary expected location (storage/app/public/...)
                    $fullPath = Storage::disk('public')->path($relative);
                    // If disk path does not exist, attempt typical symlink target under public/storage
                    if(!is_file($fullPath)){
                        $alt = public_path('storage/'.$relative);
                        if(is_file($alt)) $fullPath = $alt;
                    }
                    if(is_file($fullPath)){
                        $converter = new DocxHtmlConverter();
                        $html = $converter->convert($fullPath);
                        if($html && trim($html)!==''){
                            $reportFormat->body_html = $html;
                            $reportFormat->save();
                        } else {
                            // temporary debug marker
                            $reportFormat->body_html = $reportFormat->body_html ?? '<p><!-- conversion-empty --></p>';
                        }
                    }
                } catch(\Throwable $e) {
                    // silent; user will see empty editor
                }
            }
        }
        if(request()->wantsJson()){
            return response()->json([
                'id' => $reportFormat->id,
                'format_name' => $reportFormat->format_name,
                'version' => $reportFormat->version,
                'body_html' => $reportFormat->body_html ?? ''
            ]);
        }
        return view('superadmin.reporting.report-formats.edit-content', compact('reportFormat'));
    }

    public function update(Request $request, ReportFormat $reportFormat)
    {
        $data = $request->validate([
            'body_html' => 'required|string'
        ]);
        $clean = $this->purifier()->purify($data['body_html']);
        if($clean !== $reportFormat->body_html){
            $reportFormat->body_html = $clean;
            $reportFormat->version = ($reportFormat->version ?? 1) + 1;
            $reportFormat->save();
        }
        return response()->json(['ok'=>true,'version'=>$reportFormat->version]);
    }

    public function exportPdf(ReportFormat $reportFormat)
    {
        $html = $reportFormat->body_html ?: '<p>No content.</p>';
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml('<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:12px;}</style></head><body>'.$html.'</body></html>');
        $dompdf->setPaper('A4');
        $dompdf->render();
        $fileName = Str::slug($reportFormat->format_name.'-v'.$reportFormat->version).'.pdf';
        return response($dompdf->output(),200,[
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        ]);
    }
}
