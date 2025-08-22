<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'department_name',
        'approval_data',
        'due_date',
        'description',
        'file_path',
        'status',
        'uploaded_by', 
    ];

    // Casts for production level handling
    protected $casts = [
        'due_date' => 'date',
    ]; 

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}
