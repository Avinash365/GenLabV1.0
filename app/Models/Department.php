<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
