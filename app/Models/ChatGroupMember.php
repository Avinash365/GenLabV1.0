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
        'role',
        'last_seen_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * Get the group this membership belongs to
     */
    public function group()
    {
        return $this->belongsTo(ChatGroup::class, 'group_id');
    }

    /**
     * Get the user for this membership
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
