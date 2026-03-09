<x-filament-panels::page>
    <style>
        .cms-docs-layout { display: flex; flex-direction: column; gap: 1.5rem; }
        @media (min-width: 1024px) {
            .cms-docs-layout { flex-direction: row; }
            .cms-docs-sidebar { width: 16rem; flex-shrink: 0; }
        }
        .cms-docs-sidebar-inner { position: sticky; top: 1rem; }
        .cms-docs-content-area { min-width: 0; flex: 1; }

        .cms-markdown h2 { font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 0.75rem; line-height: 1.3; }
        .cms-markdown h3 { font-size: 1.1rem; font-weight: 600; margin: 1.25rem 0 0.5rem; line-height: 1.3; }
        .cms-markdown h4 { font-size: 1rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .cms-markdown p { margin: 0.5rem 0; line-height: 1.65; }
        .cms-markdown ul, .cms-markdown ol { margin: 0.5rem 0; padding-left: 1.5rem; }
        .cms-markdown ul { list-style-type: disc; }
        .cms-markdown ol { list-style-type: decimal; }
        .cms-markdown li { margin: 0.25rem 0; line-height: 1.5; }
        .cms-markdown li > ul, .cms-markdown li > ol { margin: 0.25rem 0; }
        .cms-markdown a { color: rgb(var(--primary-600)); text-decoration: underline; }
        .cms-markdown strong { font-weight: 600; }
        .cms-markdown em { font-style: italic; }
        .cms-markdown code { font-size: 0.875em; background: rgba(0,0,0,0.05); padding: 0.15rem 0.35rem; border-radius: 0.25rem; }
        .cms-markdown pre { background: rgba(0,0,0,0.05); padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 0.75rem 0; }
        .cms-markdown pre code { background: none; padding: 0; }
        .cms-markdown blockquote { border-left: 3px solid rgba(0,0,0,0.15); padding-left: 1rem; margin: 0.75rem 0; font-style: italic; }
        .cms-markdown hr { border: none; border-top: 1px solid rgba(0,0,0,0.1); margin: 1.5rem 0; }
        .cms-markdown table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; font-size: 0.875rem; }
        .cms-markdown th { text-align: left; padding: 0.5rem 0.75rem; font-weight: 600; border-bottom: 2px solid rgba(0,0,0,0.1); }
        .cms-markdown td { padding: 0.5rem 0.75rem; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .cms-markdown img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.75rem 0; }

        .dark .cms-markdown a { color: rgb(var(--primary-400)); }
        .dark .cms-markdown code { background: rgba(255,255,255,0.1); }
        .dark .cms-markdown pre { background: rgba(255,255,255,0.05); }
        .dark .cms-markdown blockquote { border-left-color: rgba(255,255,255,0.2); }
        .dark .cms-markdown hr { border-top-color: rgba(255,255,255,0.1); }
        .dark .cms-markdown th { border-bottom-color: rgba(255,255,255,0.15); }
        .dark .cms-markdown td { border-bottom-color: rgba(255,255,255,0.05); }
    </style>

    <div class="cms-docs-layout">
        {{-- Sidebar --}}
        <nav class="cms-docs-sidebar">
            <div class="cms-docs-sidebar-inner fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 style="margin-bottom: 0.75rem; padding: 0 0.75rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: rgb(107 114 128);">
                    Sommaire
                </h3>
                @foreach ($this->getSections() as $section)
                    <button
                        wire:click="setSection('{{ $section['slug'] }}')"
                        style="display: flex; width: 100%; align-items: center; gap: 0.75rem; border-radius: 0.5rem; padding: 0.5rem 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer; transition: background-color 0.15s;
                            {{ $activeSection === $section['slug']
                                ? 'background: rgb(var(--primary-50)); color: rgb(var(--primary-700));'
                                : 'background: transparent; color: rgb(55 65 81);' }}"
                        onmouseover="this.style.background='{{ $activeSection === $section['slug'] ? 'rgb(var(--primary-50))' : 'rgba(0,0,0,0.03)' }}'"
                        onmouseout="this.style.background='{{ $activeSection === $section['slug'] ? 'rgb(var(--primary-50))' : 'transparent' }}'"
                    >
                        <x-filament::icon
                            :icon="$section['icon']"
                            style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; {{ $activeSection === $section['slug'] ? 'color: rgb(var(--primary-600));' : 'color: rgb(156 163 175);' }}"
                        />
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $section['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- Content --}}
        <div class="cms-docs-content-area">
            @php
                $content = $this->getActiveContent();
            @endphp

            @if ($content)
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="padding: 1.5rem 2rem;">
                    <div class="cms-markdown" style="color: rgb(17 24 39);">
                        {!! $content['content'] !!}
                    </div>
                </div>
            @else
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; border: 2px dashed rgba(0,0,0,0.15); border-radius: 0.75rem;">
                    <x-filament::icon
                        icon="heroicon-o-book-open"
                        style="width: 3rem; height: 3rem; color: rgb(156 163 175); margin-bottom: 1rem;"
                    />
                    <h3 style="font-size: 1.125rem; font-weight: 500; color: rgb(17 24 39);">
                        Aucune documentation disponible
                    </h3>
                    <p style="margin-top: 0.25rem; font-size: 0.875rem; color: rgb(107 114 128);">
                        La documentation sera disponible prochainement.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
