<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource\Pages;
use Alexisgt01\CmsCore\Models\BlogTag;

class BlogTagResource extends Resource
{
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
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Articles')
                    ->counts('posts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
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
