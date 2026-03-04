@php
    use Filament\Forms\Components\Actions\Action;
    use Filament\Support\Enums\Alignment;

    $containers = $getChildComponentContainers();
    $blockPickerBlocks = $getBlockPickerBlocks();
    $hasBlockPreviews = $hasBlockPreviews();
    $hasInteractiveBlockPreviews = $hasInteractiveBlockPreviews();

    $addAction = $getAction($getAddActionName());
    $addBetweenAction = $getAction($getAddBetweenActionName());
    $cloneAction = $getAction($getCloneActionName());
    $collapseAllAction = $getAction($getCollapseAllActionName());
    $editAction = $getAction($getEditActionName());
    $expandAllAction = $getAction($getExpandAllActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isCollapsible = $isCollapsible();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $collapseAllActionIsVisible = $isCollapsible && $collapseAllAction->isVisible();
    $expandAllActionIsVisible = $isCollapsible && $expandAllAction->isVisible();

    $statePath = $getStatePath();

    $sectionTypes = $getSectionTypeDefinitions();
    $templates = $getTemplates();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            pickerOpen: false,
            afterItemUuid: null,
            search: '',
            openCategories: {},

            openPicker(afterItem = null) {
                this.afterItemUuid = afterItem;
                this.search = '';
                this.openCategories = {};
                this.pickerOpen = true;
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            },

            toggleCategory(cat) {
                this.openCategories[cat] = !this.openCategories[cat];
            },

            isCategoryOpen(cat) {
                if (this.search !== '') return true;
                return !!this.openCategories[cat];
            },

            addType(typeKey) {
                if (this.afterItemUuid) {
                    $wire.mountFormComponentAction('{{ $statePath }}', 'addBetween', {
                        block: typeKey,
                        afterItem: this.afterItemUuid,
                    });
                } else {
                    $wire.mountFormComponentAction('{{ $statePath }}', 'add', {
                        block: typeKey,
                    });
                }
                this.pickerOpen = false;
            },

            addFromTemplate(templateId) {
                $wire.mountFormComponentAction('{{ $statePath }}', 'addFromTemplate', {
                    templateId: templateId,
                    afterItem: this.afterItemUuid,
                });
                this.pickerOpen = false;
            },

            deleteTemplate(templateId) {
                this.pickerOpen = false;
                $wire.mountFormComponentAction('{{ $statePath }}', 'deleteTemplate', {
                    templateId: templateId,
                });
            },
        }"
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-builder grid grid-cols-1 gap-y-4'])
        }}
    >
        @if ($collapseAllActionIsVisible || $expandAllActionIsVisible)
            <div
                @class([
                    'flex gap-x-3',
                    'hidden' => count($containers) < 2,
                ])
            >
                @if ($collapseAllActionIsVisible)
                    <span
                        x-on:click="$dispatch('builder-collapse', '{{ $statePath }}')"
                    >
                        {{ $collapseAllAction }}
                    </span>
                @endif

                @if ($expandAllActionIsVisible)
                    <span
                        x-on:click="$dispatch('builder-expand', '{{ $statePath }}')"
                    >
                        {{ $expandAllAction }}
                    </span>
                @endif
            </div>
        @endif

        @if (count($containers))
            <ul
                x-sortable
                data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                wire:end.stop="{{ 'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })' }}"
                class="space-y-4"
            >
                @php
                    $hasBlockLabels = $hasBlockLabels();
                    $hasBlockIcons = $hasBlockIcons();
                    $hasBlockNumbers = $hasBlockNumbers();
                @endphp

                @foreach ($containers as $uuid => $item)
                    @php
                        $visibleExtraItemActions = array_filter(
                            $extraItemActions,
                            fn (Action $action): bool => $action(['item' => $uuid])->isVisible(),
                        );
                        $cloneAction = $cloneAction(['item' => $uuid]);
                        $cloneActionIsVisible = $isCloneable && $cloneAction->isVisible();
                        $deleteAction = $deleteAction(['item' => $uuid]);
                        $deleteActionIsVisible = $isDeletable && $deleteAction->isVisible();
                        $editAction = $editAction(['item' => $uuid]);
                        $editActionIsVisible = $hasBlockPreviews && $editAction->isVisible();
                        $moveDownAction = $moveDownAction(['item' => $uuid])->disabled($loop->last);
                        $moveDownActionIsVisible = $isReorderableWithButtons && $moveDownAction->isVisible();
                        $moveUpAction = $moveUpAction(['item' => $uuid])->disabled($loop->first);
                        $moveUpActionIsVisible = $isReorderableWithButtons && $moveUpAction->isVisible();
                        $reorderActionIsVisible = $isReorderableWithDragAndDrop && $reorderAction->isVisible();
                    @endphp

                    <li
                        wire:ignore.self
                        wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                        x-data="{
                            isCollapsed: @js($isCollapsed($item)),
                            hasBeenOpened: @js(! $isCollapsed($item)),
                        }"
                        x-on:builder-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = false, hasBeenOpened = true)"
                        x-on:builder-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                        x-on:expand="isCollapsed = false; hasBeenOpened = true"
                        x-sortable-item="{{ $uuid }}"
                        class="fi-fo-builder-item rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                        x-bind:class="{ 'fi-collapsed': isCollapsed }"
                    >
                        @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible || $hasBlockIcons || $hasBlockLabels || $editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions)
                            <div
                                @if ($isCollapsible)
                                    x-on:click.stop="isCollapsed = !isCollapsed; if (!isCollapsed) hasBeenOpened = true"
                                @endif
                                @class([
                                    'fi-fo-builder-item-header flex items-center gap-x-3 overflow-hidden px-4 py-3',
                                    'cursor-pointer select-none' => $isCollapsible,
                                ])
                            >
                                @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
                                    <ul class="flex items-center gap-x-3">
                                        @if ($reorderActionIsVisible)
                                            <li
                                                x-sortable-handle
                                                x-on:click.stop
                                            >
                                                {{ $reorderAction }}
                                            </li>
                                        @endif

                                        @if ($moveUpActionIsVisible || $moveDownActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $moveUpAction }}
                                            </li>

                                            <li x-on:click.stop>
                                                {{ $moveDownAction }}
                                            </li>
                                        @endif
                                    </ul>
                                @endif

                                @php
                                    $blockIcon = $item->getParentComponent()->getIcon($item->getRawState(), $uuid);
                                @endphp

                                @if ($hasBlockIcons && filled($blockIcon))
                                    <x-filament::icon
                                        :icon="$blockIcon"
                                        class="fi-fo-builder-item-header-icon h-5 w-5 text-gray-400 dark:text-gray-500"
                                    />
                                @endif

                                @if ($hasBlockLabels)
                                    <h4
                                        @class([
                                            'text-sm font-medium text-gray-950 dark:text-white',
                                            'truncate' => $isBlockLabelTruncated(),
                                        ])
                                    >
                                        {{ $item->getParentComponent()->getLabel($item->getRawState(), $uuid) }}

                                        @if ($hasBlockNumbers)
                                            {{ $loop->iteration }}
                                        @endif
                                    </h4>
                                @endif

                                @if ($editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions)
                                    <ul
                                        class="ms-auto flex items-center gap-x-3"
                                    >
                                        @foreach ($visibleExtraItemActions as $extraItemAction)
                                            <li x-on:click.stop>
                                                {{ $extraItemAction(['item' => $uuid]) }}
                                            </li>
                                        @endforeach

                                        @if ($editActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $editAction }}
                                            </li>
                                        @endif

                                        @if ($cloneActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $cloneAction }}
                                            </li>
                                        @endif

                                        @if ($deleteActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $deleteAction }}
                                            </li>
                                        @endif

                                        @if ($isCollapsible)
                                            <li
                                                class="relative transition"
                                                x-on:click.stop="isCollapsed = !isCollapsed"
                                                x-bind:class="{ '-rotate-180': isCollapsed }"
                                            >
                                                <div
                                                    class="transition"
                                                    x-bind:class="{ 'opacity-0 pointer-events-none': isCollapsed }"
                                                >
                                                    {{ $getAction('collapse') }}
                                                </div>

                                                <div
                                                    class="absolute inset-0 rotate-180 transition"
                                                    x-bind:class="{ 'opacity-0 pointer-events-none': ! isCollapsed }"
                                                >
                                                    {{ $getAction('expand') }}
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        @endif

                        <template x-if="hasBeenOpened">
                            <div
                                x-show="! isCollapsed"
                                @class([
                                    'fi-fo-builder-item-content relative border-t border-gray-100 dark:border-white/10',
                                    'p-4' => ! ($hasBlockPreviews && $item->getParentComponent()->hasPreview()),
                                ])
                            >
                                @if ($hasBlockPreviews && $item->getParentComponent()->hasPreview())
                                    <div
                                        @class([
                                            'fi-fo-builder-item-preview',
                                            'pointer-events-none' => ! $hasInteractiveBlockPreviews,
                                        ])
                                    >
                                        {{ $item->getParentComponent()->renderPreview($item->getRawState()) }}
                                    </div>

                                    @if ($editActionIsVisible && (! $hasInteractiveBlockPreviews))
                                        <div
                                            class="absolute inset-0 z-[1] cursor-pointer"
                                            role="button"
                                            x-on:click.stop="{{ '$wire.mountFormComponentAction(\'' . $statePath . '\', \'edit\', { item: \'' . $uuid . '\' })' }}"
                                        ></div>
                                    @endif
                                @else
                                    {{ $item }}
                                @endif
                            </div>
                        </template>
                    </li>

                    @if (! $loop->last)
                        @if ($isAddable && $addBetweenAction(['afterItem' => $uuid])->isVisible())
                            <li class="relative -top-2 !mt-0 h-0">
                                <div
                                    class="flex w-full justify-center opacity-0 transition duration-75 hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        x-on:click.stop="openPicker('{{ $uuid }}')"
                                        class="fi-btn fi-btn-size-sm fi-btn-color-gray inline-flex items-center justify-center gap-1 rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/5"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-m-plus"
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </div>
                            </li>
                        @elseif (filled($labelBetweenItems = $getLabelBetweenItems()))
                            <li
                                class="relative border-t border-gray-200 dark:border-white/10"
                            >
                                <span
                                    class="absolute -top-3 left-3 px-1 text-sm font-medium"
                                >
                                    {{ $labelBetweenItems }}
                                </span>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        @endif

        {{-- Add section button --}}
        @if ($isAddable && $addAction->isVisible())
            <div
                @class([
                    'flex',
                    match ($getAddActionAlignment()) {
                        Alignment::Start, Alignment::Left => 'justify-start',
                        Alignment::Center, null => 'justify-center',
                        Alignment::End, Alignment::Right => 'justify-end',
                        default => $getAddActionAlignment(),
                    },
                ])
            >
                <button
                    type="button"
                    x-on:click="openPicker()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/5"
                >
                    <x-filament::icon
                        icon="heroicon-m-plus"
                        class="h-5 w-5"
                    />
                    <span>{{ $getAddActionLabel() }}</span>
                </button>
            </div>
        @endif

        {{-- Section picker modal --}}
        <template x-teleport="body">
            <div
                x-show="pickerOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4"
                x-on:keydown.escape.window="pickerOpen = false"
            >
                <div
                    x-on:click.outside="pickerOpen = false"
                    x-show="pickerOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="flex w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10"
                    style="max-height: 80vh;"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Ajouter une section
                        </h3>
                        <button
                            type="button"
                            x-on:click="pickerOpen = false"
                            class="text-gray-400 transition hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                        >
                            <x-filament::icon
                                icon="heroicon-m-x-mark"
                                class="h-5 w-5"
                            />
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="border-b border-gray-200 px-6 py-3 dark:border-white/10">
                        <div class="relative">
                            <x-filament::icon
                                icon="heroicon-m-magnifying-glass"
                                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 dark:text-gray-500"
                            />
                            <input
                                x-ref="searchInput"
                                x-model="search"
                                type="text"
                                placeholder="Rechercher une section..."
                                class="w-full rounded-lg border-gray-300 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-primary-500"
                            />
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto p-6">
                        @php
                            $grouped = collect($sectionTypes)->groupBy(fn ($t) => filled($t['category']) ? $t['category'] : 'Autre');
                            $sortedGroups = $grouped->sortKeys();
                        @endphp

                        {{-- Section types grouped by category --}}
                        @if (count($sectionTypes) > 0)
                            <div class="space-y-2">
                                @foreach ($sortedGroups as $category => $types)
                                    @php
                                        $catSlug = \Illuminate\Support\Str::slug($category);
                                        $searchableTexts = $types->map(fn ($t) => strtolower(e($t['label'] . ' ' . ($t['description'] ?? '') . ' ' . ($t['category'] ?? ''))))->implode('||');
                                    @endphp
                                    <div
                                        x-show="search === '' || '{{ $searchableTexts }}'.includes(search.toLowerCase())"
                                        class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
                                    >
                                        <button
                                            type="button"
                                            x-on:click="toggleCategory('{{ $catSlug }}')"
                                            class="flex w-full items-center justify-between px-4 py-3 text-left transition hover:bg-gray-50 dark:hover:bg-white/5"
                                        >
                                            <span class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $category }}</span>
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">{{ $types->count() }}</span>
                                            </span>
                                            <x-filament::icon
                                                icon="heroicon-m-chevron-down"
                                                class="h-4 w-4 text-gray-400 transition-transform duration-200 dark:text-gray-500"
                                                x-bind:class="{ 'rotate-180': isCategoryOpen('{{ $catSlug }}') }"
                                            />
                                        </button>
                                        <div
                                            x-show="isCategoryOpen('{{ $catSlug }}')"
                                            x-collapse
                                            class="border-t border-gray-200 dark:border-white/10"
                                        >
                                            <div class="grid grid-cols-1 gap-2 p-3 sm:grid-cols-2">
                                                @foreach ($types as $type)
                                                    <button
                                                        type="button"
                                                        x-show="search === '' || '{{ strtolower(e($type['label'] . ' ' . ($type['description'] ?? '') . ' ' . ($type['category'] ?? ''))) }}'.includes(search.toLowerCase())"
                                                        x-on:click="addType('{{ $type['key'] }}')"
                                                        class="group flex items-start gap-3 rounded-lg p-2.5 text-left transition hover:bg-primary-50 dark:hover:bg-primary-500/10"
                                                    >
                                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition group-hover:bg-primary-100 group-hover:text-primary-600 dark:bg-white/10 dark:text-gray-400 dark:group-hover:bg-primary-500/20 dark:group-hover:text-primary-400">
                                                            <x-filament::icon
                                                                :icon="$type['icon']"
                                                                class="h-4 w-4"
                                                            />
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-medium text-gray-950 dark:text-white">
                                                                {{ $type['label'] }}
                                                            </p>
                                                            @if (! empty($type['description']))
                                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ $type['description'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Templates --}}
                        @if (count($templates) > 0)
                            <h4 class="mb-3 mt-6 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Modèles enregistrés
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($templates as $template)
                                    <div
                                        x-show="search === '' || '{{ strtolower(e($template['name'] . ' ' . $template['type_label'])) }}'.includes(search.toLowerCase())"
                                        class="group relative flex items-start gap-3 rounded-lg border border-dashed border-gray-300 p-3 text-left transition hover:border-primary-500 hover:bg-primary-50 dark:border-white/20 dark:hover:border-primary-500 dark:hover:bg-primary-500/10"
                                    >
                                        <button
                                            type="button"
                                            x-on:click="addFromTemplate({{ $template['id'] }})"
                                            class="flex flex-1 items-start gap-3"
                                        >
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition group-hover:bg-primary-100 group-hover:text-primary-600 dark:bg-white/10 dark:text-gray-400 dark:group-hover:bg-primary-500/20 dark:group-hover:text-primary-400">
                                                <x-filament::icon
                                                    :icon="$template['type_icon']"
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-950 dark:text-white">
                                                    {{ $template['name'] }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $template['type_label'] }}
                                                </p>
                                            </div>
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click.stop="deleteTemplate({{ $template['id'] }})"
                                            class="absolute right-2 top-2 rounded p-1 text-gray-400 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                            title="Supprimer le modèle"
                                        >
                                            <x-filament::icon
                                                icon="heroicon-m-trash"
                                                class="h-4 w-4"
                                            />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Empty state --}}
                        @if (count($sectionTypes) === 0 && count($templates) === 0)
                            <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucune section disponible
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
