<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class NewBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'new_bookings';

    protected $fillable = [

        'client_name',
        'client_address',
        'letter_date',
        'job_order_date',
        'report_issue_to',
        'reference_no',
        'marketing_id',
        'contact_no',
        'contact_email',
        'name_of_work', 
        'department_id', 
        'hold_status',
        'payment_option', 
        'upload_letter_path', 
        'm_s', 
        'created_by_id',
        'created_by_type', 
    ];

    protected $casts = [
        'job_order_date' => 'date',
        'hold_status' => 'boolean',
    ];


    // Inside your NewBooking model
   
    /**
     * Relationship: NewBooking has many BookingItems
     */
    public function items()
    {
        return $this->hasMany(BookingItem::class, 'new_booking_id');
    } 

    public function creator(){
        return $this->morphTo(null, 'created_by_type', 'created_by_id'); 
    } 
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function cards()
    {
        return $this->hasMany(BookingCard::class);
    }  

    public function marketingPerson()
    {
         return $this->belongsTo(User::class, 'marketing_id', 'user_code');
    }

    public function generatedInvoice()
    {
        return $this->hasOne(Invoice::class, 'new_booking_id');
    }  
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    } 

     public function getTotalAmountAttribute(): float
    {
        return $this->items()->sum('amount') ?? 0;
    }

    public function cashLetterPayments()
    {
        return $this->belongsToMany(
            CashLetterPayment::class,            // Related model
            'cash_letter_payment_bookings',      // Pivot table
            'booking_id',                        // Foreign key on pivot for this model (NewBooking)
            'cash_letter_payment_id'             // Foreign key on pivot for related model (CashLetterPayment)
        )
        ->withPivot('payment_status')
        ->withTimestamps();
    }

}
