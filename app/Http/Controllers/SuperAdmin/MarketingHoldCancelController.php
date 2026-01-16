<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class MarketingHoldCancelController extends Controller
{
    public function storeEnquiry(Request $request)
    {
        $request->validate([
            'booking_item_id' => 'required|exists:booking_items,id',
            'note' => 'required|string',
            'media.*' => 'file|max:10240', // 10MB max per file
        ]);

        $mediaData = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                // Store file publically
                $path = $file->store('marketing_enquiries', 'public');
                // Store path and original name
                $mediaData[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName()
                ];
            }
        }

        \App\Models\MarketingEnquiry::create([
            'booking_item_id' => $request->booking_item_id,
            'user_id' => Auth::id(), // Works for both guards if Auth::user() is set, but better be safe
            'note' => $request->note,
            'media_paths' => $mediaData,
        ]);

        return back()->with('status', 'Enquiry submitted successfully.');
    }

    public function index(Request $request)
    {
        $job = trim((string) $request->query('job', ''));
        $user = Auth::guard('admin')->user() ?: Auth::user();
        
        // Personalization: If user is Marketing, filter by their code.
        // We check role name containing 'market'
        $marketingFilter = null;
        if ($user) {
            $roleName = null;
            if (is_object($user->role)) {
                $roleName = $user->role->role_name ?? $user->role->name ?? null;
            } else {
                $roleName = $user->role;
            }

            if ($roleName && stripos($roleName, 'market') !== false) {
                // It is a marketing user
                $marketingFilter = $user->user_code;
            }
        }

        $items = collect();
        $header = [];

        if ($job !== '') {
            // Fetch the FIRST item of the job to populate Header Info (Client Name, Job Date, etc.)
            $firstQuery = BookingItem::with(['booking', 'receivedBy', 'analyst', 'latestMarketingEnquiry'])
                ->where('job_order_no', 'like', "%{$job}%");

            if ($marketingFilter) {
                $firstQuery->whereHas('booking', function ($b) use ($marketingFilter) {
                    $b->where('marketing_id', $marketingFilter);
                });
            }

            $first = $firstQuery->orderByDesc('id')->first();

            if ($first) {
                // --- Logic copied from HoldCancelController to populate Header ---
                $booking = $first->booking;
                $bk = $booking ? $booking->getAttributes() : [];

                $workKeys = [
                    'name_of_work','nameOfWork','work_name','workName','nature_of_work','natureOfWork','work','job_work','jobWork',
                    'project_name','projectName','project','project_title','projectTitle','work_title','workTitle',
                    'site','site_name','siteName','site_address','siteAddress','location','address',
                    'job_name','jobName','job_title','jobTitle','work_desc','work_description','workDescription'
                ];
                $issueToKeys = [
                    'issued_to','issuedTo','issue_to','issueTo',
                    'issued_to_name','issuedToName','issue_to_name','issueToName',
                    'issued_to_person','issuedToPerson','issue_to_person','issueToPerson',
                    'issued_to_department','issuedToDepartment','issue_to_department','issueToDepartment',
                    'issued_to_user','issuedToUser','issue_to_user','issueToUser',
                    'issued_to_company','issuedToCompany','issue_to_company','issueToCompany',
                    'issued_to_org','issuedToOrg','issue_to_org','issueToOrg',
                    'department','dept','contact_person','contactPerson','contact_name','contactName'
                ];

                $clientName = $booking->client_name ?? ($bk['clientName'] ?? ($bk['client'] ?? ''));
                $refNo = $booking->reference_no ?? ($bk['referenceNo'] ?? ($bk['ref_no'] ?? ($first->reference_no ?? '')));
                
                // Helper methods are private in HoldCancelController, I'll allow null coalesce or re-implement simply here or just use what we have.
                // Since I cannot call private methods of another controller, I'll replicate the core logic inline or simplified.
                // The HoldCancelController used complex extraction. I'll copy the 'pickFirstNonEmpty' helper logic into this class.
                
                $nameOfWork = $this->pickFirstNonEmpty([$booking, $first, $bk], $workKeys);
                $issuedTo = $this->pickFirstNonEmpty([$booking, $first, $bk], $issueToKeys);
                $ms = $booking->ms ?? ($bk['ms_name'] ?? ($bk['contractor'] ?? ($bk['contractor_name'] ?? ($first->ms ?? ''))));

                $jobDateRaw = $booking->job_order_date ?? ($bk['jobDate'] ?? null);
                $issueDateRaw = $booking->issue_date ?? ($bk['issued_date'] ?? ($first->issue_date ?? null));

                $header = [
                    'job_card_no' => $first->job_order_no,
                    'client_name' => $clientName,
                    'job_order_date' => $jobDateRaw ? Carbon::parse($jobDateRaw)->format('Y-m-d') : '',
                    'issue_date' => $issueDateRaw ? Carbon::parse($issueDateRaw)->format('Y-m-d') : '',
                    'reference_no' => $refNo,
                    'sample_description' => $first->sample_description ?? '',
                    'name_of_work' => $nameOfWork,
                    'issued_to' => $issuedTo,
                    'ms' => $ms,
                ];

                // --- Fetch Items (Filtered for Held/Canceled) ---
                $query = BookingItem::with(['booking', 'receivedBy', 'analyst', 'latestMarketingEnquiry'])
                    ->where('new_booking_id', $first->new_booking_id);

                // Filter logic: Show only Held or Canceled
                $query->where(function($q) {
                    $q->whereNotNull('hold_reason')
                      ->orWhere('hold_reason', '!=', '') // cover empty string cases if any
                      ->orWhereNotNull('cancel_reason');
                    
                    if (Schema::hasColumn('booking_items', 'is_canceled')) {
                        $q->orWhere('is_canceled', 1);
                    }
                });

                $items = $query->orderByDesc('id')
                    ->paginate(20)
                    ->appends(['job' => $job]);

                // Fallbacks for header if empty (using the fetched filtered items, or the original $first)
                // Note: using $first references is better as $items might be empty.
                $rows = collect([$first]); // Just use the one we found
                
                if (empty($header['name_of_work'])) {
                     $header['name_of_work'] = $this->pickFirstNonEmpty([$booking, $first, $bk], ['nameOfWork','workName','projectName','projectTitle','workTitle','jobName','jobTitle','workDescription','work_desc']);
                }
                if (empty($header['issued_to'])) {
                     $header['issued_to'] = $this->pickFirstNonEmpty([$booking, $first, $bk], ['issuedTo','issueTo','issuedToName','issueToName','contact_person','contactPerson','contact_name','contactName']);
                }
                 // Heuristic final fallback
                if (empty($header['name_of_work'])) {
                     $header['name_of_work'] = $this->pickFirstByPattern([$bk, $first, $booking], [
                        '/name[_]?of[_]?work/i','/work[_]?name/i','/nature[_]?of[_]?work/i','/project/i','/site/i','/job[_]?(name|title)/i','/work(_desc|_description)?/i'
                    ]);
                }
                if (empty($header['issued_to'])) {
                    $header['issued_to'] = $this->pickFirstByPattern([$bk, $first, $booking], [
                        '/issued[_]?to/i','/issue[_]?to/i','/contact(_person|_name)?/i','/department|dept/i','/to[_]?(name|person|user|company|org)/i'
                    ]);
                }
                if (empty($header['ms'])) {
                    $header['ms'] = $first->ms ?? ($booking->ms ?? '');
                }
                if (empty($header['issue_date'])) {
                     $d = $first->issue_date ?? ($booking->issue_date ?? null);
                     if ($d) $header['issue_date'] = Carbon::parse($d)->format('Y-m-d');
                }
            }
        } else {
            // Default view: Show ALL held/canceled items for this user/dept, sorted by recent
            $query = BookingItem::with(['booking', 'receivedBy', 'analyst', 'latestMarketingEnquiry']);

            if ($marketingFilter) {
                $query->whereHas('booking', function ($b) use ($marketingFilter) {
                    $b->where('marketing_id', $marketingFilter);
                });
            }

            // Filter logic: Show only Held or Canceled
            $query->where(function($q) {
                $q->whereNotNull('hold_reason')
                  ->orWhere('hold_reason', '!=', '')
                  ->orWhereNotNull('cancel_reason');
                
                if (Schema::hasColumn('booking_items', 'is_canceled')) {
                    $q->orWhere('is_canceled', 1);
                }
            });

            $items = $query->orderByDesc('id')->paginate(20);
        }

        return view('superadmin.reporting.marketing_hold_cancel', compact('items', 'header', 'job'));
    }

    private function pickFirstNonEmpty($sources, $keys)
    {
        foreach ($sources as $source) {
            if (!$source) continue;
            foreach ($keys as $k) {
                // Check object property or array key
                $val = is_array($source) ? ($source[$k] ?? null) : ($source->$k ?? null);
                $s = trim((string)($val ?? ''));
                if ($s !== '') return $s;
            }
        }
        return '';
    }

    private function pickFirstByPattern($sources, $patterns)
    {
        foreach ($sources as $source) {
            if (!$source) continue;
            // flatten source to array if object
            $data = is_array($source) ? $source : (method_exists($source, 'getAttributes') ? $source->getAttributes() : (array)$source);
            
            foreach ($patterns as $pattern) {
                foreach ($data as $key => $val) {
                    if (preg_match($pattern, $key)) {
                        $s = trim((string)($val ?? ''));
                         if ($s !== '') return $s;
                    }
                }
            }
        }
        return '';
    }
}
