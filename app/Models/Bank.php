<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'cheque_image_path',
    ];

    /**
     * @return HasMany<ChequeTemplate>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(ChequeTemplate::class);
    }
}
