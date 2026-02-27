<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Collections\CollectionRegistry;
use Alexisgt01\CmsCore\Filament\Concerns\HasSeoFields;
use Alexisgt01\CmsCore\Filament\Forms\Components\SerpPreview;
use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource\Pages;
use Alexisgt01\CmsCore\Models\CollectionEntry;
use Alexisgt01\CmsCore\Models\States\EntryDraft;
use Alexisgt01\CmsCore\Models\States\EntryPublished;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CollectionEntryResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = CollectionEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Collections';

    public static function shouldRegisterNavigation(): bool
    {
        return count(app(CollectionRegistry::class)->all()) > 0;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view collection entries') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create collection entries') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit collection entries') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete collection entries') ?? false;
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $registry = app(CollectionRegistry::class);
        $items = [];
        $sort = 1;

        foreach ($registry->all() as $key => $typeClass) {
            $items[] = NavigationItem::make($typeClass::label())
                ->icon($typeClass::icon())
                ->group('Collections')
                ->url(static::getUrl('index') . '?collectionType=' . $key)
                ->isActiveWhen(fn () => request()->query('collectionType') === $key)
                ->sort($sort++);
        }

        return $items;
    }

    /**
     * Resolve the current CollectionType class from request context.
     *
     * @return class-string<\Alexisgt01\CmsCore\Collections\CollectionType>|null
     */
    protected static function resolveCollectionType(): ?string
    {
        $key = request()->query('collectionType');

        if (! $key) {
            return null;
        }

        return app(CollectionRegistry::class)->resolve($key);
    }

    public static function form(Form $form): Form
    {
        $typeClass = static::resolveCollectionType();
        $collectionTypeKey = request()->query('collectionType', '');

        $tabs = [];

        // Main tab
        $mainSchema = [];

        if ($typeClass) {
            if ($typeClass::hasSlug()) {
                $mainSchema[] = Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule) => $rule->where('collection_type', $collectionTypeKey),
                    )
                    ->placeholder('Genere automatiquement depuis ' . $typeClass::slugFrom())
                    ->helperText('Laissez vide pour generer automatiquement');
            }

            // Dynamic fields from blueprint, wrapped with data. statePath
            $mainSchema[] = Forms\Components\Group::make($typeClass::schema())
                ->statePath('data');

            if ($typeClass::sortable()) {
                $mainSchema[] = Forms\Components\TextInput::make('position')
                    ->label('Position')
                    ->numeric()
                    ->default(0);
            }

            if ($typeClass::hasStates()) {
                $mainSchema[] = Forms\Components\Select::make('state')
                    ->label('Statut')
                    ->options(function (): array {
                        $options = [EntryDraft::getMorphClass() => 'Brouillon'];

                        if (auth()->user()?->can('publish collection entries')) {
                            $options[EntryPublished::getMorphClass()] = 'Publie';
                        }

                        return $options;
                    })
                    ->default(EntryDraft::getMorphClass())
                    ->required()
                    ->live();

                $mainSchema[] = Forms\Components\DateTimePicker::make('published_at')
                    ->label('Date de publication')
                    ->visible(fn (Forms\Get $get): bool => $get('state') === EntryPublished::getMorphClass())
                    ->helperText('Laissez vide pour utiliser la date actuelle');
            }

            $mainSchema[] = Forms\Components\Hidden::make('collection_type')
                ->default($collectionTypeKey);
        }

        $tabs[] = Forms\Components\Tabs\Tab::make($typeClass ? $typeClass::singularLabel() : 'Entree')
            ->schema($mainSchema);

        // SEO tabs (conditional)
        if ($typeClass && $typeClass::hasSeo()) {
            $tabs[] = Forms\Components\Tabs\Tab::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('h1')
                        ->label('H1')
                        ->maxLength(255),
                    ...static::seoKeywordFields(),
                    ...static::seoIndexingFields(),
                    ...static::seoMetaFields(),
                    static::robotsFieldset(),
                    SerpPreview::make(),
                ])
                ->columns(2);

            $tabs[] = Forms\Components\Tabs\Tab::make('Open Graph')
                ->schema(static::ogFields())
                ->columns(2);

            $tabs[] = Forms\Components\Tabs\Tab::make('Twitter')
                ->schema(static::twitterFields())
                ->columns(2);

            $tabs[] = Forms\Components\Tabs\Tab::make('Schema')
                ->schema(static::schemaFields())
                ->columns(2);
        }

        return $form->schema([
            Forms\Components\Tabs::make('CollectionEntry')
                ->tabs($tabs)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $typeClass = static::resolveCollectionType();
        $titleField = $typeClass ? $typeClass::slugFrom() : 'title';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data.' . $titleField)
                    ->label($typeClass ? $typeClass::singularLabel() : 'Titre')
                    ->searchable(query: function (Builder $query, string $search) use ($titleField): Builder {
                        return $query->where('data->' . $titleField, 'like', "%{$search}%");
                    })
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('state')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—')
                    ->color(fn ($state) => $state?->color() ?? 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifie le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('position')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (CollectionEntry $record): string => static::getUrl('edit', [
                        'record' => $record,
                        'collectionType' => $record->collection_type,
                    ])),
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
     * @return Builder<CollectionEntry>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $collectionTypeKey = request()->query('collectionType');

        if ($collectionTypeKey) {
            $query->where('collection_type', $collectionTypeKey);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionEntries::route('/'),
            'create' => Pages\CreateCollectionEntry::route('/create'),
            'edit' => Pages\EditCollectionEntry::route('/{record}/edit'),
        ];
    }
}
