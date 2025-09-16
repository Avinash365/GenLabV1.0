<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'amount',
        'notes',
        'invoice_ids'
    ];

    protected $casts = [
        'invoice_ids' => 'array',
    ];
}
