<?php

namespace App\Policies;

use App\Models\ISCode;
use App\Models\User;
use App\Models\Admin; 
use Illuminate\Auth\Access\Response;

class ISCodePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('iscode.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('iscode.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('iscode.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, ISCode $iSCode): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('iscode.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, ISCode $iSCode): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('iscode.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin|User $user, ISCode $iSCode): bool
    {
        return $user instanceof Admin;   
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin|User $user, ISCode $iSCode): bool
    {
        return $user instanceof Admin;
    }
}
