<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadministrator' => [
            'users' => 'c,r,u,d',
            'payments' => 'c,r,u,d',
            'profile' => 'r,u',
            'teams' => 'c,r,u,d',
            'owners' => 'c,r,u,d',
            'filings' => 'c,r,u,d',
        ],
        'administrator' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
            'owners' => 'c,r,u,d',
            'filings' => 'c,r,u,d',
        ],
        'user' => [
            'profile' => 'r,u',
            'owners' => 'c,r,u',
            'filings' => 'c,r,u',
        ],
        'external_owner' => [
            'owners' => 'r,u',
        ],
        
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
