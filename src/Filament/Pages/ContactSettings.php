<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Models\ContactSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class ContactSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Contact';

    protected static ?string $navigationLabel = 'Parametres';

    protected static ?string $title = 'Parametres Contact';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'cms-core::filament.pages.contact-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage contact settings') ?? false;
    }

    public function mount(): void
    {
        $settings = ContactSetting::instance();

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('default_async')
                    ->label('Envoi asynchrone par defaut')
                    ->helperText('Si active, les webhooks sont dispatches via la queue'),
                TextInput::make('inbound_secret')
                    ->label('Secret inbound')
                    ->maxLength(255)
                    ->placeholder('Pour la future route d\'ingestion')
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('generate_inbound_secret')
                            ->icon('heroicon-o-arrow-path')
                            ->action(fn (\Filament\Forms\Set $set) => $set('inbound_secret', Str::random(40))),
                    ),
                TextInput::make('retention_days')
                    ->label('Duree de retention (jours)')
                    ->numeric()
                    ->default(90)
                    ->minValue(1)
                    ->helperText('Les demandes et deliveries plus anciennes seront purgees'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = ContactSetting::instance();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('Parametres sauvegardes')
            ->success()
            ->send();
    }
}
