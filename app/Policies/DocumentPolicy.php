<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Admin; 
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('document.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('document.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin|User $user): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('document.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, Document $document): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('document.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, Document $document): bool
    {
         if ($user instanceof Admin) return true;

        return $user->hasPermission('document.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin|User $user, Document $document): bool
    {
       return $user instanceof Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin|User $user, Document $document): bool
    {
        return $user instanceof Admin;
    }
}
