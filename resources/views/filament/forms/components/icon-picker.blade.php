@php
    $statePath = $getStatePath();
    $state = $getState();
    $isDisabled = $isDisabled();
    $availableSets = $field->getAvailableSets();
    $defaultSet = $field->getDefaultSet();

    $hasCurrent = is_array($state) && ! empty($state['name']);
    $currentName = $state['name'] ?? '';
    $currentSet = $state['set'] ?? '';
    $currentVariant = $state['variant'] ?? '';
    $currentLabel = $state['label'] ?? '';
    $currentSvg = $state['svg'] ?? '';

    $searchUrl = route('cms.icons.search');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            open: false,
            search: '',
            activeSet: @js($defaultSet ?? ''),
            activeVariant: '',
            icons: [],
            loading: false,
            page: 1,
            total: 0,
            perPage: {{ (int) config('cms-icons.per_page', 60) }},
            sets: @js($availableSets),
            searchTimeout: null,

            get hasMore() {
                return this.page * this.perPage < this.total;
            },

            get activeSetVariants() {
                if (!this.activeSet) return [];
                const set = this.sets.find(s => s.name === this.activeSet);
                if (!set || !set.variants) return [];
                return Object.entries(set.variants).map(([key, label]) => ({ key, label }));
            },

            openModal() {
                this.open = true;
                if (this.icons.length === 0) {
                    this.fetchIcons();
                }
            },

            async fetchIcons(append = false) {
                if (!append) {
                    this.page = 1;
                    this.icons = [];
                }
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        q: this.search,
                        set: this.activeSet || '',
                        variant: this.activeVariant || '',
                        page: this.page,
                    });
                    const response = await fetch(`${@js($searchUrl)}?${params}`);
                    const data = await response.json();
                    if (append) {
                        this.icons = [...this.icons, ...data.items];
                    } else {
                        this.icons = data.items;
                    }
                    this.total = data.total;
                } catch (e) {
                    console.error('Icon search error:', e);
                } finally {
                    this.loading = false;
                }
            },

            onSearchInput() {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.fetchIcons();
                }, 300);
            },

            changeSet(setName) {
                this.activeSet = setName;
                this.activeVariant = '';
                this.fetchIcons();
            },

            changeVariant(variant) {
                this.activeVariant = this.activeVariant === variant ? '' : variant;
                this.fetchIcons();
            },

            loadMore() {
                this.page++;
                this.fetchIcons(true);
            },

            selectIcon(icon) {
                $wire.mountFormComponentAction('{{ $statePath }}', 'selectIcon', {
                    name: icon.name,
                    set: icon.set,
                    variant: icon.variant,
                    label: icon.label,
                });
                this.open = false;
            },

            clearSelection() {
                $wire.mountFormComponentAction('{{ $statePath }}', 'clear');
            },
        }"
        class="w-full"
    >
        {{-- Current state: empty or filled --}}
        @if ($hasCurrent)
            <div style="border: 1px solid rgb(229, 231, 235); border-radius: 8px; overflow: hidden;">
                <div style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                    {{-- Icon preview --}}
                    <div style="width: 40px; height: 40px; background: rgb(243, 244, 246); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if ($currentSvg)
                            <div style="width: 24px; height: 24px; color: rgb(55, 65, 81);">{!! $currentSvg !!}</div>
                        @elseif ($currentName)
                            <div style="width: 24px; height: 24px; color: rgb(55, 65, 81);">
                                @php
                                    try {
                                        $previewSvg = svg($currentName, ['style' => 'width: 24px; height: 24px;'])->toHtml();
                                    } catch (\Throwable $e) {
                                        $previewSvg = null;
                                    }
                                @endphp
                                @if ($previewSvg)
                                    {!! $previewSvg !!}
                                @else
                                    <x-heroicon-o-question-mark-circle style="width: 24px; height: 24px; color: rgb(156, 163, 175);" />
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 13px; font-weight: 500; color: rgb(17, 24, 39); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $currentLabel ?: $currentName }}
                        </div>
                        <div style="font-size: 11px; color: rgb(107, 114, 128); display: flex; align-items: center; gap: 4px;">
                            <span style="background: rgb(243, 244, 246); color: rgb(75, 85, 99); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">{{ $currentSet }}</span>
                            @if ($currentVariant)
                                <span style="background: rgb(219, 234, 254); color: rgb(29, 78, 216); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">{{ $currentVariant }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if (! $isDisabled)
                        <div style="display: flex; gap: 4px;">
                            <button
                                type="button"
                                @click="openModal()"
                                style="padding: 4px 8px; font-size: 12px; color: rgb(59, 130, 246); background: none; border: 1px solid rgb(59, 130, 246); border-radius: 6px; cursor: pointer; outline: none;"
                            >
                                Changer
                            </button>
                            <button
                                type="button"
                                @click="clearSelection()"
                                style="padding: 4px 8px; font-size: 12px; color: rgb(239, 68, 68); background: none; border: 1px solid rgb(239, 68, 68); border-radius: 6px; cursor: pointer; outline: none;"
                            >
                                <x-heroicon-o-x-mark style="width: 14px; height: 14px;" />
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @else
            @if (! $isDisabled)
                <button
                    type="button"
                    @click="openModal()"
                    style="width: 100%; padding: 24px; border: 2px dashed rgb(209, 213, 219); border-radius: 8px; background: rgb(249, 250, 251); cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; color: rgb(107, 114, 128); outline: none; transition: border-color 0.15s;"
                    onmouseover="this.style.borderColor='rgb(59, 130, 246)'"
                    onmouseout="this.style.borderColor='rgb(209, 213, 219)'"
                >
                    <x-heroicon-o-squares-2x2 style="width: 32px; height: 32px;" />
                    <span style="font-size: 13px; font-weight: 500;">Selectionner une icone</span>
                    <span style="font-size: 11px;">Rechercher dans les librairies d'icones</span>
                </button>
            @else
                <div style="width: 100%; padding: 24px; border: 2px dashed rgb(229, 231, 235); border-radius: 8px; background: rgb(249, 250, 251); text-align: center; color: rgb(156, 163, 175); font-size: 13px;">
                    Aucune icone selectionnee
                </div>
            @endif
        @endif

        {{-- Modal --}}
        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 24px;"
            >
                {{-- Backdrop --}}
                <div
                    @click="open = false"
                    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.5);"
                ></div>

                {{-- Modal content --}}
                <div
                    @click.stop
                    style="position: relative; z-index: 1; background: white; border-radius: 12px; width: 100%; max-width: 960px; max-height: 80vh; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); margin: 0 auto; display: flex; flex-direction: column; overflow: hidden;"
                >
                    {{-- Header --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid rgb(229, 231, 235); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                        <h3 style="font-size: 16px; font-weight: 600; color: rgb(17, 24, 39); margin: 0;">
                            Selectionner une icone
                        </h3>
                        <button
                            type="button"
                            @click="open = false"
                            style="padding: 4px; color: rgb(156, 163, 175); cursor: pointer; background: none; border: none; outline: none;"
                        >
                            <x-heroicon-o-x-mark style="width: 20px; height: 20px;" />
                        </button>
                    </div>

                    {{-- Filters bar --}}
                    <div style="padding: 12px 20px; border-bottom: 1px solid rgb(229, 231, 235); display: flex; flex-direction: column; gap: 10px; flex-shrink: 0;">
                        {{-- Search + Set filter --}}
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <div style="flex: 1; position: relative;">
                                <input
                                    type="text"
                                    x-model="search"
                                    @input="onSearchInput()"
                                    placeholder="Rechercher une icone..."
                                    style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid rgb(209, 213, 219); border-radius: 6px; font-size: 13px; outline: none; background: white;"
                                >
                                <div style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: rgb(156, 163, 175);">
                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                </div>
                            </div>

                            {{-- Set dropdown --}}
                            <select
                                x-model="activeSet"
                                @change="changeSet($event.target.value)"
                                style="padding: 8px 12px; border: 1px solid rgb(209, 213, 219); border-radius: 6px; font-size: 13px; outline: none; background: white; min-width: 160px;"
                            >
                                <option value="">Toutes les librairies</option>
                                <template x-for="set in sets" :key="set.name">
                                    <option :value="set.name" x-text="set.label"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Variant pills --}}
                        <div x-show="activeSetVariants.length > 0" x-cloak style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <template x-for="v in activeSetVariants" :key="v.key">
                                <button
                                    type="button"
                                    @click="changeVariant(v.key)"
                                    :style="activeVariant === v.key
                                        ? 'padding: 4px 12px; font-size: 12px; font-weight: 500; color: white; background: rgb(59, 130, 246); border: 1px solid rgb(59, 130, 246); border-radius: 9999px; cursor: pointer; outline: none;'
                                        : 'padding: 4px 12px; font-size: 12px; font-weight: 500; color: rgb(75, 85, 99); background: rgb(243, 244, 246); border: 1px solid rgb(229, 231, 235); border-radius: 9999px; cursor: pointer; outline: none;'"
                                    x-text="v.label"
                                ></button>
                            </template>
                        </div>

                        {{-- Result count --}}
                        <div style="font-size: 11px; color: rgb(107, 114, 128);">
                            <span x-text="total"></span> icone(s) trouvee(s)
                        </div>
                    </div>

                    {{-- Icon grid --}}
                    <div style="flex: 1 1 0%; min-height: 0; overflow-y: auto; padding: 16px 20px;">
                        {{-- Loading state --}}
                        <div x-show="loading && icons.length === 0" x-cloak style="text-align: center; padding: 40px 16px; color: rgb(156, 163, 175);">
                            <svg style="width: 32px; height: 32px; animation: spin 1s linear infinite; margin: 0 auto 8px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <p style="font-size: 13px; margin: 0;">Chargement des icones...</p>
                        </div>

                        {{-- Grid --}}
                        <div x-show="icons.length > 0" style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px;">
                            <template x-for="icon in icons" :key="icon.name">
                                <button
                                    type="button"
                                    @click="selectIcon(icon)"
                                    style="border: 1px solid rgb(229, 231, 235); border-radius: 8px; padding: 10px 4px 6px; cursor: pointer; background: white; text-align: center; outline: none; transition: all 0.15s; display: flex; flex-direction: column; align-items: center; gap: 4px;"
                                    onmouseover="this.style.borderColor='rgb(59, 130, 246)'; this.style.backgroundColor='rgb(239, 246, 255)';"
                                    onmouseout="this.style.borderColor='rgb(229, 231, 235)'; this.style.backgroundColor='white';"
                                >
                                    <div style="width: 24px; height: 24px; color: rgb(55, 65, 81); display: flex; align-items: center; justify-content: center;" x-html="icon.svg_html"></div>
                                    <span
                                        x-text="icon.label"
                                        style="font-size: 9px; color: rgb(107, 114, 128); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block;"
                                    ></span>
                                </button>
                            </template>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="!loading && icons.length === 0" x-cloak style="text-align: center; padding: 40px 16px; color: rgb(156, 163, 175);">
                            <x-heroicon-o-squares-2x2 style="width: 40px; height: 40px; margin: 0 auto 8px;" />
                            <p style="font-size: 13px; margin: 0;">Aucune icone trouvee</p>
                        </div>

                        {{-- Load more --}}
                        <div x-show="hasMore" x-cloak style="text-align: center; padding: 16px 0 4px;">
                            <button
                                type="button"
                                @click="loadMore()"
                                :disabled="loading"
                                style="padding: 8px 20px; background: rgb(243, 244, 246); color: rgb(55, 65, 81); border: 1px solid rgb(209, 213, 219); border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; outline: none;"
                            >
                                <span x-show="!loading">Charger plus</span>
                                <span x-show="loading" x-cloak>Chargement...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
