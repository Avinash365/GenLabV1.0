<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
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


    public function getJWTIdentifier()
    {
        return $this->getKey(); 
    }

    public function getJWTCustomClaims()
    {
        return []; 
    }

    /**
     * Relationship: User ↔ Role (one-to-Many)
     */
    
    
    public function role()
    {
        return $this->belongsTo(Role::class);
       
    }

    public function uploadedProfiles()
    {
        return $this->hasMany(Profile::class, 'uploaded_by');
    }

    public function uploadedApprovals(){
        return $this->hasMany(Approval::class, 'uploaded_by'); 
    }

    public function uploadedInportantletters(){
        return $this->hashMany(ImportantLetter::class, 'uploaded_by'); 
    }

    public function uploadedDocuments(){
        return $this->hashMany(Document::class, 'uploaded_by'); 
    }


    public function products()
    {
        return $this->morphMany(Product::class, 'created_by');
    }


    public function bookings(){
        return $this->morphMany(NewBooking::class, 'created_by');
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


    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'user_id', 'permission_id');
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions()
                    ->where('permission_name', $permissionName)
                    ->exists();
    }  

    public function marketingBookings()
    {
        return $this->hasMany(NewBooking::class, 'marketing_id', 'user_code'); 
    } 

}