<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'backed_booking'
    ];

    protected $casts = [
        'backed_booking' => 'boolean'
    ];
}
