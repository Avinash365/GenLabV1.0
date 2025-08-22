<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Department;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'permission_name',
        'description',
    ]; 
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_and_permissions');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

} 
