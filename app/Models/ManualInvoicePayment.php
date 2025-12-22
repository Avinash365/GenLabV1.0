<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManualInvoicePayment extends Model
{
    use HasFactory;

    protected $table = 'manual_invoice_payment';

    protected $fillable = [
        'invoice_no',
        'client_id', 
        'invoice_generated_date',
        'total_amount',
        'payment_date',
        'status',
        'marketing_person',
        'created_by',
    ];

    protected $casts = [
        'invoice_generated_date' => 'date',
        'payment_date'           => 'date',
        'total_amount'           => 'decimal:2',
        'status'                 => 'integer',
    ];

    /**
     * Marketing person (users.user_code)
     */
    public function marketingUser()
    {
        return $this->belongsTo(User::class, 'marketing_person', 'user_code');
    }

    /**
     * Creator (users.user_code)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id'); 
    }

    /* --------------------
     | Status Helpers
     |--------------------*/

    public function isPaid(): bool
    {
        return $this->status === 1;
    }

    public function isUnpaid(): bool
    {
        return $this->status === 0;
    }
}
