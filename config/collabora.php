<?php
return [
    'enabled' => env('COLLABORA_ENABLED', false),
    // Base URL to the Collabora Online server, e.g. http://localhost:9980
    'server_url' => rtrim(env('COLLABORA_SERVER_URL', ''), '/'),
    // WOPI base URL that Collabora will call (MUST be reachable from the Docker container)
    // For local dev on Windows/Mac, set to http://host.docker.internal:8000
    'wopi_public_base' => rtrim(env('COLLABORA_WOPI_PUBLIC_BASE', env('APP_URL', '')), '/'),
    // Local app base for internal generation if needed
    'wopi_base' => rtrim(env('APP_URL', ''), '/').'/wopi',
    // Secret (shared) used to sign tokens (simplified minimal flow)
    'secret' => env('COLLABORA_SECRET', 'changeme-collabora'),
    // Token lifetime seconds
    'token_ttl' => env('COLLABORA_TOKEN_TTL', 3600),
];