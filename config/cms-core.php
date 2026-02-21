<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Path
    |--------------------------------------------------------------------------
    |
    | The path prefix for the CMS admin panel.
    |
    */

    'path' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | The default roles created during migration.
    |
    */

    'roles' => [
        'super_admin',
        'editor',
        'viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | The default permissions created during migration.
    |
    */

    'permissions' => [
        'view users',
        'create users',
        'edit users',
        'delete users',
        'view roles',
        'create roles',
        'edit roles',
        'delete roles',
        'edit profile',
        'delete profile',
        'view media',
        'create media',
        'edit media',
        'delete media',
    ],

];
