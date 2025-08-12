<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IssueViewController extends Controller
{
    //
     public function index()
    {
        return view('superadmin.issueview.issueView');  
    }
}
