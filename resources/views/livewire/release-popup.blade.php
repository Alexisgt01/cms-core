<div>
    @if ($show && $release)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[999] flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-gray-900"
                x-on:click.away="open = false; $wire.dismiss()"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                            <x-filament::icon
                                icon="heroicon-o-sparkles"
                                class="h-5 w-5 text-primary-600 dark:text-primary-400"
                            />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                Nouveaute — v{{ $release['version'] }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $release['title'] }}
                            </p>
                        </div>
                    </div>
                    <button
                        x-on:click="open = false; $wire.dismiss()"
                        class="rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                    </button>
                </div>

                {{-- Content --}}
                <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
                    <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:font-semibold prose-h2:text-lg prose-h3:text-base prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-ul:my-2 prose-li:my-0.5">
                        {!! $release['content'] !!}
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <a
                        href="{{ \Alexisgt01\CmsCore\Filament\Pages\Releases::getUrl() }}"
                        wire:click="dismiss"
                        class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        Voir toutes les nouveautes
                    </a>
                    <button
                        x-on:click="open = false; $wire.dismiss()"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-500 dark:hover:bg-primary-400"
                    >
                        C'est compris !
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
