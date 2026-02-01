<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleName extends Model
{
    use HasFactory;

    protected $table = 'role_names';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
    ];
}
