<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearedExpense extends Model
{
    protected $table = 'cleared_expenses';

    protected $fillable = [
        'filename', 'path', 'approver_id', 'approved_total', 'total_expenses',
        'approved_section', 'person_name', 'person_code', 'meta', 'expense_ids'
    ];

    protected $casts = [
        'meta' => 'array',
        'expense_ids' => 'array',
        'approved_total' => 'decimal:2',
        'total_expenses' => 'decimal:2',
    ];
}
