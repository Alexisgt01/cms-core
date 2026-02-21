<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource\Pages;
use Alexisgt01\CmsCore\Models\BlogCategory;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Catégories';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Catégorie')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $old, Forms\Get $get): void {
                                        if (! $get('slug') || $get('slug') === BlogCategory::generateSlug($old ?? '')) {
                                            $set('slug', BlogCategory::generateSlug($state ?? ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Catégorie parente')
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

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('Titre meta')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('meta_description')
                                    ->label('Description meta')
                                    ->rows(2),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Open Graph')
                            ->schema([
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
                    ->label('Créée le')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
