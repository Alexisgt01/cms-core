<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Alexisgt01\CmsCore\Services\IconDiscoveryService;
use Alexisgt01\CmsCore\ValueObjects\IconSelection;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;

class IconPicker extends Field
{
    protected string $view = 'cms-core::filament.forms.components.icon-picker';

    protected string $outputMode = 'reference';

    /** @var array<int, string>|null */
    protected ?array $allowedSets = null;

    /** @var array<int, string>|null */
    protected ?array $disallowedSets = null;

    protected ?string $defaultSet = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (self $component, mixed $state): void {
            if ($state instanceof IconSelection) {
                $component->state($state->toArray());
            } elseif (is_string($state) && $state !== '') {
                $decoded = json_decode($state, true);
                if (is_array($decoded)) {
                    $component->state($decoded);
                }
            }
        });

        $this->dehydrateStateUsing(function (mixed $state) {
            if (is_array($state) && ! empty($state['name'])) {
                if ($this->getOutputMode() === 'svg' && empty($state['svg'])) {
                    $state['svg'] = app(IconDiscoveryService::class)->getSvgContent($state['name']);
                }

                return IconSelection::fromArray($state);
            }

            return null;
        });

        $this->registerActions([
            $this->selectIconAction(),
            $this->clearAction(),
        ]);
    }

    public function outputMode(string $mode): static
    {
        $this->outputMode = $mode;

        return $this;
    }

    /**
     * @param  array<int, string>  $sets
     */
    public function allowedSets(array $sets): static
    {
        $this->allowedSets = $sets;

        return $this;
    }

    /**
     * @param  array<int, string>  $sets
     */
    public function disallowedSets(array $sets): static
    {
        $this->disallowedSets = $sets;

        return $this;
    }

    public function defaultSet(string $set): static
    {
        $this->defaultSet = $set;

        return $this;
    }

    public function getOutputMode(): string
    {
        return $this->outputMode ?: config('cms-icons.default_mode', 'reference');
    }

    /**
     * @return array<int, string>|null
     */
    public function getAllowedSets(): ?array
    {
        return $this->allowedSets;
    }

    /**
     * @return array<int, string>|null
     */
    public function getDisallowedSets(): ?array
    {
        return $this->disallowedSets;
    }

    public function getDefaultSet(): ?string
    {
        return $this->defaultSet;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableSets(): array
    {
        $sets = app(IconDiscoveryService::class)->getAvailableSets();

        if ($this->allowedSets !== null) {
            $sets = array_values(array_filter($sets, fn (array $set): bool => in_array($set['name'], $this->allowedSets, true)));
        }

        if ($this->disallowedSets !== null) {
            $sets = array_values(array_filter($sets, fn (array $set): bool => ! in_array($set['name'], $this->disallowedSets, true)));
        }

        return $sets;
    }

    protected function selectIconAction(): Action
    {
        return Action::make('selectIcon')
            ->action(function (array $arguments, self $component): void {
                $iconName = $arguments['name'] ?? '';
                $set = $arguments['set'] ?? '';
                $variant = $arguments['variant'] ?? null;
                $label = $arguments['label'] ?? null;

                $svg = null;
                if ($component->getOutputMode() === 'svg') {
                    $svg = app(IconDiscoveryService::class)->getSvgContent($iconName);
                }

                $component->state([
                    'name' => $iconName,
                    'set' => $set,
                    'variant' => $variant,
                    'label' => $label,
                    'svg' => $svg,
                ]);
            });
    }

    protected function clearAction(): Action
    {
        return Action::make('clear')
            ->action(function (self $component): void {
                $component->state(null);
            });
    }
}
