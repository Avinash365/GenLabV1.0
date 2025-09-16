<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'marketing_person_id',
        'tds_percentage',
        'amount_after_tds',
        'payment_mode',
        'transaction_date',
        'amount_received',
        'notes',
        'created_by',
        'tax_amount'
    ];

    /**
     * Relationship with Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relationship with Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relationship with the user who created this payment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with Marketing Person using user_code
     */
    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_id', 'user_code');
    }
}
