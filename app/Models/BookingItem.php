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
        'lab_analysis_code',
        'job_order_no',
    'received_by_id',
    'received_by_name',
    'received_at',
    'issue_date',
    ];

    protected $casts = [
        'lab_expected_date' => 'date',
        'amount' => 'decimal:2',
    'received_at' => 'datetime',
    'issue_date' => 'date',
    ];

    /**
     * Relationship: BookingItem belongs to a NewBooking
     */
    public function booking()
    {
        return $this->belongsTo(NewBooking::class, 'new_booking_id');
    }

    /**
     * Relationship: BookingItem belongs to an Analyst (User) via lab_analysis_code -> users.user_code
     */
    public function analyst()
    {
        return $this->belongsTo(User::class, 'lab_analysis_code', 'user_code');
    }

    /**
     * Relationship: BookingItem received by (User)
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }
}
