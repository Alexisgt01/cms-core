<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Concerns\HasSeoFields;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource\Pages;
use Alexisgt01\CmsCore\Models\BlogCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BlogCategoryResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = BlogCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'Categorie';

    protected static ?string $pluralModelLabel = 'Categories';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view blog categories') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create blog categories') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit blog categories') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete blog categories') ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Tabs::make('Categorie')
                    ->tabs([
                        Schemas\Components\Tabs\Tab::make('Informations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, ?string $old, Get $get): void {
                                        if (! $get('slug') || $get('slug') === BlogCategory::generateSlug($old ?? '')) {
                                            $set('slug', BlogCategory::generateSlug($state ?? ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('h1')
                                    ->label('H1')
                                    ->maxLength(255)
                                    ->helperText('Laissez vide pour utiliser le nom'),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Categorie parente')
                                    ->options(function (?Model $record): array {
                                        $query = BlogCategory::query()->orderBy('name');

                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        return $query->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->nullable(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3),
                                Forms\Components\TextInput::make('position')
                                    ->label('Position')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2),

                        Schemas\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                ...static::seoKeywordFields(),
                                ...static::seoIndexingFields(),
                                ...static::seoMetaFields(),
                                static::robotsFieldset(),
                                SerpPreview::make(),
                            ])
                            ->columns(2),

                        Schemas\Components\Tabs\Tab::make('Contenu SEO')
                            ->schema(static::contentSeoFields()),

                        Schemas\Components\Tabs\Tab::make('Open Graph')
                            ->schema(static::ogFields())
                            ->columns(2),

                        Schemas\Components\Tabs\Tab::make('Twitter')
                            ->schema(static::twitterFields())
                            ->columns(2),

                        Schemas\Components\Tabs\Tab::make('Schema')
                            ->schema(static::schemaFields())
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parente')
                    ->placeholder('Racine'),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Articles')
                    ->counts('posts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creee le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('position')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parente')
                    ->options(fn (): array => BlogCategory::query()->pluck('name', 'id')->toArray())
                    ->placeholder('Toutes'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit' => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
