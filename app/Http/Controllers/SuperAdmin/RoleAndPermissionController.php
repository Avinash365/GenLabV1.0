<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleAndPermissionService;
use App\Models\Role; 

class RoleAndPermissionController extends Controller
{
    protected RoleAndPermissionService $service;

    public function __construct(RoleAndPermissionService $service)
    {
        $this->service = $service;
        $this->authorizeResource(Role::class, 'role');
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

    public function edit(Role $role)
    {
        return $this->service->edit($role);
    }   

    public function update(Request $request, Role $role)
    {
        return $this->service->update($request, $role);
    }

    public function destroy(Role $role)
    {
        return $this->service->destroy($role);
    }

    public function show(Role $role)
    {
        return $this->service->show($role);
    }

}
