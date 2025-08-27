<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\GetUserActiveDepartment;
use App\Models\Department;

class DashboardController extends Controller
{
    protected $departmentService;

    public function __construct(GetUserActiveDepartment $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index()
    {
       
        $departments = $this->departmentService->getDepartment();
        return view('superadmin.dashboard', compact('departments'));
    }
}
