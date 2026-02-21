<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Alexisgt01\CmsCore\Filament\Forms\Components\MediaPicker;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource\Pages;
use Alexisgt01\CmsCore\Models\BlogAuthor;

class BlogAuthorResource extends Resource
{
    protected static ?string $model = BlogAuthor::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Auteurs';

    protected static ?string $modelLabel = 'Auteur';

    protected static ?string $pluralModelLabel = 'Auteurs';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view blog authors') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create blog authors') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit blog authors') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete blog authors') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Auteur')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Identité')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Utilisateur lié')
                                    ->relationship('user', 'email')
                                    ->searchable()
                                    ->nullable()
                                    ->preload(),
                                Forms\Components\TextInput::make('display_name')
                                    ->label('Nom affiché')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $old, Forms\Get $get): void {
                                        if (! $get('slug') || $get('slug') === BlogAuthor::generateSlug($old ?? '')) {
                                            $set('slug', BlogAuthor::generateSlug($state ?? ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('job_title')
                                    ->label('Fonction')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('company')
                                    ->label('Entreprise')
                                    ->maxLength(255),
                                MediaPicker::make('avatar')
                                    ->label('Avatar'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Bio & Réseaux')
                            ->schema([
                                Forms\Components\Textarea::make('bio')
                                    ->label('Biographie')
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('website_url')
                                    ->label('Site web')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('twitter_url')
                                    ->label('Twitter')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('linkedin_url')
                                    ->label('LinkedIn')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('github_url')
                                    ->label('GitHub')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('instagram_url')
                                    ->label('Instagram')
                                    ->url()
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\Toggle::make('indexing')
                                    ->label('Indexation'),
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
                                Forms\Components\TextInput::make('og_title')
                                    ->label('Titre OG')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('og_description')
                                    ->label('Description OG')
                                    ->rows(2),
                                MediaPicker::make('og_image')
                                    ->label('Image OG'),
                                Forms\Components\TextInput::make('twitter_title')
                                    ->label('Titre Twitter')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('twitter_description')
                                    ->label('Description Twitter')
                                    ->rows(2),
                                MediaPicker::make('twitter_image')
                                    ->label('Image Twitter'),
                                Forms\Components\TextInput::make('schema_type')
                                    ->label('Type schema')
                                    ->maxLength(50),
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
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->placeholder('Non lié'),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Articles')
                    ->counts('posts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('detachUser')
                    ->label('Détacher l\'utilisateur')
                    ->icon('heroicon-o-link-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (BlogAuthor $record): bool => $record->user_id !== null)
                    ->action(function (BlogAuthor $record): void {
                        $record->update(['user_id' => null]);
                    }),
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
            'index' => Pages\ListBlogAuthors::route('/'),
            'create' => Pages\CreateBlogAuthor::route('/create'),
            'edit' => Pages\EditBlogAuthor::route('/{record}/edit'),
        ];
    }
}
