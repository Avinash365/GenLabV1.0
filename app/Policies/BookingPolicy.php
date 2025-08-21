<?php

namespace App\Policies;

use App\Models\NewBooking;
use App\Models\Admin; 
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin|User $user): bool
    {
        
        if($user instanceof Admin) return true; 
        return $user->hasPermission('booking.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin|User $user): bool
    {
         
            if ($user instanceof Admin )  return true; 
            return $user->hasPermission('booking.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create( Admin|User $user): bool
    {
        
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('booking.create');   
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin|User $user, NewBooking $newBooking): bool
    {
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('booking.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin|User $user, NewBooking $newBooking): bool
    {
        if($user instanceof Admin )  return true;  
        return $user->hasPermission('booking.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore( Admin| User $user, NewBooking $newBooking): bool
    {
         if($user instanceof Admin )  return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete( Admin|User $user, NewBooking $newBooking): bool
    {
        if($user instanceof Admin )  return true;
    }
}
