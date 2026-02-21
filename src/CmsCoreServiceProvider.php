<?php

namespace Alexisgt01\CmsCore;

use App\Models\User;
use Filament\Panel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Alexisgt01\CmsCore\Filament\CmsCorePlugin;
use Alexisgt01\CmsCore\Models\CmsMedia;
use Alexisgt01\CmsCore\Policies\CmsMediaPolicy;
use Alexisgt01\CmsCore\Policies\PermissionPolicy;
use Alexisgt01\CmsCore\Policies\RolePolicy;
use Alexisgt01\CmsCore\Policies\UserPolicy;
use Alexisgt01\CmsCore\Console\Commands\PublishScheduledPosts;
use Alexisgt01\CmsCore\Filament\Actions\CmsMediaAction;
use Alexisgt01\CmsCore\Services\UnsplashClient;

class CmsCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-core.php', 'cms-core');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-media.php', 'cms-media');

        $this->app['config']->set(
            'filament-tiptap-editor.media_action',
            CmsMediaAction::class,
        );

        Panel::configureUsing(function (Panel $panel): void {
            if ($panel->getId() !== 'admin') {
                return;
            }

            $panel->plugin(CmsCorePlugin::make());
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms-core');

        $this->publishes([
            __DIR__ . '/../config/cms-core.php' => config_path('cms-core.php'),
            __DIR__ . '/../config/cms-media.php' => config_path('cms-media.php'),
        ], 'cms-core-config');

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(CmsMedia::class, CmsMediaPolicy::class);

        $this->commands([
            PublishScheduledPosts::class,
        ]);

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix(config('cms-core.path', 'admin') . '/cms/api')
            ->group(function (): void {
                Route::get('unsplash/search', function (Request $request) {
                    if (! $request->user()?->can('create media')) {
                        abort(403);
                    }

                    if (! config('cms-media.unsplash.enabled')) {
                        return response()->json(['results' => [], 'total' => 0, 'total_pages' => 0]);
                    }

                    return response()->json(
                        app(UnsplashClient::class)->search(
                            (string) $request->query('query', ''),
                            (int) $request->query('page', 1),
                        )
                    );
                })->name('cms.unsplash.search');
            });
    }
}
