<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTds extends Model
{
    use HasFactory;

    protected $table = 'invoice_tds';

    protected $fillable = [
        'invoice_id',
        'client_id',
        'marketing_person_id',
        'tds_percentage',
        'tds_amount', 
        'amount_after_tds', 
        'created_by',
    ];

    /**
     * Relationships
     */

    // Each TDS belongs to an Invoice
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'invoice_id');
    }

    // Each TDS belongs to a Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Each TDS belongs to a Marketing Person (user_code as PK)
    public function marketingPerson()
    {
        return $this->belongsTo(MarketingPerson::class, 'marketing_person_id', 'user_code');
    }

    // Created by a User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
