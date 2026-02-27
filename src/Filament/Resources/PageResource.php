<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Concerns\HasSeoFields;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Models\States\PageDraft;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'Page';

    protected static ?string $pluralModelLabel = 'Pages';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view pages') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create pages') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit pages') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete pages') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Page')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Page')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, ?string $old, Forms\Get $get): void {
                                        if (! $get('slug') || $get('slug') === Page::generateSlug($old ?? '')) {
                                            $set('slug', Page::generateSlug($state ?? ''));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('key')
                                    ->label('Cle')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Identifiant unique pour le front-end (ex: about, contact)'),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Page parente')
                                    ->options(function (?Page $record): array {
                                        return static::buildPageOptions($record);
                                    })
                                    ->searchable()
                                    ->nullable()
                                    ->live(),
                                Forms\Components\TextInput::make('position')
                                    ->label('Position')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Forms\Get $get): bool => filled($get('parent_id'))),
                                Forms\Components\Toggle::make('is_home')
                                    ->label('Page d\'accueil'),
                                Forms\Components\KeyValue::make('meta')
                                    ->label('Metadonnees')
                                    ->nullable()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('state')
                                    ->label('Statut')
                                    ->options(function (): array {
                                        $options = [PageDraft::getMorphClass() => 'Brouillon'];

                                        if (auth()->user()?->can('publish pages')) {
                                            $options[PagePublished::getMorphClass()] = 'Publie';
                                        }

                                        return $options;
                                    })
                                    ->default(PageDraft::getMorphClass())
                                    ->required()
                                    ->live(),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->visible(fn (Forms\Get $get): bool => $get('state') === PagePublished::getMorphClass())
                                    ->helperText('Laissez vide pour utiliser la date actuelle'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Sections')
                            ->schema([
                                Forms\Components\Builder::make('sections')
                                    ->label('Sections')
                                    ->blocks(fn () => app(SectionRegistry::class)->blocks())
                                    ->addActionLabel('Ajouter une section')
                                    ->collapsible()
                                    ->blockNumbers(false)
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn () => count(app(SectionRegistry::class)->all()) > 0),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('h1')
                                    ->label('H1')
                                    ->maxLength(255)
                                    ->helperText('Laissez vide pour utiliser le nom'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Cle')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('Racine')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_home')
                    ->label('Accueil')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifie le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('position')
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Statut')
                    ->options([
                        PageDraft::getMorphClass() => 'Brouillon',
                        PagePublished::getMorphClass() => 'Publie',
                    ]),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->options(fn (): array => Page::query()->whereNotNull('parent_id')->pluck('name', 'parent_id')->unique()->toArray())
                    ->searchable(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    protected static function buildPageOptions(?Page $current): array
    {
        $query = Page::query()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('name')
            ->with('children');

        if ($current) {
            $query->where('id', '!=', $current->id);
        }

        $options = [];

        foreach ($query->get() as $root) {
            $options[$root->id] = $root->name;

            foreach ($root->children->sortBy('position')->sortBy('name') as $child) {
                if ($current && $child->id === $current->id) {
                    continue;
                }
                $options[$child->id] = '— ' . $child->name;
            }
        }

        return $options;
    }

    /**
     * @return Builder<Page>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
