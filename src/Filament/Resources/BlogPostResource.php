<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource\Pages;
use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogCategory;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\BlogSetting;
use Alexisgt01\CmsCore\Models\BlogTag;
use Alexisgt01\CmsCore\Models\States\Draft;
use Alexisgt01\CmsCore\Models\States\Published;
use Alexisgt01\CmsCore\Models\States\Scheduled;
use Illuminate\Database\Eloquent\Model;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $modelLabel = 'Article';

    protected static ?string $pluralModelLabel = 'Articles';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view blog posts') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create blog posts') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit blog posts') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete blog posts') ?? false;
    }

    public static function form(Form $form): Form
    {
        $settings = BlogSetting::instance();

        return $form
            ->schema([
                Forms\Components\Tabs::make('Article')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Contenu')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $old, Forms\Get $get): void {
                                        if (! $get('slug') || $get('slug') === BlogPost::generateSlug($old ?? '')) {
                                            $set('slug', BlogPost::generateSlug($state ?? ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Extrait')
                                    ->required()
                                    ->rows(3),
                                Forms\Components\Select::make('category_id')
                                    ->label('Catégorie')
                                    ->options(function (): array {
                                        return self::buildCategoryOptions();
                                    })
                                    ->searchable()
                                    ->nullable(),
                                Forms\Components\Select::make('tags')
                                    ->label('Tags')
                                    ->multiple()
                                    ->relationship('tags', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                                $set('slug', BlogTag::generateSlug($state ?? ''));
                                            }),
                                        Forms\Components\TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(BlogTag::class, 'slug'),
                                    ]),
                                TiptapEditor::make('content')
                                    ->label('Contenu')
                                    ->profile('default')
                                    ->disk('public')
                                    ->directory('blog-content')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Images & Auteur')
                            ->schema([
                                ...self::buildFeaturedImageFields($settings),
                                Forms\Components\Select::make('author_id')
                                    ->label('Auteur')
                                    ->options(fn (): array => BlogAuthor::query()->pluck('display_name', 'id')->toArray())
                                    ->searchable()
                                    ->nullable()
                                    ->default($settings->default_author_id),
                                Forms\Components\TextInput::make('reading_time_minutes')
                                    ->label('Temps de lecture (min)')
                                    ->numeric()
                                    ->nullable()
                                    ->helperText('Laissez vide pour un calcul automatique'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Publication')
                            ->schema([
                                Forms\Components\Select::make('state')
                                    ->label('Statut')
                                    ->options(function (): array {
                                        $options = [Draft::getMorphClass() => 'Brouillon'];

                                        if (auth()->user()?->can('publish blog posts')) {
                                            $options[Scheduled::getMorphClass()] = 'Programmé';
                                            $options[Published::getMorphClass()] = 'Publié';
                                        }

                                        return $options;
                                    })
                                    ->default(Draft::getMorphClass())
                                    ->required()
                                    ->live(),
                                Forms\Components\DateTimePicker::make('scheduled_for')
                                    ->label('Date de publication programmée')
                                    ->visible(fn (Forms\Get $get): bool => $get('state') === Scheduled::getMorphClass())
                                    ->required(fn (Forms\Get $get): bool => $get('state') === Scheduled::getMorphClass()),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->visible(fn (Forms\Get $get): bool => $get('state') === Published::getMorphClass())
                                    ->helperText('Laissez vide pour utiliser la date actuelle'),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\Toggle::make('indexing')
                                    ->label('Indexation')
                                    ->default(true),
                                Forms\Components\TextInput::make('canonical_url')
                                    ->label('URL canonique')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('Titre meta')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('meta_description')
                                    ->label('Description meta')
                                    ->rows(2),

                                Forms\Components\Fieldset::make('Robots')
                                    ->schema([
                                        Forms\Components\Toggle::make('robots_index')
                                            ->label('Index'),
                                        Forms\Components\Toggle::make('robots_follow')
                                            ->label('Follow'),
                                        Forms\Components\Toggle::make('robots_noarchive')
                                            ->label('Noarchive'),
                                        Forms\Components\Toggle::make('robots_nosnippet')
                                            ->label('Nosnippet'),
                                        Forms\Components\TextInput::make('robots_max_snippet')
                                            ->label('Max snippet')
                                            ->numeric()
                                            ->nullable(),
                                        Forms\Components\Select::make('robots_max_image_preview')
                                            ->label('Max image preview')
                                            ->options([
                                                'none' => 'None',
                                                'standard' => 'Standard',
                                                'large' => 'Large',
                                            ]),
                                        Forms\Components\TextInput::make('robots_max_video_preview')
                                            ->label('Max video preview')
                                            ->numeric()
                                            ->nullable(),
                                    ])
                                    ->columns(4),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Open Graph')
                            ->schema([
                                Forms\Components\TextInput::make('og_type')
                                    ->label('Type OG')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('og_title')
                                    ->label('Titre OG')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('og_description')
                                    ->label('Description OG')
                                    ->rows(2),
                                MediaPicker::make('og_image')
                                    ->label('Image OG'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Twitter')
                            ->schema([
                                Forms\Components\Select::make('twitter_card')
                                    ->label('Type de carte')
                                    ->options([
                                        'summary' => 'Summary',
                                        'summary_large_image' => 'Summary Large Image',
                                    ]),
                                Forms\Components\TextInput::make('twitter_title')
                                    ->label('Titre Twitter')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('twitter_description')
                                    ->label('Description Twitter')
                                    ->rows(2),
                                MediaPicker::make('twitter_image')
                                    ->label('Image Twitter'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Schema')
                            ->schema([
                                Forms\Components\Select::make('schema_type')
                                    ->label('Type de schema')
                                    ->options([
                                        'Article' => 'Article',
                                        'BlogPosting' => 'BlogPosting',
                                        'NewsArticle' => 'NewsArticle',
                                    ]),
                                Forms\Components\Textarea::make('schema_json')
                                    ->label('JSON-LD personnalisé')
                                    ->rows(4),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, MediaPicker>
     */
    protected static function buildFeaturedImageFields(BlogSetting $settings): array
    {
        $max = $settings->featured_images_max;
        $fields = [];

        for ($i = 0; $i < $max; $i++) {
            $label = $max === 1 ? 'Image à la une' : 'Image à la une ' . ($i + 1);
            $fields[] = MediaPicker::make("featured_images.{$i}")
                ->label($label);
        }

        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('author.display_name')
                    ->label('Auteur')
                    ->placeholder('Aucun'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->placeholder('Aucune')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non publié')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Statut')
                    ->options([
                        Draft::getMorphClass() => 'Brouillon',
                        Scheduled::getMorphClass() => 'Programmé',
                        Published::getMorphClass() => 'Publié',
                    ]),
                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Auteur')
                    ->options(fn (): array => BlogAuthor::query()->pluck('display_name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->options(fn (): array => BlogCategory::query()->pluck('name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('tags')
                    ->label('Tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    protected static function buildCategoryOptions(): array
    {
        $options = [];

        $roots = BlogCategory::query()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('name')
            ->with('children')
            ->get();

        foreach ($roots as $root) {
            $options[$root->id] = $root->name;

            foreach ($root->children->sortBy('position')->sortBy('name') as $child) {
                $options[$child->id] = '— ' . $child->name;
            }
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
