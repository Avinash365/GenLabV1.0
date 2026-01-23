<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApproveController extends Controller
{
    public function index(Request $request)
    {
        try {
            $leaves = $this->buildLeavesQuery($request)->get();
        } catch (\Exception $e) {
            $leaves = collect([]);
        }

        return view('superadmin.leaves.leave_approve', compact('leaves'));
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
                // ignore invalid date filter
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
