<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReportFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'format_name',
        'is_code',
        'sample',
        'stored_file_name',
        'original_file_name',
        'mime_type',
        'uploaded_by',
        'body_html',
        'version',
    ];

    public function getFileUrlAttribute(): string
    {
        // Use controller streaming route so it works even if public/storage symlink or document root is misconfigured
        try {
            return route('superadmin.reporting.report-formats.show', $this);
        } catch(\Throwable $e){
            return Storage::disk('public')->url('report-formats/'.$this->stored_file_name);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
