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
        ]);
       
        try {
            // Create role
            $role = Role::create([
                'role_name'  => $validated['role_name'],
                'created_by' => auth('admin')->id(),
            ]);

            // Attach permissions if any
            $role->permissions()->sync($validated['permissions'] ?? []);

            return redirect()
                ->route('superadmin.roles.index')
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            Log::error('Role creation failed', [
                'error'   => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Something went wrong while creating the role.');
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('superadmin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_name'   => "required|string|max:255|unique:roles,role_name,{$role->id}",
            'permissions' => 'nullable|array'
        ]);

        try {
            // dd($request->all());
            // exit; 

            // Update role name and updated_by
            $role->update([
                'role_name'  => $validated['role_name'],
                'updated_by' => null,
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
                'error'   => $e->getMessage(),
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
    public function destroy(Role $role)
    {
        try {

            $role->delete();

            return redirect()
                ->route('superadmin.roles.index')
                ->with('success', 'Role deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Role not found.');

        } catch (\Exception $e) {
            Log::error('Role deletion failed', [
                'error'   => $e->getMessage(),
                'role_id' => $id,
            ]);

            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Something went wrong while deleting the role.');
        }
    }

    /**
     * Display the specified role and its permissions.
     */
    public function show(Role $role)
    {
        $role->load('permissions');

        return view('superadmin.roles.show', [
            'role'        => $role,
            'permissions' => $role->permissions,
        ]);
    }
}
