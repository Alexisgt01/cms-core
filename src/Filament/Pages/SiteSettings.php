<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Parametres du site';

    protected static ?string $title = 'Parametres du site';

    protected static ?int $navigationSort = 98;

    protected static string $view = 'cms-core::filament.pages.site-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage site settings') ?? false;
    }

    public function mount(): void
    {
        $settings = SiteSetting::instance();

        $data = $settings->toArray();
        $data['restricted_access_password'] = null;

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Parametres')
                    ->tabs([
                        Tabs\Tab::make('Identite')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('Nom du site')
                                    ->maxLength(255),
                                TextInput::make('baseline')
                                    ->label('Baseline / Slogan')
                                    ->maxLength(255),
                                MediaPicker::make('logo_light')
                                    ->label('Logo (fond clair)'),
                                MediaPicker::make('logo_dark')
                                    ->label('Logo (fond sombre)'),
                                MediaPicker::make('favicon')
                                    ->label('Favicon'),
                                Select::make('timezone')
                                    ->label('Fuseau horaire')
                                    ->options(fn (): array => collect(timezone_identifiers_list())
                                        ->mapWithKeys(fn (string $tz) => [$tz => $tz])
                                        ->toArray())
                                    ->searchable()
                                    ->nullable(),
                                Select::make('date_format')
                                    ->label('Format de date')
                                    ->options([
                                        'd/m/Y' => 'd/m/Y (25/02/2026)',
                                        'Y-m-d' => 'Y-m-d (2026-02-25)',
                                        'd M Y' => 'd M Y (25 Feb 2026)',
                                        'd F Y' => 'd F Y (25 February 2026)',
                                        'F j, Y' => 'F j, Y (February 25, 2026)',
                                    ])
                                    ->default('d/m/Y'),
                                Select::make('time_format')
                                    ->label('Format d\'heure')
                                    ->options([
                                        'H:i' => 'H:i (14:30)',
                                        'H:i:s' => 'H:i:s (14:30:00)',
                                        'g:i A' => 'g:i A (2:30 PM)',
                                    ])
                                    ->default('H:i'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Contact')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                TagsInput::make('contact_email_recipients')
                                    ->label('Destinataires des emails de contact')
                                    ->helperText('Adresses email qui recevront les messages de contact')
                                    ->placeholder('email@example.com'),
                                TextInput::make('from_email_name')
                                    ->label('Nom d\'expediteur')
                                    ->maxLength(255),
                                TextInput::make('from_email_address')
                                    ->label('Adresse d\'expediteur')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('reply_to_email')
                                    ->label('Adresse de reponse (Reply-To)')
                                    ->email()
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Acces restreint')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Toggle::make('restricted_access_enabled')
                                    ->label('Activer l\'acces restreint')
                                    ->helperText('Active un mot de passe pour acceder au site public'),
                                TextInput::make('restricted_access_password')
                                    ->label('Mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Laissez vide pour conserver le mot de passe actuel')
                                    ->maxLength(255)
                                    ->dehydrated(fn ($state): bool => filled($state)),
                                TextInput::make('restricted_access_cookie_ttl')
                                    ->label('Duree du cookie (minutes)')
                                    ->numeric()
                                    ->default(1440)
                                    ->minValue(1)
                                    ->helperText('1440 = 24 heures'),
                                Textarea::make('restricted_access_message')
                                    ->label('Message affiche')
                                    ->rows(3)
                                    ->helperText('Message affiche sur la page de restriction'),
                                Toggle::make('restricted_access_admin_bypass')
                                    ->label('Bypass pour les administrateurs')
                                    ->helperText('Les utilisateurs connectes au panel admin ne sont pas bloques'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('SEO Global')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('default_site_title')
                                    ->label('Titre du site par defaut')
                                    ->maxLength(255),
                                Textarea::make('default_meta_description')
                                    ->label('Meta description par defaut')
                                    ->rows(3),
                                TextInput::make('title_template')
                                    ->label('Template de titre')
                                    ->maxLength(255)
                                    ->default('%title% · %site%')
                                    ->helperText('%title% = titre de la page, %site% = nom du site'),
                                MediaPicker::make('default_og_image')
                                    ->label('Image OG par defaut'),

                                Fieldset::make('Robots par defaut')
                                    ->schema([
                                        Toggle::make('default_robots_index')
                                            ->label('Index'),
                                        Toggle::make('default_robots_follow')
                                            ->label('Follow'),
                                    ])
                                    ->columns(2),

                                TextInput::make('canonical_base_url')
                                    ->label('URL canonique de base')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://monsite.com')
                                    ->helperText('Laissez vide pour utiliser APP_URL'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Admin')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Toggle::make('show_version_in_footer')
                                    ->label('Afficher la version Git dans le footer')
                                    ->helperText('Affiche le tag Git actuel dans le footer du panel admin'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (isset($data['restricted_access_password'])) {
            $data['restricted_access_password'] = Hash::make($data['restricted_access_password']);
        }

        $settings = SiteSetting::instance();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('Parametres sauvegardes')
            ->success()
            ->send();
    }
}
