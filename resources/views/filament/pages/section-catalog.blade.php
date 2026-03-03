<x-filament-panels::page>
    @php
        $types = $this->getSectionTypes();
    @endphp

    @if (count($types) === 0)
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 dark:border-gray-700">
            <x-filament::icon
                icon="heroicon-o-squares-2x2"
                class="mb-4 h-12 w-12 text-gray-400 dark:text-gray-500"
            />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Aucune section enregistree
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Enregistrez des types de section dans la configuration pour commencer.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($types as $type)
                <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                            <x-filament::icon
                                :icon="$type['icon']"
                                class="h-5 w-5 text-primary-600 dark:text-primary-400"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $type['label'] }}
                            </h3>
                            @if ($type['description'])
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $type['description'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            {{ $type['fields_count'] }} {{ $type['fields_count'] > 1 ? 'champs' : 'champ' }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            {{ $type['templates_count'] }} {{ $type['templates_count'] > 1 ? 'modeles' : 'modele' }}
                        </span>
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                        <a
                            href="{{ $type['create_template_url'] }}"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                        >
                            <x-filament::icon
                                icon="heroicon-m-plus"
                                class="h-4 w-4"
                            />
                            Creer un modele
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
