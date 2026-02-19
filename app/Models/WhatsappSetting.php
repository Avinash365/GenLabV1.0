<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number_id',
        'business_account_id',
        'access_token',
        'app_id',
        'app_secret',
        'api_version',
        'webhook_verify_token',
        'default_template_name',
        'hold_template_name',
        'report_template_name',
        'dispatch_template_name',
        'default_language',
    ];
}
