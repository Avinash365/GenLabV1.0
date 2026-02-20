<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'meta_message_id',
        'phone_number',
        'receiver_number',
        'message',
        'type',
        'status',
        'media_info',
        'meta_timestamp',
        'raw_data',
    ];

    protected $casts = [
        'media_info' => 'array',
        'meta_timestamp' => 'datetime',
    ];
}
