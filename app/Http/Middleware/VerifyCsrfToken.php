<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'chatbot/query',
        'iclock/cdata',
        'api/whatsapp/webhook', // Exclude WhatsApp Webhook
        'whatsapp/webhook',      // Just in case route mismatch
    ];
}
