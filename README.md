# CMS Core

Headless CMS core package with Filament admin panel — user management, media library, blog engine.

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
- [Appendix — Permissions reference](#appendix--permissions-reference)
- [Appendix — Package structure](#appendix--package-structure)

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
- The `blog:publish-scheduled` Artisan command becomes available
- The Tiptap editor media action is overridden to use the CMS media library
- An API route (`GET /{admin-path}/cms/api/unsplash/search`) is registered

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
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // or add your own logic
    }
}
```

> The package migration `200001_add_name_fields_to_users_table` adds `first_name` and `last_name` columns. Make sure they are in `$fillable`.

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

The package ships **13 migrations** that run in sequence:

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

**Sequence convention:** `200xxx` = admin/users, `300xxx` = media, `500xxx` = blog.

---

## Step 10 — Create an admin user

```bash
php artisan make:filament-user
```

Then assign the `super_admin` role to the user. Use tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'your@email.com')->first();
$user->assignRole('super_admin');
```

---

## Step 11 — Set up the scheduler

The blog uses scheduled publishing. Add the command to your scheduler.

In `routes/console.php` or your scheduling configuration:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('blog:publish-scheduled')->hourly();
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

- [ ] `php artisan migrate:status` — all 13 CMS migrations show "Ran"
- [ ] `php artisan route:list --name=cms` — the `cms.unsplash.search` route exists
- [ ] Navigate to `/admin` — login page appears
- [ ] Log in with super_admin user — sidebar shows: Administration (Users, Roles, Permissions), Medias (Mediatheque), Blog (Articles, Auteurs, Categories, Tags, Parametres)
- [ ] Create a media folder and upload an image — file appears in `storage/app/public/`
- [ ] Create a blog author — slug auto-generates
- [ ] Create a blog category with a child category — hierarchy works
- [ ] Create a blog tag
- [ ] Create a blog post — select category, tags, upload featured image, use Tiptap editor
- [ ] Publish the post — state changes from Draft to Published
- [ ] `php artisan blog:publish-scheduled` — command runs without error
- [ ] Blog Settings page loads — General, RSS, Images, SEO, OG, Twitter, Schema tabs

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
   - `600xxx` — Future modules (reserve ranges)

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

| Resource | Features |
|---|---|
| Users | CRUD, first/last name, email, password, role assignment |
| Roles | CRUD, permission assignment, user count |
| Permissions | Read-only list with role display (super_admin only) |
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

**MediaPicker** — reusable Filament form component:

```php
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;

MediaPicker::make('featured_image')
    ->label('Image')
    ->acceptedTypes(['image/jpeg', 'image/png'])
    ->maxUploadSize(5120)
    ->showUnsplash(false);
```

**`media_url()`** — global helper:

```php
media_url('http://example.com/image.jpg');
media_url('http://example.com/image.jpg', ['width' => 800, 'height' => 600, 'format' => 'webp', 'quality' => 85]);
media_url($media->id, ['width' => 400]);
```

## Blog

**Settings** — single-row config page with tabs: General, RSS, Images, SEO, Open Graph, Twitter, Schema/JSON-LD.

**Authors** — display name, slug, email, job title, company, bio, social profiles, avatar, full SEO.

**Categories** — hierarchical (parent/child), position ordering, name, slug, description, full SEO.

**Tags** — flat taxonomy, many-to-many with posts, inline creation from post form, full SEO.

**Posts** — title, slug, excerpt, category, tags, Tiptap content, featured images, author, state machine (Draft/Scheduled/Published), publication dates, reading time, full SEO.

**Scheduled publishing:**

```bash
php artisan blog:publish-scheduled
```

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

Without `publish blog posts` permission, only "Brouillon" (Draft) state is available.

---

# Appendix — Package structure

```
packages/cms/core/
├── config/
│   ├── cms-core.php              # Admin panel path
│   └── cms-media.php             # imgproxy, Unsplash, upload settings
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
│   └── 500008_add_tag_category_permissions
├── resources/views/filament/
│   ├── forms/components/
│   │   └── media-picker.blade.php
│   └── pages/
│       ├── blog-settings.blade.php
│       ├── media-library.blade.php
│       └── media-library-preview.blade.php
└── src/
    ├── helpers.php                # media_url() global helper
    ├── CmsCoreServiceProvider.php # Registers everything
    ├── Casts/
    │   └── MediaSelectionCast.php
    ├── Console/Commands/
    │   └── PublishScheduledPosts.php
    ├── Filament/
    │   ├── Actions/
    │   │   └── CmsMediaAction.php
    │   ├── CmsCorePlugin.php      # Registers resources & pages
    │   ├── Forms/Components/
    │   │   └── MediaPicker.php
    │   ├── Pages/
    │   │   ├── BlogSettings.php
    │   │   ├── EditProfile.php
    │   │   └── MediaLibrary.php
    │   └── Resources/
    │       ├── BlogAuthorResource.php   (+Pages/)
    │       ├── BlogCategoryResource.php (+Pages/)
    │       ├── BlogPostResource.php     (+Pages/)
    │       ├── BlogTagResource.php      (+Pages/)
    │       ├── PermissionResource.php
    │       ├── RoleResource.php         (+Pages/)
    │       └── UserResource.php         (+Pages/)
    ├── Models/
    │   ├── BlogAuthor.php
    │   ├── BlogCategory.php
    │   ├── BlogPost.php
    │   ├── BlogSetting.php
    │   ├── BlogTag.php
    │   ├── CmsMedia.php
    │   ├── CmsMediaFolder.php
    │   └── States/
    │       ├── PostState.php      # Abstract base
    │       ├── Draft.php
    │       ├── Scheduled.php
    │       └── Published.php
    ├── Policies/
    │   ├── CmsMediaPolicy.php
    │   ├── PermissionPolicy.php
    │   ├── RolePolicy.php
    │   └── UserPolicy.php
    ├── Services/
    │   ├── MediaService.php
    │   └── UnsplashClient.php
    └── ValueObjects/
        └── MediaSelection.php
```
