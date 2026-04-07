<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Services\ReleaseService;
use Filament\Pages\Page;

class Releases extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Nouveautes';

    protected static ?string $title = 'Nouveautes';

    protected static ?string $slug = 'releases';

    protected static string $view = 'cms-core::filament.pages.releases';

    protected static bool $shouldRegisterNavigation = false;

    public function getReleases(): array
    {
        return app(ReleaseService::class)->all()->toArray();
    }
}
