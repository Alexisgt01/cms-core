<div>
    @if ($show && $release)
        <style>
            .cms-popup-markdown h2 { font-size: 1.15rem; font-weight: 600; margin: 1.25rem 0 0.5rem; line-height: 1.3; }
            .cms-popup-markdown h3 { font-size: 1rem; font-weight: 600; margin: 1rem 0 0.5rem; line-height: 1.3; }
            .cms-popup-markdown p { margin: 0.5rem 0; line-height: 1.65; }
            .cms-popup-markdown ul, .cms-popup-markdown ol { margin: 0.5rem 0; padding-left: 1.5rem; }
            .cms-popup-markdown ul { list-style-type: disc; }
            .cms-popup-markdown ol { list-style-type: decimal; }
            .cms-popup-markdown li { margin: 0.25rem 0; line-height: 1.5; }
            .cms-popup-markdown a { color: rgb(var(--primary-600)); text-decoration: underline; }
            .cms-popup-markdown strong { font-weight: 600; }
            .cms-popup-markdown code { font-size: 0.875em; background: rgba(0,0,0,0.05); padding: 0.15rem 0.35rem; border-radius: 0.25rem; }
            .cms-popup-markdown hr { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 1rem 0; }
            .dark .cms-popup-markdown a { color: rgb(var(--primary-400)); }
            .dark .cms-popup-markdown code { background: rgba(255,255,255,0.1); }
        </style>

        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="position: fixed; inset: 0; z-index: 999; display: flex; align-items: center; justify-content: center; overflow-y: auto; background: rgba(0,0,0,0.5); padding: 1rem; backdrop-filter: blur(4px);"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translateY(1rem)"
                x-transition:enter-end="opacity-100 transform translateY(0)"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="position: relative; width: 100%; max-width: 42rem; border-radius: 1rem; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);"
                class="dark:bg-gray-900"
                x-on:click.away="open = false; $wire.dismiss()"
            >
                {{-- Header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1rem 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="display: flex; width: 2.5rem; height: 2.5rem; align-items: center; justify-content: center; border-radius: 9999px; background: rgb(var(--primary-50));">
                            <x-filament::icon
                                icon="heroicon-o-sparkles"
                                style="width: 1.25rem; height: 1.25rem; color: rgb(var(--primary-600));"
                            />
                        </div>
                        <div>
                            <h2 style="font-size: 1.125rem; font-weight: 700; color: rgb(17 24 39); margin: 0;">
                                Nouveaute — v{{ $release['version'] }}
                            </h2>
                            <p style="font-size: 0.875rem; color: rgb(107 114 128); margin: 0;">
                                {{ $release['title'] }}
                            </p>
                        </div>
                    </div>
                    <button
                        x-on:click="open = false; $wire.dismiss()"
                        style="border-radius: 0.5rem; padding: 0.25rem; color: rgb(156 163 175); border: none; cursor: pointer; background: transparent; transition: background-color 0.15s;"
                        onmouseover="this.style.background='rgba(0,0,0,0.05)'"
                        onmouseout="this.style.background='transparent'"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" style="width: 1.25rem; height: 1.25rem;" />
                    </button>
                </div>

                {{-- Content --}}
                <div style="max-height: 60vh; overflow-y: auto; padding: 1.25rem 1.5rem;">
                    <div class="cms-popup-markdown" style="color: rgb(55 65 81);">
                        {!! $release['content'] !!}
                    </div>
                </div>

                {{-- Footer --}}
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(0,0,0,0.05); padding: 1rem 1.5rem;">
                    <a
                        href="{{ \Alexisgt01\CmsCore\Filament\Pages\Releases::getUrl() }}"
                        wire:click="dismiss"
                        style="font-size: 0.875rem; font-weight: 500; color: rgb(var(--primary-600)); text-decoration: none;"
                        onmouseover="this.style.textDecoration='underline'"
                        onmouseout="this.style.textDecoration='none'"
                    >
                        Voir toutes les nouveautes
                    </a>
                    <button
                        x-on:click="open = false; $wire.dismiss()"
                        style="display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; background: rgb(var(--primary-600)); padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: white; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background-color 0.15s;"
                        onmouseover="this.style.background='rgb(var(--primary-500))'"
                        onmouseout="this.style.background='rgb(var(--primary-600))'"
                    >
                        C'est compris !
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
