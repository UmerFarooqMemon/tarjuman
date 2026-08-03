<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Website / General API Token
    |--------------------------------------------------------------------------
    |
    | Shared secret for unauthenticated website APIs (e.g. /api/estimate).
    | Send as X-API-Token or Authorization: Bearer {token}.
    |
    */

    'token' => env('API_TOKEN'),

];
