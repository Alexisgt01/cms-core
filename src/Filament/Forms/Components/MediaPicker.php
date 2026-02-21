<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Storage;
use Alexisgt01\CmsCore\Models\CmsMedia;
use Alexisgt01\CmsCore\Services\MediaService;
use Alexisgt01\CmsCore\Services\UnsplashClient;
use Alexisgt01\CmsCore\ValueObjects\MediaSelection;

class MediaPicker extends Field
{
    protected string $view = 'cms-core::filament.forms.components.media-picker';

    /** @var array<int, string> */
    protected array $acceptedTypes = [];

    protected int $maxUploadSize = 0;

    protected bool $unsplashEnabled = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (self $component, mixed $state): void {
            if ($state instanceof MediaSelection) {
                $component->state($state->toArray());
            } elseif (is_string($state) && $state !== '') {
                $decoded = json_decode($state, true);
                if (is_array($decoded)) {
                    $component->state($decoded);
                }
            }
        });

        $this->dehydrateStateUsing(function (mixed $state): ?MediaSelection {
            if (is_array($state) && ! empty($state['source'])) {
                return MediaSelection::fromArray($state);
            }

            return null;
        });

        $this->registerActions([
            $this->selectFromLibraryAction(),
            $this->uploadFromPickerAction(),
            $this->selectFromUnsplashAction(),
            $this->useUnsplashUrlAction(),
            $this->clearAction(),
        ]);
    }

    /**
     * @param  array<int, string>  $types
     */
    public function acceptedTypes(array $types): static
    {
        $this->acceptedTypes = $types;

        return $this;
    }

    public function maxUploadSize(int $kb): static
    {
        $this->maxUploadSize = $kb;

        return $this;
    }

    public function showUnsplash(bool $show = true): static
    {
        $this->unsplashEnabled = $show;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getAcceptedTypes(): array
    {
        return $this->acceptedTypes ?: config('cms-media.media.accepted_types', []);
    }

    public function getMaxUploadSize(): int
    {
        return $this->maxUploadSize ?: (int) config('cms-media.media.max_upload_size', 10240);
    }

    public function isUnsplashEnabled(): bool
    {
        return $this->unsplashEnabled && (bool) config('cms-media.unsplash.enabled');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CmsMedia>
     */
    public function getLibraryMedia(): \Illuminate\Database\Eloquent\Collection
    {
        return CmsMedia::query()
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();
    }

    protected function selectFromLibraryAction(): Action
    {
        return Action::make('selectFromLibrary')
            ->action(function (array $arguments, self $component): void {
                $media = CmsMedia::findOrFail($arguments['id']);

                $component->state([
                    'source' => 'library',
                    'url' => $media->url,
                    'original_url' => $media->url,
                    'media_id' => $media->id,
                    'alt' => $media->getCustomProperty('alt', ''),
                ]);
            });
    }

    protected function uploadFromPickerAction(): Action
    {
        return Action::make('uploadFromPicker')
            ->modalHeading('Uploader un fichier')
            ->modalSubmitActionLabel('Uploader & Sélectionner')
            ->form(fn (): array => [
                FileUpload::make('file')
                    ->label('Fichier')
                    ->disk('public')
                    ->directory('tmp-uploads')
                    ->acceptedFileTypes(config('cms-media.media.accepted_types', [
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                        'application/pdf',
                    ]))
                    ->maxSize((int) config('cms-media.media.max_upload_size', 10240))
                    ->required(),
            ])
            ->action(function (array $data, self $component): void {
                $tmpPath = $data['file'];
                $fullPath = Storage::disk('public')->path($tmpPath);
                $file = new \Illuminate\Http\UploadedFile(
                    $fullPath,
                    basename($tmpPath),
                    Storage::disk('public')->mimeType($tmpPath),
                    null,
                    true,
                );

                $media = app(MediaService::class)->storeUploadedFile($file);
                Storage::disk('public')->delete($tmpPath);

                $component->state([
                    'source' => 'upload',
                    'url' => $media->url,
                    'original_url' => $media->url,
                    'media_id' => $media->id,
                    'alt' => '',
                ]);
            });
    }

    protected function selectFromUnsplashAction(): Action
    {
        return Action::make('selectFromUnsplash')
            ->action(function (array $arguments, self $component): void {
                $photo = $arguments['photo'] ?? [];

                if (empty($photo['id'])) {
                    return;
                }

                $media = app(UnsplashClient::class)->downloadToLibrary($photo);

                $component->state([
                    'source' => 'unsplash',
                    'url' => $media->url,
                    'original_url' => $media->url,
                    'media_id' => $media->id,
                    'provider' => 'unsplash',
                    'unsplash_id' => $photo['id'],
                    'unsplash_author' => $photo['author'] ?? '',
                    'unsplash_author_url' => $photo['author_url'] ?? '',
                    'alt' => $photo['description'] ?? '',
                ]);
            });
    }

    protected function useUnsplashUrlAction(): Action
    {
        return Action::make('useUnsplashUrl')
            ->action(function (array $arguments, self $component): void {
                $photo = $arguments['photo'] ?? [];

                if (empty($photo['id'])) {
                    return;
                }

                $component->state([
                    'source' => 'unsplash',
                    'url' => $photo['regular'] ?? $photo['full'] ?? '',
                    'original_url' => $photo['full'] ?? $photo['regular'] ?? '',
                    'media_id' => null,
                    'provider' => 'unsplash',
                    'unsplash_id' => $photo['id'],
                    'unsplash_author' => $photo['author'] ?? '',
                    'unsplash_author_url' => $photo['author_url'] ?? '',
                    'alt' => $photo['description'] ?? '',
                ]);
            });
    }

    protected function clearAction(): Action
    {
        return Action::make('clear')
            ->action(function (self $component): void {
                $component->state(null);
            });
    }
}
