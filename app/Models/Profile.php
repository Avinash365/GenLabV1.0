<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User; 

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'file_path',
        'uploaded_by'
    ];
    
    public function getFilePathUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return config('app.cloudfront_url') . '/' . $this->file_path;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}
