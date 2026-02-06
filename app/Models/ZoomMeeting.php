<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic',
        'start_time',
        'duration',
        'agenda',
        'join_url',
        'start_url',
        'meeting_id',
        'password',
        'status',
        'created_by',
        'created_by_type',
    ];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public function creator()
    {
        return $this->morphTo('creator', 'created_by_type', 'created_by');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'zoom_meeting_attendees', 'zoom_meeting_id', 'user_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }
}
