<?php
return [
    'enabled' => env('COLLABORA_ENABLED', false),
    // Base URL to the Collabora Online server, e.g. http://localhost:9980
    'server_url' => rtrim(env('COLLABORA_SERVER_URL', ''), '/'),
    // WOPI base URL (this app) used by Collabora to call back
    'wopi_base' => rtrim(env('APP_URL', ''), '/').'/wopi',
    // Secret (shared) used to sign tokens (simplified minimal flow)
    'secret' => env('COLLABORA_SECRET', 'changeme-collabora'),
    // Token lifetime seconds
    'token_ttl' => env('COLLABORA_TOKEN_TTL', 3600),
];