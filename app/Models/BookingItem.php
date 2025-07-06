<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'new_booking_id',
        'sample_description',
        'sample_quality',
        'lab_expected_date',
        'amount',
        'lab_analysis',
        'job_order_no'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'lab_expected_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the booking that owns this item.
     */
    public function booking()
    {
        return $this->belongsTo(NewBooking::class, 'new_booking_id');
    }
}