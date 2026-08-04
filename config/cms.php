<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend (Next.js) URL
    |--------------------------------------------------------------------------
    |
    | Used for full-page CMS preview iframes and resolving seeded /images/* paths.
    |
    */

    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/'),

    /*
    |--------------------------------------------------------------------------
    | Preview postMessage origins
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of admin origins allowed to talk to the Next.js
    | preview page (and vice versa). Defaults to APP_URL.
    |
    */

    'preview_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CMS_PREVIEW_ORIGINS', env('APP_URL', 'http://localhost')))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Preview token TTL (minutes)
    |--------------------------------------------------------------------------
    */

    'preview_token_ttl' => (int) env('CMS_PREVIEW_TOKEN_TTL', 120),

    /*
    |--------------------------------------------------------------------------
    | Upload directory module key for uploadsDir()
    |--------------------------------------------------------------------------
    */

    'uploads_module' => 'cms',

];
