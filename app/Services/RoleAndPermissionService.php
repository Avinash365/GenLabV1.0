<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleAndPermissionService
{
    /**
     * Default system permissions (optional fallback).
     */
    protected array $defaultPermissions = [
        'dashboard.view',
        'user.manage',
        'role.manage',
        'content.edit',
        'settings.update',
    ];

    /**
     * Display a listing of the roles with their permissions.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('superadmin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('superadmin.roles.create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name'   => 'required|string|max:255|unique:roles,role_name',
            'permissions' => 'nullable|array',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            // Create role
            $role = Role::create([
                'role_name'   => $validated['role_name'],
                'description' => $validated['description'] ?? null,
                'created_by'  => auth('admin')->id(),
            ]);

            // Attach permissions (IDs directly)
            if (!empty($validated['permissions'])) {
                $role->permissions()->attach($validated['permissions']);
            }

            return redirect()
                ->route('superadmin.roles.index')
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            Log::error('Role creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Something went wrong while creating the role.');
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('superadmin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'role_name'   => "required|string|max:255|unique:roles,role_name,{$id}",
            'permissions' => 'nullable|array',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $role = Role::findOrFail($id);

            // Update role details
            $role->update([
                'role_name'   => $validated['role_name'],
                'description' => $validated['description'] ?? null,
                'updated_by'  => auth('admin')->id(),
            ]);

            // Sync permissions
            $role->permissions()->sync($validated['permissions'] ?? []);

            return redirect()
                ->route('superadmin.roles.index')
                ->with('success', 'Role updated successfully.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Role not found.');
                
        } catch (\Exception $e) {
            Log::error('Role update failed', [
                'error' => $e->getMessage(),
                'role_id' => $id,
                'request' => $request->all(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Something went wrong while updating the role.');
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(int $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return redirect()
                ->route('superadmin.roles.index')
                ->with('success', 'Role deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Role not found.');

        } catch (\Exception $e) {
            Log::error('Role deletion failed', [
                'error' => $e->getMessage(),
                'role_id' => $id,
            ]);

            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Something went wrong while deleting the role.');
        }
    }

    /**
     * Display the specified role and its permissions.
     */
    public function show(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('superadmin.roles.show', [
            'role' => $role,
            'permissions' => $role->permissions,
        ]);
    }
}
