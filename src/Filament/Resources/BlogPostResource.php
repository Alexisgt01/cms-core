<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Concerns\HasSeoFields;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource\Pages;
use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogCategory;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\BlogSetting;
use Alexisgt01\CmsCore\Models\BlogTag;
use Alexisgt01\CmsCore\Models\States\Draft;
use Alexisgt01\CmsCore\Models\States\Published;
use Alexisgt01\CmsCore\Models\States\Scheduled;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Model;

class BlogPostResource extends Resource
{
    use HasSeoFields;

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
                                Forms\Components\TextInput::make('h1')
                                    ->label('H1')
                                    ->maxLength(255)
                                    ->helperText('Laissez vide pour utiliser le titre'),
                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Sous-titre')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Extrait')
                                    ->required()
                                    ->rows(3),
                                Forms\Components\Textarea::make('seo_excerpt')
                                    ->label('Extrait SEO')
                                    ->rows(3)
                                    ->helperText('Distinct de l\'extrait principal, utilise pour les meta descriptions automatiques'),
                                Forms\Components\Select::make('category_id')
                                    ->label('Categorie')
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
                                TiptapEditor::make('content_seo_top')
                                    ->label('Contenu SEO (haut de page)')
                                    ->profile('default')
                                    ->nullable()
                                    ->columnSpanFull(),
                                TiptapEditor::make('content')
                                    ->label('Contenu')
                                    ->profile('default')
                                    ->disk('public')
                                    ->directory('blog-content')
                                    ->columnSpanFull(),
                                TiptapEditor::make('content_seo_bottom')
                                    ->label('Contenu SEO (bas de page)')
                                    ->profile('default')
                                    ->nullable()
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('faq_blocks')
                                    ->label('FAQ')
                                    ->schema([
                                        Forms\Components\TextInput::make('question')
                                            ->label('Question')
                                            ->required()
                                            ->maxLength(500),
                                        Forms\Components\Textarea::make('answer')
                                            ->label('Reponse')
                                            ->required()
                                            ->rows(3),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('table_of_contents')
                                    ->label('Table des matieres')
                                    ->default(false),
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
                                            $options[Scheduled::getMorphClass()] = 'Programme';
                                            $options[Published::getMorphClass()] = 'Publie';
                                        }

                                        return $options;
                                    })
                                    ->default(Draft::getMorphClass())
                                    ->required()
                                    ->live(),
                                Forms\Components\DateTimePicker::make('scheduled_for')
                                    ->label('Date de publication programmee')
                                    ->visible(fn (Forms\Get $get): bool => $get('state') === Scheduled::getMorphClass())
                                    ->required(fn (Forms\Get $get): bool => $get('state') === Scheduled::getMorphClass()),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->visible(fn (Forms\Get $get): bool => $get('state') === Published::getMorphClass())
                                    ->helperText('Laissez vide pour utiliser la date actuelle'),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                ...static::seoKeywordFields(),
                                ...static::seoIndexingFields(),
                                ...static::seoMetaFields(),
                                static::robotsFieldset(),
                                SerpPreview::make(),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Open Graph')
                            ->schema(static::ogFields())
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Twitter')
                            ->schema(static::twitterFields())
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Schema')
                            ->schema(static::schemaFields())
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
            $label = $max === 1 ? 'Image a la une' : 'Image a la une ' . ($i + 1);
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
                    ->label('Categorie')
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
                    ->label('Publie le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non publie')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
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
                        Scheduled::getMorphClass() => 'Programme',
                        Published::getMorphClass() => 'Publie',
                    ]),
                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Auteur')
                    ->options(fn (): array => BlogAuthor::query()->pluck('display_name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categorie')
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
