<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //

    public function __construct(){
        $this->middleware('permission:supplier.view'); 
    }

    public function index()
    {
        return view('superadmin.supplier.Supplier');  
    }
}
