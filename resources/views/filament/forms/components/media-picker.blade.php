@php
    $statePath = $getStatePath();
    $state = $getState();
    $isDisabled = $isDisabled();
    $libraryMedia = $field->getLibraryMedia();
    $isUnsplashEnabled = $field->isUnsplashEnabled();

    $hasCurrent = is_array($state) && ! empty($state['source']);
    $currentUrl = $state['url'] ?? '';
    $currentAlt = $state['alt'] ?? '';
    $currentSource = $state['source'] ?? '';
    $currentMediaId = $state['media_id'] ?? null;
    $currentUnsplashAuthor = $state['unsplash_author'] ?? '';
    $currentUnsplashAuthorUrl = $state['unsplash_author_url'] ?? '';

    $isImage = $hasCurrent && (
        str_contains($currentUrl, '.jpg') ||
        str_contains($currentUrl, '.jpeg') ||
        str_contains($currentUrl, '.png') ||
        str_contains($currentUrl, '.gif') ||
        str_contains($currentUrl, '.webp') ||
        str_contains($currentUrl, '.svg') ||
        str_contains($currentUrl, 'images.unsplash.com')
    );
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            open: false,
            tab: 'library',
            librarySearch: '',
            unsplashSearch: '',
            unsplashResults: [],
            unsplashLoading: false,
            unsplashDownloading: null,
            selectedUnsplashPhoto: null,

            get filteredMedia() {
                if (!this.librarySearch) return @js($libraryMedia->toArray());
                const q = this.librarySearch.toLowerCase();
                return @js($libraryMedia->toArray()).filter(m =>
                    (m.name || '').toLowerCase().includes(q) ||
                    (m.file_name || '').toLowerCase().includes(q)
                );
            },

            async searchUnsplash() {
                if (!this.unsplashSearch.trim()) return;
                this.unsplashLoading = true;
                try {
                    const response = await fetch(`{{ route('cms.unsplash.search') }}?query=${encodeURIComponent(this.unsplashSearch)}`);
                    const data = await response.json();
                    this.unsplashResults = data.results || [];
                } catch (e) {
                    this.unsplashResults = [];
                } finally {
                    this.unsplashLoading = false;
                }
            },

            selectFromLibrary(id) {
                $wire.mountFormComponentAction('{{ $statePath }}', 'selectFromLibrary', { id: id });
                this.open = false;
            },

            downloadUnsplash(photo) {
                this.unsplashDownloading = photo.id;
                this.selectedUnsplashPhoto = null;
                $wire.mountFormComponentAction('{{ $statePath }}', 'selectFromUnsplash', { photo: photo });
                this.open = false;
                this.unsplashDownloading = null;
            },

            useUnsplashUrl(photo) {
                this.selectedUnsplashPhoto = null;
                $wire.mountFormComponentAction('{{ $statePath }}', 'useUnsplashUrl', { photo: photo });
                this.open = false;
            },

            openUpload() {
                this.open = false;
                $wire.mountFormComponentAction('{{ $statePath }}', 'uploadFromPicker');
            },

            clearSelection() {
                $wire.mountFormComponentAction('{{ $statePath }}', 'clear');
            },

            isImage(url) {
                return /\.(jpg|jpeg|png|gif|webp|svg)/i.test(url || '');
            },
        }"
        class="w-full"
    >
        {{-- Current state: empty or filled --}}
        @if ($hasCurrent)
            {{-- Filled state --}}
            <div style="border: 1px solid rgb(229, 231, 235); border-radius: 8px; overflow: hidden;">
                @if ($isImage)
                    <div style="height: 160px; background: rgb(243, 244, 246); display: flex; align-items: center; justify-content: center;">
                        <img
                            src="{{ $currentUrl }}"
                            alt="{{ $currentAlt }}"
                            style="max-height: 160px; max-width: 100%; object-fit: contain;"
                        >
                    </div>
                @else
                    <div style="height: 80px; background: rgb(243, 244, 246); display: flex; align-items: center; justify-content: center;">
                        <x-heroicon-o-document style="width: 32px; height: 32px; color: rgb(156, 163, 175);" />
                    </div>
                @endif

                <div style="padding: 8px 12px; border-top: 1px solid rgb(229, 231, 235); display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 13px; font-weight: 500; color: rgb(17, 24, 39); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            @if ($currentMediaId)
                                {{ \Vendor\CmsCore\Models\CmsMedia::find($currentMediaId)?->name ?? 'Média' }}
                            @else
                                Média sélectionné
                            @endif
                        </div>
                        <div style="font-size: 11px; color: rgb(107, 114, 128); display: flex; align-items: center; gap: 4px;">
                            @if ($currentSource === 'unsplash')
                                <span style="background: rgb(219, 234, 254); color: rgb(29, 78, 216); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">Unsplash</span>
                                @if ($currentUnsplashAuthor)
                                    <span>par {{ $currentUnsplashAuthor }}</span>
                                @endif
                            @elseif ($currentSource === 'upload')
                                <span style="background: rgb(220, 252, 231); color: rgb(21, 128, 61); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">Upload</span>
                            @else
                                <span style="background: rgb(243, 244, 246); color: rgb(75, 85, 99); padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 500;">Médiathèque</span>
                            @endif
                        </div>
                    </div>

                    @if (! $isDisabled)
                        <div style="display: flex; gap: 4px;">
                            <button
                                type="button"
                                @click="open = true; tab = 'library'"
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
            {{-- Empty state --}}
            @if (! $isDisabled)
                <button
                    type="button"
                    @click="open = true; tab = 'library'"
                    style="width: 100%; padding: 24px; border: 2px dashed rgb(209, 213, 219); border-radius: 8px; background: rgb(249, 250, 251); cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; color: rgb(107, 114, 128); outline: none; transition: border-color 0.15s;"
                    onmouseover="this.style.borderColor='rgb(59, 130, 246)'"
                    onmouseout="this.style.borderColor='rgb(209, 213, 219)'"
                >
                    <x-heroicon-o-photo style="width: 32px; height: 32px;" />
                    <span style="font-size: 13px; font-weight: 500;">Sélectionner un média</span>
                    <span style="font-size: 11px;">Médiathèque, upload ou Unsplash</span>
                </button>
            @else
                <div style="width: 100%; padding: 24px; border: 2px dashed rgb(229, 231, 235); border-radius: 8px; background: rgb(249, 250, 251); text-align: center; color: rgb(156, 163, 175); font-size: 13px;">
                    Aucun média sélectionné
                </div>
            @endif
        @endif

        {{-- Modal overlay --}}
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
                    style="position: relative; z-index: 1; background: white; border-radius: 12px; width: 100%; max-width: 900px; max-height: 80vh; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); margin: 0 auto; display: flex; flex-direction: column; overflow: hidden;"
                >
                    {{-- Header --}}
                    <div style="padding: 16px 20px; border-bottom: 1px solid rgb(229, 231, 235); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                        <h3 style="font-size: 16px; font-weight: 600; color: rgb(17, 24, 39); margin: 0;">
                            Sélectionner un média
                        </h3>
                        <button
                            type="button"
                            @click="open = false"
                            style="padding: 4px; color: rgb(156, 163, 175); cursor: pointer; background: none; border: none; outline: none;"
                        >
                            <x-heroicon-o-x-mark style="width: 20px; height: 20px;" />
                        </button>
                    </div>

                    {{-- Tabs --}}
                    <div style="padding: 0 20px; border-bottom: 1px solid rgb(229, 231, 235); display: flex; gap: 0; flex-shrink: 0;">
                        <button
                            type="button"
                            @click="tab = 'library'"
                            :style="tab === 'library'
                                ? 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(59, 130, 246); border-bottom: 2px solid rgb(59, 130, 246); background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'
                                : 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(107, 114, 128); border-bottom: 2px solid transparent; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'"
                        >
                            Médiathèque
                        </button>
                        <button
                            type="button"
                            @click="tab = 'upload'"
                            :style="tab === 'upload'
                                ? 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(59, 130, 246); border-bottom: 2px solid rgb(59, 130, 246); background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'
                                : 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(107, 114, 128); border-bottom: 2px solid transparent; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'"
                        >
                            Upload
                        </button>
                        @if ($isUnsplashEnabled)
                            <button
                                type="button"
                                @click="tab = 'unsplash'"
                                :style="tab === 'unsplash'
                                    ? 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(59, 130, 246); border-bottom: 2px solid rgb(59, 130, 246); background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'
                                    : 'padding: 10px 16px; font-size: 13px; font-weight: 500; color: rgb(107, 114, 128); border-bottom: 2px solid transparent; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer; outline: none;'"
                            >
                                Unsplash
                            </button>
                        @endif
                    </div>

                    {{-- Tab content --}}
                    <div style="flex: 1 1 0%; min-height: 0; overflow-y: auto; padding: 16px 20px;">

                        {{-- Library tab --}}
                        <div x-show="tab === 'library'" x-cloak>
                            {{-- Search --}}
                            <div style="margin-bottom: 12px;">
                                <input
                                    type="text"
                                    x-model.debounce.300ms="librarySearch"
                                    placeholder="Rechercher dans la médiathèque..."
                                    style="width: 100%; padding: 8px 12px; border: 1px solid rgb(209, 213, 219); border-radius: 6px; font-size: 13px; outline: none; background: white;"
                                >
                            </div>

                            {{-- Grid --}}
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                                <template x-for="media in filteredMedia" :key="media.id">
                                    <button
                                        type="button"
                                        @click="selectFromLibrary(media.id)"
                                        style="border: 1px solid rgb(229, 231, 235); border-radius: 8px; overflow: hidden; cursor: pointer; background: white; padding: 0; text-align: left; outline: none; transition: box-shadow 0.15s;"
                                        onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'"
                                        onmouseout="this.style.boxShadow='none'"
                                    >
                                        <div style="height: 100px; background: rgb(243, 244, 246); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                            <template x-if="isImage(media.url)">
                                                <img
                                                    :src="media.url"
                                                    :alt="media.name"
                                                    style="max-height: 100px; max-width: 100%; object-fit: contain;"
                                                >
                                            </template>
                                            <template x-if="!isImage(media.url)">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 28px; height: 28px; color: rgb(156, 163, 175);">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <div style="padding: 6px 8px;">
                                            <div
                                                x-text="media.name"
                                                style="font-size: 11px; font-weight: 500; color: rgb(55, 65, 81); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                            ></div>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            {{-- Empty state --}}
                            <template x-if="filteredMedia.length === 0">
                                <div style="text-align: center; padding: 40px 16px; color: rgb(156, 163, 175);">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 40px; height: 40px; margin: 0 auto 8px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                    </svg>
                                    <p style="font-size: 13px; margin: 0;">Aucun média trouvé</p>
                                </div>
                            </template>
                        </div>

                        {{-- Upload tab --}}
                        <div x-show="tab === 'upload'" x-cloak>
                            <div style="text-align: center; padding: 40px 16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: rgb(156, 163, 175); margin: 0 auto 12px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <p style="font-size: 14px; font-weight: 500; color: rgb(55, 65, 81); margin: 0 0 4px;">Uploader un nouveau fichier</p>
                                <p style="font-size: 12px; color: rgb(107, 114, 128); margin: 0 0 16px;">Le fichier sera ajouté à la médiathèque et sélectionné</p>
                                <button
                                    type="button"
                                    @click="openUpload()"
                                    style="padding: 8px 20px; background: rgb(59, 130, 246); color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; outline: none;"
                                >
                                    Choisir un fichier
                                </button>
                            </div>
                        </div>

                        {{-- Unsplash tab --}}
                        @if ($isUnsplashEnabled)
                            <div x-show="tab === 'unsplash'" x-cloak>
                                {{-- Search --}}
                                <div style="margin-bottom: 12px; display: flex; gap: 8px;">
                                    <input
                                        type="text"
                                        x-model="unsplashSearch"
                                        @keydown.enter.prevent="searchUnsplash()"
                                        placeholder="Rechercher sur Unsplash..."
                                        style="flex: 1; padding: 8px 12px; border: 1px solid rgb(209, 213, 219); border-radius: 6px; font-size: 13px; outline: none; background: white;"
                                    >
                                    <button
                                        type="button"
                                        @click="searchUnsplash()"
                                        :disabled="unsplashLoading"
                                        style="padding: 8px 16px; background: rgb(59, 130, 246); color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; outline: none;"
                                    >
                                        <span x-show="!unsplashLoading">Rechercher</span>
                                        <span x-show="unsplashLoading" x-cloak>...</span>
                                    </button>
                                </div>

                                {{-- Results grid --}}
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                                    <template x-for="photo in unsplashResults" :key="photo.id">
                                        <div
                                            style="border: 1px solid rgb(229, 231, 235); border-radius: 8px; overflow: hidden; background: white; position: relative; cursor: pointer;"
                                            @click="selectedUnsplashPhoto = (selectedUnsplashPhoto?.id === photo.id) ? null : photo"
                                        >
                                            <div style="height: 120px; background: rgb(243, 244, 246); overflow: hidden;">
                                                <img
                                                    :src="photo.small"
                                                    :alt="photo.description"
                                                    style="width: 100%; height: 100%; object-fit: cover;"
                                                >
                                            </div>
                                            <div style="padding: 6px 8px;">
                                                <span
                                                    x-text="photo.author"
                                                    style="font-size: 11px; color: rgb(107, 114, 128); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;"
                                                ></span>
                                            </div>

                                            {{-- Selection overlay with two choices --}}
                                            <div
                                                x-show="selectedUnsplashPhoto?.id === photo.id && unsplashDownloading !== photo.id"
                                                x-cloak
                                                @click.stop
                                                style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.6); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 12px;"
                                            >
                                                <button
                                                    type="button"
                                                    @click="downloadUnsplash(photo)"
                                                    style="width: 100%; padding: 7px 10px; background: white; color: rgb(17, 24, 39); border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; outline: none; display: flex; align-items: center; justify-content: center; gap: 6px;"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                    Télécharger en local
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="useUnsplashUrl(photo)"
                                                    style="width: 100%; padding: 7px 10px; background: transparent; color: white; border: 1px solid rgba(255,255,255,0.6); border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; outline: none; display: flex; align-items: center; justify-content: center; gap: 6px;"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-4.503a4.5 4.5 0 0 0-1.242-7.244l4.5-4.5a4.5 4.5 0 0 1 6.364 6.364l-1.757 1.757" /></svg>
                                                    Utiliser l'URL Unsplash
                                                </button>
                                            </div>

                                            {{-- Loading overlay --}}
                                            <div
                                                x-show="unsplashDownloading === photo.id"
                                                x-cloak
                                                style="position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center;"
                                            >
                                                <svg style="width: 24px; height: 24px; animation: spin 1s linear infinite; color: rgb(59, 130, 246);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Empty state --}}
                                <template x-if="unsplashResults.length === 0 && !unsplashLoading">
                                    <div style="text-align: center; padding: 40px 16px; color: rgb(156, 163, 175);">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 40px; height: 40px; margin: 0 auto 8px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                        </svg>
                                        <p style="font-size: 13px; margin: 0;">Recherchez des images sur Unsplash</p>
                                    </div>
                                </template>

                                {{-- Unsplash attribution --}}
                                <div style="margin-top: 12px; text-align: center;">
                                    <span style="font-size: 10px; color: rgb(156, 163, 175);">
                                        Photos fournies par <a href="https://unsplash.com" target="_blank" rel="noopener" style="color: rgb(107, 114, 128); text-decoration: underline;">Unsplash</a>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
