<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource\Pages;
use Alexisgt01\CmsCore\Models\SectionTemplate;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SectionTemplateResource extends Resource
{
    protected static ?string $model = SectionTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?string $navigationLabel = 'Modeles de section';

    protected static ?string $modelLabel = 'Modele de section';

    protected static ?string $pluralModelLabel = 'Modeles de section';

    protected static ?int $navigationSort = 3;

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

    public static function form(Form $form): Form
    {
        $typeClass = static::resolveSectionType($form);
        $sectionTypeKey = $form?->getRecord()?->section_type ?? request()->query('sectionType', '');
        $registry = app(SectionRegistry::class);
        $isEdit = $form->getRecord() !== null;

        $schema = [
            Forms\Components\TextInput::make('name')
                ->label('Nom du modele')
                ->required()
                ->maxLength(255),
        ];

        if ($isEdit) {
            // On edit: show type as read-only placeholder + hidden field
            $schema[] = Forms\Components\Placeholder::make('section_type_label')
                ->label('Type de section')
                ->content(fn (SectionTemplate $record): string => static::getSectionTypeLabel($record->section_type));

            $schema[] = Forms\Components\Hidden::make('section_type');
        } else {
            // On create: select if no query param, otherwise hidden
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectionTemplates::route('/'),
            'create' => Pages\CreateSectionTemplate::route('/create'),
            'edit' => Pages\EditSectionTemplate::route('/{record}/edit'),
        ];
    }
}
