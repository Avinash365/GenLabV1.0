<?php 

namespace App\Policies;

use App\Models\Product;
use App\Models\Admin;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('product.view');
    }


    public function view(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('product.view');
    }

    
    public function create(Admin|User $user): bool
    {
        if ($user instanceof Admin) return true;

        return $user->hasPermission('product.create');
    }


    public function update(Admin|User $user, Product $product): bool
    {
        if ($user instanceof Admin) return true;

        // Users can update only if they have permission and own the product
        return $user->hasPermission('product.edit');
    }


    public function delete(Admin|User $user, Product $product): bool
    {
        if ($user instanceof Admin) return true;

        // Users can delete only if they have permission and own the product
        return $user->hasPermission('product.delete');
    }

    public function restore(Admin|User $user, Product $product): bool
    {
        return $user instanceof Admin;
    }
     
    public function forceDelete(Admin|User $user, Product $product): bool
    {
        return $user instanceof Admin;
    }
}
