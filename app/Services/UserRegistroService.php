<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserRegistroService
{
    
    
    public function index(){ 

        // Eager load role and permissions for each user
        $users = User::with(['role.permissions'])->get();
        $roles = Role::all();
        return view('superadmin.users.index', compact('users','roles'));

    }

    public function create()
    {
        $roles = Role::all(); // or your roles fetching logic
        $permissions = Permission::all(); // or your permissions fetching logic

        return view('superadmin.users.create', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a new user.
     *
     * @param Request $request
     * @return User|null
     */
    public function store(Request $request)
    {
        try {
          
            // Validate request
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'user_code'   => 'required|string|max:255|unique:users,user_code',
                'role'     => 'required|exists:roles,id',
                'password'    => 'required|string|min:6|confirmed',
            ]);

            // Create user
            $user = User::create([
                'name'       => $validated['name'],
                'user_code'  => $validated['user_code'],
                'role_id'    => $validated['role'],
                'password'   => Hash::make($validated['password']),
                'created_by' => auth()->id() ?? null,
            ]);

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

    /**
     * Update an existing user.
     *
     * @param Request $request
     * @param int $userId
     * @return User|null
     */
    public function update(Request $request, int $userId)
    {
        try {
            $user = User::findOrFail($userId);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'user_code'   => "required|string|max:255|unique:users,user_code,{$userId}",
                'role_id'     => 'required|exists:roles,id',
                'password'    => 'nullable|string|min:6|confirmed',
            ]);

            $data = [
                'name'       => $validated['name'],
                'user_code'  => $validated['user_code'],
                'role_id'    => $validated['role_id'],
                'updated_by' => auth('admin')->id() ?? null,
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);
            
            Log::info("User updated successfully", ['user_id' => $user->id, 'admin_id' => auth('admin')->id()]);

             return back()->with('success', 'User update successfully.');

        } catch (ValidationException $e) {
            Log::warning("User update validation failed", ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update user", ['message' => $e->getMessage(), 'user_id' => $userId]);
            return null;
        }
    }

    /**
     * Soft delete a user.
     *
     * @param int $userId
     * @return bool
     */
    public function delete(int $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            Log::info("User soft-deleted", ['user_id' => $userId, 'admin_id' => auth('admin')->id()]);

            return back()->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            Log::error("Failed to delete user", ['message' => $e->getMessage(), 'user_id' => $userId]);
            return false;
        }
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param int $userId
     * @return bool
     */
    public function restore(int $userId): bool
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->restore();

            Log::info("User restored", ['user_id' => $userId, 'admin_id' => auth('admin')->id()]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to restore user", ['message' => $e->getMessage(), 'user_id' => $userId]);
            return false;
        }
    }
}
