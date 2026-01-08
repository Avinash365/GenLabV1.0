<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;
use App\Models\BookingItem;
use App\Models\NewBooking;
use App\Jobs\SendMarketingNotificationJob; 

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

        [$safeJob, $resolvedReference] = $this->resolveLetterKey($job);
        $fallbackKey = $this->sanitizeJob($job);
        $dirKeys = array_values(array_unique(array_filter([$safeJob, $fallbackKey])));

        $lettersMap = [];

        foreach ($dirKeys as $dirKey) {
            $dir = "public/letters/{$dirKey}";
            if (!Storage::exists($dir)) {
                continue;
            }

            $files = Storage::files($dir);
            $metaPath = $dir.'/_meta.json';
            $meta = [];
            if (Storage::exists($metaPath)) {
                $rawMeta = json_decode(Storage::get($metaPath), true);
                if (is_array($rawMeta)) $meta = $rawMeta;
            }

            foreach ($files as $path) {
                $basename = basename($path);
                if ($basename === '_meta.json' || str_starts_with($basename, '_')) {
                    continue;
                }
                $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
                if ($ext && !in_array($ext, $allowed, true)) {
                    continue;
                }

                $url = Storage::url($path);
                $uploadedAt = $meta[$basename]['uploaded_at'] ?? Carbon::createFromTimestamp(Storage::lastModified($path))->toDateTimeString();
                $uploaderName = $meta[$basename]['uploader_name'] ?? null;
                $pageCount = null;
                if ($ext === 'pdf') {
                    $pageCount = $this->tryCountPdfPages($path);
                }
                $original = $meta[$basename]['original'] ?? $basename;
                $lettersMap[$dirKey.'|'.$basename] = [
                    'name' => $original,
                    'original_name' => $original,
                    'filename' => $basename,
                    'url' => $url,
                    'encoded_url' => $this->encodeUrlPath($url),
                    'download_url' => route('superadmin.reporting.letters.show', ['job' => $dirKey, 'filename' => $basename]),
                    'uploaded_at' => $uploadedAt,
                    'pages' => $pageCount,
                    'uploader_name' => $uploaderName,
                ];
            }
        }

        $letters = array_values($lettersMap);

        return response()->json([
            'ok' => true,
            'reference' => $resolvedReference,
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
            'letters.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:101200'], // 20MB each
        ]); 

        [$jobKey] = $this->resolveLetterKey($validated['job']);
        $fallbackKey = $this->sanitizeJob($validated['job']);
        $dir = "public/letters/{$jobKey}";

        $uploaded = [];
        $meta = [];
        $metaPath = $dir.'/_meta.json';
        if (Storage::exists($metaPath)) {
            $rawMeta = json_decode(Storage::get($metaPath), true);
            if (is_array($rawMeta)) $meta = $rawMeta;
        }

        // Who is uploading?
        $user = auth()->user() ?: auth('admin')->user();
        $uploaderName = $user->name ?? ($user->username ?? ($user->email ?? null));
        $uploaderId = $user->id ?? null;

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
                $meta[$storedBasename] = [
                    'original' => $original,
                    'uploaded_at' => now()->toDateTimeString(),
                    'uploader_id' => $uploaderId,
                    'uploader_name' => $uploaderName,
                ];
                $uploaded[] = [
                    'name' => $original,
                    'original_name' => $original,
                    'filename' => $storedBasename,
                    'url' => $storedUrl,
                    'encoded_url' => $this->encodeUrlPath($storedUrl),
                    'download_url' => route('superadmin.reporting.letters.show', ['job' => $jobKey, 'filename' => $storedBasename]),
                    'uploaded_at' => now()->toDateTimeString(),
                    'pages' => $pageCount,
                    'uploader_name' => $uploaderName,
                ];
            }
        }

        // Persist meta mapping
        try { Storage::put($metaPath, json_encode($meta, JSON_PRETTY_PRINT)); } catch (\Throwable $e) {}

        // New total count after upload (ignore meta and hidden files)
        $dirKeys = array_values(array_unique(array_filter([$jobKey, $fallbackKey])));
        $files = [];
        foreach ($dirKeys as $dirKey) {
            $target = "public/letters/{$dirKey}";
            if (!Storage::exists($target)) {
                continue;
            }
            $files = array_merge($files, array_values(array_filter(Storage::files($target), function ($p) {
                $b = basename($p);
                $ext = strtolower(pathinfo($b, PATHINFO_EXTENSION));
                if ($b === '_meta.json' || str_starts_with($b, '_')) return false;
                $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
                return $ext && in_array($ext, $allowed, true);
            })));
        } 

        $booking = NewBooking::where('reference_no', $request->job)->firstOrFail();
        $marketingUser = $booking->marketingPerson;

        if ($marketingUser) {

            // Build clickable file links
            $fileLinks = [];
            foreach ($uploaded as $file) {
                $fileLinks[] = $file['download_url'];
            }

            $linksText = implode("\n", array_map(fn($url, $index) => ($index+1) . ". " . $url, $fileLinks, array_keys($fileLinks)));

            $reportCount = count($files);

            SendMarketingNotificationJob::dispatch(
                $marketingUser,
                "New Report Uploaded",
                "{$booking->client_name} With Ref_no: {$booking->reference_no}.\n\n"
                ."Total Reports: {$reportCount}\n\n"
                ."Download Links:\n{$linksText}",
                [
                    "total_reports" => $reportCount,
                    "client_name"   => $booking->client->name ?? null,
                    "reference_no"  => $booking->reference_no,
                ]
            );
        }
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

    private function resolveLetterKey(string $input): array
    {
        $needle = trim($input);
        if ($needle === '') {
            return [$this->sanitizeJob($needle), null];
        }

        $booking = NewBooking::query()
            ->where('reference_no', $needle)
            ->orWhere('reference_no', 'like', "%{$needle}%")
            ->latest('id')
            ->first();

        if (!$booking) {
            $item = BookingItem::query()
                ->with('booking')
                ->where('job_order_no', $needle)
                ->orWhere('job_order_no', 'like', "%{$needle}%")
                ->latest('id')
                ->first();

            if ($item && $item->booking) {
                $booking = $item->booking;
            }
        }

        if ($booking) {
            $ref = trim((string) $booking->reference_no);
            return [$this->sanitizeJob($ref), $ref];
        }

        return [$this->sanitizeJob($needle), $needle];
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
        [$safeJob] = $this->resolveLetterKey($job);
        $candidates = array_values(array_unique(array_filter([$safeJob, $this->sanitizeJob($job)])));

        $filename = basename($filename); // prevent traversal
        if ($filename === '_meta.json' || str_starts_with($filename, '_')) {
            abort(404);
        }

        foreach ($candidates as $key) {
            $path = "public/letters/{$key}/{$filename}";
            if (!\Storage::exists($path)) {
                continue;
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

        abort(404);
    }

    // Delete a specific letter/file
    public function destroy(Request $request, string $job, string $filename)
    {
        [$safeJob] = $this->resolveLetterKey($job);
        // Also check if raw job was used as a key
        $fallbackKey = $this->sanitizeJob($job);
        $candidates = array_values(array_unique(array_filter([$safeJob, $fallbackKey])));

        $filename = basename($filename);
        if ($filename === '_meta.json' || str_starts_with($filename, '_')) {
             return response()->json(['ok' => false, 'message' => 'Cannot delete system files'], 403);
        }

        $deleted = false;
        foreach ($candidates as $key) {
            $dir = "public/letters/{$key}";
            $path = "{$dir}/{$filename}";
            if (Storage::exists($path)) {
                Storage::delete($path);
                $deleted = true;

                // Also try to cleanup meta
                $metaPath = $dir.'/_meta.json';
                if (Storage::exists($metaPath)) {
                    try {
                        $rawMeta = json_decode(Storage::get($metaPath), true);
                        if (is_array($rawMeta) && isset($rawMeta[$filename])) {
                            unset($rawMeta[$filename]);
                            Storage::put($metaPath, json_encode($rawMeta, JSON_PRETTY_PRINT));
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        if ($deleted) {
            return response()->json(['ok' => true, 'message' => 'File deleted']);
        }

        return response()->json(['ok' => false, 'message' => 'File not found'], 404);
    }
}