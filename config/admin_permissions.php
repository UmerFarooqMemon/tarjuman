<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Permission Modules
    |--------------------------------------------------------------------------
    |
    | Module keys used for middleware / Gate checks ({module}.{action}).
    | Display labels (EN + AR) are stored on the permissions table via
    | RolesAndPermissionsSeeder — not lang files.
    |
    */

    'modules' => [
        'administrators' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'roles' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'site_settings' => [
            'view',
            'edit',
        ],
        'vendors' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
    ],

    'guard' => 'admin',

    'default_role' => 'Super Admin',
];
