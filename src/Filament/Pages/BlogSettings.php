<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogSetting;

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
                Section::make('Général')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Blog activé'),
                        TextInput::make('blog_name')
                            ->label('Nom du blog')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('blog_description')
                            ->label('Description')
                            ->rows(3),
                        Select::make('default_author_id')
                            ->label('Auteur par défaut')
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

                Section::make('RSS')
                    ->schema([
                        Toggle::make('rss_enabled')
                            ->label('Flux RSS activé'),
                        TextInput::make('rss_title')
                            ->label('Titre du flux RSS')
                            ->maxLength(255),
                        Textarea::make('rss_description')
                            ->label('Description du flux RSS')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Images')
                    ->schema([
                        TextInput::make('featured_images_max')
                            ->label('Nombre max d\'images à la une')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(4),
                        Toggle::make('featured_image_required')
                            ->label('Image à la une obligatoire'),
                        MediaPicker::make('og_image_fallback')
                            ->label('Image OG par défaut'),
                        MediaPicker::make('twitter_image_fallback')
                            ->label('Image Twitter par défaut'),
                        TextInput::make('default_image_width')
                            ->label('Largeur par défaut (px)')
                            ->numeric()
                            ->default(1200),
                        TextInput::make('default_image_height')
                            ->label('Hauteur par défaut (px)')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('SEO / Indexation')
                    ->schema([
                        Toggle::make('indexing_default')
                            ->label('Indexation par défaut'),
                        Toggle::make('default_h1_from_title')
                            ->label('H1 auto depuis le titre')
                            ->helperText('Si active, le H1 est auto-rempli depuis le titre quand il est vide'),
                        Select::make('default_canonical_mode')
                            ->label('Mode canonique par défaut')
                            ->options([
                                'auto' => 'Automatique',
                                'custom' => 'Personnalisé',
                            ])
                            ->default('auto'),
                        TextInput::make('default_meta_title_template')
                            ->label('Template titre meta')
                            ->placeholder('{{title}} | {{site}}')
                            ->maxLength(255),
                        Textarea::make('default_meta_description_template')
                            ->label('Template description meta')
                            ->rows(2),

                        Fieldset::make('Robots par défaut')
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
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Open Graph')
                    ->schema([
                        TextInput::make('og_site_name')
                            ->label('Nom du site OG')
                            ->maxLength(255),
                        Select::make('og_type_default')
                            ->label('Type OG par défaut')
                            ->options([
                                'article' => 'Article',
                                'website' => 'Website',
                                'blog' => 'Blog',
                            ])
                            ->default('article'),
                        TextInput::make('og_locale')
                            ->label('Locale OG')
                            ->placeholder('fr_FR')
                            ->maxLength(10),
                        TextInput::make('og_title_template')
                            ->label('Template titre OG')
                            ->maxLength(255),
                        Textarea::make('og_description_template')
                            ->label('Template description OG')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Twitter')
                    ->schema([
                        Select::make('twitter_card_default')
                            ->label('Type de carte Twitter')
                            ->options([
                                'summary' => 'Summary',
                                'summary_large_image' => 'Summary Large Image',
                            ])
                            ->default('summary_large_image'),
                        TextInput::make('twitter_site')
                            ->label('Compte Twitter du site')
                            ->placeholder('@monsite')
                            ->maxLength(255),
                        TextInput::make('twitter_creator')
                            ->label('Compte Twitter créateur')
                            ->placeholder('@auteur')
                            ->maxLength(255),
                        TextInput::make('twitter_title_template')
                            ->label('Template titre Twitter')
                            ->maxLength(255),
                        Textarea::make('twitter_description_template')
                            ->label('Template description Twitter')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Schema / JSON-LD')
                    ->schema([
                        Toggle::make('schema_enabled')
                            ->label('Schema activé'),
                        Select::make('schema_type_default')
                            ->label('Type de schema par defaut')
                            ->options([
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
                            ])
                            ->default('BlogPosting'),
                        Select::make('default_schema_types')
                            ->label('Types de schema par defaut (multi)')
                            ->multiple()
                            ->options([
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
                            ]),
                        TextInput::make('schema_publisher_name')
                            ->label('Nom de l\'éditeur')
                            ->maxLength(255),
                        MediaPicker::make('schema_publisher_logo')
                            ->label('Logo de l\'éditeur'),
                        TextInput::make('schema_language')
                            ->label('Langue')
                            ->placeholder('fr')
                            ->maxLength(10),
                        Textarea::make('schema_custom_json')
                            ->label('JSON-LD personnalisé (merge)')
                            ->rows(4)
                            ->helperText('JSON valide qui sera fusionné avec le schema généré'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = BlogSetting::instance();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('Paramètres sauvegardés')
            ->success()
            ->send();
    }
}
