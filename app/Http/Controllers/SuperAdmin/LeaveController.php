<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        try {
            $leaves = Leave::with(['user', 'approver'])
                          ->orderBy('created_at', 'desc')
                          ->get();
        } catch (\Exception $e) {
            // If table doesn't exist yet, provide empty collection
            $leaves = collect([]);
        }
        
        try {
            $users = User::all();
        } catch (\Exception $e) {
            // If users table issues, provide empty collection
            $users = collect([]);
        }
        
        return view('superadmin.leaves.leave', compact('leaves', 'users'));  
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|in:Sick Leave,Casual Leave,Emergency Leave,Annual Leave,Maternity Leave,Paternity Leave',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'day_type' => 'required|in:Full Day,Half Day,Hours',
            'days_hours' => 'required|integer|min:1',
            'reason' => 'required|string|min:10'
        ]);

        // Get employee name
        $user = User::find($request->user_id);
        
        Leave::create([
            'user_id' => $request->user_id,
            'employee_name' => $user->name ?? 'Unknown Employee',
            'leave_type' => $request->leave_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days_hours' => $request->days_hours,
            'day_type' => $request->day_type,
            'reason' => $request->reason,
            'status' => 'Applied'
        ]);

        return redirect()->back()->with('success', 'Leave application submitted successfully.');
    }

    public function update(Request $request, Leave $leave)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|in:Sick Leave,Casual Leave,Emergency Leave,Annual Leave,Maternity Leave,Paternity Leave',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'day_type' => 'required|in:Full Day,Half Day,Hours',
            'days_hours' => 'required|integer|min:1',
            'reason' => 'required|string|min:10'
        ]);

        // Get employee name
        $user = User::find($request->user_id);

        $leave->update([
            'user_id' => $request->user_id,
            'employee_name' => $user->name ?? 'Unknown Employee',
            'leave_type' => $request->leave_type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days_hours' => $request->days_hours,
            'day_type' => $request->day_type,
            'reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Leave application updated successfully.');
    }

    public function approve(Request $request, Leave $leave)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_comments' => 'nullable|string'
        ]);

        $leave->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'admin_comments' => $request->admin_comments
        ]);

        $statusText = $request->status === 'Approved' ? 'approved' : 'rejected';
        return redirect()->back()->with('success', "Leave application {$statusText} successfully.");
    }

    public function destroy(Leave $leave)
    {
        $leave->delete();
        return redirect()->back()->with('success', 'Leave application deleted successfully.');
    }

    public function calculateDays(Request $request)
    {
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);
        
        $days = $fromDate->diffInDays($toDate) + 1;
        
        return response()->json(['days' => $days]);
    }
}
