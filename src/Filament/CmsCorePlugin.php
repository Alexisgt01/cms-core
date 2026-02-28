<?php

namespace Alexisgt01\CmsCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Alexisgt01\CmsCore\Filament\Pages\AdminDashboard;
use Alexisgt01\CmsCore\Filament\Pages\BlogDashboard;
use Alexisgt01\CmsCore\Filament\Pages\BlogSettings;
use Alexisgt01\CmsCore\Filament\Pages\ContactSettings;
use Alexisgt01\CmsCore\Filament\Pages\EditProfile;
use Alexisgt01\CmsCore\Filament\Pages\MediaLibrary;
use Alexisgt01\CmsCore\Filament\Pages\SiteSettings;
use Alexisgt01\CmsCore\Filament\Resources\ActivityLogResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;
use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource;
use Alexisgt01\CmsCore\Filament\Resources\ContactRequestResource;
use Alexisgt01\CmsCore\Filament\Resources\ContactResource;
use Alexisgt01\CmsCore\Filament\Resources\HookDeliveryResource;
use Alexisgt01\CmsCore\Filament\Resources\HookEndpointResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource;
use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Filament\Resources\PermissionResource;
use Alexisgt01\CmsCore\Filament\Resources\RedirectResource;
use Alexisgt01\CmsCore\Filament\Resources\RoleResource;
use Alexisgt01\CmsCore\Filament\Resources\UserResource;
use Alexisgt01\CmsCore\Models\SiteSetting;

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
            ->resources([
                UserResource::class,
                RoleResource::class,
                PermissionResource::class,
                BlogAuthorResource::class,
                BlogCategoryResource::class,
                BlogPostResource::class,
                BlogTagResource::class,
                PageResource::class,
                RedirectResource::class,
                CollectionEntryResource::class,
                ActivityLogResource::class,
                ContactResource::class,
                ContactRequestResource::class,
                HookEndpointResource::class,
                HookDeliveryResource::class,
            ])
            ->pages([
                BlogDashboard::class,
                AdminDashboard::class,
                MediaLibrary::class,
                BlogSettings::class,
                SiteSettings::class,
                ContactSettings::class,
            ])
            ->profile(EditProfile::class);
    }

    public function boot(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => $this->renderVersionFooter(),
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
