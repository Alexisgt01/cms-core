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
use Alexisgt01\CmsCore\Policies\CollectionEntryPolicy;
use Alexisgt01\CmsCore\Policies\ContactPolicy;
use Alexisgt01\CmsCore\Policies\ContactRequestPolicy;
use Alexisgt01\CmsCore\Policies\HookDeliveryPolicy;
use Alexisgt01\CmsCore\Policies\HookEndpointPolicy;
use Alexisgt01\CmsCore\Policies\PermissionPolicy;
use Alexisgt01\CmsCore\Policies\RolePolicy;
use Alexisgt01\CmsCore\Policies\PagePolicy;
use Alexisgt01\CmsCore\Policies\UserPolicy;
use Alexisgt01\CmsCore\Console\Commands\MakeAdminCommand;
use Alexisgt01\CmsCore\Console\Commands\GenerateSitemap;
use Alexisgt01\CmsCore\Console\Commands\PurgeActivityLog;
use Alexisgt01\CmsCore\Console\Commands\PurgeContactDataCommand;
use Alexisgt01\CmsCore\Console\Commands\PublishScheduledPosts;
use Alexisgt01\CmsCore\Console\Commands\RetryContactHooksCommand;
use Alexisgt01\CmsCore\Filament\Actions\CmsMediaAction;
use Alexisgt01\CmsCore\Filament\Widgets\AdminStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\BlogStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestPostsTable;
use Alexisgt01\CmsCore\Filament\Widgets\LatestUsersTable;
use Alexisgt01\CmsCore\Filament\Widgets\PostsPerMonthChart;
use Alexisgt01\CmsCore\Http\Middleware\HandleRedirects;
use Alexisgt01\CmsCore\Http\Middleware\HandleRestrictedAccess;
use Alexisgt01\CmsCore\Models\CollectionEntry;
use Alexisgt01\CmsCore\Models\Contact;
use Alexisgt01\CmsCore\Models\ContactRequest;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Alexisgt01\CmsCore\Models\HookEndpoint;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Alexisgt01\CmsCore\Services\ContactPipeline;
use Alexisgt01\CmsCore\Services\IconDiscoveryService;
use Alexisgt01\CmsCore\Services\UnsplashClient;
use Livewire\Livewire;

class CmsCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-core.php', 'cms-core');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-media.php', 'cms-media');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-icons.php', 'cms-icons');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-sections.php', 'cms-sections');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-collections.php', 'cms-collections');
        $this->mergeConfigFrom(__DIR__ . '/../config/cms-contacts.php', 'cms-contacts');

        $this->app->singleton(ContactPipeline::class);

        $this->app['config']->set(
            'filament-tiptap-editor.media_action',
            CmsMediaAction::class,
        );

        $this->app->singleton(Sections\SectionRegistry::class, function () {
            $registry = new Sections\SectionRegistry;

            foreach (config('cms-sections.types', []) as $typeClass) {
                $registry->register($typeClass);
            }

            return $registry;
        });

        $this->app->singleton(Collections\CollectionRegistry::class, function () {
            $registry = new Collections\CollectionRegistry;

            foreach (config('cms-collections.types', []) as $typeClass) {
                $registry->register($typeClass);
            }

            return $registry;
        });

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
            __DIR__ . '/../config/cms-icons.php' => config_path('cms-icons.php'),
            __DIR__ . '/../config/cms-sections.php' => config_path('cms-sections.php'),
            __DIR__ . '/../config/cms-collections.php' => config_path('cms-collections.php'),
            __DIR__ . '/../config/cms-contacts.php' => config_path('cms-contacts.php'),
        ], 'cms-core-config');

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(CollectionEntry::class, CollectionEntryPolicy::class);
        Gate::policy(CmsMedia::class, CmsMediaPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(ContactRequest::class, ContactRequestPolicy::class);
        Gate::policy(HookEndpoint::class, HookEndpointPolicy::class);
        Gate::policy(HookDelivery::class, HookDeliveryPolicy::class);

        $this->commands([
            MakeAdminCommand::class,
            PublishScheduledPosts::class,
            GenerateSitemap::class,
            PurgeActivityLog::class,
            RetryContactHooksCommand::class,
            PurgeContactDataCommand::class,
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

                Route::get('icons/search', function (Request $request) {
                    if (! $request->user()) {
                        abort(403);
                    }

                    return response()->json(
                        app(IconDiscoveryService::class)->searchIcons(
                            query: (string) $request->query('q', ''),
                            set: (string) $request->query('set', ''),
                            variant: (string) $request->query('variant', ''),
                            page: (int) $request->query('page', 1),
                            perPage: (int) config('cms-icons.per_page', 60),
                        )
                    );
                })->name('cms.icons.search');

                Route::get('icons/sets', function (Request $request) {
                    if (! $request->user()) {
                        abort(403);
                    }

                    return response()->json(
                        app(IconDiscoveryService::class)->getAvailableSets()
                    );
                })->name('cms.icons.sets');
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
