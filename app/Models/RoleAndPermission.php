<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleAndPermission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'role_and_permissions';

    protected $fillable = [
        'role_name',
        'permissions',
        'is_active',
        'created_by',
        'updated_by',
        'description',
        'slug',
        'icon',
        'color',
        'type',
        'group',
        'group_slug',
        'group_icon',
        'group_color',
        'group_type',
        'group_description'
    ];

    protected $casts = [
        'permissions' => 'array', // Assuming permissions are stored as a JSON array
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    protected $hidden = ['created_by', 'updated_by'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
