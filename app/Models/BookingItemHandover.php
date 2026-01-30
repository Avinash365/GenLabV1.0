<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItemHandover extends Model
{
    protected $table = 'booking_item_handovers';
    protected $guarded = [];
    protected $dates = ['handed_over_at'];
    protected $casts = [
        'handed_over_at' => 'datetime',
    ];

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class, 'booking_item_id');
    }
}
