<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'role_name',
        'slug',
        'created_by',
        'updated_by',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            $role->slug = Str::slug($role->role_name, '_');   // e.g. "Tech Manager" -> "tech_manager"
        });
    }


    /**
     * A role can belong to many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * A role can have many permissions.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_and_permissions')
                    ->withTimestamps();
    }

    /**
     * Role created by an Admin.
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Role updated by an Admin.
     */
    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
