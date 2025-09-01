<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'new_booking_id', 
        'generated_by', 
        'invoice_no',
        'type', 
        'letter_date',
        'name_of_work',
        'client_gstin',
        'sac_code',
        'invoice_letter_path',
        'issue_to', 
        'discount_percent', 
        'cgst_percent', 
        'sgst_percent', 
        'igst_percent', 
        'gst_amount', 
        'total_amount', 
        'round_of'
    ];

    /**
     * One Invoice has many booking items
     */

    public function bookingItems()
    {
        return $this->hasMany(InvoiceBookingItem::class, 'invoice_booking_id');
    }  

    public function relatedBooking()
    {
        return $this->belongsTo(NewBooking::class, 'new_booking_id');
    }

}
