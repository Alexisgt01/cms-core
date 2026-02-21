<?php

namespace Vendor\CmsCore\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Vendor\CmsCore\Models\CmsMedia;

class UnsplashClient
{
    protected string $baseUrl = 'https://api.unsplash.com';

    /**
     * @return array{results: array<int, array<string, mixed>>, total: int, total_pages: int}
     */
    public function search(string $query, int $page = 1, int $perPage = 24): array
    {
        $cacheKey = 'unsplash_search_' . md5("{$query}_{$page}_{$perPage}");

        return Cache::remember($cacheKey, 300, function () use ($query, $page, $perPage): array {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . config('cms-media.unsplash.access_key'),
            ])->get("{$this->baseUrl}/search/photos", [
                'query' => $query,
                'page' => $page,
                'per_page' => $perPage,
                'orientation' => 'landscape',
            ]);

            if (! $response->successful()) {
                return ['results' => [], 'total' => 0, 'total_pages' => 0];
            }

            $data = $response->json();

            return [
                'results' => collect($data['results'] ?? [])->map(fn (array $photo): array => [
                    'id' => $photo['id'],
                    'description' => $photo['description'] ?? $photo['alt_description'] ?? '',
                    'thumb' => $photo['urls']['thumb'] ?? '',
                    'small' => $photo['urls']['small'] ?? '',
                    'regular' => $photo['urls']['regular'] ?? '',
                    'full' => $photo['urls']['full'] ?? '',
                    'download_location' => $photo['links']['download_location'] ?? '',
                    'author' => $photo['user']['name'] ?? '',
                    'author_url' => $photo['user']['links']['html'] ?? '',
                    'width' => $photo['width'] ?? 0,
                    'height' => $photo['height'] ?? 0,
                ])->all(),
                'total' => $data['total'] ?? 0,
                'total_pages' => $data['total_pages'] ?? 0,
            ];
        });
    }

    public function downloadToLibrary(array $photo, ?int $folderId = null): CmsMedia
    {
        $this->triggerDownload($photo['download_location']);

        $imageUrl = $photo['full'] ?: $photo['regular'];
        $imageContent = Http::get($imageUrl)->body();

        $extension = 'jpg';
        $fileName = ($photo['id'] ?? 'unsplash') . '.' . $extension;

        $tmpPath = 'tmp-uploads/' . $fileName;
        Storage::disk('public')->put($tmpPath, $imageContent);

        $fullPath = Storage::disk('public')->path($tmpPath);
        $file = new UploadedFile($fullPath, $fileName, 'image/jpeg', null, true);

        $media = app(MediaService::class)->storeUploadedFile($file, $folderId);

        $media->setCustomProperty('unsplash_id', $photo['id'] ?? '');
        $media->setCustomProperty('unsplash_author', $photo['author'] ?? '');
        $media->setCustomProperty('unsplash_author_url', $photo['author_url'] ?? '');
        $media->setCustomProperty('alt', $photo['description'] ?? '');
        $media->save();

        Storage::disk('public')->delete($tmpPath);

        return $media->fresh();
    }

    protected function triggerDownload(string $downloadLocation): void
    {
        if ($downloadLocation === '') {
            return;
        }

        Http::withHeaders([
            'Authorization' => 'Client-ID ' . config('cms-media.unsplash.access_key'),
        ])->get($downloadLocation);
    }
}
