<?php

namespace Alexisgt01\CmsCore\Filament\Widgets;

use App\Models\User;
use Alexisgt01\CmsCore\Models\CmsMedia;
use Alexisgt01\CmsCore\Models\CmsMediaFolder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;

class AdminStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $usersCount = User::query()->count();

        $roleDistribution = User::query()
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', (new User)->getMorphClass());
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('roles.name, count(*) as count')
            ->groupBy('roles.name')
            ->pluck('count', 'name');

        $roleDescription = $roleDistribution
            ->map(fn (int $count, string $name) => "{$name}: {$count}")
            ->implode(' · ');

        $rolesCount = Role::query()->count();
        $mediaCount = CmsMedia::query()->count();
        $foldersCount = CmsMediaFolder::query()->count();

        return [
            Stat::make('Utilisateurs', $usersCount)
                ->description($roleDescription ?: 'Aucun rôle attribué')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Rôles', $rolesCount)
                ->icon('heroicon-o-shield-check'),

            Stat::make('Fichiers médias', $mediaCount)
                ->icon('heroicon-o-photo'),

            Stat::make('Dossiers', $foldersCount)
                ->icon('heroicon-o-folder'),
        ];
    }
}
