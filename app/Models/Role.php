<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'role_name',
        'description',
        'created_by',
        'updated_by',
    ];

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
