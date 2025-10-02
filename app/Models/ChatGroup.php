<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the members of this chat group
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_group_members', 'group_id', 'user_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /**
     * Get the messages in this chat group
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'group_id');
    }

    /**
     * Get the last message in this group
     */
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'group_id')->latest();
    }

    /**
     * Get the user who created this group
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
