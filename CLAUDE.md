# CMS Core — Agent Context

This is the `alexisgt01/cms-core` package (namespace `Alexisgt01\CmsCore`), a Filament v3 admin panel package for Laravel 12.
Everything lives in `packages/cms/core/`. The host app at the root is a sandbox for development and testing only.

## Architecture

- **ServiceProvider**: `CmsCoreServiceProvider` — registers configs (`cms-core`, `cms-media`), overrides Tiptap media_action config, auto-registers plugin via `Panel::configureUsing()` in `register()` (NOT boot), loads migrations/views, registers policies, commands, middlewares (HandleRestrictedAccess + HandleRedirects), Livewire widgets, and restricted-access POST route
- **Plugin**: `CmsCorePlugin` — registers all Filament resources, pages, and Git version footer render hook
- **Views namespace**: `cms-core` (e.g. `cms-core::filament.pages.blog-settings`)
- **Helpers**: `src/helpers.php` loaded via Composer `files` autoload. After modifying, run `composer update alexisgt01/cms-core`

## Models

| Model | Table | Key features |
|-------|-------|-------------|
| `BlogPost` | `blog_posts` | HasStates (Draft/Scheduled/Published), Tiptap content, featured_images (JSON array), belongs to BlogCategory, many-to-many BlogTag, exhaustive SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogAuthor` | `blog_authors` | Optional user linking, social profiles, avatar (MediaSelectionCast), SEO fields |
| `BlogCategory` | `blog_categories` | Hierarchical (parent/children), has many BlogPost, SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogTag` | `blog_tags` | Many-to-many with BlogPost (pivot: blog_post_tag), SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogSetting` | `blog_settings` | Singleton via `BlogSetting::instance()`, 70+ config fields, MediaSelectionCast on image fallbacks |
| `CmsMedia` | `media` (Spatie) | Extends Spatie Media, belongs to CmsMediaFolder, appends url/human_readable_size |
| `CmsMediaFolder` | `cms_media_folders` | Hierarchical (parent/children), has many CmsMedia |
| `SiteSetting` | `site_settings` | Singleton via `SiteSetting::instance()`, identity (site_name, baseline, logos, favicon, timezone, formats), contact (recipients, from, reply-to), restricted access (enabled, hashed password, cookie TTL, message, admin bypass), global SEO (title, description, template, OG image, robots, canonical), admin (show_version_in_footer). MediaSelectionCast on logo_light/logo_dark/favicon/default_og_image |
| `Redirect` | `redirects` | URL redirections (301/302/307/410), hit tracking, cache auto-invalidation, `scopeActive()`, `recordHit()`, `getCachedRedirects()` |

## State Machine (spatie/laravel-model-states)

States are in `src/Models/States/`:
- `PostState` (abstract) — defines transitions
- `Draft` (default) → Scheduled, Published
- `Scheduled` → Draft, Published
- `Published` → Draft

Cast: `'state' => PostState::class` in BlogPost model.
Transition: `$post->state->transitionTo(Published::class)`

## Value Objects & Casts

- `MediaSelection` (`src/ValueObjects/`) — immutable VO with source, url, originalUrl, mediaId, alt, unsplash metadata. Use `fromArray()` / `toArray()` / `jsonSerialize()`.
- `MediaSelectionCast` (`src/Casts/`) — Eloquent cast for JSON ↔ MediaSelection. Used on: BlogPost.og_image, BlogPost.twitter_image, BlogAuthor.avatar/og_image/twitter_image, BlogCategory.og_image/twitter_image, BlogTag.og_image/twitter_image, BlogSetting.og_image_fallback/twitter_image_fallback/schema_publisher_logo.

## Helpers

```php
// media_url() — generate image URL with optional imgproxy transforms
media_url('http://example.com/img.jpg');                    // direct URL
media_url('http://example.com/img.jpg', ['width' => 800]); // imgproxy URL
media_url($mediaId);                                         // resolve from CmsMedia ID
media_url($url, ['width' => 800, 'height' => 600, 'format' => 'webp', 'quality' => 85]);
```

Supports: width, height, resizing_type (fit/fill/force/auto), gravity, quality, format, blur, sharpen.
Signing: HMAC-SHA256 when `cms-media.proxy.key` and `cms-media.proxy.salt` are set, otherwise `/unsafe/`.

## MediaPicker (Filament Form Component)

Reusable field for any form:
```php
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;

MediaPicker::make('featured_image')
    ->label('Image')
    ->acceptedTypes(['image/jpeg', 'image/png'])
    ->maxUploadSize(5120)
    ->showUnsplash(false);
```

Three sources: library (existing media), upload (new file → media library), Unsplash (download or use URL).
State is a JSON array compatible with `MediaSelection::toArray()`.

## Services

**MediaService** (`src/Services/MediaService.php`):
- `storeUploadedFile(UploadedFile $file, ?int $folderId = null): CmsMedia`
- `deleteMedia(CmsMedia)`, `renameMedia()`, `updateMediaDetails()`, `replaceFile()`, `moveToFolder()`
- `createFolder()`, `renameFolder()`, `deleteFolder()`
- `listFolderContents(?int $folderId, ?string $search, filters..., int $limit): array`
- `bulkDelete()`, `bulkMoveToFolder()`, `bulkAddTags()`

**UnsplashClient** (`src/Services/UnsplashClient.php`):
- `search(string $query, int $page = 1, int $perPage = 24): array` — cached 5min
- `downloadToLibrary(array $photo, ?int $folderId = null): CmsMedia`

## Filament Resources

| Resource | Nav group | Model | Permissions |
|----------|-----------|-------|-------------|
| UserResource | Administration | App\Models\User | view/create/edit/delete users |
| RoleResource | Administration | Spatie Role | view/create/edit/delete roles |
| PermissionResource | Administration | Spatie Permission | super_admin only, read-only |
| BlogPostResource | Blog | BlogPost | view/create/edit/delete blog posts, publish blog posts |
| BlogAuthorResource | Blog | BlogAuthor | view/create/edit/delete blog authors |
| BlogCategoryResource | Blog | BlogCategory | view/create/edit/delete blog categories |
| BlogTagResource | Blog | BlogTag | view/create/edit/delete blog tags |
| RedirectResource | SEO | Redirect | view/create/edit/delete redirects |
| ActivityLogResource | Administration | Spatie Activity | view activity log, read-only (no create/edit/delete) |

## Filament Pages

| Page | Nav group | Description |
|------|-----------|-------------|
| BlogDashboard | Tableaux de bord | Blog stats, chart, latest posts. Route: `/blog`. Requires `view blog posts` |
| AdminDashboard | Tableaux de bord | Users, roles, media stats. Route: `/admin-overview`. Requires `view users` |
| MediaLibrary | Medias | Full media management (folders, upload, search, filters, bulk ops, Unsplash, imgproxy) |
| BlogSettings | Blog | Tabbed settings form (General, RSS, Images, SEO+SerpPreview, OG+OgPreview, Twitter+TwitterPreview, Schema+JSON-LD validation). Requires `manage blog settings` |
| SiteSettings | Administration | Tabbed settings form (Identite, Contact, Acces restreint, SEO Global, Admin). Requires `manage site settings` |
| EditProfile | — | Profile editing page |

## Dashboard Widgets

Each dashboard page controls its own widgets via `getWidgets()`. No widgets registered globally in the plugin.

### Blog Dashboard Widgets
| Widget | Type | Description |
|--------|------|-------------|
| `BlogStatsOverview` | StatsOverview | 6 stat cards: published/scheduled/draft posts (single SQL with conditional counts), categories, tags, authors. Shows last 30 days count. |
| `PostsPerMonthChart` | Chart (line) | Published posts per month over 12 months. DB-agnostic (SQLite/MySQL). |
| `LatestPostsTable` | Table | 10 latest posts with eager-loaded author + category. Links to edit page. |

### Admin Dashboard Widgets
| Widget | Type | Description |
|--------|------|-------------|
| `AdminStatsOverview` | StatsOverview | 4 stat cards: users (with role distribution), roles, media files, folders. |
| `LatestUsersTable` | Table | 10 latest users with roles badges. Links to edit page. |

All widgets have `pollingInterval = null` (no auto-refresh).

## SEO Module

### Shared Components
- **HasSeoFields** trait (`src/Filament/Concerns/HasSeoFields.php`) — provides reusable form field arrays: `seoKeywordFields()`, `seoIndexingFields()`, `seoMetaFields()`, `robotsFieldset()`, `ogFields()`, `twitterFields()`, `schemaFields()`, `contentSeoFields()`. Used by all 4 blog resources.
- **SerpPreview** (`src/Filament/Forms/Components/SerpPreview.php`) — Google SERP preview with desktop/mobile toggle, reactive via Alpine.js. Use `->forSettings()` for settings-specific view.
- **OgPreview** (`src/Filament/Forms/Components/OgPreview.php`) — Facebook share card preview. Use `->forSettings()` for settings-specific view.
- **TwitterPreview** (`src/Filament/Forms/Components/TwitterPreview.php`) — Twitter/X card preview (summary + summary_large_image). Use `->forSettings()` for settings-specific view.

### SEO Fields on All Models
All blog models (BlogPost, BlogAuthor, BlogCategory, BlogTag) have: h1, focus_keyword, secondary_keywords (JSON), content_seo_top, content_seo_bottom, indexing, canonical_url, robots (7 directives), og (type/locale/site_name/title/description/image/width/height), twitter (card/site/creator/title/description/image), schema_types (JSON multi), schema_json.

BlogPost additionally has: subtitle, seo_excerpt, faq_blocks (JSON repeater), table_of_contents (boolean).

### Publication Validation (BlogPost only)
- **Blocks** publication (Published/Scheduled) if `meta_title` is empty
- **Blocks** publication if `h1` AND `title` are both empty
- **Warns** (without blocking) if `meta_description` is missing
- **Warns** on duplicate `meta_title` or `h1` across other BlogPosts

### Schema Types (10 options)
WebPage, CollectionPage, ItemList, Article, BlogPosting, NewsArticle, FAQPage, BreadcrumbList, Person, Organization. Multi-select via `schema_types` JSON column. Legacy `schema_type` string kept for backward compat.

## Blog Post Form Structure

Tabs: Contenu (title, h1, subtitle, slug, excerpt, seo_excerpt, category, tags, content_seo_top, Tiptap content, content_seo_bottom, FAQ repeater, table_of_contents) → Images & Auteur → Publication → SEO (focus_keyword, secondary_keywords, indexing, canonical, meta_title, meta_description, robots, SerpPreview) → Open Graph (+ OgPreview) → Twitter (+ TwitterPreview) → Schema (multi-select + JSON validation).

Without `publish blog posts` permission, state select only shows "Brouillon" (Draft).

Tags can be created inline from the post form (createOptionForm with name + auto-slug).

Tiptap uploads go through `MediaService::storeUploadedFile()` via `CmsMediaAction` (custom Tiptap media action registered globally in ServiceProvider).

## BlogSettings Form Structure

Tabs: General (enabled, blog_name, description, default_author, posts_per_page, display toggles) → RSS (enabled, title, description) → Images (featured_images_max, required, default dimensions) → SEO (indexing_default, h1_from_title, canonical_mode, meta templates, robots defaults, SerpPreview) → Open Graph (site_name, type_default, locale, title/description templates, image fallback + dimensions, OgPreview) → Twitter (card_default, site, creator, title/description templates, image fallback + dimensions, TwitterPreview) → Sitemap (enabled, base_url, max_urls, crawl_depth, concurrency, exclude_patterns, change_freq, priority) → Schema (enabled, type_default, schema_types multi, publisher name/logo, organization_url, language, same_as social URLs, custom JSON-LD with validation).

BlogSetting has: og_image_fallback_width/height, twitter_image_fallback_width/height, schema_same_as (JSON array of social URLs), schema_organization_url, sitemap_* settings (enabled, base_url, max_urls, crawl_depth, concurrency, exclude_patterns, default_change_freq, default_priority).

## Redirections Module

### Model `Redirect` (`src/Models/Redirect.php`)
- source_path, destination_url, status_code (301/302/307/410), is_active, hit_count, last_hit_at, note
- `scopeActive()` — filters active redirections
- `recordHit()` — increments hit_count + updates last_hit_at
- `getCachedRedirects()` — returns cached array keyed by source_path
- `clearCache()` — invalidates `cms_redirects` cache key
- Auto cache invalidation via `booted()` hooks on saved/deleted

### Middleware `HandleRedirects` (`src/Http/Middleware/HandleRedirects.php`)
- Registered as global middleware via `Kernel::pushMiddleware()` in ServiceProvider boot()
- Matches `$request->getPathInfo()` against cached active redirections
- 301/302/307 → redirect response; 410 → abort(410)
- Hit recording dispatched after response (non-blocking)

### Filament Resource `RedirectResource`
- Nav group: SEO, icon: heroicon-o-arrow-uturn-right
- Form: source_path (prefix /), destination_url (hidden for 410), status_code Select, is_active Toggle, note
- Table: searchable/sortable/copyable source, destination, status badge (color-coded), hit_count, last_hit_at, ToggleColumn is_active
- Filters: status_code SelectFilter, is_active TernaryFilter

## Site Settings Module

### Model `SiteSetting` (`src/Models/SiteSetting.php`)
- Singleton via `SiteSetting::instance()` (firstOrCreate with sensible defaults)
- Identity: site_name, baseline, logo_light/logo_dark/favicon (MediaSelectionCast), timezone, date_format, time_format
- Contact: contact_email_recipients (JSON array), from_email_name, from_email_address, reply_to_email
- Restricted access: restricted_access_enabled, restricted_access_password (hashed), restricted_access_cookie_ttl (default 1440min), restricted_access_message, restricted_access_admin_bypass
- Global SEO: default_site_title, default_meta_description, title_template (`%title% · %site%`), default_og_image (MediaSelectionCast), default_robots_index, default_robots_follow, canonical_base_url
- Admin: show_version_in_footer

### Restricted Access System
- **Middleware** `HandleRestrictedAccess` (`src/Http/Middleware/HandleRestrictedAccess.php`) — global middleware, registered BEFORE HandleRedirects
- Logic: skip if disabled → skip admin panel routes → skip if admin_bypass + authenticated → skip if cookie `cms_restricted_access` → show restriction page
- **POST route** `/cms/restricted-access` — validates password via `Hash::check()`, sets cookie with configurable TTL
- **View** `resources/views/restricted-access.blade.php` — standalone page with password form, custom message

### SiteSettings Page (`src/Filament/Pages/SiteSettings.php`)
- Nav group: Administration, sort 98, requires `manage site settings`
- Tabs: Identite, Contact, Acces restreint, SEO Global, Admin
- Password field uses `->dehydrated(fn ($state) => filled($state))` to avoid overwriting existing hash
- On save, non-empty password is hashed via `Hash::make()`

## Activity Log Module (spatie/laravel-activitylog)

### LogsActivity Trait
Added to: BlogPost, BlogCategory, BlogTag, BlogAuthor, Redirect, SiteSetting, BlogSetting. Each implements `getActivitylogOptions(): LogOptions` with `logOnly(['*'])`, `logOnlyDirty()`, `dontSubmitEmptyLogs()`.

Auth events (Login/Logout) are listened in `CmsCoreServiceProvider::registerAuthListeners()` and logged via `activity()->event('login'/'logout')`.

For `App\Models\User`: must be added manually by host app (package cannot modify it).

### ActivityLogResource (`src/Filament/Resources/ActivityLogResource.php`)
- Nav group: Administration, read-only (canCreate returns false)
- Requires `view activity log` permission
- Table: date, user (causer), model type (translated), event (badge), description
- Filters: event (created/updated/deleted), subject_type (model)
- ViewAction modal with old/new values JSON pretty-print

### Commands
- `cms:purge-activity {--days=30}` — purges entries older than N days

## Git Version Footer

Implemented in `CmsCorePlugin::boot()` via `PanelsRenderHook::FOOTER`. Reads `SiteSetting::show_version_in_footer`, if enabled runs `git describe --tags --abbrev=0` (cached 1h), renders version in footer.

## CmsMediaAction (Tiptap Media Modal Override)

`src/Filament/Actions/CmsMediaAction.php` replaces the default `FilamentTiptapEditor\Actions\MediaAction`. Registered via `config('filament-tiptap-editor.media_action')` override in `CmsCoreServiceProvider::register()`.

Key features:
- Stores uploads through `MediaService::storeUploadedFile()` (not default disk storage)
- Preserves aspect ratio: hidden `aspect_ratio` field calculated from uploaded image dimensions
- Width/height fields are `live(onBlur: true)` with `afterStateUpdated` callbacks that recalculate the other dimension based on the stored ratio
- Supports `mountUsing` to restore existing dimensions when editing an already-inserted image

## Permissions

All created via migrations (NOT seeders). Pattern: `Permission::create()` + role assignment. Roles also created in migrations only.

**Blog permissions**: manage blog settings, view/create/edit/delete blog authors, view/create/edit/delete blog posts, publish blog posts, view/create/edit/delete blog categories, view/create/edit/delete blog tags.

**Redirect permissions**: view/create/edit/delete redirects.

**Site/Admin permissions**: manage site settings, view activity log.

super_admin = all. editor = view/create/edit authors/categories/tags + view/create/edit/publish posts (no delete, no settings) + view/create/edit redirects (no delete) + view activity log. viewer = none.

## Commands

- `cms:make-admin` — creates an admin user with `super_admin` role. Prompts for first_name, last_name, email, password. Supports `--first-name`, `--last-name`, `--email`, `--password` options for non-interactive use. Replaces `make:filament-user` (which doesn't work with first_name/last_name fields).
- `cms:publish-scheduled` — finds Scheduled posts with `scheduled_for <= now()`, transitions to Published, sets published_at/first_published_at. Run hourly via scheduler.
- `cms:sitemap` — generates sitemap.xml by HTTP crawling the site using spatie/laravel-sitemap. Reads config from BlogSetting (base_url, max_urls, concurrency, depth, exclude_patterns, change_freq, priority). Options: `--url`, `--output`, `--max-urls`, `--concurrency`, `--depth`.
- `cms:purge-activity` — purges activity log entries older than N days. Option: `--days=30`.

## Config

- `config/cms-core.php` — admin path
- `config/cms-media.php` — proxy (imgproxy), unsplash, media upload settings

Env vars: `IMGPROXY_ENABLE`, `IMGPROXY_URL`, `IMGPROXY_KEY`, `IMGPROXY_SALT`, `UNSPLASH_APP_ID`, `UNSPLASH_APP_KEY`, `UNSPLASH_SECRET_KEY`.

## Conventions

- French UI labels throughout (Filament resources, form fields, table columns)
- Migrations use sequence: 200xxx (users/roles), 300xxx (media), 500xxx (blog), 600xxx (SEO enhancements), 700xxx (redirections/sitemap), 800xxx (site settings/activity log)
- Permissions follow pattern: `view/create/edit/delete {resource}` for CRUD, `manage {resource}` for settings pages
- Permissions and roles are ONLY in migrations, never configs or seeders
- All image fields use `MediaPicker` component + `MediaSelectionCast`
- `$guarded = ['id']` on models (not `$fillable`)
- Casts defined in `casts()` method (not `$casts` property)
- Slugs auto-generated with uniqueness via `Model::generateSlug()`

## Tests

Test files in host app `tests/Feature/Filament/`:
- `MediaLibraryTest.php` — media CRUD, folders, bulk ops, filters
- `MediaPickerTest.php` — MediaSelection, MediaSelectionCast, media_url(), UnsplashClient
- `BlogTest.php` — BlogSetting, BlogAuthor, BlogCategory, BlogTag, BlogPost, states/transitions, publish-scheduled command, permissions
- `BlogSeoTest.php` — SEO column existence (all tables including settings), model casts (arrays/booleans), content storage, publication validation (block/allow/warn), duplicate detection, settings fallback dimensions, schema_same_as, schema_organization_url
- `RedirectTest.php` — columns, permissions (super_admin/editor/viewer), CRUD, casts, recordHit, scopeActive, middleware (301/302/410/inactive/non-matching), cache invalidation
- `SitemapTest.php` — sitemap settings columns, casts (boolean/array), default values, settings storage, command registration, disabled exit
- `SiteSettingsTest.php` — SiteSetting model (columns, singleton, casts, defaults), SiteSettings page (access, save, password hashing), restricted access middleware (enabled/disabled, admin bypass, cookie, password validation), activity log (logging on all CMS models, resource access, purge command), permissions
