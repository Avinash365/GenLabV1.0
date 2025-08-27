<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LabAnalystsController extends Controller
{
    // ...existing code...

    public function render(Request $request)
    {
        $request->validate([
            'f' => 'required|string',
            'reference_no' => 'nullable|string',
            'job_card_no' => 'nullable|string',
            'download' => 'nullable'
        ]);

        // Call preview() directly to preserve current auth/session context
        try {
            $preview = $this->preview($request);
        } catch (\Throwable $e) {
            return response('Failed to generate document: ' . $e->getMessage(), 500);
        }

        // Normalize preview output to HTML string
        if ($preview instanceof Response) {
            $html = $preview->getContent();
        } elseif (is_object($preview) && method_exists($preview, 'render')) {
            $html = $preview->render();
        } else {
            $html = (string) $preview;
        }

        // Extract body content if present
        $body = $html;
        if (preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $m)) {
            $body = $m[1];
        }

        $title = 'Report';
        $ref = trim((string) $request->input('reference_no', ''));
        $job = trim((string) $request->input('job_card_no', ''));
        if ($ref) {
            $title = 'Report-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $ref);
        } elseif ($job) {
            $title = 'Report-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $job);
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $doc = "<!DOCTYPE html>\n<html>\n<head>\n<meta charset=\"utf-8\">\n<title>{$safeTitle}</title>\n<style>body{font-family:Arial,Helvetica,sans-serif;} table{border-collapse:collapse;} th,td{border:1px solid #000; padding:4px;}</style>\n</head>\n<body>" . $body . "</body>\n</html>";

        $filename = Str::limit($title, 100, '') . '.doc';

        return response($doc, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}