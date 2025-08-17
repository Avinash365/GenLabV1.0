<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_items';

    protected $fillable = [
        'new_booking_id',
        'sample_description',
        'sample_quality',
        'lab_expected_date',
        'amount',
        'lab_analysis',
        'job_order_no',
    ];

    protected $casts = [
        'lab_expected_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: BookingItem belongs to a NewBooking
     */
    public function booking()
    {
        return $this->belongsTo(NewBooking::class, 'new_booking_id');
    }
}
