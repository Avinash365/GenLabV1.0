<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ]; 

    public function products()
    {
        return $this->morphMany(Product::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(NewBooking::class, 'admin_id');
    }
    
    public function hasPermission($permissionName)
    {
        return true;
    } 
    
}
