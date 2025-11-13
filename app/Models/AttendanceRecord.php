<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_ON_LEAVE = 'on_leave';
    public const STATUS_HALF_DAY = 'half_day';
    public const STATUS_WORK_FROM_HOME = 'work_from_home';
    public const STATUS_WEEKEND = 'weekend';
    public const STATUS_HOLIDAY = 'holiday';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_BIOMETRIC = 'biometric';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'status',
        'check_in_at',
        'check_out_at',
        'source',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['status_label'];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_ON_LEAVE => 'On Leave',
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_WORK_FROM_HOME => 'Work From Home',
            self::STATUS_WEEKEND => 'Weekend',
            self::STATUS_HOLIDAY => 'Holiday',
        ];
    }

    public static function statusValues(): array
    {
        return array_keys(self::statusLabels());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status ?? ''));
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
