<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Widgets\AdminStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestUsersTable;
use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    protected static string $routePath = 'admin-overview';

    protected static ?string $title = 'Administration';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Tableaux de bord';

    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view users') ?? false;
    }

    public function getWidgets(): array
    {
        return [
            AdminStatsOverview::class,
            LatestUsersTable::class,
        ];
    }
}
