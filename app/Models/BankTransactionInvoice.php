<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransactionInvoice extends Model
{
    protected $table = 'bank_transaction_invoice';

    public $timestamps = true;

    protected $fillable = [
        'bank_transaction_id',
        'invoice_no',
    ];
}