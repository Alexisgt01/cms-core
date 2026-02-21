<?php

namespace Vendor\CmsCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Vendor\CmsCore\Filament\Pages\BlogSettings;
use Vendor\CmsCore\Filament\Pages\EditProfile;
use Vendor\CmsCore\Filament\Pages\MediaLibrary;
use Vendor\CmsCore\Filament\Resources\BlogAuthorResource;
use Vendor\CmsCore\Filament\Resources\BlogPostResource;
use Vendor\CmsCore\Filament\Resources\PermissionResource;
use Vendor\CmsCore\Filament\Resources\RoleResource;
use Vendor\CmsCore\Filament\Resources\UserResource;

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
                BlogPostResource::class,
            ])
            ->pages([
                MediaLibrary::class,
                BlogSettings::class,
            ])
            ->profile(EditProfile::class);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
