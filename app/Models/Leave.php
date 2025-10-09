<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_name', 
        'leave_type',
        'from_date',
        'to_date',
        'days_hours',
        'day_type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_comments'
    ];

    protected $dates = [
        'from_date',
        'to_date', 
        'approved_at'
    ];

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Calculate days between from and to date
    public function calculateDays()
    {
        $fromDate = \Carbon\Carbon::parse($this->from_date);
        $toDate = \Carbon\Carbon::parse($this->to_date);
        return $fromDate->diffInDays($toDate) + 1;
    }

    // Get formatted days/hours display
    public function getDaysHoursFormattedAttribute()
    {
        if ($this->day_type === 'Hours') {
            return $this->days_hours . ' hrs';
        }
        
        $days = $this->days_hours;
        return $days . ($days == 1 ? ' Day' : ' Days');
    }

    // Status badge classes
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'Applied' => 'badge-warning',
            'Approved' => 'badge-success', 
            'Rejected' => 'badge-danger',
            default => 'badge-secondary'
        };
    }
}
