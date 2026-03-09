# CMS Core — Agent Context

This is the `alexisgt01/cms-core` package (namespace `Alexisgt01\CmsCore`), a Filament v3 admin panel package for Laravel 12.
Everything lives in `packages/cms/core/`. The host app at the root is a sandbox for development and testing only.

## Architecture

- **ServiceProvider**: `CmsCoreServiceProvider` — registers configs (`cms-core`, `cms-media`), overrides Tiptap media_action config, auto-registers plugin via `Panel::configureUsing()` in `register()` (NOT boot), loads migrations/views, registers policies, commands, middlewares (HandleRestrictedAccess + HandleRedirects), Livewire widgets, Livewire components (ReleasePopup), and restricted-access POST route
- **Plugin**: `CmsCorePlugin` — registers all Filament resources, pages, user menu items (Documentation + Releases), release popup render hook, and Git version footer render hook
- **Views namespace**: `cms-core` (e.g. `cms-core::filament.pages.blog-settings`)
- **Helpers**: `src/helpers.php` loaded via Composer `files` autoload. After modifying, run `composer update alexisgt01/cms-core`

## Models

| Model | Table | Key features |
|-------|-------|-------------|
| `UserReleaseView` | `user_release_views` | Tracks which release versions each user has seen. Fields: user_id, release_slug, viewed_at. Unique constraint on user_id+release_slug. Cascade delete on user. |
| `BlogPost` | `blog_posts` | HasStates (Draft/Scheduled/Published), Tiptap content, featured_images (JSON array), belongs to BlogCategory, many-to-many BlogTag, exhaustive SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogAuthor` | `blog_authors` | Optional user linking, social profiles, avatar (MediaSelectionCast), SEO fields |
| `BlogCategory` | `blog_categories` | Hierarchical (parent/children), has many BlogPost, SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogTag` | `blog_tags` | Many-to-many with BlogPost (pivot: blog_post_tag), SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogSetting` | `blog_settings` | Singleton via `BlogSetting::instance()`, 70+ config fields, MediaSelectionCast on image fallbacks |
| `CmsMedia` | `media` (Spatie) | Extends Spatie Media, belongs to CmsMediaFolder, appends url/human_readable_size |
| `CmsMediaFolder` | `cms_media_folders` | Hierarchical (parent/children), has many CmsMedia |
| `SiteSetting` | `site_settings` | Singleton via `SiteSetting::instance()`, identity (site_name, baseline, logos, favicon, timezone, formats, footer_copyright, footer_text, copyright_start_year), contact (phone, secondary_phone, recipients, from, reply-to, google_maps_url, opening_hours), legal (company_name, legal_form, share_capital, address, SIRET, SIREN, TVA, RCS, APE, director, hosting provider, DPO), social media (10 platforms), restricted access (enabled, hashed password, cookie TTL, message, admin bypass), global SEO (title, description, template, OG image, robots, canonical), admin (show_version_in_footer). MediaSelectionCast on logo_light/logo_dark/favicon/default_og_image |
| `Redirect` | `redirects` | URL redirections (301/302/307/410), hit tracking, cache auto-invalidation, `scopeActive()`, `recordHit()`, `getCachedRedirects()` |
| `Page` | `pages` | HasStates (PageDraft/PagePublished), SoftDeletes, LogsActivity, hierarchical (parent/children + position), key (front-end identifier), is_home, sections (JSON array via SectionRegistry/Builder), full SEO fields, MediaSelectionCast on og_image/twitter_image |
| `CollectionEntry` | `collection_entries` | HasStates (EntryDraft/EntryPublished), SoftDeletes, LogsActivity, collection_type discriminator, data (JSON), slug (unique per type), position, full SEO fields (optional per CollectionType), field() accessor for data, scopes: forType/ordered/published |
| `Contact` | `contacts` | LogsActivity, email unique, upsertByEmail() static, hasMany ContactRequest, casts attribs/tags/consents as array |
| `ContactRequest` | `contact_requests` | HasStates (RequestNew/RequestProcessed/RequestArchived), LogsActivity, belongsTo Contact, hasMany HookDelivery, casts state/payload/meta, idempotency_key unique |
| `ContactSetting` | `contact_settings` | Singleton via `ContactSetting::instance()`, cached 1h, casts default_async boolean + retention_days integer |
| `HookEndpoint` | `contact_hook_endpoints` | Webhook configuration, casts enabled/events/backoff/headers, hasMany HookDelivery, acceptsEvent() method |
| `HookDelivery` | `contact_hook_deliveries` | Webhook delivery tracking, casts next_retry_at datetime, belongsTo HookEndpoint + ContactRequest |
| `SectionTemplate` | `section_templates` | Reusable section templates with pre-filled data. Fields: name, section_type, data (JSON cast). Managed through SectionBuilder UI (save/load/delete) and SectionTemplateResource (CRUD with dynamic form fields per SectionType) |

## State Machine (spatie/laravel-model-states)

States are in `src/Models/States/`:
- `PostState` (abstract) — defines transitions
- `Draft` (default) → Scheduled, Published
- `Scheduled` → Draft, Published
- `Published` → Draft

Cast: `'state' => PostState::class` in BlogPost model.
Transition: `$post->state->transitionTo(Published::class)`

- `PageState` (abstract) — simplified 2-state machine for Page
- `PageDraft` (default) ↔ `PagePublished`

Cast: `'state' => PageState::class` in Page model.

- `EntryState` (abstract) — 2-state machine for CollectionEntry
- `EntryDraft` (default) ↔ `EntryPublished`

Cast: `'state' => EntryState::class` in CollectionEntry model.

- `RequestState` (abstract) — 3-state machine for ContactRequest
- `RequestNew` (default) → RequestProcessed, RequestArchived
- `RequestProcessed` → RequestNew, RequestArchived
- `RequestArchived` → RequestNew

Cast: `'state' => RequestState::class` in ContactRequest model.

## Value Objects & Casts

- `MediaSelection` (`src/ValueObjects/`) — immutable VO with source, url, originalUrl, mediaId, alt, unsplash metadata. Use `fromArray()` / `toArray()` / `jsonSerialize()`.
- `MediaSelectionCast` (`src/Casts/`) — Eloquent cast for JSON ↔ MediaSelection. Used on: BlogPost.og_image, BlogPost.twitter_image, BlogAuthor.avatar/og_image/twitter_image, BlogCategory.og_image/twitter_image, BlogTag.og_image/twitter_image, BlogSetting.og_image_fallback/twitter_image_fallback/schema_publisher_logo.
- `IconSelection` (`src/ValueObjects/`) — immutable VO with name, set, variant, label, svg. Use `fromArray()` / `toArray()` / `jsonSerialize()` / `toSvg()`.
- `IconSelectionCast` (`src/Casts/`) — Eloquent cast for JSON ↔ IconSelection.
- `SeoMeta` (`src/ValueObjects/`) — immutable VO for resolved SEO data. Properties: title, description, canonicalUrl, robots, og*, twitter*, schemaJsonLd. Methods: `toHtml()`, `toArray()`, `jsonSerialize()`, `__toString()` (renders all `<head>` tags).

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

```php
// cms_icon() — render icon from blade-icons name or IconSelection
cms_icon('heroicon-o-home', 'w-6 h-6');
cms_icon($iconSelection, 'w-6 h-6');
```

```php
// seo_meta() — resolve SEO metadata with fallbacks
seo_meta('service');   // Page by key → SeoMeta VO
seo_meta($blogPost);   // Model directly → SeoMeta VO
seo_meta();            // Global defaults → SeoMeta VO
// Returns SeoMeta (Stringable) → {!! seo_meta('about') !!} renders all <head> tags
// Fallback: entity → BlogSetting (blog entities) → SiteSetting (global)
```

```php
// collection_entries() — get all entries for a collection type
collection_entries('services');                      // published only (if hasStates)
collection_entries('services', publishedOnly: false); // all entries
// Returns ordered by position, filtered by collection_type

// collection_entry() — get a single entry by slug
collection_entry('services', 'web-design');          // CollectionEntry|null
```

```php
// contact_event() — unified contact form ingestion
contact_event('contact', [
    'email' => 'john@example.com',
    'name' => 'John Doe',
    'message' => 'Hello',
]);
// Options: idempotency_key, hook_key (string|array|null), form_id, meta
contact_event('newsletter', ['email' => 'sub@example.com'], [
    'idempotency_key' => 'unique-123',
    'form_id' => 'footer-newsletter',
    'meta' => ['ip' => request()->ip()],
]);
```

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

Four sources: library (existing media), upload (new file → media library), url (import from external URL → media library), Unsplash (download or use URL).
Modal tabs order: Bibliothèque, Upload, URL, Unsplash.
State is a JSON array compatible with `MediaSelection::toArray()`. The `source` field can be `'library'`, `'upload'`, `'url'`, or `'unsplash'`. URL imports display a purple badge in filled state.

## IconPicker (Filament Form Component)

Reusable field for any form:
```php
use Alexisgt01\CmsCore\Filament\Forms\Components\IconPicker;

IconPicker::make('icon')
    ->outputMode('reference')        // or 'svg'
    ->allowedSets(['heroicons'])
    ->defaultSet('heroicons')
    ->required();
```

Two output modes: reference (blade-icons name string) or svg (inline SVG markup).
Uses blade-ui-kit/blade-icons for icon discovery. Any installed Blade Icon set is auto-detected.
State is a JSON object compatible with `IconSelection::toArray()`.

## Services

**MediaService** (`src/Services/MediaService.php`):
- `storeUploadedFile(UploadedFile $file, ?int $folderId = null): CmsMedia`
- `storeFromUrl(string $url): CmsMedia` — downloads a file from an external URL, stores it in the media library
- `deleteMedia(CmsMedia)`, `renameMedia()`, `updateMediaDetails()`, `replaceFile()`, `moveToFolder()`
- `createFolder()`, `renameFolder()`, `deleteFolder()`
- `listFolderContents(?int $folderId, ?string $search, filters..., int $limit): array`
- `bulkDelete()`, `bulkMoveToFolder()`, `bulkAddTags()`

**UnsplashClient** (`src/Services/UnsplashClient.php`):
- `search(string $query, int $page = 1, int $perPage = 24): array` — cached 5min
- `downloadToLibrary(array $photo, ?int $folderId = null): CmsMedia`

**IconDiscoveryService** (`src/Services/IconDiscoveryService.php`):
- `getAvailableSets(): array` — returns registered icon sets with prefix, label, variants
- `searchIcons(query, set, variant, page, perPage): array` — server-side paginated search with cached manifest
- `getSvgContent(string $iconName): string` — resolves SVG via blade-icons

**SeoResolver** (`src/Services/SeoResolver.php`):
- `resolve(Model|string|null $entity = null): SeoMeta` — resolves all SEO with fallback chains: entity → BlogSetting (for blog entities) → SiteSetting (global). String arg looks up Page by key. Handles title template (`%title% · %site%`), robots directives, OG/Twitter images from MediaSelection, schema JSON-LD auto-generation.

**ContactPipeline** (`src/Services/ContactPipeline.php`):
- `handle(string $type, array $payload, array $options): ContactRequest` — unified contact form ingestion: normalizes name (name or first_name+last_name), upserts Contact by email (merging tags/consents/attribs), creates ContactRequest with state=new, dispatches hooks to matching HookEndpoints (async via queue or sync). Options: idempotency_key (skip if exists), hook_key (string|array|null, null=all active), form_id, meta.

**DocumentationService** (`src/Services/DocumentationService.php`):
- `all(): Collection` — reads all Markdown files from `resources/docs/`, parses frontmatter (title, icon, order), renders Markdown to HTML, returns sorted by order
- `find(string $slug): ?array` — returns a single doc section by slug

**ReleaseService** (`src/Services/ReleaseService.php`):
- `all(): Collection` — reads all Markdown files from `resources/releases/`, parses frontmatter (slug, version, title, date), renders Markdown to HTML, returns sorted by date descending
- `find(string $slug): ?array` — returns a single release by slug
- `getUnreadReleases(Authenticatable $user): Collection` — returns releases not yet viewed by user
- `getLatestUnreadRelease(Authenticatable $user): ?array` — returns the most recent unread release
- `hasUnreadReleases(Authenticatable $user): bool` — quick check
- `markAllAsRead(Authenticatable $user): void` — bulk inserts all unseen releases into `user_release_views`

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
| PageResource | Contenu | Page | view/create/edit/delete pages, publish pages |
| RedirectResource | SEO | Redirect | view/create/edit/delete redirects |
| CollectionEntryResource | Collections | CollectionEntry | view/create/edit/delete collection entries, publish collection entries |
| ActivityLogResource | Administration | Spatie Activity | view activity log, read-only (no create/edit/delete) |
| ContactResource | Contact | Contact | view/edit/delete contacts, no create (created via pipeline) |
| ContactRequestResource | Contact | ContactRequest | view/delete contact requests, no create, replay hooks action |
| HookEndpointResource | Contact | HookEndpoint | full CRUD for contact hooks |
| HookDeliveryResource | Contact | HookDelivery | read-only, replay action |
| SectionTemplateResource | Contenu | SectionTemplate | view/create/edit/delete pages (reuses page permissions) |

## Filament Pages

| Page | Nav group | Description |
|------|-----------|-------------|
| BlogDashboard | Tableaux de bord | Blog stats, chart, latest posts. Route: `/blog`. Requires `view blog posts` |
| AdminDashboard | Tableaux de bord | Users, roles, media stats, test email action, clear cache action. Route: `/admin-overview`. Requires `view users` |
| MediaLibrary | Medias | Full media management (folders, upload, search, filters, bulk ops, Unsplash, imgproxy) |
| BlogSettings | Blog | Tabbed settings form (General, RSS, Images, SEO+SerpPreview, OG+OgPreview, Twitter+TwitterPreview, Schema+JSON-LD validation). Requires `manage blog settings` |
| SiteSettings | Administration | Tabbed settings form (Identite, Contact, Acces restreint, SEO Global, Mentions legales, Reseaux sociaux, Admin). Requires `manage site settings` |
| ContactSettings | Contact | Contact settings (async mode, inbound secret, retention). Requires `manage contact settings` |
| SectionCatalog | Contenu | Grid catalog of registered section types with template counts and "Create template" links. Requires `view pages` |
| Documentation | — (user menu) | Markdown-based user guide with sidebar navigation. Route: `/documentation`. Accessible to all authenticated users |
| Releases | — (user menu) | Chronological release notes with collapsible sections. Route: `/releases`. Accessible to all authenticated users |
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
All blog models (BlogPost, BlogAuthor, BlogCategory, BlogTag) and Page have: h1, focus_keyword, secondary_keywords (JSON), indexing, canonical_url, robots (7 directives), og (type/locale/site_name/title/description/image/width/height), twitter (card/site/creator/title/description/image), schema_types (JSON multi), schema_json.

BlogPost additionally has: subtitle, seo_excerpt, content_seo_top, content_seo_bottom, faq_blocks (JSON repeater), table_of_contents (boolean).

Page has no rich content (content managed front-end via key/template).

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

## Page Form Structure

Tabs: Page (name, slug, key, parent_id select hierarchical, position if parent set, is_home, meta KeyValue, state, published_at) → Sections (SectionBuilder component with modal picker, visible only when types registered) → SEO (h1, focus_keyword, secondary_keywords, indexing, canonical, meta_title, meta_description, robots, SerpPreview) → Open Graph → Twitter → Schema.

Without `publish pages` permission, state select only shows "Brouillon" (PageDraft).

SoftDeletes support: TrashedFilter in list, RestoreAction + ForceDeleteAction in edit header and table actions.

Page duplication: ReplicateAction in table actions and edit page header. Requires `create pages` permission.

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
- Singleton via `SiteSetting::instance()` (cached 1h via `Cache::remember`, auto-invalidated on save/delete)
- Identity: site_name, baseline, logo_light/logo_dark/favicon (MediaSelectionCast), timezone, date_format, time_format
- Contact: phone, secondary_phone, contact_email_recipients (JSON array), from_email_name, from_email_address, reply_to_email, google_maps_url, opening_hours
- Legal: company_name, legal_form (Select: SAS/SASU/SARL/EURL/SA/SCI/SNC/EI/EIRL/Auto-entrepreneur/Association), share_capital, company_address, company_postal_code, company_city, company_country (default: France), siret, siren, tva_number, rcs, ape_code, director_name, director_email, hosting_provider_name/address/phone/email, dpo_name, dpo_email
- Social media: social_facebook, social_x, social_instagram, social_linkedin, social_youtube, social_tiktok, social_pinterest, social_github, social_threads, social_snapchat
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
- Tabs: Identite, Contact, Acces restreint, SEO Global, Mentions legales, Reseaux sociaux, Admin
- Password field uses `->dehydrated(fn ($state) => filled($state))` to avoid overwriting existing hash
- On save, non-empty password is hashed via `Hash::make()`

## Activity Log Module (spatie/laravel-activitylog)

### LogsActivity Trait
Added to: BlogPost, BlogCategory, BlogTag, BlogAuthor, Redirect, SiteSetting, BlogSetting, Page. Each implements `getActivitylogOptions(): LogOptions` with `logOnly(['*'])`, `logOnlyDirty()`, `dontSubmitEmptyLogs()`.

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

## Documentation & Releases Module

### User Documentation (`resources/docs/`)
Markdown files with frontmatter (title, icon, order) providing end-user documentation in French. 10 sections: Premiers pas, Tableau de bord, Blog, Pages, Médias, SEO, Redirections, Collections, Contact, Administration. Rendered via `DocumentationService`, displayed on the Documentation Filament page with sidebar navigation. Accessible via user profile menu "Guide d'utilisation".

### Release Notes (`resources/releases/`)
Markdown files with frontmatter (slug, version, title, date) documenting each minor version release. 10 releases from 1.0.0 to 2.4.0. Patches are grouped within their parent minor version file. Rendered via `ReleaseService`, displayed on the Releases Filament page with collapsible accordion. Accessible via user profile menu "Nouveautés".

### Release Popup (`src/Livewire/ReleasePopup.php`)
Livewire component injected via `PanelsRenderHook::BODY_END`. Shows a modal with the latest unread release when user has unread releases. On dismiss, marks ALL releases as read (prevents spam). Only ONE popup shown even if multiple releases are unread.

### User Menu Items
Two items added to Filament user profile dropdown via `$panel->userMenuItems()`:
- "Guide d'utilisation" → Documentation page (heroicon-o-book-open)
- "Nouveautés" → Releases page (heroicon-o-sparkles)

### Development Rule
**MANDATORY**: Every new feature/version MUST include:
1. Updated documentation in `resources/docs/` (relevant section)
2. New or updated release entry in `resources/releases/` (new minor version file)

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

**Page permissions**: view/create/edit/delete/publish pages.

**Redirect permissions**: view/create/edit/delete redirects.

**Site/Admin permissions**: manage site settings, view activity log.

**Collection permissions**: view/create/edit/delete/publish collection entries.

**Contact permissions**: view/create/edit/delete contacts, view/create/edit/delete contact requests, view/create/edit/delete contact hooks, manage contact settings, replay contact hooks.

super_admin = all. editor = view/create/edit authors/categories/tags + view/create/edit/publish posts (no delete, no settings) + view/create/edit/publish pages (no delete) + view/create/edit redirects (no delete) + view/create/edit/publish collection entries (no delete) + view contacts/contact requests/contact hooks + replay contact hooks + view activity log. viewer = none.

## Commands

- `cms:make-admin` — creates an admin user with `super_admin` role. Prompts for first_name, last_name, email, password. Supports `--first-name`, `--last-name`, `--email`, `--password` options for non-interactive use. Replaces `make:filament-user` (which doesn't work with first_name/last_name fields).
- `cms:publish-scheduled` — finds Scheduled posts with `scheduled_for <= now()`, transitions to Published, sets published_at/first_published_at. Run hourly via scheduler.
- `cms:sitemap` — generates sitemap.xml by HTTP crawling the site using spatie/laravel-sitemap. Reads config from BlogSetting (base_url, max_urls, concurrency, depth, exclude_patterns, change_freq, priority). Options: `--url`, `--output`, `--max-urls`, `--concurrency`, `--depth`.
- `cms:purge-activity` — purges activity log entries older than N days. Option: `--days=30`.
- `cms:contact-retry-hooks` — retries pending contact hook deliveries that are due for retry (next_retry_at <= now).
- `cms:contact-purge` — purges contact requests and hook deliveries older than retention period. Option: `--days=` (overrides ContactSetting/config).

## Contact Module

Unified contact form ingestion system with webhook dispatch.

### Pipeline Flow
1. `contact_event($type, $payload, $options)` → `ContactPipeline::handle()`
2. Check idempotency_key → skip if already processed
3. Upsert Contact by email (merge tags/consents/attribs)
4. Create ContactRequest (state=new, payload, meta)
5. Dispatch hooks to matching HookEndpoints (async or sync)

### Webhook System
- HMAC signature: `hash_hmac('sha256', timestamp . '.' . json_body, endpoint.secret)`
- Headers: `X-Contacts-Event`, `X-Contacts-Hook-Key`, `X-Contacts-Timestamp`, `X-Contacts-Signature`
- Retry with configurable backoff array (default [5, 30, 120] seconds)
- `DeliverContactHookJob` handles delivery with retry logic

### Config (`cms-contacts.php`)
- `default_async` (bool, default true) — queue vs sync webhook dispatch
- `retention_days` (int, default 90) — purge threshold
- `max_body_log_size` (int, default 4096) — truncate logged request/response bodies

## Sections Module

Blueprint system for structured page content. Host app defines SectionType classes, pages store instances as JSON.

### Key Classes (`src/Sections/`)
- **SectionField** — Fluent builder with 15 field types: text, title (2 components: text + H1-H6 select), paragraph (Textarea), richtext (TiptapEditor), icon (IconPicker), image (MediaPicker), toggle, select, link (2 components: url + label), url (3 components: Select grouped pages+routes+external + TextInput url + TextInput label), list (Repeater simple), repeater (Repeater with sub-fields), number, color. Methods: `toFormComponent(): array`, `toDefinition(): array`.
- **SectionType** — Abstract class with `key()`, `label()`, `icon()`, `fields()`, `description()`. Auto-generates `schema()` (Filament components), `toBlock()` (Builder\Block), `toDefinition()` (serializable).
- **SectionRegistry** — Singleton service. `register(string $typeClass)`, `all()`, `resolve(string $key)`, `blocks()`, `definitions()`. Validates classes extend SectionType.

### Storage
- JSON column `sections` on `pages` table, stores `[{type, data}, ...]`
- Cast: `'sections' => 'array'` in Page model
- SectionBuilder component in PageResource Sections tab (custom Builder with modal picker)

### Registration
- ServiceProvider registers SectionRegistry singleton, reads from `config('cms-sections.types')`
- Host app publishes `cms-sections.php` and lists its SectionType classes
- Sections tab auto-hidden when no types registered

### SectionBuilder (`src/Filament/Forms/Components/SectionBuilder.php`)
Custom Filament Builder component extending `Filament\Forms\Components\Builder` with:
- **Modal section picker**: replaces default dropdown with a full modal (search, icons, descriptions, grid layout). Fixes overflow/z-index issues.
- **Section templates**: save any section as a reusable template (stores type + data in `section_templates` table). Templates appear in the modal alongside regular types. Templates with unregistered types are auto-filtered.
- **Actions**: `addFromTemplate` (add block with pre-filled data), `saveAsTemplate` (per-block extra item action, icon bookmark), `deleteTemplate` (with confirmation modal).
- **View**: `cms-core::filament.forms.components.section-builder` (Alpine.js modal with teleport to body, search filtering, responsive grid)
- **Deferred rendering**: Section form HTML is only rendered server-side for expanded sections. Collapsed sections show a lightweight header only, drastically reducing Livewire payload size and server render time. Requires `HasExpandableSections` trait on the Livewire page component.
- **Memoized data**: `getSectionTypeDefinitions()` and `getTemplates()` are cached per render cycle.

### HasExpandableSections Trait (`src/Filament/Concerns/HasExpandableSections.php`)
Used by EditPage and CreatePage for PageResource. Provides:
- `$expandedSections` / `$knownSectionUuids` — Livewire public properties tracking which sections render their form
- `expandSection($uuid)` / `collapseSection($uuid)` — called from Blade via `$wire`
- `syncSectionUuids(array $uuids)` — auto-detects newly added sections and expands them, cleans up removed sections

### Page Duplication
- `ReplicateAction` on both table and edit page header
- Duplicates all data including sections (JSON copy)
- Clears: key (null), slug (auto-generated unique), state (draft), published_at (null), is_home (false)
- Permission: `create pages`

### SectionCatalog Page (`src/Filament/Pages/SectionCatalog.php`)
- Nav group: Contenu, label: Sections, icon: heroicon-o-squares-2x2, sort: 2
- Custom Blade view with responsive card grid (1/2/3 cols)
- Each card shows: icon, label, description, fields count badge, templates count badge, "Creer un modele" link
- Data from `SectionRegistry::all()` + template counts from `SectionTemplate`
- Permission: `view pages`

### SectionTemplateResource (`src/Filament/Resources/SectionTemplateResource.php`)
- Nav group: Contenu, label: Modeles de section, icon: heroicon-o-bookmark, sort: 3
- CRUD resource for `SectionTemplate` model
- **Dynamic form**: resolves SectionType from `?sectionType=key` (create) or `$record->section_type` (edit), renders type's schema fields via `Group::make($typeClass::schema())->statePath('data')`
- **List page**: custom header action opens modal to select section type, then redirects to create with query param
- **Table**: name (searchable), section_type (badge with label from registry), created_at. Filter by section_type
- Permissions: reuses page permissions (view/create/edit/delete pages)

## Collections Module

Blueprint system for structured, reusable content types. Host app defines CollectionType classes, entries stored as individual DB rows (queryable via Eloquent).

### Key Classes (`src/Collections/`)
- **CollectionType** — Abstract class with `key()`, `label()`, `singularLabel()`, `icon()`, `fields()`. Optional: `hasSlug()`, `hasSeo()`, `hasStates()`, `sortable()`, `maxEntries()`, `description()`, `slugFrom()` (default: `'title'`). Auto-generates `schema()` (Filament components), `toDefinition()` (serializable).
- **CollectionRegistry** — Singleton service. `register(string $typeClass)`, `all()`, `resolve(string $key)`, `definitions()`. Validates classes extend CollectionType.

### Storage
- Individual rows in `collection_entries` table, discriminated by `collection_type` column
- Field values stored in `data` JSON column, accessed via `$entry->field('title')`
- Full SEO columns always present, form only shows when `CollectionType::hasSeo() === true`

### Registration
- ServiceProvider registers CollectionRegistry singleton, reads from `config('cms-collections.types')`
- Host app publishes `cms-collections.php` and lists its CollectionType classes
- Each registered type auto-generates a Filament navigation item in "Collections" group

### Filament Resource
- Single `CollectionEntryResource` with `shouldRegisterNavigation()` returning true when types are registered
- Dynamic nav items per registered CollectionType via `getNavigationItems()` override
- Query param `?collectionType=key` filters and configures the resource
- Dynamic form: `Group::make($typeClass::schema())->statePath('data')` wraps blueprint fields
- Slug auto-generated server-side from `slugFrom()` field in `CreateCollectionEntry::mutateFormDataBeforeCreate()` when left empty
- Table shows title (from `data.{slugFrom}`), slug, position, state, updated_at
- SEO/OG/Twitter/Schema tabs shown conditionally based on `hasSeo()`
- State select shown conditionally based on `hasStates()`
- Position field shown conditionally based on `sortable()`

## Config

- `config/cms-core.php` — admin path
- `config/cms-media.php` — proxy (imgproxy), unsplash, media upload settings
- `config/cms-icons.php` — icon sets, output mode, pagination, cache TTL, labels, variants
- `config/cms-sections.php` — section type classes registration (host app publishes and adds its types)
- `config/cms-collections.php` — collection type classes registration (host app publishes and adds its types)
- `config/cms-contacts.php` — contact module settings (default_async, retention_days, max_body_log_size)

Env vars: `IMGPROXY_ENABLE`, `IMGPROXY_URL`, `IMGPROXY_KEY`, `IMGPROXY_SALT`, `UNSPLASH_APP_ID`, `UNSPLASH_APP_KEY`, `UNSPLASH_SECRET_KEY`.

## Conventions

- French UI labels throughout (Filament resources, form fields, table columns)
- Migrations use sequence: 200xxx (users/roles), 300xxx (media), 500xxx (blog), 600xxx (SEO enhancements), 700xxx (redirections/sitemap), 800xxx (site settings/activity log), 900xxx (pages + section templates), 1000xxx (collections), 1100xxx (contact), 1200xxx (documentation/releases)
- Permissions follow pattern: `view/create/edit/delete {resource}` for CRUD, `manage {resource}` for settings pages
- Permissions and roles are ONLY in migrations, never configs or seeders
- All image fields use `MediaPicker` component + `MediaSelectionCast`
- `$guarded = ['id']` on models (not `$fillable`)
- Casts defined in `casts()` method (not `$casts` property)
- Slugs auto-generated with uniqueness via `Model::generateSlug()`

## Tests

Test files in host app `tests/Feature/` and `tests/Feature/Filament/`:
- `MediaLibraryTest.php` — media CRUD, folders, bulk ops, filters
- `MediaPickerTest.php` — MediaSelection, MediaSelectionCast, media_url(), UnsplashClient
- `BlogTest.php` — BlogSetting, BlogAuthor, BlogCategory, BlogTag, BlogPost, states/transitions, publish-scheduled command, permissions
- `BlogSeoTest.php` — SEO column existence (all tables including settings), model casts (arrays/booleans), content storage, publication validation (block/allow/warn), duplicate detection, settings fallback dimensions, schema_same_as, schema_organization_url
- `RedirectTest.php` — columns, permissions (super_admin/editor/viewer), CRUD, casts, recordHit, scopeActive, middleware (301/302/410/inactive/non-matching), cache invalidation
- `SitemapTest.php` — sitemap settings columns, casts (boolean/array), default values, settings storage, command registration, disabled exit
- `SiteSettingsTest.php` — SiteSetting model (columns, singleton, casts, defaults), SiteSettings page (access, save, password hashing), restricted access middleware (enabled/disabled, admin bypass, cookie, password validation), activity log (logging on all CMS models, resource access, purge command), permissions
- `IconPickerTest.php` — IconSelection VO, IconSelectionCast, IconDiscoveryService, API routes, config, cms_icon() helper
- `PageTest.php` — Pages table columns, permissions, model CRUD, SoftDeletes, states (Draft ↔ Published), scopes, static helpers, casts, Filament resource, page duplication (ReplicateAction with sections/slug/key/state), SectionTemplate model CRUD and filtering
- `SectionTest.php` — SectionField factories (13 types), fluent API, toFormComponent (each type), toDefinition, SectionType (schema, toBlock, toDefinition), SectionRegistry (register/resolve, blocks, definitions), config, Filament integration (Builder::fake())
- `SeoMetaTest.php` — SeoMeta VO (properties, serialization, toHtml, Stringable, escaping), SeoResolver (global defaults, page by key, model direct for all entities, fallback chains title/description/canonical/robots/OG/Twitter/Schema), seo_meta() helper
- `CollectionTest.php` — CollectionType (methods, schema, definition), CollectionRegistry (register/resolve, definitions, rejection), migration columns, permissions, model CRUD, field accessor, scopes, SoftDeletes, slug generation, state machine, casts, config, Filament resource access, dynamic nav items, helpers (collection_entries/collection_entry), SeoResolver with CollectionEntry
- `ContactTest.php` — Migrations (5 tables), permissions (14), ContactSetting singleton, Contact CRUD/upsert/casts/relations, ContactRequest states (new→processed→archived, transitions), pipeline (contact_event, idempotency, tag merge, hook dispatch, event filtering), HookEndpoint (CRUD, acceptsEvent, casts), HookDelivery (creation, relations), Job (success/failed/retry, HMAC signature), commands (retry-hooks, contact-purge), Filament resource access, helper
- `DocumentationAndReleasesTest.php` — Migration (user_release_views table, unique constraint, cascade delete), DocumentationService (load/find/sort/render markdown), ReleaseService (load/find/sort/unread detection/markAllAsRead/per-user tracking), Filament pages (access for super_admin/editor, guest redirect, no sidebar nav), ReleasePopup Livewire component (show/dismiss/mark read), user menu items registration, release content integrity
