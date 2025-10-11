<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('user.view');
    }

    /**
     * Determine whether the user can view a specific user.
     */
    public function view(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('user.view');
    }

    /**
     * Determine whether the user can create a user.
     */
    public function create(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('user.create');
    }

    /**
     * Determine whether the user can update a specific user.
     */
    public function update(Admin|User $user, User $targetUser): bool
    {
        if ($user instanceof Admin) return true;

        // Optional: Only allow update if the user has permission and owns the target user
        // return $user->hasPermission('user.edit') && $user->id === $targetUser->id;

        if ($user->id === $targetUser->id) {
            return false;
        }

        return $user->hasPermission('user.edit');
    }

    /**
     * Determine whether the user can delete a specific user.
     */
    public function delete(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('user.delete');
    }

    /**
     * Determine whether the user can restore a specific user.
     */
    public function restore(Admin|User $user, User $targetUser): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete a specific user.
     */
    public function forceDelete(Admin|User $user, User $targetUser): bool
    {
        return $user instanceof Admin;
    }
}
