<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingEnquiry extends Model
{
    use HasFactory;

    protected $table = 'marketing_enquiries';

    protected $fillable = [
        'booking_item_id',
        'user_id',
        'note',
        'media_paths',
    ];

    protected $casts = [
        'media_paths' => 'array',
    ];

    public function bookingItem()
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
