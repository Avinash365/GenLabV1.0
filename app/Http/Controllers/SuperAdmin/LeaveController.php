<?php

namespace App\Http\Controllers\Superadmin;

use App\Exports\LeavesExport;
use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        try {
            $leaves = $this->buildLeavesQuery($request)->get();
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

    public function exportPdf(Request $request)
    {
        $leaves = $this->buildLeavesQuery($request)->get();

        if ($leaves->isEmpty()) {
            return redirect()->route('superadmin.leave.Leave')->with('error', 'No leave records available to export.');
        }

        $pdf = Pdf::loadView('superadmin.leaves.export_pdf', [
            'leaves' => $leaves,
            'generatedAt' => Carbon::now(),
            'filters' => [
                'status' => $request->input('status'),
                'date' => $request->input('date'),
                'search' => $request->input('search'),
            ],
        ])->setPaper('a4', 'landscape');

        $filename = sprintf('leave-applications-%s.pdf', Carbon::now()->format('Ymd_His'));

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $leaves = $this->buildLeavesQuery($request)->get();

        if ($leaves->isEmpty()) {
            return redirect()->route('superadmin.leave.Leave')->with('error', 'No leave records available to export.');
        }

        $filename = sprintf('leave-applications-%s.xlsx', Carbon::now()->format('Ymd_His'));

        return Excel::download(new LeavesExport($leaves), $filename);
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

        $approverUserId = $this->resolveApproverUserId();

        $payload = [
            'status' => $request->status,
            'approved_at' => Carbon::now(),
            'admin_comments' => $request->admin_comments,
        ];

        if ($approverUserId !== null) {
            $payload['approved_by'] = $approverUserId;
        }

        $leave->update($payload);

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

    protected function resolveApproverUserId(): ?int
    {
        $webUser = Auth::guard('web')->user();

        if ($webUser instanceof User) {
            return $webUser->getKey();
        }

        $adminUser = Auth::guard('admin')->user();

        if ($adminUser && !empty($adminUser->email)) {
            return User::query()
                ->where('email', $adminUser->email)
                ->value('id');
        }

        return null;
    }

    protected function buildLeavesQuery(Request $request): Builder
    {
        $query = Leave::query()
            ->with(['user', 'approver'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->startOfDay();
                $query->whereDate('from_date', '<=', $date)
                    ->whereDate('to_date', '>=', $date);
            } catch (\Exception $exception) {
                // Ignore invalid date filters
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $builder) use ($search) {
                $like = '%' . $search . '%';
                $builder->where('employee_name', 'like', $like)
                    ->orWhere('leave_type', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhereHas('user', function (Builder $subQuery) use ($like) {
                        $subQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        return $query;
    }
}
