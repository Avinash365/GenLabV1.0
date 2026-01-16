<?php

namespace App\Http\Controllers\MobileControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use App\Models\MarketingEnquiry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MarketingHoldCancelApiController extends Controller
{
    /**
     * List Held or Cancelled items for a marketing user
     */
    public function index(Request $request, $user_code)
    {
        // 1. Validate User
        $user = User::where('user_code', $user_code)->firstOrFail();
        
        // Optional: Ensure the authenticated user is the one requesting data
        // if (Auth::id() !== $user->id) abort(403);

        $job = trim((string) $request->query('job', ''));

        // 2. Prepare Query
        $itemsQuery = BookingItem::with(['booking', 'receivedBy', 'analyst', 'latestMarketingEnquiry']);

        // Filter by marketing Code (Same logic as Web Controller if role-based check was used, 
        // but here we are explicit with $user_code from URL)
        $itemsQuery->whereHas('booking', function ($b) use ($user_code) {
             $b->where('marketing_id', $user_code);
        });

        // Search Filter
        if ($job !== '') {
            $itemsQuery->where('job_order_no', 'like', "%{$job}%");
        }

        // Hold/Cancel Filter
        $itemsQuery->where(function($q) {
            $q->whereNotNull('hold_reason')
              ->orWhere('hold_reason', '!=', '') 
              ->orWhereNotNull('cancel_reason');
            
            if (Schema::hasColumn('booking_items', 'is_canceled')) {
                $q->orWhere('is_canceled', 1);
            }
        });

        $items = $itemsQuery->orderByDesc('id')->paginate(20);

        // 3. Prepare Header Info (Only if searching by Job No, similar to Blade)
        $header = null;
        if ($job !== '' && $items->isNotEmpty()) {
            $first = $items->first();
            $header = $this->prepareHeader($first);
        }

        // 4. Transform Items
        // We map the items to a clean structure expected by mobile
        $transformedItems = $items->getCollection()->map(function ($item) {
            $hr = $item->hold_reason;
            $isCancelMarker = is_string($hr) && Str::startsWith($hr, 'CANCELED:');
            $cancelMarkerReason = $isCancelMarker ? trim(Str::after($hr, 'CANCELED:')) : null;

            // Determine Status display
            $statusLabel = 'Active';
            $statusType = 'info';
            $reasonText = '';

            if (!empty($item->cancel_reason)) {
                $statusLabel = 'Canceled';
                $statusType = 'danger';
                $reasonText = $item->cancel_reason;
            } elseif ($isCancelMarker) {
                $statusLabel = 'Canceled';
                $statusType = 'danger';
                $reasonText = $cancelMarkerReason;
            } elseif (!empty($item->hold_reason)) {
                $statusLabel = 'Held';
                $statusType = 'warning';
                $reasonText = $item->hold_reason;
            }

            // Enquiry Data
            $enquiry = null;
            if ($item->latestMarketingEnquiry) {
                $rawMedia = $item->latestMarketingEnquiry->media_paths;
                $processedMedia = [];

                if (is_array($rawMedia)) {
                    foreach ($rawMedia as $mediaItem) {
                        // Handle both old string format and new array format
                        if (is_array($mediaItem)) {
                            $path = $mediaItem['path'] ?? '';
                            $name = $mediaItem['name'] ?? basename($path);
                        } else {
                            $path = $mediaItem;
                            $name = basename($path);
                        }

                        if ($path) {
                            $processedMedia[] = [
                                'path' => $path,
                                'name' => $name,
                                'url'  => asset('storage/' . $path)
                            ];
                        }
                    }
                }

                $enquiry = [
                    'id' => $item->latestMarketingEnquiry->id,
                    'note' => $item->latestMarketingEnquiry->note,
                    'media' => $processedMedia, 
                    'created_at' => $item->latestMarketingEnquiry->created_at->format('d/m/Y h:i A'),
                    'created_at_iso' => $item->latestMarketingEnquiry->created_at->toIso8601String(),
                ];
            }

            return [
                'id' => $item->id,
                'job_order_no' => $item->job_order_no,
                'description' => $item->sample_description,
                'status' => [
                    'label' => $statusLabel,
                    'type' => $statusType, // ui hint
                    'reason' => $reasonText,
                ],
                'enquiry' => $enquiry, // null if 'create' mode, object if 'view' mode
            ];
        });

        // Replace collection with transformed
        $items->setCollection($transformedItems);

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'header' => $header
        ]);
    }

    /**
     * Store Enquiry
     */
    public function storeEnquiry(Request $request, $user_code)
    {
        $request->validate([
            'booking_item_id' => 'required|exists:booking_items,id',
            'note' => 'required|string',
            'media.*' => 'file|max:10240', 
        ]);

        $mediaData = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('marketing_enquiries', 'public');
                $mediaData[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName()
                ];
            }
        }

        // Create Enquiry
        // Note: The original controller uses Auth::id(). 
        // In API Context for 'marketing-person', we should ensure we attribute it to the right user.
        // Assuming $user_code corresponds to Auth::user() or we look up the user by code.
        
        // If the API Auth user is the marketing person:
        $userId = Auth::id(); 
        // If we want to be strict that the user_code matches the creator:
        // $user = User::where('user_code', $user_code)->first(); $userId = $user->id;

        $enquiry = MarketingEnquiry::create([
            'booking_item_id' => $request->booking_item_id,
            'user_id' => $userId, 
            'note' => $request->note,
            'media_paths' => $mediaData,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Enquiry submitted successfully.',
            'data' => $enquiry
        ], 201);
    }

    /**
     * Helper to prepare header data (Copied/Adapted from Blade Logic)
     */
    private function prepareHeader($firstItem)
    {
        // $firstItem here is the TRANSFORMED item (array) because of getCollection()->map() logic above?
        // Wait, NO. map() runs on the collection. $items->first() in Laravel Paginator returns the model object BEFORE transformation locally 
        // IF I call it before `setCollection`.
        // BUT I called `first()` on `$items` variable. Let's trace.
        // I define $items = query->paginate().
        // Then I define $header using $items->first(). This is a Model.
        // Then I transform the collection.
        // So $firstItem passed here is a BookingItem Model.
        
        $booking = $firstItem->booking;
        $bk = $booking ? $booking->getAttributes() : [];

        $workKeys = [
            'name_of_work','nameOfWork','work_name','workName','nature_of_work','natureOfWork','work','job_work','jobWork',
            'project_name','projectName','project'
        ];
        $issueToKeys = ['issued_to','issuedTo','issue_to','issued_to_name','contact_person']; // Simplified list

        $nameOfWork = $this->pickFirstNonEmpty([$booking, $firstItem, $bk], $workKeys);
        $issuedTo = $this->pickFirstNonEmpty([$booking, $firstItem, $bk], $issueToKeys);
        
        // Fallbacks
        $clientName = $booking->client_name ?? ($bk['clientName'] ?? '');
        $refNo = $booking->reference_no ?? ($firstItem->reference_no ?? '');
        $ms = $booking->ms ?? ($firstItem->ms ?? '-');

        return [
            'job_card_no' => $firstItem->job_order_no,
            'client_name' => $clientName,
            'job_order_date' => $booking->job_order_date ?? null,
            'issue_date' => $booking->issue_date ?? null,
            'reference_no' => $refNo,
            'sample_description' => $firstItem->sample_description ?? '',
            'name_of_work' => $nameOfWork,
            'issued_to' => $issuedTo,
            'ms' => $ms,
        ];
    }

    private function pickFirstNonEmpty($sources, $keys)
    {
        foreach ($keys as $key) {
            foreach ($sources as $source) {
                if (is_array($source)) {
                    if (!empty($source[$key])) return $source[$key];
                } elseif (is_object($source)) {
                    if (!empty($source->$key)) return $source->$key;
                }
            }
        }
        return '';
    }
}
