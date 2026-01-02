<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'engine_number', 'handed_over_person', 'rc_expiry_date', 'rc_copy_path', 'insurance_path', 'puc_path', 'puc_expiry_date'
    ];

    protected $dates = ['rc_expiry_date', 'puc_expiry_date'];
    protected $casts = [
        'rc_expiry_date' => 'date',
        'puc_expiry_date' => 'date',
    ];

    public function services()
    {
        return $this->hasMany(VehicleService::class);
    }
}
