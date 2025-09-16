<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewBooking;
use App\Models\Department;
use App\Services\GetUserActiveDepartment;


class ShowBookingController extends Controller
{
    
    protected $departmentService;
    
    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;

    }
    
    public function index(Request $request, Department $department = null)
{
    $search = $request->input('search');
    $month  = $request->input('month');
    $year   = $request->input('year');

    $query = NewBooking::with(['items', 'department', 'marketingPerson']);

    // Filter by department
    if ($department) {
        $query->where('department_id', $department->id);
    }

    // Search filter
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
              ->orWhere('reference_no', 'like', "%{$search}%")
              ->orWhere('client_name', 'like', "%{$search}%")
              ->orWhere('contact_email', 'like', "%{$search}%")
              ->orWhere('contact_no', 'like', "%{$search}%")
              ->orWhereHas('department', function ($deptQ) use ($search) {
                  $deptQ->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('items', function ($itemQ) use ($search) {
                  $itemQ->where('sample_description', 'like', "%{$search}%")
                        ->orWhere('sample_quality', 'like', "%{$search}%")
                        ->orWhere('lab_analysis_code', 'like', "%{$search}%")
                        ->orWhere('particulars', 'like', "%{$search}%");
              })
              ->orWhereHas('marketingPerson', function ($mpQ) use ($search) {
                  $mpQ->where('name', 'like', "%{$search}%");
              })
              ->orWhere('job_order_date', 'like', "%{$search}%");
        });
    }

    // Month & Year filter
    if (!empty($month)) {
        $query->whereMonth('job_order_date', $month);
    }

    if (!empty($year)) {
        $query->whereYear('job_order_date', $year);
    }

    $bookings = $query->latest()->paginate(10);

    $departments = $this->departmentService->getDepartment();
    
    // return view('superadmin.showbooking.bookingByLetter', compact('bookings', 'department', 'departments', 'search', 'month', 'year'));

    return view('superadmin.showbooking.showbooking', compact('bookings', 'department', 'departments', 'search', 'month', 'year'));
}

}