# CMS Core — Agent Context

This is the `vendor/cms-core` package (namespace `Vendor\CmsCore`), a Filament v3 admin panel package for Laravel 12.
Everything lives in `packages/cms/core/`. The host app at the root is a sandbox for testing only.

## Architecture

- **ServiceProvider**: `CmsCoreServiceProvider` — registers configs (`cms-core`, `cms-media`), auto-registers plugin via `Panel::configureUsing()` in `register()` (NOT boot), loads migrations/views, registers policies and commands
- **Plugin**: `CmsCorePlugin` — registers all Filament resources and pages
- **Views namespace**: `cms-core` (e.g. `cms-core::filament.pages.blog-settings`)
- **Helpers**: `src/helpers.php` loaded via Composer `files` autoload. After modifying, run `composer update vendor/cms-core`

## Models

| Model | Table | Key features |
|-------|-------|-------------|
| `BlogPost` | `blog_posts` | HasStates (Draft/Scheduled/Published), Tiptap content, featured_images (JSON array), exhaustive SEO fields, MediaSelectionCast on og_image/twitter_image |
| `BlogAuthor` | `blog_authors` | Optional user linking, social profiles, avatar (MediaSelectionCast), SEO fields |
| `BlogSetting` | `blog_settings` | Singleton via `BlogSetting::instance()`, 70+ config fields, MediaSelectionCast on image fallbacks |
| `CmsMedia` | `media` (Spatie) | Extends Spatie Media, belongs to CmsMediaFolder, appends url/human_readable_size |
| `CmsMediaFolder` | `cms_media_folders` | Hierarchical (parent/children), has many CmsMedia |

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
- `MediaSelectionCast` (`src/Casts/`) — Eloquent cast for JSON ↔ MediaSelection. Used on: BlogPost.og_image, BlogPost.twitter_image, BlogAuthor.avatar/og_image/twitter_image, BlogSetting.og_image_fallback/twitter_image_fallback/schema_publisher_logo.

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
use Vendor\CmsCore\Filament\Forms\Components\MediaPicker;

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

## Filament Pages

| Page | Nav group | Description |
|------|-----------|-------------|
| MediaLibrary | Medias | Full media management (folders, upload, search, filters, bulk ops, Unsplash, imgproxy) |
| BlogSettings | Blog | Single-row settings form (General, RSS, Images, SEO, OG, Twitter, Schema). Requires `manage blog settings` |
| EditProfile | — | Profile editing page |

## Blog Post Form Structure

Tabs: Contenu (title, slug, excerpt, Tiptap content) → Images & Auteur (featured images, author, reading time) → Publication (state select, conditional dates) → SEO → Open Graph → Twitter → Schema.

Without `publish blog posts` permission, state select only shows "Brouillon" (Draft).

Tiptap uploads go through `MediaService::storeUploadedFile()` via `CmsMediaAction` (custom Tiptap media action registered globally in ServiceProvider).

## CmsMediaAction (Tiptap Media Modal Override)

`src/Filament/Actions/CmsMediaAction.php` replaces the default `FilamentTiptapEditor\Actions\MediaAction`. Registered via `config('filament-tiptap-editor.media_action')` override in `CmsCoreServiceProvider::register()`.

Key features:
- Stores uploads through `MediaService::storeUploadedFile()` (not default disk storage)
- Preserves aspect ratio: hidden `aspect_ratio` field calculated from uploaded image dimensions
- Width/height fields are `live(onBlur: true)` with `afterStateUpdated` callbacks that recalculate the other dimension based on the stored ratio
- Supports `mountUsing` to restore existing dimensions when editing an already-inserted image

## Permissions

All created via migrations (NOT seeders). Pattern: `Permission::create()` + role assignment.

**Blog permissions**: manage blog settings, view/create/edit/delete blog authors, view/create/edit/delete blog posts, publish blog posts.

super_admin = all. editor = view/create/edit authors + view/create/edit/publish posts (no delete, no settings). viewer = none.

## Commands

- `blog:publish-scheduled` — finds Scheduled posts with `scheduled_for <= now()`, transitions to Published, sets published_at/first_published_at. Run hourly via scheduler.

## Config

- `config/cms-core.php` — admin path, roles/permissions reference
- `config/cms-media.php` — proxy (imgproxy), unsplash, media upload settings

Env vars: `IMGPROXY_ENABLE`, `IMGPROXY_URL`, `IMGPROXY_KEY`, `IMGPROXY_SALT`, `UNSPLASH_APP_ID`, `UNSPLASH_APP_KEY`, `UNSPLASH_SECRET_KEY`.

## Conventions

- French UI labels throughout (Filament resources, form fields, table columns)
- Migrations use sequence: 200xxx (users/roles), 300xxx (media), 500xxx (blog)
- Permissions follow pattern: `view/create/edit/delete {resource}` for CRUD, `manage {resource}` for settings pages
- All image fields use `MediaPicker` component + `MediaSelectionCast`
- `$guarded = ['id']` on models (not `$fillable`)
- Casts defined in `casts()` method (not `$casts` property)
- Slugs auto-generated with uniqueness via `Model::generateSlug()`

## Tests

Test files in host app `tests/Feature/Filament/`:
- `MediaLibraryTest.php` — media CRUD, folders, bulk ops, filters
- `MediaPickerTest.php` — MediaSelection, MediaSelectionCast, media_url(), UnsplashClient
- `BlogTest.php` — BlogSetting, BlogAuthor, BlogPost, states/transitions, publish-scheduled command, permissions
