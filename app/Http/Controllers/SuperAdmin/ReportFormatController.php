<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportFormat;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\DocxHtmlConverter;

class ReportFormatController extends Controller
{
    public function index()
    {
        $query = ReportFormat::query()->latest();
        if (request()->wantsJson()) {
            return $query->get()->map(function($f){
                return [
                    'id' => $f->id,
                    'format_name' => $f->format_name,
                    'is_code' => $f->is_code,
                    'sample' => $f->sample,
                    'file_name' => $f->original_file_name,
                    'url' => $f->file_url,
                    'uploaded_at' => optional($f->created_at)->format('d M Y'),
                ];
            });
        }
        $formats = $query->paginate(25);
        return view('superadmin.reporting.report-formats.index', compact('formats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'format_name' => 'required|string|max:255',
            'is_code' => 'nullable|string|max:255',
            'sample' => 'nullable|string|max:255',
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $validated['file'];
        $storedFileName = uniqid('format_').'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('report-formats', $storedFileName, 'public');

        // Only set uploaded_by if the authenticated principal is an App\Models\User
        $authUser = auth()->user();
        $uploadedBy = ($authUser instanceof \App\Models\User) ? $authUser->getKey() : null;        

        $bodyHtml = null;
        $ext = strtolower($file->getClientOriginalExtension());
        if(in_array($ext, ['docx','doc','odt'])){
            try {
                $converter = new DocxHtmlConverter();
                $fullPath = Storage::disk('public')->path($path);
                $bodyHtml = $converter->convert($fullPath);
            } catch(\Throwable $e){ $bodyHtml = null; }
        }

        $format = ReportFormat::create([
            'format_name' => $validated['format_name'],
            'is_code' => $validated['is_code'] ?? null,
            'sample' => $validated['sample'] ?? null,
            'stored_file_name' => $storedFileName,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by' => $uploadedBy,
            'body_html' => $bodyHtml,
        ]);

        return redirect()->route('superadmin.reporting.report-formats.index')
            ->with('success', 'Report format uploaded successfully.');
    }

    public function show(ReportFormat $reportFormat)
    {
        // stream file if exists
        $disk = Storage::disk('public');
        $path = 'report-formats/'.$reportFormat->stored_file_name;
        if (!$disk->exists($path)) {
            abort(404);
        }
        $mime = $reportFormat->mime_type ?: $disk->mimeType($path);
        return new StreamedResponse(function () use ($disk, $path) {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$reportFormat->original_file_name.'"'
        ]);
    }
}
