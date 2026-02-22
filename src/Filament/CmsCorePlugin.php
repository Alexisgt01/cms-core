<?php

namespace Alexisgt01\CmsCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Alexisgt01\CmsCore\Filament\Pages\BlogSettings;
use Alexisgt01\CmsCore\Filament\Pages\EditProfile;
use Alexisgt01\CmsCore\Filament\Pages\MediaLibrary;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource;
use Alexisgt01\CmsCore\Filament\Resources\PermissionResource;
use Alexisgt01\CmsCore\Filament\Resources\RedirectResource;
use Alexisgt01\CmsCore\Filament\Resources\RoleResource;
use Alexisgt01\CmsCore\Filament\Resources\UserResource;

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
                RedirectResource::class,
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
