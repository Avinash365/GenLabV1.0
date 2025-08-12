<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    //
    public function index()
    {
        return view('superadmin.issue.Issue');  
    }
}
