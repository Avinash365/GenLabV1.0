<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Admin;
use App\Models\User;

class ProductPolicy
{
    /**
     * Anyone logged in can view list.
     */
    public function viewAny(Admin|User $user): bool
    {
        return true;
    }

    /**
     * Anyone logged in can view a product.
     */
    public function view(Admin|User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Only Admins can create products.
     */
    public function create(Admin|User $user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Admins: can update all
     * Users: can update only their own
     */
    public function update(Admin|User $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        if ($user instanceof User) {
            return $user->id === $product->created_by;
        }

        return false;
    }

    /**
     * Admins: can delete all
     * Users: can delete only their own
     */
    public function delete(Admin|User $user, Product $product): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        if ($user instanceof User) {
            return $user->id === $product->created_by;
        }

        return false;
    }

    /**
     * Restore (admins only).
     */
    public function restore(Admin|User $user, Product $product): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Force delete (admins only).
     */
    public function forceDelete(Admin|User $user, Product $product): bool
    {
        return $user instanceof Admin;
    }
}
