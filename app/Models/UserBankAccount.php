<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserBankAccount extends Model
{
    use HasFactory;
    protected $table = 'user_bank_accounts';

    protected $fillable = [
        'bank_name',
        'account_no',
        'account_holder_name',
        'created_by',
    ];

    /**
     * Relation: BankAccount belongs to User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
