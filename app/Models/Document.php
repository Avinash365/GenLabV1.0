<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'file_path',
        'description',
        'uploaded_by',
        'status'
    ];

    // Relationship: Document belongs to User
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    
    
}
