<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Widgets\BlogStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestPostsTable;
use Alexisgt01\CmsCore\Filament\Widgets\PostsPerMonthChart;
use Filament\Pages\Dashboard;

class BlogDashboard extends Dashboard
{
    protected static string $routePath = 'blog';

    protected static ?string $title = 'Blog';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Tableaux de bord';

    protected static ?int $navigationSort = -3;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view blog posts') ?? false;
    }

    public function getWidgets(): array
    {
        return [
            BlogStatsOverview::class,
            PostsPerMonthChart::class,
            LatestPostsTable::class,
        ];
    }
}
