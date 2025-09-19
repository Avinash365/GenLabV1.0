<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

// Optional PDF page count support; if library missing we'll skip.

class ReportingLettersController extends Controller
{
    // List uploaded letters for a given job as JSON
    public function index(Request $request)
    {
        $job = trim((string) $request->query('job', ''));
        if ($job === '') {
            return response()->json(['ok' => true, 'count' => 0, 'letters' => []]);
        }

        $safeJob = $this->sanitizeJob($job);
        $dir = "public/letters/{$safeJob}";
        $files = Storage::exists($dir) ? Storage::files($dir) : [];
        $metaPath = $dir.'/_meta.json';
        $meta = [];
        if (Storage::exists($metaPath)) {
            $rawMeta = json_decode(Storage::get($metaPath), true);
            if (is_array($rawMeta)) $meta = $rawMeta;
        }

        $letters = [];
        foreach ($files as $path) {
            $basename = basename($path);
            $url = Storage::url($path);
            $uploadedAt = Carbon::createFromTimestamp(Storage::lastModified($path))->toDateTimeString();
            $pageCount = null;
            if (strtolower(pathinfo($basename, PATHINFO_EXTENSION)) === 'pdf') {
                $pageCount = $this->tryCountPdfPages($path);
            }
            $original = $meta[$basename]['original'] ?? $basename;
            $letters[] = [
                'name' => $original,
                'original_name' => $original,
                'filename' => $basename,
                'url' => $url,
                'encoded_url' => $this->encodeUrlPath($url),
                'download_url' => route('superadmin.reporting.letters.show', ['job' => $safeJob, 'filename' => $basename]),
                'uploaded_at' => $uploadedAt,
                'pages' => $pageCount,
            ];
        }

        return response()->json([
            'ok' => true,
            'count' => count($letters),
            'letters' => $letters,
        ]);
    }

    // Upload one or multiple letters for a job
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'job' => ['required', 'string', 'max:255'],
            'letters' => ['required'],
            'letters.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'], // 20MB each
        ]);

        $job = $this->sanitizeJob($validated['job']);
        $dir = "public/letters/{$job}";

        $uploaded = [];
        $meta = [];
        $metaPath = $dir.'/_meta.json';
        if (Storage::exists($metaPath)) {
            $rawMeta = json_decode(Storage::get($metaPath), true);
            if (is_array($rawMeta)) $meta = $rawMeta;
        }

        foreach ($request->file('letters', []) as $file) {
            if (!$file->isValid()) continue;
            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $base = Str::limit(pathinfo($original, PATHINFO_FILENAME), 100, '');
            $filename = $base . '-' . now()->format('YmdHis') . '-' . Str::random(6) . ($ext ? ".{$ext}" : '');
            $path = $file->storeAs($dir, $filename);
            if ($path) {
                $storedBasename = basename($path);
                $storedUrl = Storage::url($path);
                $pageCount = null;
                if (strtolower($ext) === 'pdf') {
                    $pageCount = $this->tryCountPdfPages($path);
                }
                // Record mapping
                $meta[$storedBasename] = [ 'original' => $original, 'uploaded_at' => now()->toDateTimeString() ];
                $uploaded[] = [
                    'name' => $original,
                    'original_name' => $original,
                    'filename' => $storedBasename,
                    'url' => $storedUrl,
                    'encoded_url' => $this->encodeUrlPath($storedUrl),
                    'download_url' => route('superadmin.reporting.letters.show', ['job' => $job, 'filename' => $storedBasename]),
                    'uploaded_at' => now()->toDateTimeString(),
                    'pages' => $pageCount,
                ];
            }
        }

        // Persist meta mapping
        try { Storage::put($metaPath, json_encode($meta, JSON_PRETTY_PRINT)); } catch (\Throwable $e) {}

        // New total count after upload
        $files = Storage::exists($dir) ? Storage::files($dir) : [];
        return response()->json([
            'ok' => true,
            'uploaded' => $uploaded,
            'count' => count($files),
        ]);
    }

    private function sanitizeJob(string $job): string
    {
        // Allow alphanumerics, dash and underscore to prevent path traversal
        return preg_replace('/[^A-Za-z0-9_\-]/', '-', $job) ?: 'unknown';
    }

    private function tryCountPdfPages(string $storagePath): ?int
    {
        try {
            // Prefer smalot/pdfparser if installed
            if (class_exists('Smalot\\PdfParser\\Parser')) {
                $full = Storage::path($storagePath);
                if (!is_readable($full)) return null;
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($full);
                $details = $pdf->getDetails();
                if (isset($details['Pages'])) {
                    return (int) $details['Pages'];
                }
                // Fallback: count pages via objects
                $pages = $pdf->getPages();
                return count($pages) ?: null;
            }
            // Lightweight manual count (may overcount but good fallback)
            $full = Storage::path($storagePath);
            if (!is_readable($full)) return null;
            $content = @file_get_contents($full);
            if ($content === false) return null;
            if (preg_match_all('/\/Type\s*\/Page[^s]/', $content, $m)) {
                return count($m[0]);
            }
        } catch (Throwable $e) {
            // ignore
        }
        return null;
    }

    private function encodeUrlPath(string $url): string
    {
        try {
            $u = new \Illuminate\Support\Fluent(parse_url($url));
            if (!$u->path) return $url;
            $encodedPath = implode('/', array_map(function ($seg) {
                return rawurlencode(rawurldecode($seg));
            }, explode('/', ltrim($u->path, '/'))));
            $schemeHost = ($u->scheme ?? '') ? ($u->scheme . '://' . $u->host . (isset($u->port) ? ':' . $u->port : '')) : '';
            return $schemeHost . '/' . $encodedPath . (isset($u->query) ? '?' . $u->query : '');
        } catch (\Throwable $e) { return $url; }
    }

    public function show(string $job, string $filename)
    {
        $safeJob = $this->sanitizeJob($job);
        $filename = basename($filename); // prevent traversal
        $path = "public/letters/{$safeJob}/{$filename}";
        if (!\Storage::exists($path)) {
            abort(404);
        }
        $mime = \Storage::mimeType($path) ?: 'application/octet-stream';
        $stream = \Storage::readStream($path);
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"'
        ]);
    }
}
