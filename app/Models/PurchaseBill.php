<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $table = 'purchase_bills';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'description',
        'party',
        'amount',
        'gst_amount',
        'purchased_by',
        'bill_upload',
        'gst_type',
        'purchase_date',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'purchase_date' => 'date',
    ];
}
