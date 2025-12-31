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

        // 2 Get all items under same booking
        $items = BookingItem::where('new_booking_id', $bookingId)->get();

        // 3 Get all invoices related to this booking
        $invoices = Invoice::where('new_booking_id', $bookingId)
            ->with(['client', 'marketingPerson', 'transactions', 'tdsTransaction'])
            ->get();

        return view ('superadmin.jobOrderInfo.index', compact('invoices','items'));

    }
}
