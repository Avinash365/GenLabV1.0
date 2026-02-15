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
use App\Models\SiteSetting;
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

        \Illuminate\Support\Facades\Log::info('Upload: resolving marketing person', [
            'booking_ref' => $request->job,
            'marketing_user_found' => $marketingUser ? 'yes' : 'no',
            'marketing_user_id' => $marketingUser->id ?? null
        ]);

        if ($marketingUser) {

            // Build clickable file links
            $fileLinks = [];
            foreach ($uploaded as $file) {
                // Use the public route for WhatsApp links so they don't require login
                $fileLinks[] = route('public.reports.download', [
                    'job' => $jobKey, 
                    'filename' => $file['filename']
                ]);
            }

            // Format links: if multiple, use numbered list. If single, just the URL.
            if (count($fileLinks) > 1) {
                $linksText = implode("\n", array_map(fn($url, $index) => ($index+1) . ". " . $url, $fileLinks, array_keys($fileLinks)));
            } else {
                $linksText = $fileLinks[0] ?? '';
            }

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

            // Send WhatsApp Notification
            try {
                $waPhone = $marketingUser->employee->phone_primary ?? null;
                
                \Illuminate\Support\Facades\Log::info('WhatsApp: Checking phone', [
                    'marketing_id' => $marketingUser->id,
                    'phone' => $waPhone
                ]);

                if ($waPhone) {
                    $invoice = $booking->generatedInvoice;
                    $invoiceNo = $invoice ? $invoice->invoice_no : 'Not Generated';
                    
                    // Determine Payment Status
                    // 0: Unpaid, 1: Paid, 2: Cancelled, 3: Partial, 4: Settled
                    // Default to 'Pending' if no invoice or status unknown
                    $paymentStatus = 'Pending';
                    if ($invoice) {
                        $paymentStatus = match((int)$invoice->status) {
                            0 => 'Unpaid',
                            1 => 'Paid',
                            2 => 'Cancelled',
                            3 => 'Partial',
                            4 => 'Settled',
                            default => 'Pending'
                        };
                    }

                    // Prepare links for Letter Path and Report Path
                    // Both buttons will point to the document repository index page to ensure 
                    // the user sees all available documents (Letter + Reports) in one place.
                    
                    $indexRoute = route('public.reports.index', ['job' => $jobKey]);
                    
                    // If the booking has a specific letter upload path, point the Letter button directly to it
                    // The path is stored in 'upload_letter_path' in uploads/bookings.
                    // We need to generate a full URL for it.
                    if (!empty($booking->upload_letter_path)) {
                        $letterPath = asset($booking->upload_letter_path); // 'uploads/bookings/...' is usually relative to public
                    } else {
                        $letterPath = $indexRoute;
                    }
                    
                    $reportPath = $indexRoute;


                    $contactName = SiteSetting::first()?->company_name ?? 'GenLab';

                    // Template parameters mapping
                    // The template 'testing_send_itl' expects 5 body parameters.
                    // {{1}}: Client Name
                    // {{2}}: Letter No
                    // {{3}}: Invoice No
                    // {{4}}: Payment Status
                    // {{5}}: Thanks and Regards (Signoff)
                    
                    // The links are likely handled by buttons or need to be appended if the template allowed it.
                    // Since the error says expected 5, we only send 5 body components.
                    // If we want to send links and they are not in the body, we can't force them in as {{6}} and {{7}}.
                    // Should we try to append them to {{5}}?
                    // "GenLab \n\n Links: ..."
                    
                    $signoff = $contactName;
                    
                    // Template has buttons.
                    // Based on previous errors and user description:
                    // Body has 5 variables: Client, Ref, Invoice, Payment, Signoff.
                    // And "Please use the buttons below to view your documents."
                    // Error 131008 says "Button at index 0 of type Url requires a parameter".
                    // This implies the template has a URL button with a variable, e.g. "https://domain.com/{{1}}"
                    
                    // We have 2 potential links: Letter and Report.
                    // If the template has TWO buttons (Letter Path, Report Path):
                    // We need to pass parameters for them if they are dynamic.
                    
                    // Process URLs for Buttons (Dynamic URL Buttons)
                    // The template likely expects a suffix to append to a base URL.
                    // We extract the path component of the generated URL.
                    
                    // NOTE: If letterPath is a full URL to an upload, we need to handle it carefully.
                    // If the button expects a SUFFIX (path), we strip the domain.
                    // If the button variable is the entire URL, we pass the entire URL.
                    // Assuming the template is configured as "https://domain.com/{{1}}" for the button,
                    // we must pass ONLY the path.
                    // BUT, if letters are in "uploads/bookings/..." and reports are in "public/reports/view/...",
                    // they share the same domain (APP_URL). So stripping the domain should work for both.
                    
                    $letterUrl = ($letterPath !== 'N/A') ? $letterPath : 'https://genlab.com';
                    $reportUrl = ($reportPath !== 'N/A') ? $reportPath : 'https://genlab.com';

                    // Extract suffix (path) for the buttons
                    // For uploads: 'http://localhost/uploads/bookings/file.pdf' -> '/uploads/bookings/file.pdf'
                    // For reports: 'http://localhost/public/reports/view/job' -> '/public/reports/view/job'
                    // IMPORTANT: WhatsApp API with Dynamic URL buttons often PREPENDS the base URL configured in the template.
                    // If the template is "https://domain.com/{{1}}", then we must send ONLY "uploads/bookings/file.pdf".
                    // However, if the configured base URL is DIFFERENT (e.g. "https://domain.com/public/"), 
                    // then sending "uploads/bookings/..." would result in ".../public/uploads/bookings/..." which is wrong.
                    
                    // Assuming the template base URL is the domain root "https://domain.com/".
                    // The 'ltrim' removes the leading slash, so it becomes "uploads/bookings/file.pdf".
                    // This seems correct IF the template is "https://domain.com/{{1}}".
                    
                    // But if the template is "https://domain.com/public/{{1}}", then "reports/view/job" works,
                    // but "uploads/bookings/..." fails because 'uploads' is usually at root, not inside 'public' folder url-wise (unless served from public).
                    // In Laravel, 'public/...' URLs are usually correct relative to domain root.
                    // Wait, users screenshot shows ".../%7B%7B%7D%7Duploads/bookings/...". That looks like "{{}}" was literally in the URL.
                    // This suggests the template might be malformed or we are passing data incorrectly.
                    // But standard button parameters are just strings.
                    
                    // Let's ensure we are passing exactly what is needed.
                    $letterSuffix = parse_url($letterUrl, PHP_URL_PATH);
                    // Remove leading slash to make it relative to the template's base URL
                    $letterSuffix = ltrim($letterSuffix, '/');
                    
                    $reportSuffix = parse_url($reportUrl, PHP_URL_PATH);
                    $reportSuffix = ltrim($reportSuffix, '/');
                    
                    // HOTFIX: If the letter path starts with 'public/', and the template base includes 'public/',
                    // we might be doubling it. But here 'uploads/bookings' does NOT start with 'public/'.
                    // If the template base is "https://domain.com/public/", adding "uploads/bookings" -> "https://domain.com/public/uploads/bookings".
                    // But 'uploads' is in 'public' folder physically, so URL is 'http://domain.com/uploads/...' or 'http://domain.com/public/uploads/...'?
                    // Laravel assets are usually served from root. 'http://domain.com/uploads/file.pdf'.
                    // If the template forces 'public/', we need to escape it using '../' maybe? No, that's risky.
                    
                    // If the template is configured as "https://domain.com/public/reports/view/{{1}}",
                    // then for reports we send "JOB-ID".
                    // But for letters, we are sending "uploads/bookings/file.pdf".
                    // This would result in "https://domain.com/public/reports/view/uploads/bookings/file.pdf" -> 404.
                    
                    // THIS IS LIKELY THE ISSUE.
                    // If the template is rigid (e.g. fixed path prefix), we CANNOT use it for arbitrary file uploads handling.
                    // We must check if we can override the full URL or if we are stuck with the suffix.
                    // If stuck, we can only link to pages that follow the template's structure.
                    // So, we should revert the 'Letter' button to point to an 'interstitial' page that redirects to the file.
                    // i.e., Point to a route like 'public/reports/download-letter/{job}' which then 302 redirects to the storage file.
                    
                    // Let's revert the letter button to match the report button structure for now to fix the 404s,
                    // but add a specific route to handle the redirection/download.
                    
                    $letterPath = route('public.reports.index', ['job' => $jobKey]); // Revert to index for safety first
                    
                    // But wait, the user wants the letter.
                    // Let's create a route that matches the 'public/reports/view/...' pattern but downloads the letter.
                    // Route: '/public/reports/view/{job}/letter' -> downloads letter.
                    // Then we can assume the template is '.../public/reports/view/{{1}}'.
                    // We pass "{job}/letter".
                    
                    // Let's assume the template base is '.../public/reports/view/'.
                    // Suffix for report: "{job}". Result: '.../public/reports/view/{job}' -> Works.
                    // Suffix for letter: "{job}/letter". Result: '.../public/reports/view/{job}/letter'.
                    // We need to implement this route in web.php or ReportingLettersController.
                    
                    // Let's try to detect if we can just pass the job key for both and they land on the same page,
                    // which has the download buttons.
                    // The user said "now its showing both link not found".
                    // This implies even the report link is broken.
                    // Report link generation: $reportSuffix = ltrim(parse_url($reportUrl, PHP_URL_PATH), '/');
                    // $reportUrl is '.../public/reports/view/{job}'.
                    // Suffix: 'public/reports/view/{job}'.
                    // If the template ALREADY has 'public/reports/view/' hardcoded,
                    // then appending 'public/reports/view/{job}' results in:
                    // '.../public/reports/view/public/reports/view/{job}' -> 404.
                    
                    // FIX: We need to know the Template Configuration.
                    // Since we don't, we should look at what WAS working before (or what was consistent).
                    // Previous code sent path suffix.
                    // If previous template was '.../{{1}}', then sending 'public/reports/view/{job}' works.
                    // If user changed template to include path, we need to adjust.
                    
                    // Based on "showing both link not found", I suspect I broke the matching logic or the double-path issue.
                    // I will sanitize the suffix to ensure it is just the JOB ID if the template expects that.
                    // BUT valid generic template usually expects full path suffix.
                    
                    // Let's revert to sending just the JOB ID if we suspect the template is "fixed path".
                    // But the code previously calculated full path.
                    
                    // Let's assume the template is just a base domain.
                    // Re-verify the URL: '.../public/reports/view/1301-P-6-24-11-05-2023-'
                    // If this 404s, its because the folder logic is still failing OR the URL is malformed.
                    // The screenshot shows the URL is correct (no double 'public/reports/view').
                    // So the 404 is truly the Controller returning 404.
                    
                    // So the Controller Logic I just fixed must be failing or not deploying correctly?
                    // I checked '1301...' folder. It exists WITHOUT hyphen.
                    // I updated logic to try rtrim.
                    
                    // Let's re-examine the 'both links not found' claim.
                    // If the Letter link was 'uploads/bookings/...' and it failed, that's one thing.
                    // If the Report link was '.../view/1301...' and that *also* failed, then the controller logic is the culprit.
                    
                    // I will add extenstive logging to 'viewReports' to see WHY it is failing.
                    // And I will revert the "Direct Upload Link" for the letter button because we can't guarantee the template handles arbitrary paths.
                    // I will point both to the Index page (which I know how to generate relative to the controller).
                    
                    $letterPath = $indexRoute; // Safety revert
                    $letterSuffix = parse_url($letterPath, PHP_URL_PATH);
                    $letterSuffix = ltrim($letterSuffix, '/');
                    
                    $reportSuffix = parse_url($reportPath, PHP_URL_PATH);
                    $reportSuffix = ltrim($reportSuffix, '/');

                    $waComponents = [
                        // Body
                        [
                            'type' => 'body',
                            'parameters' => [
                                [ 'type' => 'text', 'text' => $booking->client_name ?? 'Valued Client' ],       // {{1}}
                                [ 'type' => 'text', 'text' => $booking->reference_no ?? 'N/A' ],                // {{2}}
                                [ 'type' => 'text', 'text' => $invoiceNo ],                                     // {{3}}
                                [ 'type' => 'text', 'text' => $paymentStatus ],                                 // {{4}}
                                [ 'type' => 'text', 'text' => $signoff ],                                      // {{5}}
                            ]
                        ],
                        // Button 0 (Letter)
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => 0,
                            'parameters' => [
                                [ 'type' => 'text', 'text' => $letterSuffix ?: 'home' ]
                            ]
                        ],
                         // Button 1 (Report)
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => 1,
                            'parameters' => [
                                [ 'type' => 'text', 'text' => $reportSuffix ?: 'home' ]
                            ]
                        ]
                    ];
                    
                    // Instantiate service and send
                    $waService = new \App\Services\WhatsAppService();
                    // Correct order: phone, template, components, language
                    $waService->sendTemplateMessage($waPhone, 'testing_send_itl', $waComponents, 'en');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('WhatsApp Notification Failed: ' . $e->getMessage());
            }

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
        // Trim hyphens only from start/end if you want to normalize, 
        // BUT if folders have trailing hyphens, we must keep them or handle them.
        // Let's keep the raw input as a candidate too.
        $needle = trim($input);
        
        // Debugging
        \Illuminate\Support\Facades\Log::info("Resolving {$input}. Needle: {$needle}");

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
        
        // Try cleaning dashes?
        if (!$booking) {
             $clean = str_replace('-', '', $needle);
             $booking = NewBooking::query()
                ->whereRaw("REPLACE(reference_no, '-', '') LIKE ?", ["%{$clean}%"])
                ->latest('id')
                ->first();
        }

        if ($booking) {
            $ref = trim((string) $booking->reference_no);
            \Illuminate\Support\Facades\Log::info("Resolved DB ref: {$ref}");
            return [$this->sanitizeJob($ref), $ref];
        }
        
        // Return multiple?
        // Let's create variations here.
        // 1301-P...-2023- vs 1301-P...-2023
        $variations = [ $this->sanitizeJob($needle), $needle ];
        if (str_ends_with($needle, '-')) {
             $trimmed = rtrim($needle, '-');
             $variations[] = $this->sanitizeJob($trimmed);
             $variations[] = $trimmed;
        } else {
             $padded = $needle . '-';
             $variations[] = $this->sanitizeJob($padded);
             $variations[] = $padded;
        }

        return array_unique($variations);
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



    public function viewReports(string $job)
    {
        // Debugging: Log entry - CRITICAL
        \Illuminate\Support\Facades\Log::emergency("viewReports HIT. Job: {$job}");

        // 1. Try raw input first
        $candidates = [$job];

        // Handles trailing hyphen issue
        if (str_ends_with($job, '-')) {
            $candidates[] = substr($job, 0, -1);
        } else {
            $candidates[] = $job . '-';
        }
        
        // 2. Decode and try
        $decoded = urldecode($job);
        if ($decoded !== $job) {
            $candidates[] = $decoded;
            if (str_ends_with($decoded, '-')) {
                $candidates[] = substr($decoded, 0, -1);
            }
        }

        // 3. Resolve via DB logic
        // This function now returns an array of variations based on DB findings if any, or simply clean versions
        $dbCandidates = $this->resolveLetterKey($job);
        $candidates = array_merge($candidates, $dbCandidates);

        // Deduplicate and filter
        $candidates = array_values(array_unique(array_filter($candidates)));

        \Illuminate\Support\Facades\Log::info("Viewing Reports. Input: {$job}", [
            'candidates' => $candidates
        ]);

        $targetDir = null;
        $foundKey = $job;

        foreach ($candidates as $key) {
             if (empty($key)) continue;
             
             // Check standard path
             // Log checks
             $checkPath = "public/letters/{$key}";
             $exists = Storage::exists($checkPath);
             \Illuminate\Support\Facades\Log::info("Checking path: {$checkPath} -> " . ($exists ? 'EXISTS' : 'NO'));

             if ($exists) {
                 $targetDir = $checkPath;
                 $foundKey = $key;
                 break;
             }
        }

        // Fallback: Fuzzy (Case-Insensitive) Search
        // This handles issues where DB casing differs from URL or Sanitization differences
        if (!$targetDir) {
            \Illuminate\Support\Facades\Log::info("Exact match failed for {$job}. Starting fuzzy search in public/letters.");
            
            $allDirs = Storage::directories('public/letters');
            
            $candidatesLower = array_map('strtolower', $candidates);
            // Also check for stripped hyphen versions
            $candidatesStripped = array_map(function($c) { return rtrim($c, '-'); }, $candidatesLower);
            
            foreach ($allDirs as $dirPath) {
                // $dirPath is like 'public/letters/MyJob'
                // We want just 'MyJob'
                // Use pathinfo or basename logic carefully
                $dirName = basename($dirPath); 

                $dirLower = strtolower($dirName);
                if (in_array($dirLower, $candidatesLower) || in_array($dirLower, $candidatesStripped)) {
                    $targetDir = $dirPath; // Store full path
                    $foundKey = $dirName;
                    \Illuminate\Support\Facades\Log::info("Fuzzy match found: {$dirPath}");
                    break;
                }
            }
        }

        if (!$targetDir) {
             \Illuminate\Support\Facades\Log::warning("Reports view 404: Directory not found.", ['candidates' => $candidates]);
             // Return JSON for easier debugging if it fails, or standard 404 page?
             // Stick to abort so standard error page shows, but maybe with message
             abort(404);
        }

        $allFiles = Storage::files($targetDir);
        $fileList = [];

        foreach ($allFiles as $path) {
            $name = basename($path);
            if ($name === '_meta.json' || str_starts_with($name, '_')) continue;

            $size = Storage::size($path);
            $ts = Storage::lastModified($path);
            
            // formatting size
            $units = ['B', 'KB', 'MB', 'GB'];
            $power = $size > 0 ? floor(log($size, 1024)) : 0;
            $formattedSize = number_format($size / pow(1024, $power), 2, '.', '') . ' ' . ($units[$power] ?? 'B');

            $fileList[] = [
                'filename' => $name,
                'size' => $formattedSize,
                'date' => Carbon::createFromTimestamp($ts)->format('Y-m-d H:i')
            ];
        }

        return view('public.reports.index', [
            'job' => $foundKey,
            'files' => $fileList
        ]);
    }

    public function show(string $job, string $filename)
    {
        // Decode the job parameter to handle potential URL encoding issues
        $job = urldecode($job);

        // Resolve path candidates
        [$safeJob] = $this->resolveLetterKey($job);
        $candidates = array_values(array_unique(array_filter([$safeJob, $this->sanitizeJob($job), $job])));

        // Sanitize filename to prevent traversal
        $filenameRaw = $filename;
        $filename = basename($filename);
        
        \Illuminate\Support\Facades\Log::info("Download Request. Job: {$job}, File: {$filename}", [
             'candidates' => $candidates
        ]);

        if ($filename === '_meta.json' || str_starts_with($filename, '_')) {
            abort(404);
        }

        foreach ($candidates as $key) {
            // 1. Direct match
            $path = "public/letters/{$key}/{$filename}";
            if (\Storage::exists($path)) {
                return $this->streamFile($path, $filename);
            }
            
            // 2. Try URL decoding the filename (handling %20 vs space)
            $decodedFilename = urldecode($filename);
            if ($decodedFilename !== $filename) {
                $pathDecoded = "public/letters/{$key}/{$decodedFilename}";
                if (\Storage::exists($pathDecoded)) {
                    return $this->streamFile($pathDecoded, $decodedFilename);
                }
            }

            // 3. Try checking file existence via directory scanning if encoding is tricky?
            // (Optional: iterate directory if not found yet)
        }
        
        \Illuminate\Support\Facades\Log::warning("Download 404. Job: {$job}, File: {$filename}", [
             'candidates' => $candidates,
             'tried_paths' => array_map(fn($k) => "public/letters/{$k}/{$filename}", $candidates)
        ]);
        
        abort(404);
    }

    private function streamFile($path, $filename) {
        $mime = \Storage::mimeType($path) ?: 'application/octet-stream';
        $stream = \Storage::readStream($path);
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"'
        ]);
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