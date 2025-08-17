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
        'admin_id',
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
        'upload_letter_path',
    ];

    protected $casts = [
        'job_order_date' => 'date',
        'hold_status' => 'boolean',
    ];

    /**
     * Relationship: NewBooking belongs to an Admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Relationship: NewBooking has many BookingItems
     */
    public function items()
    {
        return $this->hasMany(BookingItem::class, 'new_booking_id');
    }
}
