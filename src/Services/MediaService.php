<?php

namespace Alexisgt01\CmsCore\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Alexisgt01\CmsCore\Models\CmsMedia;
use Alexisgt01\CmsCore\Models\CmsMediaFolder;

class MediaService
{
    public function storeUploadedFile(UploadedFile $file, ?int $folderId = null): CmsMedia
    {
        $media = CmsMedia::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'conversions_disk' => 'public',
            'collection_name' => 'default',
            'model_type' => 'standalone',
            'model_id' => 0,
            'size' => 0,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'folder_id' => $folderId,
        ]);

        $file->storeAs((string) $media->id, $file->getClientOriginalName(), 'public');

        $media->update([
            'model_id' => $media->id,
            'size' => $file->getSize(),
        ]);

        return $media->fresh();
    }

    public function moveToFolder(CmsMedia $media, ?int $folderId): void
    {
        $media->update(['folder_id' => $folderId]);
    }

    public function renameMedia(CmsMedia $media, string $newName): void
    {
        $media->update(['name' => $newName]);
    }

    /**
     * @param  array{name: string, alt: ?string, description: ?string, tags: array<int, string>}  $data
     */
    public function updateMediaDetails(CmsMedia $media, array $data): void
    {
        $media->name = $data['name'];
        $media->setCustomProperty('alt', $data['alt'] ?? '');
        $media->setCustomProperty('description', $data['description'] ?? '');
        $media->setCustomProperty('tags', $data['tags'] ?? []);
        $media->save();
    }

    public function replaceFile(CmsMedia $media, UploadedFile $file): void
    {
        Storage::disk($media->disk)->deleteDirectory((string) $media->id);

        $file->storeAs((string) $media->id, $file->getClientOriginalName(), 'public');

        $media->update([
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function deleteMedia(CmsMedia $media): void
    {
        Storage::disk($media->disk)->deleteDirectory((string) $media->id);

        $media->delete();
    }

    /**
     * @param  Collection<int, int>  $ids
     */
    public function bulkDelete(Collection $ids): int
    {
        $mediaItems = CmsMedia::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($mediaItems as $media) {
            $this->deleteMedia($media);
            $count++;
        }

        return $count;
    }

    /**
     * @param  Collection<int, int>  $ids
     */
    public function bulkMoveToFolder(Collection $ids, ?int $folderId): int
    {
        $mediaItems = CmsMedia::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($mediaItems as $media) {
            $this->moveToFolder($media, $folderId);
            $count++;
        }

        return $count;
    }

    /**
     * @param  Collection<int, int>  $ids
     * @param  array<int, string>  $tags
     */
    public function bulkAddTags(Collection $ids, array $tags): int
    {
        $mediaItems = CmsMedia::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($mediaItems as $media) {
            $existingTags = $media->getCustomProperty('tags', []);
            $mergedTags = array_values(array_unique(array_merge($existingTags, $tags)));
            $media->setCustomProperty('tags', $mergedTags);
            $media->save();
            $count++;
        }

        return $count;
    }

    /**
     * @return array{folders: \Illuminate\Database\Eloquent\Collection, media: \Illuminate\Database\Eloquent\Collection, hasMore: bool}
     */
    public function listFolderContents(
        ?int $folderId = null,
        ?string $search = null,
        ?string $typeFilter = null,
        ?string $tagFilter = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $limit = 30,
    ): array {
        $folders = CmsMediaFolder::query()
            ->where('parent_id', $folderId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $mediaQuery = CmsMedia::query()
            ->where('folder_id', $folderId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($typeFilter === 'images', fn ($q) => $q->where('mime_type', 'like', 'image/%'))
            ->when($typeFilter === 'pdf', fn ($q) => $q->where('mime_type', 'application/pdf'))
            ->when($typeFilter === 'other', fn ($q) => $q->where('mime_type', 'not like', 'image/%')->where('mime_type', '!=', 'application/pdf'))
            ->when($tagFilter, fn ($q, $tag) => $q->where('custom_properties', 'like', "%\"{$tag}\"%"))
            ->when($dateFrom, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($dateTo, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at');

        $media = $mediaQuery->limit($limit + 1)->get();
        $hasMore = $media->count() > $limit;

        return [
            'folders' => $folders,
            'media' => $media->take($limit),
            'hasMore' => $hasMore,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableTags(): array
    {
        return CmsMedia::query()
            ->whereNotNull('custom_properties')
            ->get()
            ->flatMap(fn (CmsMedia $media) => $media->getCustomProperty('tags', []))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function createFolder(string $name, ?int $parentId = null): CmsMediaFolder
    {
        return CmsMediaFolder::create([
            'name' => $name,
            'parent_id' => $parentId,
        ]);
    }

    public function renameFolder(CmsMediaFolder $folder, string $name): void
    {
        $folder->update(['name' => $name]);
    }

    public function deleteFolder(CmsMediaFolder $folder): void
    {
        if ($folder->media()->exists() || $folder->children()->exists()) {
            throw new \RuntimeException('Cannot delete a folder that is not empty.');
        }

        $folder->delete();
    }
}
