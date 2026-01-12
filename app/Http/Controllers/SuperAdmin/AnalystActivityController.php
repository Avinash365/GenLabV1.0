<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NewBooking;
use App\Models\BookingItem;
use App\Models\Role; 

class AnalystActivityController extends Controller
{
    public function index(Request $request)
    {
        // Fetch users who have 'analyst' in their role slug
        $analysts = User::whereHas('role', function($q) {
            $q->where('slug', 'like', '%analyst%');
        })->get();
        
        // Fallback if no analysts found (e.g. roles not set up yet)
        if ($analysts->isEmpty()) {
             $analysts = User::all();
        }

        $query = BookingItem::with('booking');

        // Filter by Analyst
        if ($request->filled('analyst_id')) {
            $analystUser = User::find($request->analyst_id);
            if ($analystUser && $analystUser->user_code) {
                $query->where('lab_analysis_code', $analystUser->user_code);
            }
        }

        // Search logic
        if ($request->has('search') && $request->search) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('job_order_no', 'like', "%{$search}%")
                   ->orWhere('sample_description', 'like', "%{$search}%")
                   ->orWhereHas('booking', function($b) use ($search) {
                        $b->where('client_name', 'like', "%{$search}%")
                          ->orWhere('reference_no', 'like', "%{$search}%");
                   });
             });
        }
        
        // Pagination logic
        $perPage = $request->input('per_page', 25);
        $jobOrders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('superadmin.analyst_activities.index', compact('analysts', 'jobOrders'));
    }

    public function createJob(Request $request)
    {
        // TODO: Implement job creation logic
        return redirect()->back()->with('success', 'Job creation function to be implemented');
    }

    public function assignJob(Request $request)
    {
        $request->validate([
            'analyst_id' => 'required|exists:users,id',
            'job_order_no' => 'required|string',
        ]);

        $analyst = User::findOrFail($request->analyst_id);
         if (!$analyst->user_code) {
             return redirect()->back()->with('error', 'Selected analyst does not have a user code.');
        }

        // Parse input: split by comma, newline, or whitespace
        $inputJobOrders = preg_split('/[\s,]+/', $request->job_order_no, -1, PREG_SPLIT_NO_EMPTY);
        $inputJobOrders = array_unique($inputJobOrders); // Remove duplicates from input

        if (empty($inputJobOrders)) {
            return redirect()->back()->with('error', 'No valid Job Order Numbers provided.');
        }

        // Find existing booking items
        $bookingItems = BookingItem::whereIn('job_order_no', $inputJobOrders)->get();
        $foundJobOrders = $bookingItems->pluck('job_order_no')->toArray();

        // Calculate missing
        $missingJobOrders = array_diff($inputJobOrders, $foundJobOrders);

        // Update found items
        if ($bookingItems->isNotEmpty()) {
            BookingItem::whereIn('job_order_no', $foundJobOrders)
                ->update([
                    'lab_analysis_code' => $analyst->user_code,
                    'status' => '' . $analyst->name
                ]);
        }

        // Construct message
        $successCount = $bookingItems->count();
        $message = "";

        if ($successCount > 0) {
            $message .= "{$successCount} Job Order(s) successfully assigned to {$analyst->name}. ";
        }

        if (!empty($missingJobOrders)) {
            $missingStr = implode(', ', $missingJobOrders);
            $message .= "Warning: The following Job Order Numbers were not found: {$missingStr}";
            return redirect()->back()->with($successCount > 0 ? 'warning' : 'error', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function transferJob(Request $request) 
    {
        $request->validate([
            'target_analyst_id' => 'required|exists:users,id',
            'job_ids' => 'required|string',
        ]);

        $jobIds = explode(',', $request->job_ids);
        $targetAnalyst = User::findOrFail($request->target_analyst_id);

        if (!$targetAnalyst->user_code) {
             return redirect()->back()->with('error', 'Target analyst does not have a user code.');
        }

        // Logic: Transfer selected items to the new analyst
        // We are now operating on BookingItem IDs directly.
        
        $count = BookingItem::whereIn('id', $jobIds)
                    ->update([
                        'lab_analysis_code' => $targetAnalyst->user_code,
                        'status' => 'Assigned to ' . $targetAnalyst->name
                    ]);

        return redirect()->back()->with('success', "Successfully transferred {$count} items to {$targetAnalyst->name}.");
    }
}
