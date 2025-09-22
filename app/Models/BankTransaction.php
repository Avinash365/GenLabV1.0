<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class BankTransaction extends Model
{   
    use SoftDeletes;

    protected $fillable = [
        'date',
        'tran_id',
        'transaction_remarks',
        'chq_ref_no',
        'value_date',
        'withdrawal',
        'deposit',
        'closing_balance', 
        'note', 
        'marketing_person'
    ];

    // Optional: specify date columns (soft deletes + others)
    protected $dates = [
        'date',
        'value_date',
        'deleted_at', // Soft delete
    ]; 

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'bank_transaction_client');
    } 

}
