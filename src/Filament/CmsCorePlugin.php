<?php

namespace Alexisgt01\CmsCore\Filament;

use Alexisgt01\CmsCore\Filament\Pages\AdminDashboard;
use Alexisgt01\CmsCore\Filament\Pages\BlogDashboard;
use Alexisgt01\CmsCore\Filament\Pages\BlogSettings;
use Alexisgt01\CmsCore\Filament\Pages\ContactSettings;
use Alexisgt01\CmsCore\Filament\Pages\Documentation;
use Alexisgt01\CmsCore\Filament\Pages\EditProfile;
use Alexisgt01\CmsCore\Filament\Pages\MediaLibrary;
use Alexisgt01\CmsCore\Filament\Pages\Releases;
use Alexisgt01\CmsCore\Filament\Pages\SectionCatalog;
use Alexisgt01\CmsCore\Filament\Pages\SiteSettings;
use Alexisgt01\CmsCore\Filament\Resources\ActivityLogResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource;
use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource;
use Alexisgt01\CmsCore\Filament\Resources\ContactRequestResource;
use Alexisgt01\CmsCore\Filament\Resources\ContactResource;
use Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource;
use Alexisgt01\CmsCore\Filament\Resources\HookDeliveryResource;
use Alexisgt01\CmsCore\Filament\Resources\HookEndpointResource;
use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Filament\Resources\PermissionResource;
use Alexisgt01\CmsCore\Filament\Resources\RedirectResource;
use Alexisgt01\CmsCore\Filament\Resources\RoleResource;
use Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource;
use Alexisgt01\CmsCore\Filament\Resources\UserResource;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

class CmsCorePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'cms-core';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources($this->resolveResources())
            ->pages($this->resolvePages())
            ->profile(EditProfile::class)
            ->userMenuItems([
                'documentation' => MenuItem::make()
                    ->label('Guide d\'utilisation')
                    ->url(fn (): string => Documentation::getUrl())
                    ->icon('heroicon-o-book-open'),
                'releases' => MenuItem::make()
                    ->label('Nouveautes')
                    ->url(fn (): string => Releases::getUrl())
                    ->icon('heroicon-o-sparkles'),
            ]);
    }

    /**
     * @return array<class-string>
     */
    protected function resolveResources(): array
    {
        $resources = [];

        // Administration (always on: users, roles, site settings)
        $resources[] = UserResource::class;
        $resources[] = RoleResource::class;

        if (cms_feature('administration_permissions')) {
            $resources[] = PermissionResource::class;
        }
        if (cms_feature('administration_activity_log')) {
            $resources[] = ActivityLogResource::class;
        }

        // Blog (mandatory: posts; optional: authors, categories, tags)
        if (cms_feature('blog')) {
            $resources[] = BlogPostResource::class;

            if (cms_feature('blog_authors')) {
                $resources[] = BlogAuthorResource::class;
            }
            if (cms_feature('blog_categories')) {
                $resources[] = BlogCategoryResource::class;
            }
            if (cms_feature('blog_tags')) {
                $resources[] = BlogTagResource::class;
            }
        }

        // Pages (mandatory: pages resource; optional: templates)
        if (cms_feature('pages')) {
            $resources[] = PageResource::class;

            if (cms_feature('pages_templates')) {
                $resources[] = SectionTemplateResource::class;
            }

            if (cms_feature('pages_global_sections')) {
                $resources[] = GlobalSectionResource::class;
            }
        }

        // SEO (mandatory: redirections when seo on)
        if (cms_feature('seo')) {
            $resources[] = RedirectResource::class;
        }

        // Collections
        if (cms_feature('collections')) {
            $resources[] = CollectionEntryResource::class;
        }

        // Contact (mandatory: contacts, requests; optional: webhooks, deliveries)
        if (cms_feature('contact')) {
            $resources[] = ContactResource::class;
            $resources[] = ContactRequestResource::class;

            if (cms_feature('contact_webhooks')) {
                $resources[] = HookEndpointResource::class;
            }
            if (cms_feature('contact_deliveries')) {
                $resources[] = HookDeliveryResource::class;
            }
        }

        return $resources;
    }

    /**
     * @return array<class-string>
     */
    protected function resolvePages(): array
    {
        $pages = [];

        // Dashboards
        if (cms_feature('dashboards_blog')) {
            $pages[] = BlogDashboard::class;
        }
        if (cms_feature('dashboards_admin')) {
            $pages[] = AdminDashboard::class;
        }

        // Media
        if (cms_feature('media')) {
            $pages[] = MediaLibrary::class;
        }

        // Blog settings (mandatory when blog is on)
        if (cms_feature('blog')) {
            $pages[] = BlogSettings::class;
        }

        // Site settings (always on)
        $pages[] = SiteSettings::class;

        // Contact settings (mandatory when contact is on)
        if (cms_feature('contact')) {
            $pages[] = ContactSettings::class;
        }

        // Sections catalog
        if (cms_feature('pages_sections')) {
            $pages[] = SectionCatalog::class;
        }

        // Always registered (user menu, not sidebar)
        $pages[] = Documentation::class;
        $pages[] = Releases::class;

        return $pages;
    }

    public function boot(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => $this->renderVersionFooter(),
        );

        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => auth()->check()
                ? Blade::render('<livewire:cms-release-popup />')
                : '',
        );

        $panel->renderHook(
            PanelsRenderHook::SIDEBAR_FOOTER,
            fn (): string => auth()->user()?->can('manage site settings')
                ? Blade::render('<livewire:cms-navigation-customizer />')
                : '',
        );
    }

    protected function renderVersionFooter(): string
    {
        try {
            $settings = SiteSetting::instance();
        } catch (\Throwable) {
            return '';
        }

        if (! $settings->show_version_in_footer) {
            return '';
        }

        $version = Cache::remember('cms_git_version', 3600, function (): ?string {
            $tag = trim((string) @shell_exec('git describe --tags --abbrev=0 2>/dev/null'));

            return $tag !== '' ? $tag : null;
        });

        if (! $version) {
            return '';
        }

        return Blade::render('<div class="text-center text-xs text-gray-400 dark:text-gray-500 py-2">{{ $version }}</div>', [
            'version' => $version,
        ]);
    }
}
