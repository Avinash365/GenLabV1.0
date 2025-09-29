<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank',
        'cheque_no',
        'payee_name',
        'date',
        'purpose',
        'handed_over_to',
        'amount',
        'amount_in_words',
        'status',
        'received_party_name',
        'received_cheque_date',
        'received_amount',
        'received_copy_path',
        'received_note',
        'deposit_date',
        'deposit_person',
        'deposit_status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'received_cheque_date' => 'date',
        'received_amount' => 'decimal:2',
        'deposit_date' => 'date',
        'deposit_status' => 'boolean',
    ];
}
