<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /** 
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_code',
        'password',
        'role_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_verified_at' => 'datetime',
    ]; 

    
    /**
     * Relationship: User ↔ Role (one-to-Many)
     */
    
    public function role()
    {
        return $this->belongsTo(Role::class);
       
    }


    public function products()
    {
        return $this->morphMany(Product::class, 'created_by');
    }

    /**
     * Relationship: User created by an Admin
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Relationship: User updated by an Admin
     */
    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    } 


    public function hasPermission($permissionName)
    {
        return $this->role
            ->permissions()
            ->where('permission_name', $permissionName)
            ->exists();
    } 
}
