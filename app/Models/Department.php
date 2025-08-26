<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Permission;


class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'codes',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'codes' => 'array',       // JSON column
        'is_active' => 'boolean', // boolean column
    ];

    public function bookings()
    {
        return $this->hasMany(NewBooking::class);
    }

    public function bookingsWithDepartment()
    {
        return $this->bookings()->with('department'); // eager load department
    }


    public function permissions()
    {
        return $this->hasMany(Permission::class, 'department_id');
    }


    /**
     * Optional: Accessor to return codes as comma-separated string
     */
    public function getCodesStringAttribute()
    {
        return implode(', ', $this->codes ?? []);
    }

    /**
     * Optional: Mutator to ensure codes are always uppercase
     */
    public function setCodesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['codes'] = json_encode(array_map('strtoupper', $value));
        } else {
            $this->attributes['codes'] = json_encode([]);
        }
    }  


    protected static function booted()
    {
        static::created(function ($department) {
            $department->syncPermissions();
        });

        static::updated(function ($department) {
            $department->syncPermissions();
        });

        static::deleted(function ($department) {
            $department->deletePermissions();
        });
    }

    public function syncPermissions()
    {
        $permissions = [
            'dept_' . $this->name . '.view',
            'dept_' . $this->name . '.create',
            'dept_' . $this->name . '.edit',
            'dept_' . $this->name . '.delete',
        ];

        // Delete old permissions for this department
        $this->deletePermissions();

        // Insert new permissions
        foreach ($permissions as $perm) {
            Permission::create([
                'permission_name' => $perm,
                'department_id' => $this->id, 
            ]);
        }
    } 

    // Delete permissions
    public function deletePermissions()
    {
        Permission::where('department_id', $this->id)->delete();
    }

}
