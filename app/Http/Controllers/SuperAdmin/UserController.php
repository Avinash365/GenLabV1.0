<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission; 
use Illuminate\Http\Request;
use App\Services\UserRegistroService; 
use App\Models\User;


class UserController extends Controller
{
    
    protected UserRegistroService $service;

    public function __construct(UserRegistroService $service)
    {
        $this->service = $service;
        $this->authorizeResource(User::class, 'user');
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

    
    public function update(Request $request, User $user)
    {
        return $this->service->update($request, $user);
    }

    public function updatePermissions(Request $request, User $user){
        
        $this->authorize('update', $user);
        return $this->service->updatePermissions($request, $user);
    }

    public function destroy(User $user)
    {
        return $this->service->delete($user);
    }
}