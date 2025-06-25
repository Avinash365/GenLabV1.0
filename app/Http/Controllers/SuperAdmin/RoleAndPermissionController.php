<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleAndPermissionService;

class RoleAndPermissionController extends Controller
{
    protected RoleAndPermissionService $service;

    public function __construct(RoleAndPermissionService $service)
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

    public function edit($id)
    {
        return $this->service->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    // public function assignPermissions(Request $request, $id)
    // {
    //     return $this->service->assignPermissions($request, $id);
    // }

    // public function removePermissions(Request $request, $id)
    // {
    //     return $this->service->removePermissions($request, $id);
    // }

    // public function toggleActiveStatus($id)
    // {
    //     return $this->service->toggleActiveStatus($id);
    // }

    // public function bulkDelete(Request $request)
    // {
    //     return $this->service->bulkDelete($request);
    // }

    // public function exportRoles()
    // {
    //     return $this->service->exportRoles();
    // }

    // public function importRoles(Request $request)
    // {
    //     return $this->service->importRoles($request);
    // }

    // public function searchRoles(Request $request)
    // {
    //     return $this->service->searchRoles($request);
    // }
}
