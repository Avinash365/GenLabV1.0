<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{BookingItem,Invoice};

class JobOrderController extends Controller
{
    public function index(string $jobOrderNumber)
    {
       
        // 1️ Find one item by job order number
        $item = BookingItem::where('job_order_no', $jobOrderNumber)->first();

         if (!$item) {
            return redirect()->back()->with('success', 'Job Order not found.');
            // or ->with('info', 'Job Order not found.')
        }

        $bookingId = $item->new_booking_id;

        // Determine per-page size (allow user to change via query param)
        $perPage = (int) request()->input('perPage', 25);

        // 2 Get paginated items under same booking
        $items = BookingItem::where('new_booking_id', $bookingId)->paginate($perPage);

        // 3 Get paginated invoices related to this booking
        // $invoices = Invoice::where('new_booking_id', $bookingId)
        //     ->with(['client', 'marketingPerson', 'transactions', 'tdsTransaction'])
        //     ->paginate($perPage);

        $invoices = Invoice::where(function ($query) use ($bookingId) {
                $query->where('new_booking_id', $bookingId)
                    ->orWhereRaw('FIND_IN_SET(?, invoice_booking_ids)', [$bookingId]);
            })
            ->with(['client', 'marketingPerson', 'transactions', 'tdsTransaction'])
            ->paginate($perPage);

        return view ('superadmin.jobOrderInfo.index', compact('invoices','items'));

    }

    public function verify(string $jobOrderNumber)
    {
        // Check if job order exists in database
        $exists = BookingItem::where('job_order_no', strtoupper($jobOrderNumber))->exists();
        
        return view('public.verify_job_order', [
            'verified' => $exists,
            'job_order_no' => $jobOrderNumber
        ]);
    }
}
