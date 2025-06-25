<?php

namespace App\Services;

use App\Models\RoleAndPermission;
use Illuminate\Http\Request;

class RoleAndPermissionService
{
    protected array $defaultPermissions = [
        'dashboard.view',
        'user.manage',
        'role.manage',
        'content.edit',
        'settings.update',
    ];

    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = RoleAndPermission::all();
        return view('superadmin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('superadmin.roles.create', [
            'permissions' => $this->defaultPermissions,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name'   => 'required|string|max:255|unique:role_and_permissions,role_name',
            'permissions' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $validated['permissions'] = json_encode($request->input('permissions', []));
        $validated['created_by'] = auth('admin')->id();

        RoleAndPermission::create($validated);

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        $role = RoleAndPermission::findOrFail($id);

        return view('superadmin.roles.edit', [
            'role' => $role,
            'permissions' => $this->defaultPermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        $role = RoleAndPermission::findOrFail($id);

        $validated = $request->validate([
            'role_name'   => "required|string|max:255|unique:role_and_permissions,role_name,{$id}",
            'permissions' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $validated['permissions'] = json_encode($request->input('permissions', []));
        $validated['updated_by'] = auth('admin')->id();

        $role->update($validated);

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        $role = RoleAndPermission::findOrFail($id);
        $role->delete();

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Display the specified role and its permissions.
     */
    public function show($id)
    {
        $role = RoleAndPermission::findOrFail($id);

        return view('superadmin.roles.show', [
            'role' => $role,
            'permissions' => $this->defaultPermissions,
        ]);
    }
}
