<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeetingAttendee extends Model
{
    use HasFactory;

    protected $table = 'zoom_meeting_attendees';

    protected $fillable = [
        'zoom_meeting_id',
        'user_id',
        'user_type',
        'attendee_name',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meeting_id');
    }
}
