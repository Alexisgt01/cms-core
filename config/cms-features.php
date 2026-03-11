<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles (defaults)
    |--------------------------------------------------------------------------
    |
    | Default values for feature toggles. These can be overridden from
    | the sidebar customizer in the admin panel. Disabled features are
    | hidden from the sidebar and inaccessible.
    |
    | Parent keys control entire groups: disabling 'blog' hides all blog
    | items including mandatory sub-features (Posts, Settings).
    | Child keys control optional sub-items only.
    |
    */

    // Modules (entire nav groups)
    'dashboards' => true,
    'blog' => true,
    'media' => true,
    'pages' => true,
    'seo' => true,
    'collections' => true,
    'contact' => true,

    // Optional sub-items (only shown when parent module is on)
    'dashboards_blog' => true,
    'dashboards_admin' => true,
    'blog_authors' => true,
    'blog_categories' => true,
    'blog_tags' => true,
    'pages_sections' => true,
    'pages_templates' => true,
    'contact_webhooks' => true,
    'contact_deliveries' => true,
    'administration_permissions' => true,
    'administration_activity_log' => true,

];
