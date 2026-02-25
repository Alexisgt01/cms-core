<?php

namespace Alexisgt01\CmsCore;

use App\Models\User;
use Filament\Panel;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
use Alexisgt01\CmsCore\Console\Commands\MakeAdminCommand;
use Alexisgt01\CmsCore\Console\Commands\GenerateSitemap;
use Alexisgt01\CmsCore\Console\Commands\PurgeActivityLog;
use Alexisgt01\CmsCore\Console\Commands\PublishScheduledPosts;
use Alexisgt01\CmsCore\Filament\Actions\CmsMediaAction;
use Alexisgt01\CmsCore\Filament\Widgets\AdminStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\BlogStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestPostsTable;
use Alexisgt01\CmsCore\Filament\Widgets\LatestUsersTable;
use Alexisgt01\CmsCore\Filament\Widgets\PostsPerMonthChart;
use Alexisgt01\CmsCore\Http\Middleware\HandleRedirects;
use Alexisgt01\CmsCore\Http\Middleware\HandleRestrictedAccess;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Alexisgt01\CmsCore\Services\UnsplashClient;
use Livewire\Livewire;

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
            MakeAdminCommand::class,
            PublishScheduledPosts::class,
            GenerateSitemap::class,
            PurgeActivityLog::class,
        ]);

        $this->app[\Illuminate\Contracts\Http\Kernel::class]->appendMiddlewareToGroup('web', HandleRestrictedAccess::class);
        $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(HandleRedirects::class);

        $this->registerLivewireWidgets();
        $this->registerRoutes();
        $this->registerAuthListeners();
    }

    protected function registerLivewireWidgets(): void
    {
        $widgets = [
            AdminStatsOverview::class,
            BlogStatsOverview::class,
            LatestPostsTable::class,
            LatestUsersTable::class,
            PostsPerMonthChart::class,
        ];

        foreach ($widgets as $widget) {
            $name = collect(explode('\\', $widget))
                ->map(fn (string $part) => \Illuminate\Support\Str::kebab($part))
                ->implode('.');

            Livewire::component($name, $widget);
        }
    }

    protected function registerAuthListeners(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            activity()
                ->causedBy($event->user)
                ->event('login')
                ->log('Connexion');
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user) {
                activity()
                    ->causedBy($event->user)
                    ->event('logout')
                    ->log('Deconnexion');
            }
        });
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

        Route::middleware('web')
            ->post('cms/restricted-access', function (Request $request) {
                $settings = SiteSetting::instance();

                if (! $settings->restricted_access_password) {
                    return redirect('/');
                }

                if (! \Illuminate\Support\Facades\Hash::check($request->input('password', ''), $settings->restricted_access_password)) {
                    return back()->withErrors(['password' => 'Mot de passe incorrect.']);
                }

                $ttl = $settings->restricted_access_cookie_ttl ?? 1440;

                return redirect('/')
                    ->withCookie(cookie('cms_restricted_access', 'granted', $ttl));
            })->name('cms.restricted-access.validate');
    }
}
