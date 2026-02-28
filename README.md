# CMS Core

Headless CMS core package with Filament admin panel — user management, media library, blog engine, site settings, activity log.

**Package:** `alexisgt01/cms-core`
**Namespace:** `Alexisgt01\CmsCore`
**Repository:** [github.com/Alexisgt01/cms-core](https://github.com/Alexisgt01/cms-core)

---

## Table of contents

- [Part 1 — Installation guide](#part-1--installation-guide)
  - [Requirements](#requirements)
  - [Step 1 — Add the repository](#step-1--add-the-repository)
  - [Step 2 — Require the package](#step-2--require-the-package)
  - [Step 3 — Install Filament](#step-3--install-filament)
  - [Step 4 — Publish Spatie Permission migrations](#step-4--publish-spatie-permission-migrations)
  - [Step 5 — Configure the User model](#step-5--configure-the-user-model)
  - [Step 6 — Configure Spatie Media Library](#step-6--configure-spatie-media-library)
  - [Step 7 — Set up Tiptap Editor theme](#step-7--set-up-tiptap-editor-theme)
  - [Step 8 — Environment variables](#step-8--environment-variables)
  - [Step 9 — Run migrations](#step-9--run-migrations)
  - [Step 10 — Create an admin user](#step-10--create-an-admin-user)
  - [Step 11 — Set up the scheduler](#step-11--set-up-the-scheduler)
  - [Step 12 — Access the admin panel](#step-12--access-the-admin-panel)
  - [Post-installation checklist](#post-installation-checklist)
  - [Optional — Publish config files](#optional--publish-config-files)
  - [Configuration reference](#configuration-reference)
- [Part 2 — Update & evolution guide](#part-2--update--evolution-guide)
  - [Standard update procedure](#standard-update-procedure)
  - [Adding a new migration](#adding-a-new-migration)
  - [Adding a new config file](#adding-a-new-config-file)
  - [Adding a new model](#adding-a-new-model)
  - [Adding a new Filament resource or page](#adding-a-new-filament-resource-or-page)
  - [Adding new permissions or roles](#adding-new-permissions-or-roles)
  - [Adding a new Artisan command](#adding-a-new-artisan-command)
  - [Adding a new helper function](#adding-a-new-helper-function)
  - [Adding new views](#adding-new-views)
  - [Adding a new route](#adding-a-new-route)
  - [Adding a new policy](#adding-a-new-policy)
  - [Modifying an existing config file](#modifying-an-existing-config-file)
  - [Modifying an existing migration (column change)](#modifying-an-existing-migration-column-change)
  - [Versioning & releases](#versioning--releases)
  - [Release checklist](#release-checklist)
  - [Backward compatibility rules](#backward-compatibility-rules)
  - [Changelog format](#changelog-format)
- [Appendix — Modules reference](#appendix--modules-reference)
  - [Administration](#administration)
  - [Media Library](#media-library)
  - [Blog](#blog)
  - [Dashboards](#dashboards)
  - [Redirections](#redirections)
  - [Sitemap](#sitemap)
  - [Site Settings](#site-settings)
  - [Restricted Access](#restricted-access)
  - [Activity Log](#activity-log)
  - [Git Version Footer](#git-version-footer)
  - [Icon Picker](#icon-picker)
  - [Pages](#pages)
  - [Sections](#sections)
  - [SEO Helper](#seo-helper)
  - [Collections](#collections)
- [Appendix — Artisan commands](#appendix--artisan-commands)
- [Appendix — Permissions reference](#appendix--permissions-reference)
- [Appendix — Package structure](#appendix--package-structure)
- [Appendix — Test files](#appendix--test-files)

---

# Part 1 — Installation guide

## Requirements

| Dependency | Version |
|---|---|
| PHP | >= 8.2 |
| Laravel | >= 12.0 |
| Filament | >= 3.0 |
| Database | SQLite, MySQL or PostgreSQL |
| Node.js | >= 18 (for Filament theme build) |

The package pulls these dependencies automatically via Composer:

| Package | Version |
|---|---|
| `filament/filament` | ^3.0 |
| `spatie/laravel-permission` | ^6.0 |
| `spatie/laravel-medialibrary` | ^11.0 |
| `spatie/laravel-model-states` | ^2.0 |
| `awcodes/filament-tiptap-editor` | ^3.0 |
| `spatie/laravel-activitylog` | ^4.0 |

---

## Step 1 — Add the repository

In your Laravel project's `composer.json`, add the path repository:

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

> For production with a tagged release on Packagist/GitHub, use the standard VCS repository or omit this step entirely.

---

## Step 2 — Require the package

```bash
composer require alexisgt01/cms-core:@dev
```

> For a tagged version: `composer require alexisgt01/cms-core:^1.0`

The ServiceProvider is **auto-discovered** via `composer.json` `extra.laravel.providers`. No manual registration needed.

**What happens automatically:**
- Config files `cms-core` and `cms-media` are merged into the application config
- The `CmsCorePlugin` is auto-registered on the Filament panel with ID `admin`
- Migrations are loaded from the package
- Views are registered under the `cms-core` namespace
- Policies are registered for User, Role, Permission, and CmsMedia models
- The `cms:publish-scheduled` and `cms:purge-activity` Artisan commands become available
- The Tiptap editor media action is overridden to use the CMS media library
- An API route (`GET /{admin-path}/cms/api/unsplash/search`) is registered
- The `HandleRestrictedAccess` and `HandleRedirects` global middlewares are registered
- A POST route `/cms/restricted-access` is registered for password validation

---

## Step 3 — Install Filament

If Filament is not already installed:

```bash
php artisan filament:install --panels --no-interaction
```

This creates `app/Providers/Filament/AdminPanelProvider.php`.

**Critical:** The panel must have ID `admin`. The package auto-registers its plugin only on this panel ID. Verify in your `AdminPanelProvider`:

```php
return $panel
    ->default()
    ->id('admin')   // ← must be 'admin'
    ->path('admin')
    ->login()
    // ...
```

---

## Step 4 — Publish Spatie Permission migrations

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
```

This publishes the Spatie Permission migration files required before running the CMS migrations.

---

## Step 5 — Configure the User model

Add the `HasRoles` trait and `FilamentUser` interface to `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentName(): string
    {
        return "$this->first_name $this->last_name";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // or add your own logic
    }
}
```

**Critical:** The `HasName` interface and `getFilamentName()` method are **required**. Without them, Filament will throw `TypeError: getUserName(): Return value must be of type string, null returned` because the package migration removes the `name` column (replaced by `first_name` and `last_name`).

---

## Step 6 — Configure Spatie Media Library

Publish the media-library config if not already done:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --no-interaction
```

Then edit `config/media-library.php` and set the custom media model:

```php
'media_model' => Alexisgt01\CmsCore\Models\CmsMedia::class,
```

This is **required** — the CMS extends Spatie's base media model with folder support and custom attributes.

---

## Step 7 — Set up Tiptap Editor theme

The Tiptap rich text editor requires its CSS to be included in a Filament custom theme.

**If you don't have a Filament theme yet**, create one:

```bash
php artisan make:filament-theme
```

Then follow the instructions output by the command. In the generated theme CSS file, add:

```css
@import '/vendor/awcodes/filament-tiptap-editor/resources/css/plugin.css';
```

In `tailwind.config.js`, add to the `content` array:

```js
'./vendor/awcodes/filament-tiptap-editor/resources/**/*.blade.php',
```

Build the theme:

```bash
npm run build
```

Register the theme in your `AdminPanelProvider` if not done by the make command:

```php
->viteTheme('resources/css/filament/admin/theme.css')
```

---

## Step 8 — Environment variables

Add these to your `.env` file. All are **optional** — the CMS works without them.

```env
# ─── imgproxy (image transformation proxy) ───
IMGPROXY_ENABLE=false
IMGPROXY_URL=              # e.g. https://imgproxy.example.com
IMGPROXY_KEY=              # hex-encoded HMAC key
IMGPROXY_SALT=             # hex-encoded HMAC salt

# ─── Unsplash (stock photos integration) ───
UNSPLASH_APP_ID=
UNSPLASH_APP_KEY=          # also enables/disables the feature
UNSPLASH_SECRET_KEY=
```

| Variable | Required | Description |
|---|---|---|
| `IMGPROXY_ENABLE` | No | Enable imgproxy URL generation (`true`/`false`) |
| `IMGPROXY_URL` | Only if enabled | Base URL of your imgproxy instance |
| `IMGPROXY_KEY` | No | HMAC signing key (hex). If empty, uses `/unsafe/` URLs |
| `IMGPROXY_SALT` | No | HMAC signing salt (hex). Required with `IMGPROXY_KEY` |
| `UNSPLASH_APP_ID` | No | Unsplash application ID |
| `UNSPLASH_APP_KEY` | No | Unsplash access key. Presence enables the integration |
| `UNSPLASH_SECRET_KEY` | No | Unsplash secret key |

---

## Step 9 — Run migrations

```bash
php artisan migrate
```

The package ships **23 migrations** that run in sequence:

| Sequence | Migration | Description |
|---|---|---|
| 200001 | `add_name_fields_to_users_table` | Adds `first_name`, `last_name` to `users` |
| 200002 | `create_default_roles_and_permissions` | Creates roles (`super_admin`, `editor`, `viewer`) and base permissions |
| 300001 | `create_cms_media_tables` | Extends Spatie `media` table, creates `cms_media_folders` |
| 300002 | `add_media_permissions` | Creates media CRUD permissions |
| 500000 | `create_blog_authors_table` | Creates `blog_authors` |
| 500001 | `create_blog_settings_table` | Creates `blog_settings` (singleton config) |
| 500002 | `create_blog_posts_table` | Creates `blog_posts` with state machine |
| 500003 | `add_blog_permissions` | Creates blog CRUD + publish permissions |
| 500004 | `create_blog_categories_table` | Creates `blog_categories` (hierarchical) |
| 500005 | `add_category_id_to_blog_posts` | Adds `category_id` FK to `blog_posts` |
| 500006 | `create_blog_tags_table` | Creates `blog_tags` |
| 500007 | `create_blog_post_tag_pivot` | Creates `blog_post_tag` pivot table |
| 500008 | `add_tag_category_permissions` | Creates tag/category CRUD permissions |
| 600001 | `add_seo_enhancements_to_blog_posts` | Adds h1, subtitle, seo_excerpt, content_seo, keywords, faq, OG/Twitter/schema fields |
| 600002 | `add_seo_enhancements_to_blog_authors` | Adds h1, content_seo, keywords, robots, OG/Twitter/schema fields |
| 600003 | `add_seo_enhancements_to_blog_categories` | Same as authors + indexing, canonical_url |
| 600004 | `add_seo_enhancements_to_blog_tags` | Identical to categories |
| 600005 | `add_seo_defaults_to_blog_settings` | Adds default_h1_from_title, default_schema_types |
| 600006 | `add_seo_enhancements_to_blog_settings` | Adds OG/Twitter fallback dimensions, schema_same_as, schema_organization_url |
| 700001 | `create_redirects_table` | Creates `redirects` (source_path, destination_url, status_code, hit tracking) |
| 700002 | `add_redirect_permissions` | Creates view/create/edit/delete redirects permissions |
| 700003 | `add_sitemap_settings_to_blog_settings` | Adds sitemap config columns (enabled, base_url, max_urls, crawl_depth, concurrency, exclude_patterns, change_freq, priority) |
| 800001 | `create_site_settings_table` | Creates `site_settings` (identity, contact, restricted access, global SEO, admin) |
| 800002 | `add_site_settings_and_activity_log_permissions` | Creates `manage site settings` and `view activity log` permissions |
| 800003 | `add_legal_social_columns_to_site_settings` | Adds legal (company, SIRET, hosting, DPO), contact (phone, maps), social media (10 platforms) |
| 800004 | `add_footer_and_opening_hours_to_site_settings` | Adds footer (copyright, text, start year), contact (opening hours) |

**Sequence convention:** `200xxx` = admin/users, `300xxx` = media, `500xxx` = blog, `600xxx` = SEO enhancements, `700xxx` = redirections/sitemap, `800xxx` = site settings/activity log.

---

## Step 10 — Create an admin user

```bash
php artisan cms:make-admin
```

The command prompts for first name, last name, email, and password, then creates the user with the `super_admin` role.

For non-interactive use (CI, scripts):

```bash
php artisan cms:make-admin --first-name=Admin --last-name=User --email=admin@example.com --password=your-password
```

> Do **not** use `make:filament-user` — it expects a `name` field which does not exist (the CMS uses `first_name` and `last_name`).

---

## Step 11 — Set up the scheduler

The blog uses scheduled publishing. Add the command to your scheduler.

In `routes/console.php` or your scheduling configuration:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('cms:publish-scheduled')->hourly();
```

This transitions posts in `Scheduled` state to `Published` when `scheduled_for <= now()`.

---

## Step 12 — Access the admin panel

```
http://your-app.test/admin
```

The path is configurable via `config('cms-core.path')` (default: `admin`).

---

## Post-installation checklist

Run through this after installation to confirm everything works:

- [ ] `php artisan migrate:status` — all 23 CMS migrations show "Ran"
- [ ] `php artisan route:list --name=cms` — the `cms.unsplash.search` route exists
- [ ] Navigate to `/admin` — login page appears
- [ ] Log in with super_admin user — sidebar shows: Administration (Users, Roles, Permissions, Journal d'activite, Parametres du site), Medias (Mediatheque), Blog (Articles, Auteurs, Categories, Tags, Parametres)
- [ ] Create a media folder and upload an image — file appears in `storage/app/public/`
- [ ] Create a blog author — slug auto-generates
- [ ] Create a blog category with a child category — hierarchy works
- [ ] Create a blog tag
- [ ] Create a blog post — select category, tags, upload featured image, use Tiptap editor
- [ ] Publish the post — state changes from Draft to Published
- [ ] `php artisan cms:make-admin` — creates admin user with super_admin role
- [ ] `php artisan cms:publish-scheduled` — command runs without error
- [ ] `php artisan cms:sitemap` — generates sitemap.xml in public/
- [ ] `php artisan cms:purge-activity` — purges old activity log entries
- [ ] Blog Settings page loads — General, RSS, Images, SEO (+ SERP preview), OG (+ OG preview + fallback dimensions), Twitter (+ Twitter preview + fallback dimensions), Schema (+ JSON-LD validation + social profiles sameAs), Sitemap
- [ ] Site Settings page loads — Identite, Contact, Acces restreint, SEO Global, Admin
- [ ] Activity Log page loads — lists recent model changes (read-only)
- [ ] Edit a blog post — activity log entry is created automatically

---

## Optional — Publish config files

Only publish if you need to customize defaults:

```bash
php artisan vendor:publish --tag=cms-core-config
```

This copies to your application:
- `config/cms-core.php` — admin panel path
- `config/cms-media.php` — imgproxy, Unsplash, upload settings

> If you don't publish, the package defaults are used. Published files take precedence.

---

## Configuration reference

### `cms-core.php`

| Key | Type | Default | Description |
|---|---|---|---|
| `path` | string | `'admin'` | URL prefix for the admin panel |

### `cms-media.php`

| Key | Type | Default | Description |
|---|---|---|---|
| `proxy.enabled` | bool | `false` | Enable imgproxy |
| `proxy.url` | string | `''` | imgproxy base URL |
| `proxy.key` | string | `''` | HMAC signing key (hex) |
| `proxy.salt` | string | `''` | HMAC signing salt (hex) |
| `unsplash.enabled` | bool | auto | Enabled when `UNSPLASH_APP_KEY` is set |
| `unsplash.app_id` | string | `''` | Unsplash app ID |
| `unsplash.access_key` | string | `''` | Unsplash access key |
| `unsplash.secret_key` | string | `''` | Unsplash secret key |
| `media.max_upload_size` | int | `10240` | Max upload size in KB (10 MB) |
| `media.accepted_types` | array | `['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']` | Allowed MIME types |

---

# Part 2 — Update & evolution guide

This section documents how to evolve the package — add features, ship migrations, manage releases, and deploy updates to host applications.

---

## Standard update procedure

When a new version of the package is released, the host application updates with:

```bash
# 1. Update the package
composer update alexisgt01/cms-core

# 2. Run new migrations
php artisan migrate

# 3. Re-publish config if needed (won't overwrite existing)
php artisan vendor:publish --tag=cms-core-config

# 4. Clear caches
php artisan optimize:clear

# 5. Rebuild frontend assets if Tiptap/Filament views changed
npm run build
```

**If using a path repository (development):**

```bash
# The symlink means code changes are instant.
# Only run these if you changed:
composer update alexisgt01/cms-core   # autoload (new class, helper, namespace)
php artisan migrate                    # new migrations
php artisan optimize:clear             # config/route/view changes
```

---

## Adding a new migration

### Procedure

1. **Create the migration file** in `packages/cms/core/database/migrations/`.

2. **Follow the naming convention:**
   ```
   {YYYY}_{MM}_{DD}_{SEQUENCE}_description.php
   ```
   Sequences:
   - `200xxx` — Administration (users, roles, permissions)
   - `300xxx` — Media library
   - `500xxx` — Blog
   - `600xxx` — SEO enhancements
   - `700xxx` — Redirections/sitemap
   - `800xxx` — Site settings/activity log

   Increment from the last migration in the sequence. Example: if the last blog migration is `500008`, the next is `500009`.

3. **Always write a `down()` method** for rollback support.

4. **For column additions on existing tables**, create a new migration — never modify an existing migration that has already been released.

5. **For new permissions**, create a dedicated migration (see [Adding new permissions](#adding-new-permissions-or-roles)).

### Impact on host apps

- `php artisan migrate` runs the new migration automatically (package migrations are auto-loaded).
- No Composer update needed if using symlink. For tagged releases, `composer update` first.

### Validation

```bash
php artisan migrate                     # apply
php artisan migrate:rollback --step=1   # verify rollback
php artisan migrate                     # re-apply
php artisan test --compact              # full test suite
```

### Example: adding a column to blog_posts

```php
// database/migrations/2026_03_15_500009_add_reading_count_to_blog_posts.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('reading_count')->default(0)->after('reading_time');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('reading_count');
        });
    }
};
```

---

## Adding a new config file

### Procedure

1. **Create the config file** in `packages/cms/core/config/`, e.g. `cms-newsletter.php`.

2. **Register it in `CmsCoreServiceProvider::register()`:**
   ```php
   $this->mergeConfigFrom(__DIR__ . '/../config/cms-newsletter.php', 'cms-newsletter');
   ```

3. **Add it to the publish list in `CmsCoreServiceProvider::boot()`:**
   ```php
   $this->publishes([
       __DIR__ . '/../config/cms-core.php' => config_path('cms-core.php'),
       __DIR__ . '/../config/cms-media.php' => config_path('cms-media.php'),
       __DIR__ . '/../config/cms-newsletter.php' => config_path('cms-newsletter.php'),  // ← add
   ], 'cms-core-config');
   ```

4. **Use `config()` in code**, never `env()` directly:
   ```php
   config('cms-newsletter.enabled')   // ✓
   env('NEWSLETTER_ENABLED')          // ✗
   ```

5. **Document the new env vars** in this README under [Environment variables](#step-8--environment-variables) and [Configuration reference](#configuration-reference).

### Impact on host apps

- `mergeConfigFrom()` means the package defaults work immediately — **no action needed** unless the host wants to override.
- To override: `php artisan vendor:publish --tag=cms-core-config` (or manually copy).
- **If the host already published configs**, the new file won't appear automatically. They must re-publish or manually copy it.

### Validation

```bash
php artisan config:show cms-newsletter   # verify config loads
php artisan optimize:clear               # clear config cache
php artisan test --compact
```

---

## Adding a new model

### Procedure

1. **Create the model** in `packages/cms/core/src/Models/`.

2. **Follow the conventions:**
   - `$guarded = ['id']` (not `$fillable`)
   - Casts in `casts()` method (not `$casts` property)
   - Use `MediaSelectionCast` for all image JSON fields
   - Add `generateSlug()` static method if the model has a slug
   - Type-hint all relationships

3. **Create the migration** in `database/migrations/` (see [Adding a new migration](#adding-a-new-migration)).

4. **Create a policy** if needed (see [Adding a new policy](#adding-a-new-policy)).

5. **Create permissions** via migration if the model has CRUD access control (see [Adding new permissions](#adding-new-permissions-or-roles)).

6. **Update `CLAUDE.md`** — add the model to the Models table.

### Impact on host apps

- Models are autoloaded via PSR-4. No registration needed.
- If the model requires a migration, `php artisan migrate` is needed.

### Example

```php
// src/Models/BlogNewsletter.php

<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogNewsletter extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'author_id');
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
```

---

## Adding a new Filament resource or page

### Procedure — Resource

1. **Create the resource** in `packages/cms/core/src/Filament/Resources/`.

2. **Create its Pages** in a subdirectory:
   ```
   src/Filament/Resources/BlogNewsletterResource/Pages/
   ├── ListBlogNewsletters.php
   ├── CreateBlogNewsletter.php
   └── EditBlogNewsletter.php
   ```

3. **Add authorization gates** (mandatory):
   ```php
   public static function canAccess(): bool
   {
       return auth()->user()?->can('view blog newsletters') ?? false;
   }

   public static function canCreate(): bool
   {
       return auth()->user()?->can('create blog newsletters') ?? false;
   }

   public static function canEdit(Model $record): bool
   {
       return auth()->user()?->can('edit blog newsletters') ?? false;
   }

   public static function canDelete(Model $record): bool
   {
       return auth()->user()?->can('delete blog newsletters') ?? false;
   }
   ```

4. **Register it in `CmsCorePlugin.php`:**
   ```php
   ->resources([
       // ... existing resources
       BlogNewsletterResource::class,  // ← add
   ])
   ```

5. **Create permissions** via migration.

### Procedure — Page

1. **Create the page** in `packages/cms/core/src/Filament/Pages/`.

2. **Add a `canAccess()` method.**

3. **Register it in `CmsCorePlugin.php`:**
   ```php
   ->pages([
       MediaLibrary::class,
       BlogSettings::class,
       BlogNewsletterDashboard::class,  // ← add
   ])
   ```

### Impact on host apps

- Registered via the plugin — appears in the admin panel immediately after `composer update` (or instantly with symlink).
- New permissions require `php artisan migrate`.

### Validation

```bash
php artisan migrate          # for new permissions
php artisan optimize:clear   # clear route/view cache
# Navigate to the new resource in the admin panel
php artisan test --compact
```

---

## Adding new permissions or roles

### Rules

- Permissions and roles are **only** created in migrations. Never in configs, seeders, or code.
- Each set of related permissions gets its own migration.
- Always assign to roles in the same migration.

### Procedure

1. **Create a new migration:**

```php
// database/migrations/2026_03_15_500009_add_newsletter_permissions.php

<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'view blog newsletters',
            'create blog newsletters',
            'edit blog newsletters',
            'delete blog newsletters',
            'send blog newsletters',
        ];

        foreach ($permissions as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign to super_admin (all permissions)
        $superAdmin = Role::findByName('super_admin', 'web');
        $superAdmin->givePermissionTo($permissions);

        // Assign to editor (view/create/edit + send, not delete)
        $editor = Role::findByName('editor', 'web');
        $editor->givePermissionTo([
            'view blog newsletters',
            'create blog newsletters',
            'edit blog newsletters',
            'send blog newsletters',
        ]);
    }

    public function down(): void
    {
        $permissions = [
            'view blog newsletters',
            'create blog newsletters',
            'edit blog newsletters',
            'delete blog newsletters',
            'send blog newsletters',
        ];

        foreach ($permissions as $name) {
            Permission::findByName($name, 'web')?->delete();
        }
    }
};
```

2. **Permission naming convention:**
   - CRUD: `view {resource}`, `create {resource}`, `edit {resource}`, `delete {resource}`
   - Settings: `manage {resource}`
   - Special actions: `publish blog posts`, `send blog newsletters`

3. **Role convention:**
   - `super_admin` — receives all permissions
   - `editor` — receives view/create/edit (not delete, not manage)
   - `viewer` — receives nothing (or explicit view-only if needed)

---

## Adding a new Artisan command

### Procedure

1. **Create the command** in `packages/cms/core/src/Console/Commands/`.

2. **Register it in `CmsCoreServiceProvider::boot()`:**
   ```php
   $this->commands([
       PublishScheduledPosts::class,
       SendScheduledNewsletters::class,  // ← add
   ]);
   ```

3. **Document the command** in this README.

4. **If it needs scheduling**, document the scheduler line for the host app.

### Impact on host apps

- Available immediately after `composer update` (auto-discovered, no manual registration in host app).
- If scheduled, the host must add the schedule line to `routes/console.php`.

---

## Adding a new helper function

### Procedure

1. **Add the function** in `packages/cms/core/src/helpers.php`.

2. **Wrap in `function_exists` check:**
   ```php
   if (! function_exists('my_helper')) {
       function my_helper(): string
       {
           // ...
       }
   }
   ```

3. **Regenerate autoload:**
   ```bash
   composer update alexisgt01/cms-core
   ```

### Impact on host apps

- Available globally after `composer update` (loaded via Composer `files` autoload).
- The `function_exists` check prevents conflicts if the host defines the same function.

---

## Adding new views

### Procedure

1. **Create the view** in `packages/cms/core/resources/views/`.

2. **Reference it** using the `cms-core` namespace:
   ```php
   protected static string $view = 'cms-core::filament.pages.my-new-page';
   ```

### Impact on host apps

- Views are loaded automatically via the namespace. No action needed.
- Views are **not published by default**. If a host needs to override a view, they can publish it manually:
  ```bash
  php artisan vendor:publish --tag=cms-core-views
  ```
  (Only if you add a `cms-core-views` publish tag. Currently not registered.)

---

## Adding a new route

### Procedure

1. **Add the route** in `CmsCoreServiceProvider::registerRoutes()`:
   ```php
   Route::get('my-endpoint', function (Request $request) {
       // Always check permissions
       if (! $request->user()?->can('some permission')) {
           abort(403);
       }
       // ...
   })->name('cms.my-endpoint');
   ```

2. **Always include:**
   - Permission check (never rely on middleware alone)
   - Named route (`cms.*` prefix)
   - The route inherits `['web', 'auth']` middleware and the admin path prefix

### Impact on host apps

- Available immediately. No registration needed.

---

## Adding a new policy

### Procedure

1. **Create the policy** in `packages/cms/core/src/Policies/`.

2. **Register it in `CmsCoreServiceProvider::boot()`:**
   ```php
   Gate::policy(BlogNewsletter::class, BlogNewsletterPolicy::class);
   ```

3. **Follow the pattern:**
   ```php
   public function viewAny(User $user): bool
   {
       return $user->can('view blog newsletters');
   }

   public function create(User $user): bool
   {
       return $user->can('create blog newsletters');
   }

   public function update(User $user, BlogNewsletter $newsletter): bool
   {
       return $user->can('edit blog newsletters');
   }

   public function delete(User $user, BlogNewsletter $newsletter): bool
   {
       return $user->can('delete blog newsletters');
   }
   ```

---

## Modifying an existing config file

### Procedure

1. **Add the new key** to the config file in the package.

2. **Use a sensible default** — existing hosts that haven't published the config must get the default automatically via `mergeConfigFrom()`.

3. **Document the new key** in this README.

### Impact on host apps

- **If the host did NOT publish the config:** the new key is available immediately with its default value. No action needed.
- **If the host DID publish the config:** `mergeConfigFrom()` performs a shallow merge (first-level keys only). New top-level keys are available. New nested keys under an existing top-level key are **NOT merged** — the host must manually add them to their published file.

### Example

Adding `media.image_quality` under the existing `media` key in `cms-media.php`:

```php
// Package default
'media' => [
    'max_upload_size' => 10240,
    'accepted_types' => [...],
    'image_quality' => 85,        // ← new key
],
```

If the host published `config/cms-media.php`, they must manually add `image_quality` to their `media` array. Document this in the release notes.

---

## Modifying an existing migration (column change)

**Never modify a migration that has been released.** Create a new migration instead.

### Example: making a nullable column required

```php
// database/migrations/2026_03_15_500009_make_excerpt_required_on_blog_posts.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // When modifying a column, include ALL attributes (nullable, default, etc.)
            $table->text('excerpt')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->change();
        });
    }
};
```

> **Laravel 12 rule:** when modifying a column, the migration must include all existing attributes. Omitted attributes are dropped.

---

## Versioning & releases

### Semantic versioning

```
MAJOR.MINOR.PATCH

1.0.0 → 1.1.0 → 1.1.1 → 2.0.0
```

| Type | When | Example |
|---|---|---|
| **PATCH** (1.0.X) | Bug fixes, security patches, typo fixes | Fix XSS in media URL |
| **MINOR** (1.X.0) | New features, new migrations, new models, new config keys — backward compatible | Add newsletter module |
| **MAJOR** (X.0.0) | Breaking changes: renamed config keys, removed features, changed DB schema incompatibly | Rename `cms-core.path` to `cms-core.admin_path` |

### Tagging a release

```bash
git tag -a v1.1.0 -m "v1.1.0 — Add newsletter module"
git push origin v1.1.0
```

### What constitutes a breaking change

- Removing or renaming a config key
- Removing or renaming a public method/class
- Changing a migration that has been released (instead of creating a new one)
- Removing a permission name
- Changing a model's table name
- Removing a route or changing its signature

---

## Release checklist

Before every release, run through this:

### Code quality

- [ ] `vendor/bin/pint --format agent` — code style passes
- [ ] `php artisan test --compact` — all tests pass
- [ ] No `dd()`, `dump()`, `ray()` left in code
- [ ] No hardcoded values that should be config

### Migrations

- [ ] New migrations have unique sequence numbers
- [ ] New migrations have `down()` methods
- [ ] `php artisan migrate:fresh` works from scratch
- [ ] `php artisan migrate:rollback` works for new migrations

### Permissions

- [ ] New permissions are created via migration
- [ ] New permissions are assigned to appropriate roles
- [ ] Resources have `canAccess`/`canCreate`/`canEdit`/`canDelete` gates

### Config

- [ ] New config keys have sensible defaults
- [ ] No `env()` calls outside config files
- [ ] Published config impact documented in release notes

### Registration

- [ ] New resources registered in `CmsCorePlugin.php`
- [ ] New policies registered in `CmsCoreServiceProvider::boot()`
- [ ] New commands registered in `CmsCoreServiceProvider::boot()`
- [ ] New config files merged in `CmsCoreServiceProvider::register()`

### Documentation

- [ ] `README.md` updated (this file)
- [ ] `CLAUDE.md` updated (agent context)
- [ ] CHANGELOG updated with version, date, and changes

### Deployment

- [ ] Git tag created (`v{MAJOR}.{MINOR}.{PATCH}`)
- [ ] Release notes written (see [Changelog format](#changelog-format))
- [ ] Host app update instructions documented

---

## Backward compatibility rules

1. **Config merging** — `mergeConfigFrom()` only merges first-level keys. Adding nested keys under an existing key requires documentation for hosts that published the config.

2. **Migrations** — never modify a released migration. Always create a new one. The package migrations auto-load, so they run in sequence based on filename.

3. **Permissions** — never rename or delete a permission. If a permission name must change, create the new one, migrate data, then deprecate the old one in a subsequent major release.

4. **Models** — adding properties or methods is safe. Removing or renaming is breaking.

5. **Helpers** — adding new helpers is safe (with `function_exists` guard). Changing signatures is breaking.

6. **Routes** — adding routes is safe. Changing method/path/name of existing routes is breaking.

---

## Changelog format

Use this format for each release:

```markdown
## [1.1.0] — 2026-03-15

### Added
- Newsletter module: model, resource, permissions, scheduled sending
- `cms-newsletter.php` config file
- `send blog newsletters` permission
- `blog:send-newsletters` Artisan command

### Changed
- Media upload now accepts WebP by default

### Fixed
- Tag filter SQL injection vulnerability

### Migration required
- `500009_add_newsletter_permissions` — creates newsletter permissions
- `500010_create_blog_newsletters_table` — creates newsletters table

### Config changes
- New file: `cms-newsletter.php` (auto-loaded, publish optional)
- New key: `cms-media.media.image_quality` (default: 85)
  - **If you published `cms-media.php`:** add `'image_quality' => 85` to the `media` array

### Host app actions
1. `composer update alexisgt01/cms-core`
2. `php artisan migrate`
3. Add `Schedule::command('blog:send-newsletters')->hourly()` to scheduler
```

---

# Appendix — Modules reference

## Administration

| Resource / Page | Features |
|---|---|
| Users | CRUD, first/last name, email, password, role assignment |
| Roles | CRUD, permission assignment, user count |
| Permissions | Read-only list with role display (super_admin only) |
| Activity Log | Read-only table of model changes (causer, event, old/new values). Filters by event type and model. Requires `view activity log` |
| Site Settings | Tabbed settings form (Identite, Contact, Acces restreint, SEO Global, Admin). Requires `manage site settings` |
| Profile | Self-edit (first/last name, email, password) |

## Media Library

- Hierarchical folders with drag & drop
- File upload with type/size validation (JPEG, PNG, GIF, WebP, PDF)
- Metadata editing (name, alt, description, tags)
- Search, filters (type, date range, tags)
- Bulk operations (delete, move, tag)
- Unsplash integration (search, download or use URL directly)
- imgproxy support (signed URLs, resize, format conversion)
- Infinite scroll

**MediaPicker** — reusable Filament form component with 4 sources: library, upload, URL import, Unsplash:

```php
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;

MediaPicker::make('featured_image')
    ->label('Image')
    ->acceptedTypes(['image/jpeg', 'image/png'])
    ->maxUploadSize(5120)
    ->showUnsplash(false);
```

The modal provides four tabs: Bibliothèque (existing media), Upload, URL (import from external URL), and Unsplash. URL imports download the file and store it in the media library. Each source displays a colored badge in the filled state.

**`media_url()`** — global helper:

```php
media_url('http://example.com/image.jpg');
media_url('http://example.com/image.jpg', ['width' => 800, 'height' => 600, 'format' => 'webp', 'quality' => 85]);
media_url($media->id, ['width' => 400]);
```

## Blog

**Settings** — single-row config page with tabs: General, RSS, Images, SEO (robots defaults + SERP preview), Open Graph (image fallback + dimensions + OG preview), Twitter (image fallback + dimensions + Twitter preview), Schema/JSON-LD (10 types multi-select, publisher info, organization URL, social profiles sameAs, JSON-LD validation).

**Authors** — display name, slug, email, job title, company, bio, social profiles, avatar, full SEO.

**Categories** — hierarchical (parent/child), position ordering, name, slug, description, full SEO.

**Tags** — flat taxonomy, many-to-many with posts, inline creation from post form, full SEO.

**Posts** — title, h1, subtitle, slug, excerpt, seo_excerpt, category, tags, content_seo_top, Tiptap content, content_seo_bottom, FAQ blocks, table_of_contents, featured images, author, state machine (Draft/Scheduled/Published), publication dates, reading time, full SEO (focus_keyword, secondary_keywords, indexing, canonical, robots, OG complet, Twitter complet, schema multi-types).

**SEO complet sur toutes les entites** — H1 independant, focus keyword, secondary keywords, contenu SEO haut/bas de page, robots (index/follow/noarchive/nosnippet/max-snippet/max-image-preview/max-video-preview), OG complet (type/locale/site_name/title/description/image/width/height), Twitter complet (card/site/creator/title/description/image), schema multi-types (10 types), JSON-LD personnalise.

**Previews integrees** — SERP Google (desktop/mobile avec compteurs), Facebook OG, Twitter Card.

**Validation publication** — Bloque si meta_title vide, bloque si h1+title vides, alerte meta_description manquante, detection duplicate meta_title/h1.

**Scheduled publishing:**

```bash
php artisan cms:publish-scheduled
```

## Dashboards

2 tableaux de bord dedies, chacun avec ses propres widgets et controle d'acces. Navigation groupee sous "Tableaux de bord".

### Blog Dashboard (`/admin/blog`)

Acces : `view blog posts`. Widgets :

- **BlogStatsOverview** — 6 stats cards en une seule requete SQL (conditional counts) : articles publies (+ 30 derniers jours), programmes, brouillons, categories, tags, auteurs
- **PostsPerMonthChart** — graphique ligne 12 mois des articles publies. Compatible SQLite/MySQL
- **LatestPostsTable** — 10 derniers articles avec eager loading (author, category), liens vers edition

### Admin Dashboard (`/admin/admin-overview`)

Acces : `view users`. Widgets :

- **AdminStatsOverview** — utilisateurs (avec distribution par role), roles, fichiers medias, dossiers
- **LatestUsersTable** — 10 derniers utilisateurs avec roles en badges, liens vers edition

Actions en header :
- **Tester l'email** — envoie un email de test a une adresse libre (pre-remplie avec l'email de l'utilisateur connecte). Utilise les parametres `from` de Site Settings si configures. Hint vers mail-tester.com pour tester le score spam.
- **Vider le cache** — vide tous les caches de l'application (config, routes, vues, events)

## Redirections

Gestion complete des redirections URL depuis le panel admin (groupe SEO).

- **301** (Permanent), **302** (Temporaire), **307** (Temporaire strict), **410** (Supprime/Gone)
- Tracking automatique : hit_count + last_hit_at
- Cache `rememberForever` invalide automatiquement a chaque modification
- Middleware global intercepte toutes les requetes avant le routing
- Table avec filtres (code HTTP, actif/inactif), tri, recherche, copie, toggle inline
- Permissions : view/create/edit/delete redirects (editor n'a pas delete)

## Sitemap

Generation de sitemap XML par crawl HTTP via `spatie/laravel-sitemap`.

- Commande `cms:sitemap` — crawl HTTP optimise avec concurrence configurable
- Configuration centralisee dans les parametres du blog (onglet Sitemap)
- Exclusion par patterns glob (`/admin/*`, `/api/*`, etc.)
- Frequence de changement et priorite par defaut configurables
- Options CLI : `--url`, `--output`, `--max-urls`, `--concurrency`, `--depth`

```bash
php artisan cms:sitemap
php artisan cms:sitemap --url=https://monsite.com --concurrency=20 --depth=5
```

## Site Settings

Parametres globaux du site stockes dans un modele singleton `SiteSetting` (table `site_settings`). Accessible via `SiteSetting::instance()`.

**7 onglets de configuration :**

- **Identite** — nom du site, baseline, logos (clair/sombre), favicon, fuseau horaire, formats date/heure, footer (copyright avec `%year%`/`%start_year%`, texte additionnel, annee de debut)
- **Contact** — telephone principal/secondaire, destinataires emails (JSON array), expediteur (nom + adresse), reply-to, URL Google Maps, horaires d'ouverture
- **Acces restreint** — activation, mot de passe (hashe), duree du cookie (TTL en minutes), message personnalise, bypass admin
- **SEO Global** — titre par defaut, meta description, template titre (`%title% · %site%`), image OG par defaut, directives robots, URL canonique de base
- **Mentions legales** — raison sociale, forme juridique (Select 11 formes), capital social, siege social (adresse/CP/ville/pays), immatriculation (SIRET/SIREN/TVA/RCS/APE), directeur de publication, hebergeur (nom/adresse/tel/email), DPO/RGPD (nom/email)
- **Reseaux sociaux** — Facebook, X (Twitter), Instagram, LinkedIn, YouTube, TikTok, Pinterest, GitHub, Threads, Snapchat
- **Admin** — affichage de la version Git dans le footer du panel

Toutes les images utilisent `MediaPicker` + `MediaSelectionCast`.

## Restricted Access

Protection par mot de passe de l'ensemble du site (hors panel admin).

- **Middleware** `HandleRestrictedAccess` — enregistre globalement, s'execute AVANT `HandleRedirects`
- **Logique** : desactive → passe / route admin → passe / admin_bypass + authentifie → passe / cookie `cms_restricted_access` valide → passe / sinon → affiche page de restriction
- **Route POST** `/cms/restricted-access` — valide le mot de passe via `Hash::check()`, pose un cookie avec TTL configurable
- **Vue** `resources/views/restricted-access.blade.php` — page standalone avec formulaire mot de passe et message personnalisable
- Configuration centralisee dans les parametres du site (onglet Acces restreint)

## Activity Log

Journalisation automatique des modifications sur les modeles CMS via `spatie/laravel-activitylog`.

**Modeles traces :** BlogPost, BlogCategory, BlogTag, BlogAuthor, Redirect. Chaque modele implemente `LogsActivity` avec `logOnly(['*'])`, `logOnlyDirty()`, `dontSubmitEmptyLogs()`.

> Pour `App\Models\User` : le trait doit etre ajoute manuellement par l'application hote (le package ne peut pas modifier ce modele).

**ActivityLogResource** — ressource Filament en lecture seule (groupe Administration) :

- Table : date, utilisateur (causer), type de modele (traduit), evenement (badge), description
- Filtres : type d'evenement (created/updated/deleted), type de modele (subject_type)
- ViewAction modale avec ancien/nouveau valeurs en JSON formatte
- Requiert la permission `view activity log`

**Purge** — commande Artisan pour nettoyer les anciennes entrees :

```bash
php artisan cms:purge-activity              # purge > 30 jours (defaut)
php artisan cms:purge-activity --days=90    # purge > 90 jours
```

## Git Version Footer

Affichage optionnel de la version Git (dernier tag) dans le footer du panel Filament.

- Active/desactive via `SiteSetting::show_version_in_footer` (onglet Admin des parametres du site)
- Lit `git describe --tags --abbrev=0` avec cache de 1 heure
- Rendu via `PanelsRenderHook::FOOTER` dans `CmsCorePlugin::boot()`

## Icon Picker

Composant Filament de recherche, preview et selection d'icones depuis plusieurs bibliotheques. Utilise `blade-ui-kit/blade-icons` comme backend de decouverte — tout jeu d'icones Blade installe est automatiquement detecte.

**Dependances optionnelles :**

| Package | Description | Version |
|---|---|---|
| `owenvoke/blade-fontawesome` | Icones Font Awesome | ^2.0 |
| `codeat3/blade-simple-icons` | Icones de marques (Netflix, Docker, Laravel, etc.) | ^5.0 |

Heroicons est toujours disponible (fourni avec Filament).

**IconPicker** — composant Filament reutilisable :

```php
use Alexisgt01\CmsCore\Filament\Forms\Components\IconPicker;

IconPicker::make('icon')
    ->label('Icone')
    ->outputMode('reference')        // ou 'svg' pour stocker le SVG inline
    ->allowedSets(['heroicons'])     // filtrer les jeux disponibles
    ->defaultSet('heroicons')
    ->required();
```

**Modes de sortie :**

- **reference** (defaut) — stocke le nom blade-icons (ex: `heroicon-o-home`)
- **svg** — stocke le balisage SVG complet inline

**Cast Eloquent** — `IconSelectionCast` pour la serialisation JSON automatique :

```php
use Alexisgt01\CmsCore\Casts\IconSelectionCast;

protected function casts(): array
{
    return [
        'icon' => IconSelectionCast::class,
    ];
}
```

**`cms_icon()`** — helper global de rendu :

```php
// Rendu depuis un nom blade-icons
cms_icon('heroicon-o-home', 'w-6 h-6');

// Rendu depuis un IconSelection
cms_icon($model->icon, 'w-6 h-6');
```

**Configuration** — publiez et personnalisez `cms-icons.php` :

| Cle | Description | Defaut |
|---|---|---|
| `sets` | Filtrer les jeux d'icones exposes (null = tous) | `null` |
| `default_mode` | Mode de sortie par defaut | `'reference'` |
| `per_page` | Icones par page de recherche | `60` |
| `cache_ttl` | Cache du manifeste en secondes | `3600` |
| `labels` | Labels lisibles pour les jeux connus | — |
| `variants` | Mappings de styles/variantes par jeu | — |

## Pages

Pages statiques du site avec hierarchie parent/enfant, SEO complet et SoftDeletes.

- **Modele `Page`** — name, slug (unique), key (identifiant front-end unique, ex: `about`, `contact`), meta (JSON), hierarchie (parent_id + position), is_home, SoftDeletes
- **States** — `PageDraft` (defaut) ↔ `PagePublished` (simplifie, 2 etats uniquement)
- **SEO complet** — meme pattern que BlogPost : h1, focus_keyword, secondary_keywords, indexing, canonical_url, meta_title, meta_description, robots (7 directives), OG complet, Twitter complet, schema multi-types + JSON-LD
- **Hierarchie** — parent/enfant avec position ordering, scopes `roots()` et `published()`
- **Helpers statiques** — `Page::findByKey('about')`, `Page::home()`, `Page::generateSlug('name')`
- **Formulaire avec onglets** — Page (nom, slug, cle, parent, position, accueil, meta, statut, date publication), SEO, Open Graph, Twitter, Schema
- **Permissions** — view/create/edit/delete/publish pages. Sans `publish pages`, seul "Brouillon" est disponible
- **SoftDeletes** — corbeille avec restauration et suppression definitive (TrashedFilter, RestoreAction, ForceDeleteAction)
- **Nav group** — Contenu
- **Migrations** — 900001 (table pages), 900002 (permissions)

## Sections

Systeme de blueprints de sections pour structurer le contenu des pages. Deux niveaux : definition du type (blueprint) et instances par page (valeurs).

### Architecture

```
SectionField (fluent builder)     → definit un champ declarativement
    ↓ toFormComponent()
SectionType (abstract class)      → blueprint avec key/label/icon/fields()
    ↓ toBlock()
SectionRegistry (singleton)       → collecte les types, produit les Builder\Block[]
    ↓ blocks()
PageResource (Filament Builder)   → onglet "Sections" avec Builder component
    ↓
Page.sections (JSON column)       → stocke [{type, data}, ...] par page
```

### Types de champs disponibles (SectionField)

| Type | Factory | Composant Filament |
|---|---|---|
| `text` | `SectionField::text('name')` | TextInput |
| `title` | `SectionField::title('heading')` | TextInput + Select H1-H6 |
| `paragraph` | `SectionField::paragraph('desc')` | Textarea |
| `richtext` | `SectionField::richtext('content')` | TiptapEditor |
| `icon` | `SectionField::icon('icon')` | IconPicker |
| `image` | `SectionField::image('hero')` | MediaPicker |
| `toggle` | `SectionField::toggle('show')` | Toggle |
| `select` | `SectionField::select('style')` | Select |
| `link` | `SectionField::link('cta')` | 2x TextInput (url + label) |
| `url` | `SectionField::url('cta')` | Select groupe (Pages CMS + Routes Laravel + Lien externe) + TextInput url + TextInput label |
| `list` | `SectionField::list('items')` | Repeater simple |
| `repeater` | `SectionField::repeater('cards')` | Repeater (sous-champs) |
| `number` | `SectionField::number('cols')` | TextInput numeric |
| `color` | `SectionField::color('bg')` | ColorPicker |

API fluente : `label()`, `required()`, `maxLength()`, `rows()`, `level()`, `placeholder()`, `maxItems()`, `min()`, `max()`, `default()`, `options()`, `helperText()`, `columnSpanFull()`, `fields()`.

### Creer un type de section (app hote)

```php
// app/Sections/CtaContactSection.php
use Alexisgt01\CmsCore\Sections\SectionField;
use Alexisgt01\CmsCore\Sections\SectionType;

class CtaContactSection extends SectionType
{
    public static function key(): string { return 'cta_contact'; }
    public static function label(): string { return 'CTA Contact'; }
    public static function icon(): string { return 'heroicon-o-phone'; }

    public static function fields(): array
    {
        return [
            SectionField::title('title')->label('Titre')->required()->level('h2'),
            SectionField::paragraph('paragraph')->label('Paragraphe'),
            SectionField::icon('icon')->label('Icone'),
            SectionField::repeater('bullets')->label('Points cles')->maxItems(6)->fields([
                SectionField::text('text')->label('Texte')->required(),
            ]),
        ];
    }
}
```

### Enregistrer les types

```php
// config/cms-sections.php
return [
    'types' => [
        \App\Sections\CtaContactSection::class,
    ],
];
```

- **Config** — `config/cms-sections.php` (publiable via `cms-core-config`)
- **Stockage** — colonne JSON `sections` sur la table `pages`, stocke `[{type, data}, ...]`
- **Onglet Sections** — automatiquement visible dans PageResource quand au moins un type est enregistre
- **Migration** — 900003 (ajout colonne sections)

## SEO Helper

Helper frontend pour injecter les balises SEO dans le `<head>`, avec resolution automatique des fallbacks.

### Usage

```blade
{{-- Par cle de page --}}
{!! seo_meta('service') !!}

{{-- Par model directement --}}
{!! seo_meta($blogPost) !!}

{{-- Defauts globaux (homepage) --}}
{!! seo_meta() !!}

{{-- Acces programmatique --}}
@php $seo = seo_meta('about'); @endphp
<title>{{ $seo->title }}</title>
```

### Chaine de fallback

| Donnee | Entite → | BlogSetting → | SiteSetting |
|---|---|---|---|
| Title | `meta_title` ou `name`/`title` dans `title_template` | — | `default_site_title` |
| Description | `meta_description` → `seo_excerpt` → `excerpt` → `description` → `bio` | — | `default_meta_description` |
| Canonical | `canonical_url` ou auto (`canonical_base_url` + slug) | — | `canonical_base_url` |
| Robots | `robots_*` directives | `default_robots_*` (blog entities) | `default_robots_index/follow` |
| OG | `og_*` champs | `og_site_name`, `og_type_default`, `og_locale`, `og_image_fallback` | `site_name`, `default_og_image` |
| Twitter | `twitter_*` champs | `twitter_card_default`, `twitter_site`, `twitter_creator`, `twitter_image_fallback` | — (fallback vers OG) |
| Schema | `schema_json` ou auto-genere depuis `schema_types` | `schema_*` settings | — |

### Rendu HTML

`seo_meta()` retourne un `SeoMeta` (Value Object) qui implemente `Stringable`. Le `toHtml()` genere :

- `<title>` — titre avec template applique
- `<meta name="description">` — si description presente
- `<link rel="canonical">` — si canonical resolu
- `<meta name="robots">` — directives combinees
- `<meta property="og:*">` — toutes les proprietes OG
- `<meta name="twitter:*">` — toutes les proprietes Twitter
- `<script type="application/ld+json">` — schema JSON-LD si present

---

## Collections

Systeme de contenu structure reutilisable. Permet de definir des types de contenu (services, temoignages, equipe...) avec des champs personnalises, stockes en base et gerables via Filament.

### Architecture

```
Host App (code)                    Package (code + DB)
CollectionType (abstract)  ←───── ServiceCollection (host app)
CollectionRegistry         ←───── config/cms-collections.php
CollectionEntry (Eloquent) ←───── table collection_entries
CollectionEntryResource    ←───── 1 resource, N items de navigation
```

### Definir un type de collection

Creer une classe dans le host app :

```php
namespace App\Collections;

use Alexisgt01\CmsCore\Collections\CollectionType;
use Alexisgt01\CmsCore\Sections\SectionField;

class ServiceCollection extends CollectionType
{
    public static function key(): string { return 'services'; }
    public static function label(): string { return 'Services'; }
    public static function singularLabel(): string { return 'Service'; }
    public static function icon(): string { return 'heroicon-o-briefcase'; }

    public static function fields(): array
    {
        return [
            SectionField::text('title')->label('Titre')->required(),
            SectionField::paragraph('description')->label('Description'),
            SectionField::image('image')->label('Image'),
        ];
    }

    public static function hasSlug(): bool { return true; }
    public static function hasStates(): bool { return true; }
    public static function sortable(): bool { return true; }
    // public static function hasSeo(): bool { return true; }
    // public static function maxEntries(): int { return 10; }
}
```

Enregistrer dans `config/cms-collections.php` :

```php
'types' => [
    \App\Collections\ServiceCollection::class,
],
```

### Options du CollectionType

| Methode | Defaut | Description |
|---------|--------|-------------|
| `hasSlug()` | `false` | Active la colonne slug (unique par type) |
| `hasSeo()` | `false` | Active les onglets SEO/OG/Twitter/Schema |
| `hasStates()` | `false` | Active Draft/Published avec `publish collection entries` |
| `sortable()` | `false` | Active le champ position et le tri |
| `maxEntries()` | `0` | Limite le nombre d'entrees (0 = illimite) |

### Helpers

```php
// Recuperer toutes les entrees (publiees si hasStates)
$services = collection_entries('services');

// Toutes les entrees sans filtre de publication
$all = collection_entries('services', publishedOnly: false);

// Entree par slug
$service = collection_entry('services', 'web-design');

// Acceder aux champs
$service->field('title');
$service->field('image');
$service->field('missing', 'default');

// SEO (si hasSeo active)
{!! seo_meta($service) !!}
```

### Navigation Filament

Chaque CollectionType enregistre genere automatiquement un item de navigation dans le groupe "Collections". Un seul resource Filament (`CollectionEntryResource`) gere tous les types via le query parameter `?collectionType=`.

### Permissions

| Permission | super_admin | editor |
|---|:---:|:---:|
| view collection entries | oui | oui |
| create collection entries | oui | oui |
| edit collection entries | oui | oui |
| delete collection entries | oui | — |
| publish collection entries | oui | oui |

---

# Appendix — Artisan commands

| Command | Description |
|---|---|
| `cms:make-admin` | Cree un utilisateur admin avec le role `super_admin`. Options : `--first-name`, `--last-name`, `--email`, `--password` |
| `cms:publish-scheduled` | Publie les articles planifies dont la date `scheduled_for` est passee. A executer via le scheduler (hourly) |
| `cms:sitemap` | Genere un sitemap.xml par crawl HTTP du site. Options : `--url`, `--output`, `--max-urls`, `--concurrency`, `--depth` |
| `cms:purge-activity` | Purge les entrees du journal d'activite plus anciennes que N jours. Option : `--days=30` (defaut) |

---

# Appendix — Permissions reference

## Roles

| Role | Description |
|---|---|
| `super_admin` | All permissions |
| `editor` | Content management (no delete, no settings) |
| `viewer` | Read-only access |

## Permission matrix

| Permission | super_admin | editor | viewer |
|---|:---:|:---:|:---:|
| view/create/edit/delete users | all | view/create/edit | view |
| view/create/edit/delete roles | all | view | view |
| edit/delete profile | all | edit | — |
| view/create/edit/delete media | all | view/create | — |
| manage blog settings | yes | — | — |
| view/create/edit/delete blog authors | all | view/create/edit | — |
| view/create/edit/delete blog posts | all | view/create/edit | — |
| publish blog posts | yes | yes | — |
| view/create/edit/delete blog categories | all | view/create/edit | — |
| view/create/edit/delete blog tags | all | view/create/edit | — |
| view/create/edit/delete pages | all | view/create/edit | — |
| publish pages | yes | yes | — |
| view/create/edit/delete redirects | all | view/create/edit | — |
| view/create/edit/delete collection entries | all | view/create/edit | — |
| publish collection entries | yes | yes | — |
| manage site settings | yes | — | — |
| view activity log | yes | yes | — |

Without `publish blog posts` permission, only "Brouillon" (Draft) state is available.

---

# Appendix — Package structure

```
packages/cms/core/
├── config/
│   ├── cms-core.php              # Admin panel path
│   ├── cms-media.php             # imgproxy, Unsplash, upload settings
│   ├── cms-icons.php             # Icon sets, output mode, pagination
│   ├── cms-sections.php          # Section types registration
│   └── cms-collections.php      # Collection types registration
├── database/migrations/
│   ├── 200001_add_name_fields_to_users_table
│   ├── 200002_create_default_roles_and_permissions
│   ├── 300001_create_cms_media_tables
│   ├── 300002_add_media_permissions
│   ├── 500000_create_blog_authors_table
│   ├── 500001_create_blog_settings_table
│   ├── 500002_create_blog_posts_table
│   ├── 500003_add_blog_permissions
│   ├── 500004_create_blog_categories_table
│   ├── 500005_add_category_id_to_blog_posts
│   ├── 500006_create_blog_tags_table
│   ├── 500007_create_blog_post_tag_pivot
│   ├── 500008_add_tag_category_permissions
│   ├── 600001_add_seo_enhancements_to_blog_posts
│   ├── 600002_add_seo_enhancements_to_blog_authors
│   ├── 600003_add_seo_enhancements_to_blog_categories
│   ├── 600004_add_seo_enhancements_to_blog_tags
│   ├── 600005_add_seo_defaults_to_blog_settings
│   ├── 600006_add_seo_enhancements_to_blog_settings
│   ├── 700001_create_redirects_table
│   ├── 700002_add_redirect_permissions
│   ├── 700003_add_sitemap_settings_to_blog_settings
│   ├── 800001_create_site_settings_table
│   ├── 800002_add_site_settings_and_activity_log_permissions
│   ├── 800003_add_legal_social_columns_to_site_settings
│   ├── 800004_add_footer_and_opening_hours_to_site_settings
│   ├── 900001_create_pages_table
│   ├── 900002_add_page_permissions
│   ├── 900003_add_sections_to_pages
│   ├── 1000001_create_collection_entries_table
│   └── 1000002_add_collection_entry_permissions
├── resources/views/filament/
│   ├── forms/components/
│   │   ├── media-picker.blade.php
│   │   ├── serp-preview.blade.php
│   │   ├── serp-preview-settings.blade.php
│   │   ├── og-preview.blade.php
│   │   ├── og-preview-settings.blade.php
│   │   ├── twitter-preview.blade.php
│   │   └── twitter-preview-settings.blade.php
│   └── pages/
│       ├── blog-settings.blade.php
│       ├── media-library.blade.php
│       ├── media-library-preview.blade.php
│       └── site-settings.blade.php
├── resources/views/
│   └── restricted-access.blade.php
└── src/
    ├── helpers.php                # media_url(), seo_meta(), collection_entries(), collection_entry(), cms_icon()
    ├── CmsCoreServiceProvider.php # Registers everything
    ├── Http/Middleware/
    │   ├── HandleRestrictedAccess.php  # Global restricted access middleware
    │   └── HandleRedirects.php         # Global redirect middleware
    ├── Casts/
    │   └── MediaSelectionCast.php
    ├── Console/Commands/
    │   ├── GenerateSitemap.php
    │   ├── MakeAdminCommand.php
    │   ├── PublishScheduledPosts.php
    │   └── PurgeActivityLog.php
    ├── Filament/
    │   ├── Actions/
    │   │   └── CmsMediaAction.php
    │   ├── CmsCorePlugin.php      # Registers resources & pages
    │   ├── Concerns/
    │   │   └── HasSeoFields.php     # Shared SEO form fields trait
    │   ├── Forms/Components/
    │   │   ├── MediaPicker.php
    │   │   ├── SerpPreview.php      # Google SERP preview
    │   │   ├── OgPreview.php        # Facebook OG preview
    │   │   └── TwitterPreview.php   # Twitter Card preview
    │   ├── Pages/
    │   │   ├── AdminDashboard.php  # Admin dashboard
    │   │   ├── BlogDashboard.php   # Blog dashboard
    │   │   ├── BlogSettings.php
    │   │   ├── EditProfile.php
    │   │   ├── MediaLibrary.php
    │   │   ├── SiteSettings.php    # Site-wide settings
    │   ├── Widgets/
    │   │   ├── AdminStatsOverview.php    # Admin: users, roles, media, folders
    │   │   ├── BlogStatsOverview.php     # Blog: posts by state, categories, tags, authors
    │   │   ├── LatestPostsTable.php      # Blog: 10 latest posts
    │   │   ├── LatestUsersTable.php      # Admin: 10 latest users
    │   │   ├── PostsPerMonthChart.php    # Blog: line chart (12 months)
    │   └── Resources/
    │       ├── ActivityLogResource.php  (+Pages/)
    │       ├── BlogAuthorResource.php   (+Pages/)
    │       ├── BlogCategoryResource.php (+Pages/)
    │       ├── BlogPostResource.php     (+Pages/)
    │       ├── BlogTagResource.php      (+Pages/)
    │       ├── CollectionEntryResource.php (+Pages/)
    │       ├── PageResource.php         (+Pages/)
    │       ├── PermissionResource.php
    │       ├── RedirectResource.php    (+Pages/)
    │       ├── RoleResource.php         (+Pages/)
    │       └── UserResource.php         (+Pages/)
    ├── Models/
    │   ├── BlogAuthor.php         # LogsActivity
    │   ├── BlogCategory.php       # LogsActivity
    │   ├── BlogPost.php           # LogsActivity
    │   ├── BlogSetting.php
    │   ├── BlogTag.php              # LogsActivity
    │   ├── CollectionEntry.php  # LogsActivity, SoftDeletes, HasStates
    │   ├── Page.php              # LogsActivity, SoftDeletes, HasStates
    │   ├── CmsMedia.php
    │   ├── CmsMediaFolder.php
    │   ├── Redirect.php               # LogsActivity
    │   ├── SiteSetting.php            # Singleton (site-wide settings)
    │   └── States/
    │       ├── PostState.php      # Abstract base
    │       ├── Draft.php
    │       ├── Scheduled.php
    │       ├── Published.php
    │       ├── PageState.php    # Abstract base (Draft ↔ Published)
    │       ├── PageDraft.php
    │       ├── PagePublished.php
    │       ├── EntryState.php   # Abstract base (Draft ↔ Published)
    │       ├── EntryDraft.php
    │       └── EntryPublished.php
    ├── Policies/
    │   ├── CmsMediaPolicy.php
    │   ├── CollectionEntryPolicy.php
    │   ├── PagePolicy.php
    │   ├── PermissionPolicy.php
    │   ├── RolePolicy.php
    │   └── UserPolicy.php
    ├── Collections/
    │   ├── CollectionType.php       # Abstract blueprint class
    │   └── CollectionRegistry.php   # Singleton registry service
    ├── Sections/
    │   ├── SectionField.php         # Fluent builder (15 field types)
    │   ├── SectionType.php          # Abstract blueprint class
    │   └── SectionRegistry.php      # Singleton registry service
    ├── Services/
    │   ├── MediaService.php
    │   ├── SeoResolver.php          # SEO fallback resolution engine
    │   └── UnsplashClient.php
    └── ValueObjects/
        ├── MediaSelection.php
        └── SeoMeta.php              # SEO data VO with toHtml()
```

---

# Appendix — Test files

Test files live in the host app at `tests/Feature/Filament/`:

| File | Coverage |
|---|---|
| `MediaLibraryTest.php` | Media CRUD, folders, bulk operations, filters |
| `MediaPickerTest.php` | MediaSelection, MediaSelectionCast, media_url(), UnsplashClient |
| `BlogTest.php` | BlogSetting, BlogAuthor, BlogCategory, BlogTag, BlogPost, states/transitions, publish-scheduled command, permissions |
| `BlogSeoTest.php` | SEO columns, model casts, content storage, publication validation, duplicate detection, settings fallback dimensions, schema |
| `RedirectTest.php` | Columns, permissions (super_admin/editor/viewer), CRUD, casts, recordHit, scopeActive, middleware, cache invalidation |
| `SitemapTest.php` | Sitemap settings columns, casts, default values, command registration |
| `SiteSettingsTest.php` | SiteSetting model (columns, singleton, casts, defaults), SiteSettings page (access, save, password hashing), restricted access middleware (enabled/disabled, admin bypass, cookie, password validation), activity log (logging on all CMS models, resource access, purge command), permissions |
| `IconPickerTest.php` | IconSelection VO, IconSelectionCast, IconDiscoveryService, IconPicker component, cms_icon() helper, API routes |
| `PageTest.php` | Pages table columns, permissions (super_admin/editor/viewer), model CRUD, SoftDeletes (delete/restore), states (Draft ↔ Published), scopes (roots/published), static helpers (findByKey/home/generateSlug), casts, Filament resource (list/create/edit, form submission, viewer denied) |
| `SectionTest.php` | SectionField factories (15 types incl. url smart link), fluent API, toFormComponent (each type), toDefinition, SectionType contract (schema, toBlock, toDefinition), SectionRegistry (register/resolve, blocks, definitions, invalid class rejection), config, Filament integration (form render, save via Builder::fake(), hidden tab) |
| `SeoMetaTest.php` | SeoMeta VO (properties, toArray, toHtml, Stringable, HTML escaping), SeoResolver (global defaults, page by key, non-existent key, BlogPost/Category/Tag/Author direct, title/description/canonical fallbacks, robots inheritance SiteSetting/BlogSetting, OG/Twitter fallbacks, schema JSON-LD), seo_meta() helper (key, model, no args, Blade usage) |
| `CollectionTest.php` | CollectionType contract (methods, schema, definition), CollectionRegistry (register/resolve, definitions, invalid class rejection), migration columns, permissions (super_admin/editor/viewer), model CRUD, field accessor, scopes (forType/ordered/published), SoftDeletes, slug generation, state machine (Draft ↔ Published), casts, config, Filament resource access, dynamic navigation items, collection_entries()/collection_entry() helpers, SeoResolver with CollectionEntry |
