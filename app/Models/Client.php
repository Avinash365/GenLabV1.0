<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'gstin', 'address'
    ];

    public function bookings()
    {
        return $this->hasMany(NewBooking::class);
    }
}
