<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Models\Admin; 
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true; 
        return $user->hasPermission('department.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true; 
        return $user->hasPermission('department.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('department.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, Department $department): bool
    {
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('department.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, Department $department): bool
    {
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('department.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin|User $user, Department $department): bool
    {
        if($user instanceof Admin )  return true;  
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin|User $user, Department $department): bool
    {
        if($user instanceof Admin )  return true;    
        return true; 
    }
}
