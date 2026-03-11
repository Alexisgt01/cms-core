<div
    x-data="{
        show: false,
        features: @js($features),
        saving: false,
        save() {
            this.saving = true;
            $wire.save(this.features);
        }
    }"
>
    {{-- Trigger link in sidebar --}}
    <button
        x-on:click="show = true"
        style="display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.5rem 0.75rem; margin-top: 0.5rem; border: none; background: transparent; cursor: pointer; font-size: 0.8125rem; color: rgb(156 163 175); transition: color 0.15s;"
        onmouseover="this.style.color='rgb(107 114 128)'"
        onmouseout="this.style.color='rgb(156 163 175)'"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1rem; height: 1rem; flex-shrink: 0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
        </svg>
        <span>Personnaliser la navigation</span>
    </button>

    {{-- Modal --}}
    <template x-teleport="body">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:keydown.escape.window="show = false"
            style="position: fixed; inset: 0; z-index: 999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); padding: 1rem;"
        >
            <div
                x-on:click.away="show = false"
                x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                style="position: relative; width: 100%; max-width: 36rem; max-height: 85vh; border-radius: 1rem; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; overflow: hidden;"
                class="dark:bg-gray-900"
            >
                {{-- Header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.06); flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem; color: rgb(107 114 128);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <h2 style="font-size: 1rem; font-weight: 600; color: rgb(17 24 39); margin: 0;">
                            Personnaliser la navigation
                        </h2>
                    </div>
                    <button
                        x-on:click="show = false"
                        style="padding: 0.25rem; border: none; background: transparent; cursor: pointer; color: rgb(156 163 175); border-radius: 0.375rem;"
                        onmouseover="this.style.color='rgb(107 114 128)'"
                        onmouseout="this.style.color='rgb(156 163 175)'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div style="overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
                    <p style="font-size: 0.8125rem; color: rgb(107 114 128); margin: 0 0 1.25rem;">
                        Cochez les modules a afficher dans la barre laterale. Les elements essentiels d'un module restent visibles tant que le module est actif.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">

                        {{-- Tableaux de bord --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'dashboards',
                            'label' => 'Tableaux de bord',
                            'children' => [
                                ['key' => 'dashboards_blog', 'label' => 'Dashboard Blog'],
                                ['key' => 'dashboards_admin', 'label' => 'Dashboard Administration'],
                            ],
                        ])

                        {{-- Blog --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'blog',
                            'label' => 'Blog',
                            'mandatory' => 'Articles, Parametres',
                            'children' => [
                                ['key' => 'blog_authors', 'label' => 'Auteurs'],
                                ['key' => 'blog_categories', 'label' => 'Categories'],
                                ['key' => 'blog_tags', 'label' => 'Tags'],
                            ],
                        ])

                        {{-- Medias --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'media',
                            'label' => 'Medias',
                        ])

                        {{-- Pages --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'pages',
                            'label' => 'Pages',
                            'mandatory' => 'Pages',
                            'children' => [
                                ['key' => 'pages_sections', 'label' => 'Catalogue de sections'],
                                ['key' => 'pages_templates', 'label' => 'Modeles de section'],
                            ],
                        ])

                        {{-- SEO --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'seo',
                            'label' => 'SEO / Redirections',
                        ])

                        {{-- Collections --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'collections',
                            'label' => 'Collections',
                        ])

                        {{-- Contact --}}
                        @include('cms-core::livewire.partials.feature-group', [
                            'key' => 'contact',
                            'label' => 'Contact',
                            'mandatory' => 'Contacts, Demandes, Parametres',
                            'children' => [
                                ['key' => 'contact_webhooks', 'label' => 'Webhooks'],
                                ['key' => 'contact_deliveries', 'label' => 'Deliveries'],
                            ],
                        ])

                        {{-- Administration --}}
                        <div style="border: 1px solid rgba(0,0,0,0.08); border-radius: 0.5rem; padding: 0.75rem 1rem;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: rgb(17 24 39); margin-bottom: 0.5rem;">
                                Administration
                            </div>
                            <p style="font-size: 0.75rem; color: rgb(156 163 175); margin: 0 0 0.5rem;">
                                Utilisateurs, Roles et Parametres du site sont toujours visibles.
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 0.375rem; padding-left: 0.25rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: rgb(55 65 81); cursor: pointer;">
                                    <input type="checkbox" x-model="features.administration_permissions" style="accent-color: rgb(var(--primary-600));" />
                                    Permissions
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: rgb(55 65 81); cursor: pointer;">
                                    <input type="checkbox" x-model="features.administration_activity_log" style="accent-color: rgb(var(--primary-600));" />
                                    Journal d'activite
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid rgba(0,0,0,0.06); flex-shrink: 0;">
                    <button
                        x-on:click="show = false"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: rgb(107 114 128); border: 1px solid rgba(0,0,0,0.15); border-radius: 0.5rem; background: white; cursor: pointer;"
                        onmouseover="this.style.background='rgb(249 250 251)'"
                        onmouseout="this.style.background='white'"
                    >
                        Annuler
                    </button>
                    <button
                        x-on:click="save()"
                        x-bind:disabled="saving"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; color: white; border: none; border-radius: 0.5rem; background: rgb(var(--primary-600)); cursor: pointer;"
                        onmouseover="this.style.background='rgb(var(--primary-500))'"
                        onmouseout="this.style.background='rgb(var(--primary-600))'"
                    >
                        <span x-show="!saving">Enregistrer</span>
                        <span x-show="saving">Enregistrement...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
