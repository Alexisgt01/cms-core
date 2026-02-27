<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Widgets\AdminStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestUsersTable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Artisan;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_cache')
                ->label('Vider le cache')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Vider le cache')
                ->modalDescription('Cette action va vider tous les caches de l\'application (config, routes, vues, events).')
                ->modalSubmitActionLabel('Vider')
                ->action(function (): void {
                    Artisan::call('optimize:clear');

                    Notification::make()
                        ->title('Cache vide')
                        ->body('Tous les caches ont ete vides avec succes.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            AdminStatsOverview::class,
            LatestUsersTable::class,
        ];
    }
}
