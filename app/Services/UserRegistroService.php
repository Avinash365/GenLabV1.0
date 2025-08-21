<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UserRegistroService
{
    
    
    public function index(){ 
   
        $users = User::with(['role.permissions'])->get();
        $roles = Role::all();
        $permissions = Permission::all();
        return view('superadmin.users.index', compact('users', 'roles', 'permissions'));
    }

    public function create()
    {
        
        // Load roles with only 'id' and 'role_name', and their permissions (id + name)
        $roles = Role::with(['permissions:id,permission_name,id']) 
                    ->get(['id', 'role_name']);

        // Load all permissions for the matrix table
        $permissions = Permission::all();

        return view('superadmin.users.create', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    
    public function store(Request $request)
    {
        try {
        
            // Validate request
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'user_code'   => 'required|string|max:255|unique:users,user_code',
                'role'     => 'required|exists:roles,id',
                'password'    => 'required|string|min:6|confirmed',
                'permissions' => 'nullable|array',
            ]);

            // Create user
            $user = User::create([
                'name'       => $validated['name'],
                'user_code'  => $validated['user_code'],
                'role_id'    => $validated['role'],
                'password'   => Hash::make($validated['password']),
                'created_by' => null,
            ]);

            // Attach permissions if any
            $user->permissions()->sync($validated['permissions'] ?? []);

            Log::info("User created successfully", ['user_id' => $user->id, 'admin_id' => auth('admin')->id()]);
            
            $roles = Role::all(); 
            // $permissions = Permission::all();

            return back()->with('success', 'User created successfully.');


        } catch (ValidationException $e) { 
            Log::warning("User creation validation failed", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to create user", ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function update(Request $request, User $user)
    {
    
        try { 
            
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'user_code'   => "required|string|max:255|unique:users,user_code,{$user->id}",
                'role_id'     => 'required|exists:roles,id',
                'password'    => 'nullable|string|min:6|confirmed',
            ]);

            $data = [
                'name'       => $validated['name'],
                'user_code'  => $validated['user_code'],
                'role_id'    => $validated['role_id'],
                'updated_by' =>  null,
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            Log::info("User updated successfully", [
                'user_id'  => $user->id,
                'admin_id' => auth('admin')->id()
            ]);

            return back()->with('success', 'User updated successfully.');

        } catch (ValidationException $e) {
            Log::warning("User update validation failed", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update user", ['message' => $e->getMessage(), 'user_id' => $user->id]);
            return back()->with('error', 'Failed to update user.');
        }
    }

    public function updatePermissions(Request $request, User $user)
    {
        

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($validated['permissions'] ?? []);
       
        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }

    public function delete(User $user)
    {
        try {
            $user->delete();

            Log::info("User soft-deleted", [
                'user_id'  => $user->id,
                'admin_id' => auth('admin')->id()
            ]);

            return back()->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            Log::error("Failed to delete user", ['message' => $e->getMessage(), 'user_id' => $user->id]);
            return back()->with('error', 'Failed to delete user.');
        }
    }

   
    public function restore(User $user): bool
    {
        try {
            // Ensure we can restore soft-deleted user
            $user->restore();

            Log::info("User restored", [
                'user_id'  => $user->id,
                'admin_id' => auth('admin')->id()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to restore user", [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return false;
        }
    }
}
