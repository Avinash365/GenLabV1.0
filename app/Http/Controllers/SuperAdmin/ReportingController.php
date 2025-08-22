<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingItem;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    /**
     * Show Received Reports page with optional Job Order search.
     */
    public function received(Request $request)
    {
        $job = trim((string) $request->get('job'));

        $baseQuery = BookingItem::query()->with(['booking', 'analyst', 'receivedBy']);

        $header = null;
        if ($job !== '') {
            // Find first matching item to determine its booking/reference
            $firstItem = (clone $baseQuery)
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $b = $firstItem->booking;
                // Build header data
                $header = [
                    'job_card_no'      => $firstItem->job_order_no,
                    'client_name'      => $b->client_name,
                    'job_order_date'   => optional($b->job_order_date)->format('Y-m-d'),
                    'issue_date'       => optional($b->job_order_date)->format('Y-m-d'), // using job_order_date as placeholder
                    'reference_no'     => $b->reference_no,
                    'sample_description'=> $firstItem->sample_description,
                    'name_of_work'     => $b->client_address,
                    'issued_to'        => $b->report_issue_to,
                    'ms'               => $b->contractor_name,
                ];

                // Show all items for the same booking/reference
                $items = $b->items()->with(['booking', 'analyst', 'receivedBy'])->latest('id')->paginate(20)->withQueryString();

                return view('superadmin.reporting.received', compact('items', 'job', 'header'));
            }
        }

    // Default: no auto-listing; show empty when no search or not found
    $items = BookingItem::query()->whereRaw('1=0')->paginate(20)->withQueryString();
    return view('superadmin.reporting.received', compact('items', 'job', 'header'));
    }

    /**
     * Receive a single report item (assign to current user).
     */
    public function receiveOne(Request $request, BookingItem $item)
    {
    // Only set receiver to a front-end user (users table) to satisfy FK
        $receiverId = auth('web')->check() ? auth('web')->id() : null; // FK constraint
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        $item->received_by_id = $receiverId;
        $item->received_by_name = $receiverName;
        $item->received_at = now();
    $item->save();
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'received_by' => $item->receivedBy->name ?? $item->received_by_name ?? $receiverName,
                'received_at' => optional($item->received_at)->toDateTimeString(),
                'id' => $item->id,
                'receiver_name' => $receiverName,
            ]);
        }
        return back()->with('status', 'Report received');
    }

    /**
     * Receive all filtered reports.
     */
    public function receiveAll(Request $request)
    {
        $job = trim((string) $request->get('job'));

        // If job is provided, try to scope to that booking's items
        if ($job !== '') {
            $firstItem = BookingItem::with('booking')
                ->where('job_order_no', 'like', "%{$job}%")
                ->latest('id')
                ->first();

            if ($firstItem && $firstItem->booking) {
                $receiverId = auth('web')->check() ? auth('web')->id() : null;
                $receiverName = auth('web')->check()
                    ? optional(auth('web')->user())->name
                    : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
                $firstItem->booking->items()->update([
                    'received_by_id' => $receiverId,
                    'received_by_name' => $receiverName,
                    'received_at'    => now(),
                ]);
                if ($request->wantsJson()) {
                    return response()->json(['ok' => true, 'scope' => 'booking', 'booking_id' => $firstItem->booking->id, 'receiver_name' => $receiverName, 'received_at' => now()->toDateTimeString()]);
                }
                return back()->with('status', 'All matching reports marked as received');
            }
        }

        // Fallback: mark all items as received (use sparingly)
        $receiverId = auth('web')->check() ? auth('web')->id() : null;
        $receiverName = auth('web')->check()
            ? optional(auth('web')->user())->name
            : (auth('admin')->check() ? optional(auth('admin')->user())->name : null);
        BookingItem::query()->update([
            'received_by_id' => $receiverId,
            'received_by_name' => $receiverName,
            'received_at' => now()
        ]);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'scope' => 'all', 'receiver_name' => $receiverName, 'received_at' => now()->toDateTimeString()]);
        }

    return back()->with('status', 'All matching reports marked as received');
    }
}
