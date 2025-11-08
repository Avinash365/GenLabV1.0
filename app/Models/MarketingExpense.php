<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_person_code',
        'person_name',
        'section',
        'amount',
        'approved_amount',
        'from_date',
        'to_date',
        'file_path',
        'description',
        'approval_note',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'amount'    => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function marketingPerson()
    {
        return $this->belongsTo(User::class, 'marketing_person_code', 'user_code');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
