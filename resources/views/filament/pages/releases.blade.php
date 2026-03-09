<x-filament-panels::page>
    @php
        $releases = $this->getReleases();
    @endphp

    @if (count($releases) === 0)
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 dark:border-gray-700">
            <x-filament::icon
                icon="heroicon-o-sparkles"
                class="mb-4 h-12 w-12 text-gray-400 dark:text-gray-500"
            />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Aucune nouveaute pour le moment
            </h3>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($releases as $index => $release)
                <div
                    x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }"
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <button
                        x-on:click="open = !open"
                        class="flex w-full items-center justify-between px-6 py-4 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-sm font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                v{{ $release['version'] }}
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $release['title'] }}
                                </h3>
                                @if ($release['date'])
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($release['date'])->translatedFormat('j F Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <x-filament::icon
                            icon="heroicon-m-chevron-down"
                            class="h-5 w-5 text-gray-400 transition-transform duration-200 dark:text-gray-500"
                            x-bind:class="{ 'rotate-180': open }"
                        />
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                    >
                        <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800">
                            <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:font-semibold prose-h2:text-lg prose-h3:text-base prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-ul:my-2 prose-li:my-0.5">
                                {!! $release['content'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
