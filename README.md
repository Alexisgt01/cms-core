# CMS Core

Headless CMS core package with Filament admin panel — user management, media library, blog engine.

## Requirements

- PHP 8.2+
- Laravel 12+
- Filament 3
- SQLite, MySQL or PostgreSQL

## Installation

### 1. Add the path repository

In your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/cms/core",
            "options": { "symlink": true }
        }
    ]
}
```

### 2. Require the package

```bash
composer require vendor/cms-core:@dev
```

### 3. Install Filament

If not already installed:

```bash
php artisan filament:install --panels --no-interaction
```

This creates `app/Providers/Filament/AdminPanelProvider.php` with panel ID `admin`.

### 4. Publish Spatie Permission migrations

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
```

### 5. Configure the User model

Add the `HasRoles` trait and `FilamentUser` interface to `app/Models/User.php`:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
```

### 6. Set up Tiptap Editor theme

The Tiptap editor requires a custom Filament theme. In your theme CSS:

```css
@import '/vendor/awcodes/filament-tiptap-editor/resources/css/plugin.css';
```

In `tailwind.config.js`, add to content:

```js
'./vendor/awcodes/filament-tiptap-editor/resources/**/*.blade.php',
```

Then rebuild: `npm run build`

### 7. Run migrations

```bash
php artisan migrate
```

### 8. Create an admin user

```bash
php artisan make:filament-user
```

Then assign the `super_admin` role to the user.

### 9. Access the admin panel

```
http://your-app.test/admin
```

## Configuration

Publish config files:

```bash
php artisan vendor:publish --tag=cms-core-config
```

This publishes:
- `config/cms-core.php` — admin panel path, default roles/permissions reference
- `config/cms-media.php` — imgproxy, Unsplash, upload settings

### Environment variables

```env
# imgproxy (optional)
IMGPROXY_ENABLE=false
IMGPROXY_URL=
IMGPROXY_KEY=
IMGPROXY_SALT=

# Unsplash (optional)
UNSPLASH_APP_ID=
UNSPLASH_APP_KEY=
UNSPLASH_SECRET_KEY=
```

## Modules

### Administration

| Resource    | Features                                       |
|-------------|------------------------------------------------|
| Users       | CRUD, first/last name, email, password, roles  |
| Roles       | CRUD, permission assignment, user count         |
| Permissions | Read-only list with role display                |
| Profile     | Self-edit (first/last name, email, password)    |

### Media Library

Full-featured media management:
- Hierarchical folders with drag & drop
- File upload with type/size validation
- Metadata editing (name, alt, description, tags)
- Search, filters (type, date range, tags)
- Bulk operations (delete, move, tag)
- Unsplash integration (search, download or use URL directly)
- imgproxy support (signed URLs, resize, format conversion)
- Infinite scroll

**MediaPicker** — reusable Filament form component for selecting media from library, uploading, or choosing from Unsplash.

**`media_url()`** — global helper for generating image URLs with optional imgproxy transformations:

```php
// Direct URL
media_url('http://example.com/image.jpg');

// With imgproxy transformations
media_url('http://example.com/image.jpg', [
    'width' => 800,
    'height' => 600,
    'format' => 'webp',
    'quality' => 85,
]);

// From media ID
media_url($media->id, ['width' => 400]);
```

### Blog

Complete blog engine with:

**Settings** (single-row config page):
- General: blog name, description, default author, posts per page, display toggles
- RSS: enable/disable, custom title/description
- Images: featured images max count (1-4), required toggle, OG/Twitter fallback images
- SEO defaults: indexation, canonical mode, meta title/description templates, robots directives
- Open Graph defaults: site name, type, locale, title/description templates
- Twitter defaults: card type, site/creator accounts, title/description templates
- Schema/JSON-LD defaults: type (Article/BlogPosting/NewsArticle), publisher info, language, custom JSON merge

**Authors**:
- Identity: display name, slug, email, job title, company
- Link to existing User (create from user, detach)
- Bio & social profiles (website, Twitter, LinkedIn, GitHub, Instagram)
- Avatar via MediaPicker
- Full SEO per author (meta, OG, Twitter, Schema)

**Posts**:
- Content: title, slug (auto-generated), excerpt (required), rich content via Tiptap editor
- Featured images via MediaPicker (count controlled by settings)
- Author assignment (fallback to default author)
- State machine via spatie/laravel-model-states:
  - **Draft** → Scheduled, Published
  - **Scheduled** → Draft, Published
  - **Published** → Draft
- Publication dates: published_at, scheduled_for, first_published_at, updated_content_at
- Reading time auto-calculation
- Full SEO per post: meta, robots, Open Graph, Twitter Cards, Schema/JSON-LD

**Scheduled publishing**:

```bash
php artisan blog:publish-scheduled
```

Add to your scheduler for hourly execution:

```php
$schedule->command('blog:publish-scheduled')->hourly();
```

## Permissions

### Roles

| Role | Description |
|------|-------------|
| `super_admin` | All permissions |
| `editor` | Content management (no delete, no settings) |
| `viewer` | Read-only access |

### Permission matrix

| Permission | super_admin | editor | viewer |
|---|:---:|:---:|:---:|
| view/create/edit/delete users | x | view/create/edit | view |
| view/create/edit/delete roles | x | view | view |
| edit/delete profile | x | edit | |
| view/create/edit/delete media | x | view/create | |
| manage blog settings | x | | |
| view/create/edit/delete blog authors | x | view/create/edit | |
| view/create/edit/delete blog posts | x | view/create/edit | |
| publish blog posts | x | x | |

Without `publish blog posts` permission, only "Brouillon" (Draft) is available — no publish or schedule.

## Package structure

```
packages/cms/core/
├── config/
│   ├── cms-core.php
│   └── cms-media.php
├── database/migrations/
│   ├── 200001_add_name_fields_to_users_table
│   ├── 200002_create_default_roles_and_permissions
│   ├── 300001_create_cms_media_tables
│   ├── 300002_add_media_permissions
│   ├── 500000_create_blog_authors_table
│   ├── 500001_create_blog_settings_table
│   ├── 500002_create_blog_posts_table
│   └── 500003_add_blog_permissions
├── resources/views/filament/
│   ├── forms/components/media-picker.blade.php
│   └── pages/
│       ├── blog-settings.blade.php
│       ├── media-library.blade.php
│       └── media-library-preview.blade.php
└── src/
    ├── helpers.php
    ├── CmsCoreServiceProvider.php
    ├── Casts/MediaSelectionCast.php
    ├── Console/Commands/PublishScheduledPosts.php
    ├── Filament/
    │   ├── Actions/CmsMediaAction.php
    │   ├── CmsCorePlugin.php
    │   ├── Forms/Components/MediaPicker.php
    │   ├── Pages/
    │   │   ├── BlogSettings.php
    │   │   ├── EditProfile.php
    │   │   └── MediaLibrary.php
    │   └── Resources/
    │       ├── BlogAuthorResource.php (+Pages)
    │       ├── BlogPostResource.php (+Pages)
    │       ├── PermissionResource.php
    │       ├── RoleResource.php
    │       └── UserResource.php (+Pages)
    ├── Models/
    │   ├── BlogAuthor.php
    │   ├── BlogPost.php
    │   ├── BlogSetting.php
    │   ├── CmsMedia.php
    │   ├── CmsMediaFolder.php
    │   └── States/ (Draft, Scheduled, Published, PostState)
    ├── Policies/
    │   ├── CmsMediaPolicy.php
    │   ├── PermissionPolicy.php
    │   ├── RolePolicy.php
    │   └── UserPolicy.php
    ├── Services/
    │   ├── MediaService.php
    │   └── UnsplashClient.php
    └── ValueObjects/MediaSelection.php
```
