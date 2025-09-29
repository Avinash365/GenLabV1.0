<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashLetterPartialPaymentEntry extends Model
{
    use HasFactory;

    protected $table = 'cash_letter_partial_payment_entry';

    protected $fillable = [
        'client_id',
        'marketing_person_id',
        'cash_letter_payment_id',
        'payment_mode',
        'transaction_date',
        'amount_received',
        'note',
        'created_by'
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_id', 'user_code');
    }

    public function cashLetterPayment()
    {
        return $this->belongsTo(CashLetterPayment::class, 'cash_letter_payment_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
