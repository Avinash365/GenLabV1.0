<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class InvoiceBookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_booking_id',
        'invoice_no',
        'job_order_no',
        'qty',
        'rate',
        'sample_discription'
    ];

    /**
     * Each booking item belongs to one invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_booking_id');
    }
}
