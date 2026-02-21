<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Alexisgt01\CmsCore\Models\CmsMedia;
use Alexisgt01\CmsCore\Models\CmsMediaFolder;
use Alexisgt01\CmsCore\Services\MediaService;

class MediaLibrary extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Médias';

    protected static ?string $navigationLabel = 'Médiathèque';

    protected static ?string $title = 'Médiathèque';

    protected static ?string $slug = 'media-library';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'cms-core::filament.pages.media-library';

    public ?int $currentFolderId = null;

    /** @var array<int, int> */
    public array $selectedMediaIds = [];

    public string $search = '';

    public string $typeFilter = '';

    public string $tagFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 30;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view media') ?? false;
    }

    // ── Filters lifecycle ───────────────────────────────────────

    public function updatedSearch(): void
    {
        $this->resetPagination();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPagination();
    }

    public function updatedTagFilter(): void
    {
        $this->resetPagination();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPagination();
    }

    public function updatedDateTo(): void
    {
        $this->resetPagination();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->tagFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPagination();
    }

    public function loadMore(): void
    {
        $this->perPage += 30;
    }

    protected function resetPagination(): void
    {
        $this->perPage = 30;
        $this->selectedMediaIds = [];
    }

    protected function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->typeFilter !== ''
            || $this->tagFilter !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }

    // ── Selection ───────────────────────────────────────────────

    public function toggleMediaSelection(int $id): void
    {
        if (in_array($id, $this->selectedMediaIds)) {
            $this->selectedMediaIds = array_values(
                array_filter($this->selectedMediaIds, fn (int $selectedId): bool => $selectedId !== $id)
            );
        } else {
            $this->selectedMediaIds[] = $id;
        }
    }

    public function selectAllMedia(): void
    {
        $contents = app(MediaService::class)->listFolderContents(
            folderId: $this->currentFolderId,
            search: $this->search ?: null,
            typeFilter: $this->typeFilter ?: null,
            tagFilter: $this->tagFilter ?: null,
            dateFrom: $this->dateFrom ?: null,
            dateTo: $this->dateTo ?: null,
            limit: 1000,
        );

        $this->selectedMediaIds = $contents['media']->pluck('id')->all();
    }

    public function deselectAllMedia(): void
    {
        $this->selectedMediaIds = [];
    }

    // ── Navigation ──────────────────────────────────────────────

    public function openFolder(int $id): void
    {
        $this->currentFolderId = $id;
        $this->selectedMediaIds = [];
        $this->clearFilters();
    }

    public function goBack(): void
    {
        if ($this->currentFolderId === null) {
            return;
        }

        $folder = CmsMediaFolder::find($this->currentFolderId);
        $this->currentFolderId = $folder?->parent_id;
        $this->selectedMediaIds = [];
        $this->clearFilters();
    }

    // ── Header Actions ──────────────────────────────────────────

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Uploader')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('files')
                        ->label('Fichiers')
                        ->multiple()
                        ->disk('public')
                        ->directory('tmp-uploads')
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                            'application/pdf',
                        ])
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $service = app(MediaService::class);
                    $count = 0;

                    foreach ($data['files'] as $tmpPath) {
                        $fullPath = Storage::disk('public')->path($tmpPath);
                        $file = new \Illuminate\Http\UploadedFile(
                            $fullPath,
                            basename($tmpPath),
                            Storage::disk('public')->mimeType($tmpPath),
                            null,
                            true,
                        );

                        $service->storeUploadedFile($file, $this->currentFolderId);
                        Storage::disk('public')->delete($tmpPath);
                        $count++;
                    }

                    Notification::make()
                        ->title("{$count} fichier(s) uploadé(s)")
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()?->can('create media')),

            Action::make('createFolder')
                ->label('Nouveau dossier')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->form([
                    TextInput::make('name')
                        ->label('Nom du dossier')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    app(MediaService::class)->createFolder($data['name'], $this->currentFolderId);

                    Notification::make()
                        ->title('Dossier créé')
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()?->can('create media')),
        ];
    }

    // ── Single item actions ─────────────────────────────────────

    public function editMediaAction(): Action
    {
        return Action::make('editMedia')
            ->slideOver()
            ->modalHeading(fn (array $arguments): string => CmsMedia::find($arguments['id'])?->name ?? 'Modifier le média')
            ->modalWidth('md')
            ->modalContent(fn (array $arguments): View => view(
                'cms-core::filament.pages.media-library-preview',
                ['media' => CmsMedia::find($arguments['id'])]
            ))
            ->form([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('alt')
                    ->label('Texte alternatif')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
                TagsInput::make('tags')
                    ->label('Tags'),
                FileUpload::make('replacement')
                    ->label('Remplacer le fichier')
                    ->disk('public')
                    ->directory('tmp-uploads')
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                        'application/pdf',
                    ])
                    ->maxSize(10240)
                    ->helperText('Laissez vide pour conserver le fichier actuel.'),
            ])
            ->fillForm(function (array $arguments): array {
                $media = CmsMedia::findOrFail($arguments['id']);

                return [
                    'name' => $media->name,
                    'alt' => $media->getCustomProperty('alt', ''),
                    'description' => $media->getCustomProperty('description', ''),
                    'tags' => $media->getCustomProperty('tags', []),
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $media = CmsMedia::findOrFail($arguments['id']);
                $this->authorize('update', $media);

                $service = app(MediaService::class);

                if (! empty($data['replacement'])) {
                    $tmpPath = $data['replacement'];
                    $fullPath = Storage::disk('public')->path($tmpPath);
                    $file = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        basename($tmpPath),
                        Storage::disk('public')->mimeType($tmpPath),
                        null,
                        true,
                    );

                    $service->replaceFile($media, $file);
                    Storage::disk('public')->delete($tmpPath);
                }

                $service->updateMediaDetails($media, $data);

                Notification::make()
                    ->title('Média mis à jour')
                    ->success()
                    ->send();
            })
            ->modalSubmitActionLabel('Enregistrer')
            ->visible(fn () => auth()->user()?->can('edit media'));
    }

    public function deleteMediaAction(): Action
    {
        return Action::make('deleteMedia')
            ->requiresConfirmation()
            ->modalHeading('Supprimer le média')
            ->modalDescription('Cette action est irréversible. Voulez-vous continuer ?')
            ->modalSubmitActionLabel('Supprimer')
            ->color('danger')
            ->action(function (array $arguments): void {
                $media = CmsMedia::findOrFail($arguments['id']);
                $this->authorize('delete', $media);

                app(MediaService::class)->deleteMedia($media);

                Notification::make()
                    ->title('Média supprimé')
                    ->success()
                    ->send();
            })
            ->visible(fn () => auth()->user()?->can('delete media'));
    }

    public function moveMediaAction(): Action
    {
        return Action::make('moveMedia')
            ->modalHeading('Déplacer le média')
            ->form([
                Select::make('folder_id')
                    ->label('Dossier de destination')
                    ->options(fn () => CmsMediaFolder::query()->orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Racine')
                    ->nullable(),
            ])
            ->fillForm(function (array $arguments): array {
                $media = CmsMedia::findOrFail($arguments['id']);

                return ['folder_id' => $media->folder_id];
            })
            ->action(function (array $data, array $arguments): void {
                $media = CmsMedia::findOrFail($arguments['id']);
                $this->authorize('update', $media);

                app(MediaService::class)->moveToFolder($media, $data['folder_id']);

                Notification::make()
                    ->title('Média déplacé')
                    ->success()
                    ->send();
            })
            ->visible(fn () => auth()->user()?->can('edit media'));
    }

    public function renameFolderAction(): Action
    {
        return Action::make('renameFolder')
            ->modalHeading('Renommer le dossier')
            ->form([
                TextInput::make('name')
                    ->label('Nom du dossier')
                    ->required()
                    ->maxLength(255),
            ])
            ->fillForm(function (array $arguments): array {
                $folder = CmsMediaFolder::findOrFail($arguments['id']);

                return ['name' => $folder->name];
            })
            ->action(function (array $data, array $arguments): void {
                $folder = CmsMediaFolder::findOrFail($arguments['id']);
                $this->authorize('update', new CmsMedia);

                app(MediaService::class)->renameFolder($folder, $data['name']);

                Notification::make()
                    ->title('Dossier renommé')
                    ->success()
                    ->send();
            })
            ->visible(fn () => auth()->user()?->can('edit media'));
    }

    public function deleteFolderAction(): Action
    {
        return Action::make('deleteFolder')
            ->requiresConfirmation()
            ->modalHeading('Supprimer le dossier')
            ->modalDescription('Cette action est irréversible. Le dossier doit être vide.')
            ->modalSubmitActionLabel('Supprimer')
            ->color('danger')
            ->action(function (array $arguments): void {
                $folder = CmsMediaFolder::findOrFail($arguments['id']);
                $this->authorize('delete', new CmsMedia);

                try {
                    app(MediaService::class)->deleteFolder($folder);

                    Notification::make()
                        ->title('Dossier supprimé')
                        ->success()
                        ->send();
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title('Impossible de supprimer')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn () => auth()->user()?->can('delete media'));
    }

    // ── Bulk actions ────────────────────────────────────────────

    public function bulkDeleteAction(): Action
    {
        return Action::make('bulkDelete')
            ->requiresConfirmation()
            ->modalHeading('Supprimer les médias sélectionnés')
            ->modalDescription(fn (): string => count($this->selectedMediaIds) . ' média(s) seront supprimé(s). Cette action est irréversible.')
            ->modalSubmitActionLabel('Supprimer')
            ->color('danger')
            ->action(function (): void {
                $this->authorize('delete', new CmsMedia);

                $count = app(MediaService::class)->bulkDelete(collect($this->selectedMediaIds));
                $this->selectedMediaIds = [];

                Notification::make()
                    ->title("{$count} média(s) supprimé(s)")
                    ->success()
                    ->send();
            })
            ->visible(fn (): bool => auth()->user()?->can('delete media') && count($this->selectedMediaIds) > 0);
    }

    public function bulkMoveAction(): Action
    {
        return Action::make('bulkMove')
            ->modalHeading('Déplacer les médias sélectionnés')
            ->form([
                Select::make('folder_id')
                    ->label('Dossier de destination')
                    ->options(fn () => CmsMediaFolder::query()->orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Racine')
                    ->nullable(),
            ])
            ->action(function (array $data): void {
                $this->authorize('update', new CmsMedia);

                $count = app(MediaService::class)->bulkMoveToFolder(
                    collect($this->selectedMediaIds),
                    $data['folder_id'],
                );
                $this->selectedMediaIds = [];

                Notification::make()
                    ->title("{$count} média(s) déplacé(s)")
                    ->success()
                    ->send();
            })
            ->visible(fn (): bool => auth()->user()?->can('edit media') && count($this->selectedMediaIds) > 0);
    }

    public function bulkTagAction(): Action
    {
        return Action::make('bulkTag')
            ->modalHeading('Ajouter des tags')
            ->form([
                TagsInput::make('tags')
                    ->label('Tags à ajouter')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $this->authorize('update', new CmsMedia);

                $count = app(MediaService::class)->bulkAddTags(
                    collect($this->selectedMediaIds),
                    $data['tags'],
                );
                $this->selectedMediaIds = [];

                Notification::make()
                    ->title("Tags ajoutés à {$count} média(s)")
                    ->success()
                    ->send();
            })
            ->visible(fn (): bool => auth()->user()?->can('edit media') && count($this->selectedMediaIds) > 0);
    }

    // ── Drag & Drop ─────────────────────────────────────────────

    public function dropMediaOnFolder(int $mediaId, int $folderId): void
    {
        $media = CmsMedia::findOrFail($mediaId);
        $this->authorize('update', $media);

        app(MediaService::class)->moveToFolder($media, $folderId);

        Notification::make()
            ->title('Média déplacé')
            ->success()
            ->send();
    }

    // ── Imgproxy ────────────────────────────────────────────────

    public function imgproxyAction(): Action
    {
        return Action::make('imgproxy')
            ->modalHeading('Consulter avec imgproxy')
            ->modalSubmitActionLabel('Ouvrir')
            ->form([
                TextInput::make('width')
                    ->label('Largeur')
                    ->numeric()
                    ->placeholder('auto'),
                TextInput::make('height')
                    ->label('Hauteur')
                    ->numeric()
                    ->placeholder('auto'),
                Select::make('resizing_type')
                    ->label('Type de redimensionnement')
                    ->options([
                        'fit' => 'Fit (conserver les proportions)',
                        'fill' => 'Fill (remplir et recadrer)',
                        'fill-down' => 'Fill-down (remplir si plus grand)',
                        'force' => 'Force (déformer)',
                        'auto' => 'Auto',
                    ])
                    ->default('fit'),
                Select::make('gravity')
                    ->label('Gravité (point focal)')
                    ->options([
                        'ce' => 'Centre',
                        'no' => 'Nord',
                        'so' => 'Sud',
                        'ea' => 'Est',
                        'we' => 'Ouest',
                        'noea' => 'Nord-Est',
                        'nowe' => 'Nord-Ouest',
                        'soea' => 'Sud-Est',
                        'sowe' => 'Sud-Ouest',
                        'sm' => 'Smart (détection de contenu)',
                    ])
                    ->default('ce'),
                TextInput::make('quality')
                    ->label('Qualité')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->default(80),
                Select::make('format')
                    ->label('Format de sortie')
                    ->options([
                        '' => 'Original',
                        'webp' => 'WebP',
                        'avif' => 'AVIF',
                        'png' => 'PNG',
                        'jpg' => 'JPEG',
                    ])
                    ->default(''),
                TextInput::make('blur')
                    ->label('Flou (sigma)')
                    ->numeric()
                    ->placeholder('0')
                    ->minValue(0),
                TextInput::make('sharpen')
                    ->label('Netteté (sigma)')
                    ->numeric()
                    ->placeholder('0')
                    ->minValue(0),
            ])
            ->action(function (array $data, array $arguments): void {
                $media = CmsMedia::findOrFail($arguments['id']);
                $baseUrl = rtrim((string) config('cms-media.proxy.url'), '/');

                $options = [];

                $width = (int) ($data['width'] ?? 0);
                $height = (int) ($data['height'] ?? 0);
                $resizingType = $data['resizing_type'] ?? 'fit';

                if ($width > 0 || $height > 0) {
                    $options[] = "rs:{$resizingType}:{$width}:{$height}";
                }

                $gravity = $data['gravity'] ?? 'ce';
                if ($gravity !== 'ce') {
                    $options[] = "g:{$gravity}";
                }

                $quality = (int) ($data['quality'] ?? 0);
                if ($quality > 0 && $quality < 100) {
                    $options[] = "q:{$quality}";
                }

                $format = $data['format'] ?? '';
                if ($format !== '') {
                    $options[] = "f:{$format}";
                }

                $blur = (float) ($data['blur'] ?? 0);
                if ($blur > 0) {
                    $options[] = "bl:{$blur}";
                }

                $sharpen = (float) ($data['sharpen'] ?? 0);
                if ($sharpen > 0) {
                    $options[] = "sh:{$sharpen}";
                }

                $optionsPath = $options !== [] ? implode('/', $options) : 'raw:true';
                $sourceUrl = $media->url;

                $url = "{$baseUrl}/unsafe/{$optionsPath}/plain/{$sourceUrl}";

                $this->js("window.open('{$url}', '_blank')");
            })
            ->visible(fn (): bool => config('cms-media.proxy.url') !== null && config('cms-media.proxy.url') !== '');
    }

    // ── Download ────────────────────────────────────────────────

    public function downloadMedia(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $media = CmsMedia::findOrFail($id);
        $this->authorize('view', $media);

        return Storage::disk($media->disk)->download($media->id . '/' . $media->file_name);
    }

    // ── View Data ───────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $service = app(MediaService::class);

        $contents = $service->listFolderContents(
            folderId: $this->currentFolderId,
            search: $this->search ?: null,
            typeFilter: $this->typeFilter ?: null,
            tagFilter: $this->tagFilter ?: null,
            dateFrom: $this->dateFrom ?: null,
            dateTo: $this->dateTo ?: null,
            limit: $this->perPage,
        );

        $breadcrumbs = collect();
        if ($this->currentFolderId) {
            $folder = CmsMediaFolder::find($this->currentFolderId);
            while ($folder) {
                $breadcrumbs->prepend($folder);
                $folder = $folder->parent;
            }
        }

        return [
            'folders' => $contents['folders'],
            'media' => $contents['media'],
            'hasMore' => $contents['hasMore'],
            'breadcrumbs' => $breadcrumbs,
            'availableTags' => $service->getAvailableTags(),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ];
    }
}
