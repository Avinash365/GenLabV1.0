<?php

namespace App\Policies;

use App\Models\ImportantLetter;
use App\Models\User;
use App\Models\Admin; 
use Illuminate\Auth\Access\Response;

class ImportantLetterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('letter.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('letter.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('letter.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, ImportantLetter $importantLetter): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('letter.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, ImportantLetter $importantLetter): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('letter.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ImportantLetter $importantLetter): bool
    {
            return $user instanceof Admin;

    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ImportantLetter $importantLetter): bool
    {
            return $user instanceof Admin;

    }
}
