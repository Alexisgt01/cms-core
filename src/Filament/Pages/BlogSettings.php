<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Forms\Components\OgPreview;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Forms\Components\TwitterPreview;
use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogSetting;
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

class BlogSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $title = 'Paramètres du blog';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'cms-core::filament.pages.blog-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage blog settings') ?? false;
    }

    public function mount(): void
    {
        $settings = BlogSetting::instance();

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Parametres')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Toggle::make('enabled')
                                    ->label('Blog active'),
                                TextInput::make('blog_name')
                                    ->label('Nom du blog')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('blog_description')
                                    ->label('Description')
                                    ->rows(3),
                                Select::make('default_author_id')
                                    ->label('Auteur par defaut')
                                    ->options(fn (): array => BlogAuthor::query()->pluck('display_name', 'id')->toArray())
                                    ->searchable()
                                    ->nullable(),
                                TextInput::make('posts_per_page')
                                    ->label('Articles par page')
                                    ->numeric()
                                    ->default(12)
                                    ->minValue(1)
                                    ->maxValue(100),
                                Toggle::make('show_author_on_post')
                                    ->label('Afficher l\'auteur sur les articles'),
                                Toggle::make('show_reading_time')
                                    ->label('Afficher le temps de lecture'),
                                Toggle::make('enable_comments')
                                    ->label('Activer les commentaires'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('RSS')
                            ->icon('heroicon-o-rss')
                            ->schema([
                                Toggle::make('rss_enabled')
                                    ->label('Flux RSS active'),
                                TextInput::make('rss_title')
                                    ->label('Titre du flux RSS')
                                    ->maxLength(255),
                                Textarea::make('rss_description')
                                    ->label('Description du flux RSS')
                                    ->rows(2),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Images')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                TextInput::make('featured_images_max')
                                    ->label('Nombre max d\'images a la une')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(4),
                                Toggle::make('featured_image_required')
                                    ->label('Image a la une obligatoire'),
                                TextInput::make('default_image_width')
                                    ->label('Largeur par defaut (px)')
                                    ->numeric()
                                    ->default(1200),
                                TextInput::make('default_image_height')
                                    ->label('Hauteur par defaut (px)')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Toggle::make('indexing_default')
                                    ->label('Indexation par defaut'),
                                Toggle::make('default_h1_from_title')
                                    ->label('H1 auto depuis le titre')
                                    ->helperText('Si active, le H1 est auto-rempli depuis le titre quand il est vide'),
                                Select::make('default_canonical_mode')
                                    ->label('Mode canonique par defaut')
                                    ->options([
                                        'auto' => 'Automatique',
                                        'custom' => 'Personnalise',
                                    ])
                                    ->default('auto'),
                                TextInput::make('default_meta_title_template')
                                    ->label('Template titre meta')
                                    ->placeholder('{{title}} | {{site}}')
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                Textarea::make('default_meta_description_template')
                                    ->label('Template description meta')
                                    ->rows(2)
                                    ->live(onBlur: true),

                                Fieldset::make('Robots par defaut')
                                    ->schema([
                                        Toggle::make('default_robots_index')
                                            ->label('Index'),
                                        Toggle::make('default_robots_follow')
                                            ->label('Follow'),
                                        Toggle::make('default_robots_noarchive')
                                            ->label('Noarchive'),
                                        Toggle::make('default_robots_nosnippet')
                                            ->label('Nosnippet'),
                                        TextInput::make('default_robots_max_snippet')
                                            ->label('Max snippet')
                                            ->numeric()
                                            ->nullable(),
                                        Select::make('default_robots_max_image_preview')
                                            ->label('Max image preview')
                                            ->options([
                                                'none' => 'None',
                                                'standard' => 'Standard',
                                                'large' => 'Large',
                                            ])
                                            ->default('large'),
                                        TextInput::make('default_robots_max_video_preview')
                                            ->label('Max video preview')
                                            ->numeric()
                                            ->nullable(),
                                    ])
                                    ->columns(4),

                                SerpPreview::make()->forSettings(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Open Graph')
                            ->icon('heroicon-o-share')
                            ->schema([
                                TextInput::make('og_site_name')
                                    ->label('Nom du site OG')
                                    ->maxLength(255),
                                Select::make('og_type_default')
                                    ->label('Type OG par defaut')
                                    ->options([
                                        'article' => 'Article',
                                        'website' => 'Website',
                                        'blog' => 'Blog',
                                        'profile' => 'Profile',
                                    ])
                                    ->default('article'),
                                TextInput::make('og_locale')
                                    ->label('Locale OG')
                                    ->placeholder('fr_FR')
                                    ->maxLength(10),
                                TextInput::make('og_title_template')
                                    ->label('Template titre OG')
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                Textarea::make('og_description_template')
                                    ->label('Template description OG')
                                    ->rows(2)
                                    ->live(onBlur: true),
                                MediaPicker::make('og_image_fallback')
                                    ->label('Image OG par defaut'),
                                TextInput::make('og_image_fallback_width')
                                    ->label('Largeur image OG (px)')
                                    ->numeric()
                                    ->nullable(),
                                TextInput::make('og_image_fallback_height')
                                    ->label('Hauteur image OG (px)')
                                    ->numeric()
                                    ->nullable(),
                                OgPreview::make()->forSettings(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Twitter')
                            ->icon('heroicon-o-chat-bubble-left')
                            ->schema([
                                Select::make('twitter_card_default')
                                    ->label('Type de carte Twitter')
                                    ->options([
                                        'summary' => 'Summary',
                                        'summary_large_image' => 'Summary Large Image',
                                    ])
                                    ->default('summary_large_image')
                                    ->live(),
                                TextInput::make('twitter_site')
                                    ->label('Compte Twitter du site')
                                    ->placeholder('@monsite')
                                    ->maxLength(255),
                                TextInput::make('twitter_creator')
                                    ->label('Compte Twitter createur')
                                    ->placeholder('@auteur')
                                    ->maxLength(255),
                                TextInput::make('twitter_title_template')
                                    ->label('Template titre Twitter')
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                Textarea::make('twitter_description_template')
                                    ->label('Template description Twitter')
                                    ->rows(2)
                                    ->live(onBlur: true),
                                MediaPicker::make('twitter_image_fallback')
                                    ->label('Image Twitter par defaut'),
                                TextInput::make('twitter_image_fallback_width')
                                    ->label('Largeur image Twitter (px)')
                                    ->numeric()
                                    ->nullable(),
                                TextInput::make('twitter_image_fallback_height')
                                    ->label('Hauteur image Twitter (px)')
                                    ->numeric()
                                    ->nullable(),
                                TwitterPreview::make()->forSettings(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Sitemap')
                            ->icon('heroicon-o-map')
                            ->schema([
                                Toggle::make('sitemap_enabled')
                                    ->label('Sitemap active'),
                                TextInput::make('sitemap_base_url')
                                    ->label('URL de base')
                                    ->url()
                                    ->placeholder('https://monsite.com')
                                    ->maxLength(255)
                                    ->helperText('Laissez vide pour utiliser APP_URL'),
                                TextInput::make('sitemap_max_urls')
                                    ->label('Nombre max d\'URLs')
                                    ->numeric()
                                    ->default(5000)
                                    ->minValue(1)
                                    ->maxValue(50000),
                                TextInput::make('sitemap_crawl_depth')
                                    ->label('Profondeur de crawl')
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(1)
                                    ->maxValue(255),
                                TextInput::make('sitemap_concurrency')
                                    ->label('Requetes simultanees')
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(1)
                                    ->maxValue(50),
                                TagsInput::make('sitemap_exclude_patterns')
                                    ->label('Patterns d\'exclusion')
                                    ->helperText('Patterns glob pour exclure des URLs (ex: /admin/*, /api/*)')
                                    ->placeholder('/admin/*'),
                                Select::make('sitemap_default_change_freq')
                                    ->label('Frequence de changement par defaut')
                                    ->options([
                                        'always' => 'Always',
                                        'hourly' => 'Hourly',
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                        'yearly' => 'Yearly',
                                        'never' => 'Never',
                                    ])
                                    ->default('weekly'),
                                TextInput::make('sitemap_default_priority')
                                    ->label('Priorite par defaut')
                                    ->numeric()
                                    ->default(0.5)
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->step(0.1),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Schema')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Toggle::make('schema_enabled')
                                    ->label('Schema active'),
                                Select::make('schema_type_default')
                                    ->label('Type de schema par defaut')
                                    ->options(static::schemaTypeOptions())
                                    ->default('BlogPosting'),
                                Select::make('default_schema_types')
                                    ->label('Types de schema par defaut (multi)')
                                    ->multiple()
                                    ->options(static::schemaTypeOptions()),
                                TextInput::make('schema_publisher_name')
                                    ->label('Nom de l\'editeur')
                                    ->maxLength(255),
                                MediaPicker::make('schema_publisher_logo')
                                    ->label('Logo de l\'editeur'),
                                TextInput::make('schema_organization_url')
                                    ->label('URL de l\'organisation')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('URL du site principal de l\'organisation'),
                                TextInput::make('schema_language')
                                    ->label('Langue')
                                    ->placeholder('fr')
                                    ->maxLength(10),
                                TagsInput::make('schema_same_as')
                                    ->label('Profils sociaux (sameAs)')
                                    ->helperText('URLs des profils sociaux de l\'organisation (Facebook, Twitter, LinkedIn, etc.)')
                                    ->placeholder('https://facebook.com/monsite'),
                                Textarea::make('schema_custom_json')
                                    ->label('JSON-LD personnalise (merge)')
                                    ->rows(6)
                                    ->formatStateUsing(fn ($state): ?string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                                    ->dehydrateStateUsing(fn ($state) => is_string($state) && $state !== '' ? json_decode($state, true) : $state)
                                    ->rule(static function (): \Closure {
                                        return static function (string $attribute, $value, \Closure $fail): void {
                                            if ($value !== null && $value !== '') {
                                                json_decode(is_string($value) ? $value : '');
                                                if (json_last_error() !== JSON_ERROR_NONE) {
                                                    $fail('Le JSON-LD n\'est pas valide : ' . json_last_error_msg());
                                                }
                                            }
                                        };
                                    })
                                    ->helperText('JSON valide qui sera fusionne avec le schema genere'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * @return array<string, string>
     */
    protected static function schemaTypeOptions(): array
    {
        return [
            'WebPage' => 'WebPage',
            'CollectionPage' => 'CollectionPage',
            'ItemList' => 'ItemList',
            'Article' => 'Article',
            'BlogPosting' => 'BlogPosting',
            'NewsArticle' => 'NewsArticle',
            'FAQPage' => 'FAQPage',
            'BreadcrumbList' => 'BreadcrumbList',
            'Person' => 'Person',
            'Organization' => 'Organization',
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = BlogSetting::instance();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('Parametres sauvegardes')
            ->success()
            ->send();
    }
}
