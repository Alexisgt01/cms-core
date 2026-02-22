<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Concerns\HasSeoFields;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource\Pages;
use Alexisgt01\CmsCore\Models\BlogTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BlogTagResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = BlogTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Tags';

    protected static ?string $modelLabel = 'Tag';

    protected static ?string $pluralModelLabel = 'Tags';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view blog tags') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create blog tags') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit blog tags') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete blog tags') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tag')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $old, Forms\Get $get): void {
                                        if (! $get('slug') || $get('slug') === BlogTag::generateSlug($old ?? '')) {
                                            $set('slug', BlogTag::generateSlug($state ?? ''));
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
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                ...static::seoKeywordFields(),
                                ...static::seoIndexingFields(),
                                ...static::seoMetaFields(),
                                static::robotsFieldset(),
                                SerpPreview::make(),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Contenu SEO')
                            ->schema(static::contentSeoFields()),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Articles')
                    ->counts('posts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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
            'index' => Pages\ListBlogTags::route('/'),
            'create' => Pages\CreateBlogTag::route('/create'),
            'edit' => Pages\EditBlogTag::route('/{record}/edit'),
        ];
    }
}
