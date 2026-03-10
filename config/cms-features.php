<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles (defaults)
    |--------------------------------------------------------------------------
    |
    | Default values for feature toggles. These can be overridden from
    | the admin panel (Site Settings > Fonctionnalites). Disabled features
    | are hidden from the sidebar and inaccessible.
    |
    | Parent keys control entire groups: disabling 'blog' also hides all
    | blog sub-features (blog_authors, blog_categories, etc.).
    |
    */

    // Tableaux de bord
    'dashboards' => true,
    'dashboards_blog' => true,
    'dashboards_admin' => true,

    // Blog
    'blog' => true,
    'blog_authors' => true,
    'blog_categories' => true,
    'blog_tags' => true,
    'blog_settings' => true,

    // Medias
    'media' => true,

    // Contenu (Pages)
    'pages' => true,
    'pages_sections' => true,
    'pages_templates' => true,

    // SEO
    'seo' => true,
    'seo_redirections' => true,

    // Collections
    'collections' => true,

    // Contact
    'contact' => true,
    'contact_contacts' => true,
    'contact_requests' => true,
    'contact_webhooks' => true,
    'contact_deliveries' => true,
    'contact_settings' => true,

    // Administration
    'administration_users' => true,
    'administration_roles' => true,
    'administration_permissions' => true,
    'administration_site_settings' => true,
    'administration_activity_log' => true,

];
