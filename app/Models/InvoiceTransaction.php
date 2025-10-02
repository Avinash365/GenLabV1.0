<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTransaction extends Model
{
    use HasFactory;

    protected $table = 'invoice_transactions';

    protected $fillable = [
        'invoice_id',
        'client_id',
        'marketing_person_id',
        'payment_mode',
        'transaction_date',
        'amount_received',
        'transaction_reference', 
        'notes',
        'created_by',
    ];

    /**
     * Relationships
     */

    // Each Transaction belongs to an Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // Each Transaction belongs to a Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Each Transaction belongs to a Marketing Person (user_code as PK)
    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_id', 'user_code');
    }

    // Created by a User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
