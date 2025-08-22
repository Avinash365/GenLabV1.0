<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calibration extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_name',
        'equipment_name',
        'issue_date',
        'expire_date',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expire_date' => 'date',
    ];

    // Relation with User (if needed)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
