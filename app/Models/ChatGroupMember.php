<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroupMember extends Model
{
    use HasFactory;

    protected $table = 'chat_group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'last_seen_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];
}
