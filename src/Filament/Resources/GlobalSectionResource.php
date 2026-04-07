<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource\Pages;
use Alexisgt01\CmsCore\Models\GlobalSection;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GlobalSectionResource extends Resource
{
    protected static ?string $model = GlobalSection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenu';

    protected static ?string $navigationLabel = 'Sections globales';

    protected static ?string $modelLabel = 'Section globale';

    protected static ?string $pluralModelLabel = 'Sections globales';

    protected static ?int $navigationSort = 4;

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

    /**
     * Resolve the SectionType class from request context or record.
     *
     * @return class-string<\Alexisgt01\CmsCore\Sections\SectionType>|null
     */
    protected static function resolveSectionType(?Form $form = null): ?string
    {
        $key = request()->query('sectionType');

        if (! $key && $form?->getRecord()) {
            $key = $form->getRecord()->section_type;
        }

        if (! $key) {
            return null;
        }

        return app(SectionRegistry::class)->resolve($key);
    }

    public static function form(Schema $form): Schema
    {
        $typeClass = static::resolveSectionType($form);
        $sectionTypeKey = $form?->getRecord()?->section_type ?? request()->query('sectionType', '');
        $registry = app(SectionRegistry::class);
        $isEdit = $form->getRecord() !== null;

        $schema = [
            Forms\Components\TextInput::make('name')
                ->label('Nom de la section globale')
                ->required()
                ->maxLength(255),
        ];

        if ($isEdit) {
            $schema[] = Forms\Components\Placeholder::make('section_type_label')
                ->label('Type de section')
                ->content(fn (GlobalSection $record): string => static::getSectionTypeLabel($record->section_type));

            $schema[] = Forms\Components\Hidden::make('section_type');

            // Show pages using this global section
            $schema[] = Forms\Components\Placeholder::make('usage')
                ->label('Utilisation')
                ->content(function (GlobalSection $record): string {
                    $pages = static::getPagesUsingGlobalSection($record->id);

                    if ($pages->isEmpty()) {
                        return 'Cette section n\'est utilisee sur aucune page.';
                    }

                    return $pages->count() . ' page(s) : ' . $pages->pluck('name')->implode(', ');
                })
                ->columnSpanFull();
        } else {
            if ($typeClass) {
                $schema[] = Forms\Components\Placeholder::make('section_type_label')
                    ->label('Type de section')
                    ->content($typeClass::label());

                $schema[] = Forms\Components\Hidden::make('section_type')
                    ->default($sectionTypeKey);
            } else {
                $schema[] = Forms\Components\Select::make('section_type')
                    ->label('Type de section')
                    ->options(function () use ($registry): array {
                        $options = [];

                        foreach ($registry->all() as $key => $class) {
                            $options[$key] = $class::label();
                        }

                        return $options;
                    })
                    ->required()
                    ->live();
            }
        }

        // Dynamic fields from the SectionType blueprint
        if ($typeClass) {
            $schema[] = Forms\Components\Group::make($typeClass::schema())
                ->statePath('data');
        }

        return $form->schema($schema);
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
                Tables\Columns\TextColumn::make('section_type')
                    ->label('Type de section')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getSectionTypeLabel($state)),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Pages')
                    ->state(fn (GlobalSection $record): int => static::getPagesUsingGlobalSection($record->id)->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section_type')
                    ->label('Type de section')
                    ->options(function (): array {
                        $registry = app(SectionRegistry::class);
                        $options = [];

                        foreach ($registry->all() as $key => $class) {
                            $options[$key] = $class::label();
                        }

                        return $options;
                    }),
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

    /**
     * Get the human-readable label for a section type key.
     */
    protected static function getSectionTypeLabel(string $key): string
    {
        $typeClass = app(SectionRegistry::class)->resolve($key);

        return $typeClass ? $typeClass::label() : $key;
    }

    /**
     * Get all pages that reference a given global section ID.
     *
     * @return \Illuminate\Support\Collection<int, Page>
     */
    public static function getPagesUsingGlobalSection(int $globalSectionId): \Illuminate\Support\Collection
    {
        return Page::query()
            ->whereNotNull('sections')
            ->get()
            ->filter(function (Page $page) use ($globalSectionId): bool {
                foreach ($page->sections ?? [] as $section) {
                    if (($section['type'] ?? '') === '__global' && ($section['data']['global_section_id'] ?? null) == $globalSectionId) {
                        return true;
                    }
                }

                return false;
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalSections::route('/'),
            'create' => Pages\CreateGlobalSection::route('/create'),
            'edit' => Pages\EditGlobalSection::route('/{record}/edit'),
        ];
    }
}
