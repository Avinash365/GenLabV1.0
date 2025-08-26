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
        'job_order_date',
        'report_issue_to',
        'reference_no',
        'marketing_id',
        'contact_no',
        'contact_email',
        'department_id', 
        'hold_status',
        'booking_type', 
        'upload_letter_path', 
        'created_by_id',
        'created_by_type', 
    ];

    protected $casts = [
        'job_order_date' => 'date',
        'hold_status' => 'boolean',
    ];

    
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
    
}
