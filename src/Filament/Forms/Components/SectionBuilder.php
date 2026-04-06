<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Alexisgt01\CmsCore\Models\SectionTemplate;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;

class SectionBuilder extends Builder
{
    protected string $view = 'cms-core::filament.forms.components.section-builder';

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $cachedSectionTypeDefinitions = null;

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $cachedTemplates = null;

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
        ]);

        $this->registerActions([
            fn (SectionBuilder $component): Action => $this->getAddFromTemplateAction(),
            fn (SectionBuilder $component): Action => $this->getDeleteTemplateAction(),
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
}
