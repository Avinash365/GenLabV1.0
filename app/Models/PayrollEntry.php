<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'payroll_cycle_id',
        'employee_id',
        'gross_amount',
        'leave_deductions',
        'other_deductions',
        'net_amount',
        'status',
        'payout_due_date',
        'payout_released_at',
        'remarks',
        'meta',
    ];

    protected $casts = [
        'gross_amount' => 'float',
        'leave_deductions' => 'float',
        'other_deductions' => 'float',
        'net_amount' => 'float',
        'payout_due_date' => 'date',
        'payout_released_at' => 'datetime',
        'meta' => 'array',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID => 'Paid',
        ];
    }
}
