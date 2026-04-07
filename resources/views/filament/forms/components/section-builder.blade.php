@php
    use Filament\Forms\Components\Actions\Action;
    use Filament\Support\Enums\Alignment;

    $fieldWrapperView = $getFieldWrapperView();
    $items = $getChildComponentContainers();
    $blockPickerBlocks = $getBlockPickerBlocks();
    $blockPickerColumns = $getBlockPickerColumns();
    $blockPickerWidth = $getBlockPickerWidth();
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
    $persistCollapsed = $shouldPersistCollapsed();

    $key = $getKey();
    $statePath = $getStatePath();

    $isBlockLabelTruncated = $isBlockLabelTruncated();
    $labelBetweenItems = $getLabelBetweenItems();

    // Expandable sections: detect trait on Livewire component
    $supportsExpandable = method_exists($this, 'isSectionExpanded');
    $expandedSections = $supportsExpandable ? $this->expandedSections : [];

    // Global section resolver closure (component is accessible via $field in Filament v3)
    $sectionBuilderComponent = $field;
    $resolveGlobalSection = fn (array $rawState) => $sectionBuilderComponent->resolveGlobalSection($rawState);

    // Sync known UUIDs to auto-expand newly added sections
    if ($supportsExpandable && count($items)) {
        $this->syncSectionUuids(array_keys($items));
        $expandedSections = $this->expandedSections;
    }
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
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

            addGlobalSection(globalSectionId) {
                $wire.mountFormComponentAction('{{ $statePath }}', 'addGlobalSection', {
                    globalSectionId: globalSectionId,
                    afterItem: this.afterItemUuid,
                });
                this.pickerOpen = false;
            },
        }"
    >
    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-fo-builder',
                    'fi-collapsible' => $isCollapsible,
                ])
        }}
    >
        @if ($collapseAllActionIsVisible || $expandAllActionIsVisible)
            <div
                @class([
                    'fi-fo-builder-actions',
                    'fi-hidden' => count($items) < 2,
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

        @if (count($items))
            <ul
                x-sortable
                data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                wire:end.stop="{{ 'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })' }}"
                class="fi-fo-builder-items"
            >
                @php
                    $hasBlockLabels = $hasBlockLabels();
                    $hasBlockIcons = $hasBlockIcons();
                    $hasBlockNumbers = $hasBlockNumbers();
                @endphp

                @foreach ($items as $itemKey => $item)
                    @php
                        $rawState = $item->getRawState();
                        $isGlobalItem = ($rawState['type'] ?? '') === '__global';
                        $globalInfo = $isGlobalItem ? $resolveGlobalSection($rawState) : null;

                        $visibleExtraItemActions = array_filter(
                            $extraItemActions,
                            fn (Action $action): bool => $action(['item' => $itemKey])->isVisible(),
                        );
                        $cloneAction = $cloneAction(['item' => $itemKey]);
                        $cloneActionIsVisible = ! $isGlobalItem && $isCloneable && $cloneAction->isVisible();
                        $deleteAction = $deleteAction(['item' => $itemKey]);
                        $deleteActionIsVisible = $isDeletable && $deleteAction->isVisible();
                        $editAction = $editAction(['item' => $itemKey]);
                        $editActionIsVisible = ! $isGlobalItem && $hasBlockPreviews && $editAction->isVisible();
                        $moveDownAction = $moveDownAction(['item' => $itemKey])->disabled($loop->last);
                        $moveDownActionIsVisible = $isReorderableWithButtons && $moveDownAction->isVisible();
                        $moveUpAction = $moveUpAction(['item' => $itemKey])->disabled($loop->first);
                        $moveUpActionIsVisible = $isReorderableWithButtons && $moveUpAction->isVisible();
                        $reorderActionIsVisible = $isReorderableWithDragAndDrop && $reorderAction->isVisible();
                        $hasItemHeader = $reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible || $hasBlockIcons || $hasBlockLabels || $editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions || $isGlobalItem;
                        $isExpanded = $isGlobalItem ? false : ($supportsExpandable ? in_array($itemKey, $expandedSections, true) : ! $isCollapsed($item));
                    @endphp

                    <li
                        wire:ignore.self
                        wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                        x-data="{
                            isCollapsed: @if ($isGlobalItem) true @elseif ($supportsExpandable) @js(! $isExpanded) @elseif ($persistCollapsed) $persist(@js($isCollapsed($item))).as(`builder-${@js($key)}-${@js($itemKey)}-isCollapsed`) @else @js($isCollapsed($item)) @endif,
                        }"
                        x-on:builder-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = {{ $isGlobalItem ? 'true' : 'false' }})"
                        x-on:builder-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                        x-on:expand="isCollapsed = {{ $isGlobalItem ? 'true' : 'false' }}"
                        x-sortable-item="{{ $itemKey }}"
                        {{
                            $item->getParentComponent()->getExtraAttributeBag()
                                ->class([
                                    'fi-fo-builder-item',
                                    'fi-fo-builder-item-has-header' => $hasItemHeader,
                                ])
                        }}
                        x-bind:class="{ 'fi-collapsed': isCollapsed }"
                        @if ($isGlobalItem) style="border-color: rgb(34 197 94 / 0.4); background-color: rgb(34 197 94 / 0.03);" @endif
                    >
                        @if ($isGlobalItem)
                            {{-- Global section: locked header --}}
                            <div class="fi-fo-builder-item-header">
                                @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
                                    <ul class="fi-fo-builder-item-header-start-actions">
                                        @if ($reorderActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $reorderAction->extraAttributes(['x-sortable-handle' => true], merge: true) }}
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

                                @if ($globalInfo)
                                    <x-filament::icon :icon="$globalInfo['type_icon']" class="fi-fo-builder-item-header-icon" />

                                    <p class="fi-fo-builder-item-header-label" style="display: flex; align-items: center; gap: 8px;">
                                        {{ $globalInfo['name'] }}
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; background-color: rgb(34 197 94 / 0.1); color: rgb(22 163 74);">
                                            <x-filament::icon icon="heroicon-m-globe-alt" class="h-3 w-3" />
                                            Globale
                                        </span>
                                    </p>
                                @else
                                    <x-filament::icon icon="heroicon-o-globe-alt" class="fi-fo-builder-item-header-icon" />

                                    <p class="fi-fo-builder-item-header-label" style="display: flex; align-items: center; gap: 8px;">
                                        <span style="color: rgb(156 163 175);">Section globale supprimee</span>
                                    </p>
                                @endif

                                <ul class="fi-fo-builder-item-header-end-actions">
                                    @if ($globalInfo)
                                        <li x-on:click.stop>
                                            <a
                                                href="{{ \Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource::getUrl('edit', ['record' => $globalInfo['id']]) }}"
                                                target="_blank"
                                                class="fi-btn fi-btn-size-sm fi-color-gray"
                                                style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500; color: rgb(107 114 128); transition: all 150ms; text-decoration: none;"
                                                onmouseover="this.style.color='rgb(79 70 229)'"
                                                onmouseout="this.style.color='rgb(107 114 128)'"
                                            >
                                                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
                                                Modifier
                                            </a>
                                        </li>
                                    @endif

                                    @if ($deleteActionIsVisible)
                                        <li x-on:click.stop>
                                            {{ $deleteAction }}
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @elseif ($hasItemHeader)
                            <div
                                @if ($isCollapsible)
                                    x-on:click.stop="
                                        if (isCollapsed) {
                                            isCollapsed = false;
                                            @if ($supportsExpandable && ! $isExpanded)
                                                $wire.expandSection('{{ $itemKey }}');
                                            @endif
                                        } else {
                                            isCollapsed = true;
                                            @if ($supportsExpandable)
                                                $wire.collapseSection('{{ $itemKey }}');
                                            @endif
                                        }
                                    "
                                @endif
                                class="fi-fo-builder-item-header"
                            >
                                @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
                                    <ul
                                        class="fi-fo-builder-item-header-start-actions"
                                    >
                                        @if ($reorderActionIsVisible)
                                            <li x-on:click.stop>
                                                {{ $reorderAction->extraAttributes(['x-sortable-handle' => true], merge: true) }}
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
                                    $blockIcon = $item->getParentComponent()->getIcon($item->getRawState(), $itemKey);
                                @endphp

                                @if ($hasBlockIcons && filled($blockIcon))
                                    <x-filament::icon :icon="$blockIcon" class="fi-fo-builder-item-header-icon" />
                                @endif

                                @if ($hasBlockLabels)
                                    <p
                                        @class([
                                            'fi-fo-builder-item-header-label',
                                            'fi-truncated' => $isBlockLabelTruncated,
                                        ])
                                    >
                                        {{ $item->getParentComponent()->getLabel($item->getRawState(), $itemKey) }}

                                        @if ($hasBlockNumbers)
                                            {{ $loop->iteration }}
                                        @endif
                                    </p>
                                @endif

                                @if ($editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions)
                                    <ul
                                        class="fi-fo-builder-item-header-end-actions"
                                    >
                                        @foreach ($visibleExtraItemActions as $extraItemAction)
                                            <li x-on:click.stop>
                                                {{ $extraItemAction(['item' => $itemKey]) }}
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
                                                class="fi-fo-builder-item-header-collapsible-actions"
                                                x-on:click.stop="
                                                    if (isCollapsed) {
                                                        isCollapsed = false;
                                                        @if ($supportsExpandable && ! $isExpanded)
                                                            $wire.expandSection('{{ $itemKey }}');
                                                        @endif
                                                    } else {
                                                        isCollapsed = true;
                                                        @if ($supportsExpandable)
                                                            $wire.collapseSection('{{ $itemKey }}');
                                                        @endif
                                                    }
                                                "
                                            >
                                                <div
                                                    class="fi-fo-builder-item-header-collapse-action"
                                                >
                                                    {{ $getAction('collapse') }}
                                                </div>

                                                <div
                                                    class="fi-fo-builder-item-header-expand-action"
                                                >
                                                    {{ $getAction('expand') }}
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        @endif

                        {{-- Section content: only render form HTML for expanded sections --}}
                        @if ($isExpanded)
                            <div
                                x-show="! isCollapsed"
                                @class([
                                    'fi-fo-builder-item-content',
                                    'fi-fo-builder-item-content-has-preview' => $hasBlockPreviews && $item->getParentComponent()->hasPreview(),
                                ])
                            >
                                @if ($hasBlockPreviews && $item->getParentComponent()->hasPreview())
                                    <div
                                        @class([
                                            'fi-fo-builder-item-preview',
                                            'fi-interactive' => $hasInteractiveBlockPreviews,
                                        ])
                                    >
                                        {{ $item->getParentComponent()->renderPreview($item->getRawState()) }}
                                    </div>

                                    @if ($editActionIsVisible && (! $hasInteractiveBlockPreviews))
                                        <div
                                            class="fi-fo-builder-item-preview-edit-overlay"
                                            role="button"
                                            x-on:click.stop="{{ '$wire.mountFormComponentAction(\'' . $statePath . '\', \'edit\', { item: \'' . $itemKey . '\' })' }}"
                                        ></div>
                                    @endif
                                @else
                                    {{ $item }}
                                @endif
                            </div>
                        @elseif (! $isGlobalItem)
                            {{-- Placeholder shown while loading collapsed section --}}
                            <div
                                x-show="! isCollapsed"
                                class="fi-fo-builder-item-content"
                            >
                                <div style="display:flex;align-items:center;justify-content:center;gap:8px;padding:24px;font-size:13px;color:#9ca3af;">
                                    <x-filament::loading-indicator style="width:20px;height:20px;" />
                                    Chargement de la section...
                                </div>
                            </div>
                        @endif
                    </li>

                    @if (! $loop->last)
                        @if ($isAddable && $addBetweenAction(['afterItem' => $itemKey])->isVisible())
                            <li class="relative -top-2 !mt-0 h-0">
                                <div class="flex w-full justify-center opacity-0 transition duration-75 hover:opacity-100">
                                    <button
                                        type="button"
                                        x-on:click.stop="openPicker('{{ $itemKey }}')"
                                        class="rounded-lg bg-white p-1 shadow-sm ring-1 ring-gray-950/10 transition hover:bg-gray-50 dark:bg-gray-900 dark:ring-white/20 dark:hover:bg-white/5"
                                    >
                                        <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    </button>
                                </div>
                            </li>
                        @elseif (filled($labelBetweenItems))
                            <li class="fi-fo-builder-label-between-items-ctn">
                                <div
                                    class="fi-fo-builder-label-between-items-divider-before"
                                ></div>

                                <span class="fi-fo-builder-label-between-items">
                                    {{ $labelBetweenItems }}
                                </span>

                                <div
                                    class="fi-fo-builder-label-between-items-divider-after"
                                ></div>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        @endif

        {{-- Add section button --}}
        @if ($isAddable && $addAction->isVisible())
            <div class="fi-fo-builder-block-picker-ctn" x-on:click.stop="openPicker()">
                {{ $addAction }}
            </div>
        @endif

    </div>{{-- close fi-fo-builder --}}

        {{-- Section picker modal --}}
        @php
            $sectionTypes = $getSectionTypeDefinitions();
            $templates = $getTemplates();
            $globalSections = $getGlobalSections();
        @endphp

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
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="border-b border-gray-200 px-6 py-3 dark:border-white/10">
                        <div class="relative">
                            <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-5 w-5" />
                            </div>
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
                                            <div class="text-gray-400 transition-transform duration-200 dark:text-gray-500" x-bind:class="{ 'rotate-180': isCategoryOpen('{{ $catSlug }}') }">
                                                <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
                                            </div>
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
                                                            <x-filament::icon :icon="$type['icon']" class="h-4 w-4" />
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
                                Modeles enregistres
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
                                                <x-filament::icon :icon="$template['type_icon']" class="h-5 w-5" />
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
                                            title="Supprimer le modele"
                                        >
                                            <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Global sections --}}
                        @if (count($globalSections) > 0)
                            <h4 class="mb-3 mt-6 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Sections globales
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($globalSections as $globalSection)
                                    <button
                                        type="button"
                                        x-show="search === '' || '{{ strtolower(e($globalSection['name'] . ' ' . $globalSection['type_label'])) }}'.includes(search.toLowerCase())"
                                        x-on:click="addGlobalSection({{ $globalSection['id'] }})"
                                        class="group relative flex items-start gap-3 rounded-lg border-2 border-green-300 p-3 text-left transition hover:border-green-500 hover:bg-green-50 dark:border-green-700 dark:hover:border-green-500 dark:hover:bg-green-500/10"
                                    >
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 transition group-hover:bg-green-200 dark:bg-green-500/20 dark:text-green-400 dark:group-hover:bg-green-500/30">
                                            <x-filament::icon :icon="$globalSection['type_icon']" class="h-5 w-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $globalSection['name'] }}
                                            </p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                <x-filament::icon icon="heroicon-m-globe-alt" class="h-3 w-3 text-green-500" />
                                                {{ $globalSection['type_label'] }}
                                            </p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Empty state --}}
                        @if (count($sectionTypes) === 0 && count($templates) === 0 && count($globalSections) === 0)
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
