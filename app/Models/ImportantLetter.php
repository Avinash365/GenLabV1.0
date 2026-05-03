<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportantLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'client_name',
        'letter_no',
        'sample',
        'file_path',
        'uploaded_by',
        'status', 
        'letter_data', 
        'remarks'
    ];

    public function getFilePathUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return config('app.cloudfront_url') . '/' . $this->file_path;
    }   

    // Relation to User
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
