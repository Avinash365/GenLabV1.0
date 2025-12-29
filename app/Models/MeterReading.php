<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $table = 'meter_readings';

    protected $fillable = [
        'start_description',
        'starting_reading',
        'starting_image_path',
        'starting_at',
        'end_description',
        'ending_reading',
        'ending_at',
        'image_path',
        'total_reading',
        'user_id',
    ];

    protected $casts = [
        'starting_reading' => 'float',
        'ending_reading' => 'float',
        'total_reading' => 'float',
        'starting_at' => 'datetime',
        'ending_at' => 'datetime',
    ];
}
