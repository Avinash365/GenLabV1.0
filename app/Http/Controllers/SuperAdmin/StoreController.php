<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    
    public function __construct(){
        $this->middleware('permission:store.view'); 
    }
    public function index()
    {
        return view('superadmin.store.Store');  
    }
}
