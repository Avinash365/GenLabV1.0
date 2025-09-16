<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithoutBillTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'total_amount',
        'paid_amount',
        'due_amount',
        'carry_forward',
        'payment_mode',
        'reference',
        'payment_month',
    ];

    protected $casts = [
        'payment_month' => 'date',
    ];

    // relation with client (assuming client stored in users table)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
