<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;

class EmailSetting extends Model
{
    use HasFactory;

    protected $table = 'email_settings';

    protected $fillable = [
        'user_id',
        'admin_id',
        'email',
        'password',
        'smtp_host',
        'smtp_port',
        'imap_host',
        'imap_port',
        'encryption',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function booted()
    {
        static::created(function ($emailSetting) {

            $actions = ['view', 'edit', 'create', 'delete'];

            
            $emailIdentifier = str_replace(['@', '.'], '_', $emailSetting->email);

            foreach ($actions as $action) {
                Permission::create([
                    'department_id'   => null, 
                    'permission_name' => "{$emailIdentifier}.{$action}",
                    'description'     => "Permission to {$action} email settings for {$emailSetting->email}",
                ]);
            }
        });
    }
}
