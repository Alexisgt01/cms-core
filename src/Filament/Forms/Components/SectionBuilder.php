<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Alexisgt01\CmsCore\Models\GlobalSection;
use Alexisgt01\CmsCore\Models\SectionTemplate;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class SectionBuilder extends Builder
{
    protected string $view = 'cms-core::filament.forms.components.section-builder';

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $cachedSectionTypeDefinitions = null;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $cachedTemplates = null;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $cachedGlobalSections = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extraItemActions([
            fn (): Action => Action::make('saveAsTemplate')
                ->icon('heroicon-o-bookmark')
                ->label('Sauvegarder comme modèle')
                ->form([
                    TextInput::make('name')
                        ->label('Nom du modèle')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $arguments, array $data, SectionBuilder $component): void {
                    $items = $component->getState();
                    $item = $items[$arguments['item']] ?? null;

                    if (! $item) {
                        return;
                    }

                    SectionTemplate::create([
                        'name' => $data['name'],
                        'section_type' => $item['type'],
                        'data' => $item['data'] ?? [],
                    ]);

                    Notification::make()
                        ->title('Modèle sauvegardé')
                        ->success()
                        ->send();
                }),
            fn (): Action => Action::make('convertToGlobal')
                ->icon('heroicon-o-globe-alt')
                ->label('Convertir en section globale')
                ->form([
                    TextInput::make('name')
                        ->label('Nom de la section globale')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $arguments, array $data, SectionBuilder $component): void {
                    $items = $component->getState();
                    $item = $items[$arguments['item']] ?? null;

                    if (! $item || ($item['type'] ?? '') === '__global') {
                        return;
                    }

                    $globalSection = GlobalSection::create([
                        'name' => $data['name'],
                        'section_type' => $item['type'],
                        'data' => $item['data'] ?? [],
                    ]);

                    // Replace the section with a global reference
                    $items[$arguments['item']] = [
                        'type' => '__global',
                        'data' => ['global_section_id' => $globalSection->id],
                    ];

                    $component->state($items);
                    $component->callAfterStateUpdated();

                    Notification::make()
                        ->title('Section convertie en globale')
                        ->success()
                        ->send();
                })
                ->visible(function (array $arguments, SectionBuilder $component): bool {
                    if (! cms_feature('pages_global_sections')) {
                        return false;
                    }

                    $items = $component->getState();
                    $item = $items[$arguments['item']] ?? null;

                    return $item && ($item['type'] ?? '') !== '__global';
                }),
        ]);

        $this->registerActions([
            fn (SectionBuilder $component): Action => $this->getAddFromTemplateAction(),
            fn (SectionBuilder $component): Action => $this->getDeleteTemplateAction(),
            fn (SectionBuilder $component): Action => $this->getAddGlobalSectionAction(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSectionTypeDefinitions(): array
    {
        if ($this->cachedSectionTypeDefinitions !== null) {
            return $this->cachedSectionTypeDefinitions;
        }

        $registry = app(SectionRegistry::class);

        $this->cachedSectionTypeDefinitions = collect($registry->all())
            ->map(fn (string $class) => [
                'key' => $class::key(),
                'label' => $class::label(),
                'icon' => $class::icon(),
                'description' => $class::description(),
                'category' => $class::category(),
            ])
            ->values()
            ->all();

        return $this->cachedSectionTypeDefinitions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTemplates(): array
    {
        if ($this->cachedTemplates !== null) {
            return $this->cachedTemplates;
        }

        $registry = app(SectionRegistry::class);
        $registeredKeys = array_keys($registry->all());

        $this->cachedTemplates = SectionTemplate::query()
            ->orderBy('name')
            ->get()
            ->filter(fn (SectionTemplate $template) => in_array($template->section_type, $registeredKeys))
            ->map(function (SectionTemplate $template) use ($registry) {
                $typeClass = $registry->resolve($template->section_type);

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'section_type' => $template->section_type,
                    'type_label' => $typeClass ? $typeClass::label() : $template->section_type,
                    'type_icon' => $typeClass ? $typeClass::icon() : 'heroicon-o-squares-2x2',
                    'data' => $template->data,
                ];
            })
            ->values()
            ->all();

        return $this->cachedTemplates;
    }

    protected function getAddFromTemplateAction(): Action
    {
        return Action::make('addFromTemplate')
            ->action(function (array $arguments, SectionBuilder $component): void {
                $template = SectionTemplate::find($arguments['templateId'] ?? null);

                if (! $template) {
                    return;
                }

                $newUuid = $component->generateUuid();
                $data = $template->data ?? [];
                $afterItem = $arguments['afterItem'] ?? null;

                if ($afterItem) {
                    $items = [];

                    foreach ($component->getState() ?? [] as $key => $item) {
                        $items[$key] = $item;

                        if ($key === $afterItem) {
                            if ($newUuid) {
                                $items[$newUuid] = [
                                    'type' => $template->section_type,
                                    'data' => $data,
                                ];
                            } else {
                                $items[] = [
                                    'type' => $template->section_type,
                                    'data' => $data,
                                ];

                                $newUuid = array_key_last($items);
                            }
                        }
                    }

                    $component->state($items);
                } else {
                    $items = $component->getState();

                    if ($newUuid) {
                        $items[$newUuid] = [
                            'type' => $template->section_type,
                            'data' => $data,
                        ];
                    } else {
                        $items[] = [
                            'type' => $template->section_type,
                            'data' => $data,
                        ];

                        $newUuid = array_key_last($items);
                    }

                    $component->state($items);
                }

                $component->getChildComponentContainer($newUuid)->fill(filled($data) ? $data : null);

                $component->collapsed(false, shouldMakeComponentCollapsible: false);

                $component->callAfterStateUpdated();
            })
            ->livewireClickHandlerEnabled(false);
    }

    protected function getDeleteTemplateAction(): Action
    {
        return Action::make('deleteTemplate')
            ->requiresConfirmation()
            ->modalHeading('Supprimer le modèle')
            ->modalDescription('Êtes-vous sûr de vouloir supprimer ce modèle de section ?')
            ->action(function (array $arguments): void {
                SectionTemplate::findOrFail($arguments['templateId'])->delete();

                Notification::make()
                    ->title('Modèle supprimé')
                    ->success()
                    ->send();
            })
            ->livewireClickHandlerEnabled(false);
    }

    protected function getAddGlobalSectionAction(): Action
    {
        return Action::make('addGlobalSection')
            ->action(function (array $arguments, SectionBuilder $component): void {
                $globalSection = GlobalSection::find($arguments['globalSectionId'] ?? null);

                if (! $globalSection) {
                    return;
                }

                $newUuid = $component->generateUuid();
                $afterItem = $arguments['afterItem'] ?? null;

                $newItem = [
                    'type' => '__global',
                    'data' => ['global_section_id' => $globalSection->id],
                ];

                if ($afterItem) {
                    $items = [];

                    foreach ($component->getState() ?? [] as $key => $item) {
                        $items[$key] = $item;

                        if ($key === $afterItem) {
                            if ($newUuid) {
                                $items[$newUuid] = $newItem;
                            } else {
                                $items[] = $newItem;
                                $newUuid = array_key_last($items);
                            }
                        }
                    }

                    $component->state($items);
                } else {
                    $items = $component->getState();

                    if ($newUuid) {
                        $items[$newUuid] = $newItem;
                    } else {
                        $items[] = $newItem;
                        $newUuid = array_key_last($items);
                    }

                    $component->state($items);
                }

                $component->callAfterStateUpdated();
            })
            ->livewireClickHandlerEnabled(false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getGlobalSections(): array
    {
        if ($this->cachedGlobalSections !== null) {
            return $this->cachedGlobalSections;
        }

        if (! cms_feature('pages_global_sections')) {
            $this->cachedGlobalSections = [];

            return $this->cachedGlobalSections;
        }

        $registry = app(SectionRegistry::class);
        $registeredKeys = array_keys($registry->all());

        $this->cachedGlobalSections = GlobalSection::query()
            ->orderBy('name')
            ->get()
            ->filter(fn (GlobalSection $section) => in_array($section->section_type, $registeredKeys))
            ->map(function (GlobalSection $section) use ($registry) {
                $typeClass = $registry->resolve($section->section_type);

                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'section_type' => $section->section_type,
                    'type_label' => $typeClass ? $typeClass::label() : $section->section_type,
                    'type_icon' => $typeClass ? $typeClass::icon() : 'heroicon-o-squares-2x2',
                ];
            })
            ->values()
            ->all();

        return $this->cachedGlobalSections;
    }

    /**
     * Check if an item is a global section reference.
     */
    public function isGlobalSection(array $item): bool
    {
        return ($item['type'] ?? '') === '__global';
    }

    /**
     * Resolve a global section from an item's data.
     */
    public function resolveGlobalSection(array $item): ?array
    {
        if (! $this->isGlobalSection($item)) {
            return null;
        }

        $globalSectionId = $item['data']['global_section_id'] ?? null;

        if (! $globalSectionId) {
            return null;
        }

        $registry = app(SectionRegistry::class);
        $globalSection = GlobalSection::find($globalSectionId);

        if (! $globalSection) {
            return null;
        }

        $typeClass = $registry->resolve($globalSection->section_type);

        return [
            'id' => $globalSection->id,
            'name' => $globalSection->name,
            'section_type' => $globalSection->section_type,
            'type_label' => $typeClass ? $typeClass::label() : $globalSection->section_type,
            'type_icon' => $typeClass ? $typeClass::icon() : 'heroicon-o-squares-2x2',
        ];
    }
}
