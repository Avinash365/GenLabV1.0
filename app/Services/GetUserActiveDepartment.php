<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class GetUserActiveDepartment
{
    public function getDepartment()
    {
        if (Auth::guard('admin')->check()) {
            // Admins get all active departments (as collection of objects)
            return Department::where('is_active', 1)->get();
        }

        // Other users get only departments they have permissions for
        $user = Auth::user();

        return Department::where('is_active', 1)
            ->whereHas('permissions', function ($q) use ($user) {
                $q->whereIn('permissions.id', $user->permissions->pluck('id'));
            })
            ->get();
    }
}
