<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewBooking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'new_bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_name',
        'client_name',
        'client_address',
        'client_email',
        'client_phone',
        'job_order_date',
        'report_issue_to',
        'reference_no',
        'marketing_code',
        'contact_no',
        'contact_email',
        'contractor_name',
        'hold_status',
        'upload_letter_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'job_order_date' => 'date',
        'hold_status' => 'boolean'
    ];

    /**
     * Get the booking items for the booking.
     */
    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }
}