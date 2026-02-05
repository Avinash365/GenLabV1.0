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

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, NewBooking::class, 'client_id', 'new_booking_id', 'id', 'id');
    } 
    
    public function bankTransactions()
    {
        return $this->belongsToMany(
            \App\Models\BankTransaction::class,
            'bank_transaction_client',
            'client_id',
            'bank_transaction_id'
        );
    }
}
