<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Navigation --}}
        <div class="flex items-center justify-between">
            <nav class="flex items-center gap-1 text-sm">
                @if ($currentFolderId)
                    <button
                        wire:click="goBack"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5"
                    >
                        <x-heroicon-m-arrow-left class="h-4 w-4" />
                    </button>
                @endif

                <button
                    wire:click="$set('currentFolderId', null)"
                    class="font-medium {{ $currentFolderId ? 'text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' : 'text-gray-900 dark:text-white' }}"
                >
                    Médiathèque
                </button>

                @foreach ($breadcrumbs as $crumb)
                    <x-heroicon-m-chevron-right class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" />
                    <button
                        wire:click="openFolder({{ $crumb->id }})"
                        class="font-medium truncate max-w-[150px] {{ $loop->last ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                    >
                        {{ $crumb->name }}
                    </button>
                @endforeach
            </nav>

            @if ($media->isNotEmpty())
                <button
                    wire:click="{{ count($selectedMediaIds) > 0 && count($selectedMediaIds) === $media->count() ? 'deselectAllMedia' : 'selectAllMedia' }}"
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    {{ count($selectedMediaIds) > 0 && count($selectedMediaIds) === $media->count() ? 'Tout désélectionner' : 'Tout sélectionner' }}
                    @if (count($selectedMediaIds) > 0)
                        <span class="text-primary-600 dark:text-primary-400">({{ count($selectedMediaIds) }})</span>
                    @endif
                </button>
            @endif
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[180px] max-w-xs">
                <x-heroicon-m-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" style="z-index: 1;" />
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Rechercher..."
                    style="border-radius: 6px; padding: 6px 12px 6px 34px; font-size: 13px; border: 1px solid rgb(209, 213, 219); background: white; outline: none;"
                    class="w-full text-gray-900 dark:text-white dark:bg-white/5 dark:border-white/10 placeholder-gray-400"
                />
            </div>

            <select
                wire:model.live="typeFilter"
                style="border-radius: 6px; padding: 6px 28px 6px 10px; font-size: 13px; border: 1px solid rgb(209, 213, 219); background: white; outline: none; -webkit-appearance: menulist;"
                class="text-gray-700 dark:text-gray-300 dark:bg-white/5 dark:border-white/10"
            >
                <option value="">Tous types</option>
                <option value="images">Images</option>
                <option value="pdf">PDF</option>
                <option value="other">Autres</option>
            </select>

            @if (count($availableTags) > 0)
                <select
                    wire:model.live="tagFilter"
                    style="border-radius: 6px; padding: 6px 28px 6px 10px; font-size: 13px; border: 1px solid rgb(209, 213, 219); background: white; outline: none; -webkit-appearance: menulist;"
                    class="text-gray-700 dark:text-gray-300 dark:bg-white/5 dark:border-white/10"
                >
                    <option value="">Tous tags</option>
                    @foreach ($availableTags as $tag)
                        <option value="{{ $tag }}">{{ $tag }}</option>
                    @endforeach
                </select>
            @endif

            <input
                wire:model.live="dateFrom"
                type="date"
                style="border-radius: 6px; padding: 6px 10px; font-size: 13px; border: 1px solid rgb(209, 213, 219); background: white; outline: none;"
                class="text-gray-700 dark:text-gray-300 dark:bg-white/5 dark:border-white/10"
            />
            <span class="text-gray-300 dark:text-gray-600" style="font-size: 13px;">–</span>
            <input
                wire:model.live="dateTo"
                type="date"
                style="border-radius: 6px; padding: 6px 10px; font-size: 13px; border: 1px solid rgb(209, 213, 219); background: white; outline: none;"
                class="text-gray-700 dark:text-gray-300 dark:bg-white/5 dark:border-white/10"
            />

            @if ($hasActiveFilters)
                <button
                    wire:click="clearFilters"
                    style="border-radius: 6px; padding: 6px 10px; font-size: 12px; border: 1px solid rgb(209, 213, 219); background: white; outline: none;"
                    class="inline-flex items-center gap-1 font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:bg-white/5 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                >
                    <x-heroicon-m-x-mark class="h-3.5 w-3.5" />
                    Effacer
                </button>
            @endif
        </div>

        {{-- Empty state --}}
        @if ($folders->isEmpty() && $media->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                    <x-heroicon-o-photo class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                @if ($hasActiveFilters)
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Aucun résultat</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Aucun fichier ne correspond à ces filtres.</p>
                    <button
                        wire:click="clearFilters"
                        class="mt-3 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        Effacer les filtres
                    </button>
                @else
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Dossier vide</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Uploadez des fichiers ou créez un dossier.</p>
                @endif
            </div>
        @else
            {{-- Folders (compact row, separate from media grid) --}}
            @if ($folders->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($folders as $folder)
                        <div
                            wire:key="folder-{{ $folder->id }}"
                            x-data="{ dragOver: false }"
                            x-on:dragover.prevent="dragOver = true; $event.dataTransfer.dropEffect = 'move';"
                            x-on:dragleave.self="dragOver = false"
                            x-on:drop.prevent="
                                dragOver = false;
                                let mediaId = parseInt($event.dataTransfer.getData('text/plain'));
                                if (mediaId) { $wire.dropMediaOnFolder(mediaId, {{ $folder->id }}); }
                            "
                            wire:click="openFolder({{ $folder->id }})"
                            class="group relative inline-flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all duration-150"
                            :class="dragOver
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 ring-2 ring-primary-500/20'
                                : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:bg-gray-50 dark:hover:bg-white/10 hover:border-gray-300 dark:hover:border-white/20'"
                        >
                            <x-heroicon-s-folder x-show="!dragOver" class="h-4 w-4 text-amber-500 shrink-0" />
                            <x-heroicon-s-folder-open x-show="dragOver" x-cloak class="h-4 w-4 text-primary-500 shrink-0" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-[140px]">{{ $folder->name }}</span>

                            {{-- Folder context menu --}}
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity" x-data="{ open: false }">
                                <button
                                    @click.stop="open = !open"
                                    style="outline: none; padding: 2px; border-radius: 4px;"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <x-heroicon-m-ellipsis-horizontal class="h-4 w-4" />
                                </button>

                                <div
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute left-0 top-full mt-1 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 overflow-hidden"
                                >
                                    @can('edit media')
                                        <button
                                            @click.stop="$wire.mountAction('renameFolder', { id: {{ $folder->id }} }); open = false;"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <x-heroicon-m-pencil class="w-3.5 h-3.5 text-gray-400" />
                                            Renommer
                                        </button>
                                    @endcan
                                    @can('delete media')
                                        <button
                                            @click.stop="$wire.mountAction('deleteFolder', { id: {{ $folder->id }} }); open = false;"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10"
                                        >
                                            <x-heroicon-m-trash class="w-3.5 h-3.5" />
                                            Supprimer
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Media grid --}}
            @if ($media->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @foreach ($media as $item)
                        @php
                            $isSelected = in_array($item->id, $selectedMediaIds);
                            $extension = strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION));
                        @endphp

                        <div
                            wire:key="media-{{ $item->id }}"
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $item->id }}'); $event.dataTransfer.effectAllowed = 'move';"
                            class="group relative rounded-lg border transition-all duration-150
                                {{ $isSelected
                                    ? 'border-primary-500 ring-2 ring-primary-500/20'
                                    : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}
                                bg-white dark:bg-white/5"
                        >
                            {{-- Checkbox (always visible) --}}
                            <div
                                class="absolute z-10"
                                style="top: 8px; left: 8px;"
                                @mousedown.stop
                                @dragstart.stop.prevent
                            >
                                <button
                                    type="button"
                                    wire:click.stop="toggleMediaSelection({{ $item->id }})"
                                    style="width: 20px; height: 20px; border-radius: 4px; border: 2px solid; outline: none; display: flex; align-items: center; justify-content: center;"
                                    class="transition-all duration-100
                                        {{ $isSelected
                                            ? 'bg-primary-500 border-primary-500 text-white'
                                            : 'bg-white/90 dark:bg-gray-800/90 border-gray-300 dark:border-gray-500 hover:border-primary-500' }}"
                                >
                                    @if ($isSelected)
                                        <x-heroicon-m-check class="w-3 h-3" />
                                    @endif
                                </button>
                            </div>

                            {{-- Thumbnail --}}
                            <div
                                wire:click="mountAction('editMedia', { id: {{ $item->id }} })"
                                class="cursor-pointer"
                                style="padding: 6px 6px 0 6px;"
                            >
                                @if (str_starts_with($item->mime_type, 'image/'))
                                    <div class="aspect-square bg-gray-100 dark:bg-white/5 flex items-center justify-center" style="border-radius: 6px; overflow: hidden;">
                                        <img
                                            src="{{ media_url($item->url, ['width' => 400, 'height' => 400, 'resizing_type' => 'fit']) }}"
                                            alt="{{ $item->getCustomProperty('alt', $item->name) }}"
                                            class="w-full h-full object-contain"
                                            loading="lazy"
                                        />
                                    </div>
                                @elseif ($item->mime_type === 'application/pdf')
                                    <div class="aspect-square bg-red-50 dark:bg-red-900/10 flex items-center justify-center" style="border-radius: 6px;">
                                        <x-heroicon-o-document-text class="h-10 w-10 text-red-300 dark:text-red-500/50" />
                                    </div>
                                @else
                                    <div class="aspect-square bg-gray-50 dark:bg-white/5 flex items-center justify-center" style="border-radius: 6px;">
                                        <x-heroicon-o-document class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div
                                wire:click="mountAction('editMedia', { id: {{ $item->id }} })"
                                class="flex items-center gap-2 px-2.5 py-2 border-t border-gray-100 dark:border-white/5 cursor-pointer"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-semibold uppercase px-1 py-px rounded
                                            {{ match($extension) {
                                                'PNG' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                                'JPG', 'JPEG' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                                                'GIF' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
                                                'WEBP' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400',
                                                'SVG' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                                                'PDF' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                                default => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                            } }}">{{ $extension }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $item->human_readable_size }}</span>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div @mousedown.stop @dragstart.stop.prevent x-data="{ open: false }">
                                    <button
                                        type="button"
                                        @click.stop="open = !open"
                                        style="outline: none; padding: 4px; border-radius: 4px;"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10"
                                    >
                                        <x-heroicon-m-ellipsis-horizontal class="h-4 w-4" />
                                    </button>

                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute right-1 bottom-full mb-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 overflow-hidden"
                                    >
                                        <a
                                            href="{{ $item->url }}"
                                            target="_blank"
                                            @click.stop="open = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5 text-gray-400" />
                                            Ouvrir
                                        </a>
                                        <button
                                            @click.stop="$wire.downloadMedia({{ $item->id }}); open = false;"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5 text-gray-400" />
                                            Télécharger
                                        </button>
                                        @if (str_starts_with($item->mime_type, 'image/') && config('cms-media.proxy.url'))
                                            <button
                                                @click.stop="$wire.mountAction('imgproxy', { id: {{ $item->id }} }); open = false;"
                                                style="outline: none;"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                            >
                                                <x-heroicon-m-adjustments-horizontal class="w-3.5 h-3.5 text-gray-400" />
                                                Imgproxy
                                            </button>
                                        @endif
                                        @can('edit media')
                                            <button
                                                @click.stop="$wire.mountAction('moveMedia', { id: {{ $item->id }} }); open = false;"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                            >
                                                <x-heroicon-m-folder-arrow-down class="w-3.5 h-3.5 text-gray-400" />
                                                Déplacer
                                            </button>
                                        @endcan
                                        @can('delete media')
                                            <button
                                                @click.stop="$wire.mountAction('deleteMedia', { id: {{ $item->id }} }); open = false;"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10"
                                            >
                                                <x-heroicon-m-trash class="w-3.5 h-3.5" />
                                                Supprimer
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Infinite scroll sentinel --}}
            @if ($hasMore)
                <div x-intersect="$wire.loadMore()" class="flex justify-center py-6">
                    <x-filament::loading-indicator class="h-5 w-5 text-gray-400" wire:loading wire:target="loadMore" />
                </div>
            @endif
        @endif
    </div>

    {{-- Bulk action bar (sticky instead of fixed to work inside Filament layout) --}}
    @if (count($selectedMediaIds) > 0)
        <div
            style="position: sticky; bottom: 1rem; z-index: 40; margin-top: 1rem;"
            class="flex items-center justify-center"
        >
            <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                    {{ count($selectedMediaIds) }} sélectionné{{ count($selectedMediaIds) > 1 ? 's' : '' }}
                </span>

                <span class="h-5 w-px bg-gray-200 dark:bg-gray-700"></span>

                @can('edit media')
                    <button
                        wire:click="mountAction('bulkMove')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10"
                    >
                        <x-heroicon-m-folder-arrow-down class="w-4 h-4" />
                        Déplacer
                    </button>
                    <button
                        wire:click="mountAction('bulkTag')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10"
                    >
                        <x-heroicon-m-tag class="w-4 h-4" />
                        Taguer
                    </button>
                @endcan

                @can('delete media')
                    <button
                        wire:click="mountAction('bulkDelete')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10"
                    >
                        <x-heroicon-m-trash class="w-4 h-4" />
                        Supprimer
                    </button>
                @endcan

                <span class="h-5 w-px bg-gray-200 dark:bg-gray-700"></span>

                <button
                    wire:click="deselectAllMedia"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10"
                >
                    <x-heroicon-m-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
