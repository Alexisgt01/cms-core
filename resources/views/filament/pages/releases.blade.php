<x-filament-panels::page>
    <style>
        .cms-markdown h2 { font-size: 1.15rem; font-weight: 600; margin: 1.25rem 0 0.5rem; line-height: 1.3; }
        .cms-markdown h3 { font-size: 1rem; font-weight: 600; margin: 1rem 0 0.5rem; line-height: 1.3; }
        .cms-markdown p { margin: 0.5rem 0; line-height: 1.65; }
        .cms-markdown ul, .cms-markdown ol { margin: 0.5rem 0; padding-left: 1.5rem; }
        .cms-markdown ul { list-style-type: disc; }
        .cms-markdown ol { list-style-type: decimal; }
        .cms-markdown li { margin: 0.25rem 0; line-height: 1.5; }
        .cms-markdown a { color: rgb(var(--primary-600)); text-decoration: underline; }
        .cms-markdown strong { font-weight: 600; }
        .cms-markdown code { font-size: 0.875em; background: rgba(0,0,0,0.05); padding: 0.15rem 0.35rem; border-radius: 0.25rem; }
        .cms-markdown hr { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 1rem 0; }
        .dark .cms-markdown a { color: rgb(var(--primary-400)); }
        .dark .cms-markdown code { background: rgba(255,255,255,0.1); }
        .dark .cms-markdown hr { border-top-color: rgba(255,255,255,0.1); }
    </style>

    @php
        $releases = $this->getReleases();
    @endphp

    @if (count($releases) === 0)
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; border: 2px dashed rgba(0,0,0,0.15); border-radius: 0.75rem;">
            <x-filament::icon
                icon="heroicon-o-sparkles"
                style="width: 3rem; height: 3rem; color: rgb(156 163 175); margin-bottom: 1rem;"
            />
            <h3 style="font-size: 1.125rem; font-weight: 500; color: rgb(17 24 39);">
                Aucune nouveaute pour le moment
            </h3>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach ($releases as $index => $release)
                <div
                    x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }"
                    class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    style="overflow: hidden;"
                >
                    <button
                        x-on:click="open = !open"
                        style="display: flex; width: 100%; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; text-align: left; border: none; cursor: pointer; background: transparent; transition: background-color 0.15s;"
                        onmouseover="this.style.background='rgba(0,0,0,0.02)'"
                        onmouseout="this.style.background='transparent'"
                    >
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="display: inline-flex; align-items: center; border-radius: 9999px; background: rgb(var(--primary-50)); padding: 0.25rem 0.75rem; font-size: 0.875rem; font-weight: 700; color: rgb(var(--primary-700));">
                                v{{ $release['version'] }}
                            </span>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 600; color: rgb(17 24 39); margin: 0;">
                                    {{ $release['title'] }}
                                </h3>
                                @if ($release['date'])
                                    <p style="margin: 0.125rem 0 0; font-size: 0.75rem; color: rgb(107 114 128);">
                                        {{ \Carbon\Carbon::parse($release['date'])->translatedFormat('j F Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <x-filament::icon
                            icon="heroicon-m-chevron-down"
                            style="width: 1.25rem; height: 1.25rem; color: rgb(156 163 175); transition: transform 0.2s;"
                            x-bind:style="open ? 'transform: rotate(180deg)' : ''"
                        />
                    </button>

                    <div
                        x-show="open"
                        x-collapse
                    >
                        <div style="border-top: 1px solid rgba(0,0,0,0.05); padding: 1.25rem 1.5rem;">
                            <div class="cms-markdown" style="color: rgb(55 65 81);">
                                {!! $release['content'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
