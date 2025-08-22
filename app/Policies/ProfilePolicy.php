<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\Admin; 

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('profile.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
                if ($user instanceof Admin) return true;

        return $user->hasPermission('profile.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
                if ($user instanceof Admin) return true;

        return $user->hasPermission('profile.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, Profile $profile): bool
    {
                if ($user instanceof Admin) return true;

        return $user->hasPermission('profile.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, Profile $profile): bool
    {
                if ($user instanceof Admin) return true;

        return $user->hasPermission('profile.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin|User $user, Profile $profile): bool
    {
            return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin|User $user, Profile $profile): bool
    {
            return $user instanceof Admin;
    }
}
