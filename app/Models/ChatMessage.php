<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id', 'user_id', 'type', 'content', 'file_path', 'original_name',
        'sender_guard', 'sender_name', 'reply_to_message_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatReaction::class, 'message_id');
    }
}
