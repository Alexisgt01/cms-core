<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Widgets\AdminStatsOverview;
use Alexisgt01\CmsCore\Filament\Widgets\LatestUsersTable;
use Alexisgt01\CmsCore\Mail\TestEmail;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

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
            Action::make('send_test_email')
                ->label('Tester l\'email')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label('Adresse email')
                        ->email()
                        ->required()
                        ->default(fn (): ?string => auth()->user()?->email),
                ])
                ->modalHeading('Envoyer un email de test')
                ->modalDescription('Un email de test sera envoye pour verifier la configuration SMTP. Testez votre score spam avec mail-tester.com.')
                ->modalSubmitActionLabel('Envoyer')
                ->action(function (array $data): void {
                    $settings = SiteSetting::instance();
                    $siteName = $settings->site_name ?: config('app.name');

                    $mailable = new TestEmail($siteName);

                    if ($settings->from_email_address) {
                        $mailable->from($settings->from_email_address, $settings->from_email_name ?: $siteName);
                    }

                    try {
                        Mail::to($data['email'])->send($mailable);

                        Notification::make()
                            ->title('Email envoye')
                            ->body('Un email de test a ete envoye a ' . $data['email'])
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Erreur d\'envoi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

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
