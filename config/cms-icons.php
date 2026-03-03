<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Icon Sets
    |--------------------------------------------------------------------------
    |
    | Which Blade Icon sets are exposed in the picker. Set to null to expose
    | all registered sets, or provide an array of set names to filter.
    |
    */

    'sets' => null,

    /*
    |--------------------------------------------------------------------------
    | Render Mode
    |--------------------------------------------------------------------------
    |
    | How cms_icon() renders icons in the frontend:
    |
    | 'svg'   — Inline SVG via blade-icons (default, backward compatible).
    | 'class' — CSS class <i> tags for Font Awesome icons. Icons that have
    |           no CSS font equivalent (Heroicons, Simple Icons) fall back
    |           to inline SVG automatically.
    |
    */

    'render_mode' => env('CMS_ICON_RENDER_MODE', 'svg'),

    /*
    |--------------------------------------------------------------------------
    | Default Output Mode
    |--------------------------------------------------------------------------
    |
    | 'reference' stores the blade-icons name (e.g. 'heroicon-o-home').
    | 'svg' stores the full SVG markup inline.
    |
    */

    'default_mode' => 'reference',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'per_page' => 60,

    /*
    |--------------------------------------------------------------------------
    | Manifest Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to cache the icon manifest. Set to 0 to disable caching.
    |
    */

    'cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Human-readable Labels
    |--------------------------------------------------------------------------
    |
    | Display labels for known icon sets. Sets not listed here will use
    | their registered name with ucfirst.
    |
    */

    'labels' => [
        'heroicons' => 'Heroicons',
        'fontawesome' => 'Font Awesome',
        'simple-icons' => 'Simple Icons (Marques)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Variant / Style Mappings
    |--------------------------------------------------------------------------
    |
    | Maps icon name prefixes within a set to human-readable style names.
    | Used to parse e.g. 'o-home' → variant 'o' (Outline), label 'home'.
    |
    */

    'variants' => [
        'heroicons' => [
            'o' => 'Outline',
            's' => 'Solid',
            'm' => 'Micro',
            'c' => 'Mini',
        ],
        'fontawesome' => [
            'fas' => 'Solid',
            'far' => 'Regular',
            'fab' => 'Brands',
        ],
    ],

];
