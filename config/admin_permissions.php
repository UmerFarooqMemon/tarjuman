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
        'platform_settings' => [
            'view',
            'edit',
        ],
        'cms_pages' => [
            'view',
            'edit',
        ],
        'vendors' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'languages' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'currencies' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'document_types' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'authorities' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'add_ons' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'delivery_speeds' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'plans' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
        'orders' => [
            'view',
            'edit',
        ],
        'pricing_rules' => [
            'view',
            'create',
            'edit',
            'delete',
        ],
    ],

    'guard' => 'admin',

    'default_role' => 'Super Admin',
];
