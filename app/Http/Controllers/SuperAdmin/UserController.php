<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission; 
use Illuminate\Http\Request;
use App\Services\UserRegistroService; 


class UserController extends Controller
{
    
    protected UserRegistroService $service;

    public function __construct(UserRegistroService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
       return $this->service->index(); 
    }

    public function create()
    {
       return $this->service->create(); 
    }  

    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    
    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
