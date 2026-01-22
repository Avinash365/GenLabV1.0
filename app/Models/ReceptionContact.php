<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionContact extends Model
{
    use HasFactory;

    protected $table = 'reception_contacts';

    protected $fillable = [
        'name',
        'role',
        'phone',
        'alternate_phone',
        'address',
        'notes',
        'created_by'
    ];
}
