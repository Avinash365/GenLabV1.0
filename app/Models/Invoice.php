<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


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
        'address', 
        'round_of', 
        'invoice_date'
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


     // Mutator → runs when saving to DB
    public function setInvoiceDateAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['invoice_date'] = Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        }
    }

    public function getInvoiceDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

}
