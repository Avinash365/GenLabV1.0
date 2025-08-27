<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        $letters = [];
        foreach ($files as $path) {
            $letters[] = [
                'name' => basename($path),
                'filename' => basename($path),
                'url' => Storage::url($path),
                'uploaded_at' => Carbon::createFromTimestamp(Storage::lastModified($path))->toDateTimeString(),
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
        foreach ($request->file('letters', []) as $file) {
            if (!$file->isValid()) continue;
            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $base = Str::limit(pathinfo($original, PATHINFO_FILENAME), 100, '');
            $filename = $base . '-' . now()->format('YmdHis') . '-' . Str::random(6) . ($ext ? ".{$ext}" : '');
            $path = $file->storeAs($dir, $filename);
            if ($path) {
                $uploaded[] = [
                    'name' => $original,
                    'filename' => basename($path),
                    'url' => Storage::url($path),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

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
}
