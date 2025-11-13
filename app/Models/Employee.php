<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone_primary',
        'phone_secondary',
        'designation',
        'department',
        'date_of_joining',
        'employment_status',
        'manager_id',
        'profile_photo_path',
        'resume_path',
        'bio',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_swift',
        'ctc',
        'dob',
        'gender',
        'blood_group',
        'additional_details',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'dob' => 'date',
        'additional_details' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function getResumeUrlAttribute(): ?string
    {
        return $this->resume_path ? Storage::disk('public')->url($this->resume_path) : null;
    }
}
