<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ISCode extends Model
{
    use HasFactory;

    protected $table = 'IS_CODE';

    protected $fillable = [
        'Is_code',
        'Description',
        'upload_file',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
