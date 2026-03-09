<x-filament-panels::page>
    <div class="flex flex-col gap-6 lg:flex-row">
        {{-- Sidebar --}}
        <nav class="w-full shrink-0 lg:w-64">
            <div class="sticky top-4 space-y-1 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Sommaire
                </h3>
                @foreach ($this->getSections() as $section)
                    <button
                        wire:click="setSection('{{ $section['slug'] }}')"
                        @class([
                            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors',
                            'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' => $activeSection === $section['slug'],
                            'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800' => $activeSection !== $section['slug'],
                        ])
                    >
                        <x-filament::icon
                            :icon="$section['icon']"
                            @class([
                                'h-5 w-5 shrink-0',
                                'text-primary-600 dark:text-primary-400' => $activeSection === $section['slug'],
                                'text-gray-400 dark:text-gray-500' => $activeSection !== $section['slug'],
                            ])
                        />
                        <span class="truncate">{{ $section['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- Content --}}
        <div class="min-w-0 flex-1">
            @php
                $content = $this->getActiveContent();
            @endphp

            @if ($content)
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">
                    <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:font-semibold prose-h2:text-xl prose-h3:text-lg prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-img:rounded-lg prose-table:text-sm prose-th:bg-gray-50 dark:prose-th:bg-gray-800 prose-th:px-3 prose-th:py-2 prose-td:px-3 prose-td:py-2">
                        {!! $content['content'] !!}
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 dark:border-gray-700">
                    <x-filament::icon
                        icon="heroicon-o-book-open"
                        class="mb-4 h-12 w-12 text-gray-400 dark:text-gray-500"
                    />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Aucune documentation disponible
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        La documentation sera disponible prochainement.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
