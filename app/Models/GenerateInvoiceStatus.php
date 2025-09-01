<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerateInvoiceStatus extends Model
{
    use HasFactory;
    protected $table = 'booking_invoice_statuses'; 

    protected $fillable = [
        'new_booking_id', 
        'generate_invoice_status'
    ]; 

    public function booking(){
        return $this->belongsTo(NewBooking::class, 'new_booking_id'); 
    }

}
