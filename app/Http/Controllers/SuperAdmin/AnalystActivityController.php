<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NewBooking;
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

        $query = NewBooking::query();

        // Filter by Analyst
        if ($request->filled('analyst_id')) {
            $analystUser = User::find($request->analyst_id);
            if ($analystUser && $analystUser->user_code) {
                // Filter bookings where at least one item is assigned to this analyst
                $query->whereHas('items', function($q) use ($analystUser) {
                    $q->where('lab_analysis_code', $analystUser->user_code);
                });
            }
        }

        // Search logic
        if ($request->has('search') && $request->search) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('id', 'like', "%{$search}%")
                   ->orWhere('sample_code', 'like', "%{$search}%")
                   ->orWhere('client_name', 'like', "%{$search}%")
                   ->orWhere('reference_no', 'like', "%{$search}%");
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

        // Logic: Transfer all items in the selected bookings to the new analyst
        // Or should we only transfer items currently assigned to the filtered analyst (if any)?
        // For simplicity, let's assume "Transfer Job" means reassigning the whole Job Order's items to the new analyst.
        
        // Find bookings
        $bookings = NewBooking::whereIn('id', $jobIds)->get();
        
        $count = 0;
        foreach ($bookings as $booking) {
            foreach ($booking->items as $item) {
                $item->lab_analysis_code = $targetAnalyst->user_code;
                $item->save();
                $count++;
            }
        }

        return redirect()->back()->with('success', "Successfully transferred {$count} items to {$targetAnalyst->name}.");
    }
}
