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

                                Fieldset::make('Footer')
                                    ->schema([
                                        TextInput::make('footer_copyright')
                                            ->label('Texte copyright')
                                            ->maxLength(255)
                                            ->placeholder('© %year% Mon Entreprise. Tous droits reserves.')
                                            ->helperText('%year% sera remplace par l\'annee en cours, %start_year% par l\'annee de debut'),
                                        TextInput::make('copyright_start_year')
                                            ->label('Annee de debut')
                                            ->numeric()
                                            ->minValue(1900)
                                            ->maxValue(2100)
                                            ->placeholder(date('Y'))
                                            ->helperText('Pour afficher "© 2020-2026"'),
                                        Textarea::make('footer_text')
                                            ->label('Texte additionnel footer')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Contact')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Telephone principal')
                                    ->tel()
                                    ->maxLength(30),
                                TextInput::make('secondary_phone')
                                    ->label('Telephone secondaire')
                                    ->tel()
                                    ->maxLength(30),
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
                                TextInput::make('google_maps_url')
                                    ->label('URL Google Maps')
                                    ->url()
                                    ->maxLength(500)
                                    ->helperText('Lien vers la localisation sur Google Maps')
                                    ->columnSpanFull(),
                                Textarea::make('opening_hours')
                                    ->label('Horaires d\'ouverture')
                                    ->rows(4)
                                    ->placeholder("Lundi - Vendredi : 9h00 - 18h00\nSamedi : 10h00 - 16h00\nDimanche : Ferme")
                                    ->columnSpanFull(),
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

                        Tabs\Tab::make('Mentions legales')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Fieldset::make('Entreprise')
                                    ->schema([
                                        TextInput::make('company_name')
                                            ->label('Raison sociale')
                                            ->maxLength(255),
                                        Select::make('legal_form')
                                            ->label('Forme juridique')
                                            ->options([
                                                'SAS' => 'SAS',
                                                'SASU' => 'SASU',
                                                'SARL' => 'SARL',
                                                'EURL' => 'EURL',
                                                'SA' => 'SA',
                                                'SCI' => 'SCI',
                                                'SNC' => 'SNC',
                                                'EI' => 'Entreprise individuelle',
                                                'EIRL' => 'EIRL',
                                                'Auto-entrepreneur' => 'Auto-entrepreneur',
                                                'Association' => 'Association',
                                            ])
                                            ->searchable()
                                            ->nullable(),
                                        TextInput::make('share_capital')
                                            ->label('Capital social')
                                            ->maxLength(100)
                                            ->placeholder('10 000 EUR'),
                                    ])
                                    ->columns(3),

                                Fieldset::make('Siege social')
                                    ->schema([
                                        TextInput::make('company_address')
                                            ->label('Adresse')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('company_postal_code')
                                            ->label('Code postal')
                                            ->maxLength(20),
                                        TextInput::make('company_city')
                                            ->label('Ville')
                                            ->maxLength(255),
                                        TextInput::make('company_country')
                                            ->label('Pays')
                                            ->maxLength(255)
                                            ->default('France'),
                                    ])
                                    ->columns(3),

                                Fieldset::make('Immatriculation')
                                    ->schema([
                                        TextInput::make('siret')
                                            ->label('SIRET')
                                            ->maxLength(20)
                                            ->placeholder('123 456 789 00012'),
                                        TextInput::make('siren')
                                            ->label('SIREN')
                                            ->maxLength(15)
                                            ->placeholder('123 456 789'),
                                        TextInput::make('tva_number')
                                            ->label('TVA intracommunautaire')
                                            ->maxLength(30)
                                            ->placeholder('FR 12 345678901'),
                                        TextInput::make('rcs')
                                            ->label('RCS')
                                            ->maxLength(100)
                                            ->placeholder('RCS Paris B 123 456 789'),
                                        TextInput::make('ape_code')
                                            ->label('Code APE / NAF')
                                            ->maxLength(10)
                                            ->placeholder('6201Z'),
                                    ])
                                    ->columns(3),

                                Fieldset::make('Directeur de publication')
                                    ->schema([
                                        TextInput::make('director_name')
                                            ->label('Nom complet')
                                            ->maxLength(255),
                                        TextInput::make('director_email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Fieldset::make('Hebergeur')
                                    ->schema([
                                        TextInput::make('hosting_provider_name')
                                            ->label('Nom')
                                            ->maxLength(255),
                                        TextInput::make('hosting_provider_address')
                                            ->label('Adresse')
                                            ->maxLength(500),
                                        TextInput::make('hosting_provider_phone')
                                            ->label('Telephone')
                                            ->tel()
                                            ->maxLength(30),
                                        TextInput::make('hosting_provider_email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Fieldset::make('DPO / RGPD')
                                    ->schema([
                                        TextInput::make('dpo_name')
                                            ->label('Nom du DPO')
                                            ->maxLength(255),
                                        TextInput::make('dpo_email')
                                            ->label('Email du DPO')
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Reseaux sociaux')
                            ->icon('heroicon-o-share')
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label('Facebook')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://facebook.com/votre-page'),
                                TextInput::make('social_x')
                                    ->label('X (Twitter)')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://x.com/votre-compte'),
                                TextInput::make('social_instagram')
                                    ->label('Instagram')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://instagram.com/votre-compte'),
                                TextInput::make('social_linkedin')
                                    ->label('LinkedIn')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://linkedin.com/company/votre-entreprise'),
                                TextInput::make('social_youtube')
                                    ->label('YouTube')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://youtube.com/@votre-chaine'),
                                TextInput::make('social_tiktok')
                                    ->label('TikTok')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://tiktok.com/@votre-compte'),
                                TextInput::make('social_pinterest')
                                    ->label('Pinterest')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://pinterest.com/votre-compte'),
                                TextInput::make('social_github')
                                    ->label('GitHub')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://github.com/votre-compte'),
                                TextInput::make('social_threads')
                                    ->label('Threads')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://threads.net/@votre-compte'),
                                TextInput::make('social_snapchat')
                                    ->label('Snapchat')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://snapchat.com/add/votre-compte'),
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
