<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, HasApiTokens,Notifiable;

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

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

    /**
     * Relationships
     */
    public function products()
    {
        return $this->morphMany(Product::class, 'created_by');
    }

    public function bookings()
    {
        return $this->morphMany(NewBooking::class, 'created_by');
    }

    /**
     * Permission Check
     */
    public function hasPermission($permissionName)
    {
        //  for now always true, 
        // but ideally check against a `permissions` table or role system
        return true;
    }
}
