<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'field_name',
        'top',
        'left',
        'font_size',
        'letter_spacing',
    ];

    /**
     * @return BelongsTo<Bank,ChequeTemplate>
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
