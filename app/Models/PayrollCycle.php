<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollCycle extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW = 'in_review';
    public const STATUS_READY = 'ready_for_payment';
    public const STATUS_SENT = 'sent_to_accounts';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'month',
        'year',
        'status',
        'processed_at',
        'locked_at',
        'notes',
        'gross_total',
        'deduction_total',
        'net_total',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'locked_at' => 'datetime',
        'gross_total' => 'float',
        'deduction_total' => 'float',
        'net_total' => 'float',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function getLabelAttribute(): string
    {
        $date = Carbon::create($this->year, $this->month, 1);

        return $date->format('F Y');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_REVIEW => 'In Review',
            self::STATUS_READY => 'Ready For Payment',
            self::STATUS_SENT => 'Sent To Accounts',
            self::STATUS_PAID => 'Paid',
        ];
    }
}
