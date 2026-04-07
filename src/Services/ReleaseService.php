<?php

namespace Alexisgt01\CmsCore\Services;

use Alexisgt01\CmsCore\Models\UserReleaseView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReleaseService
{
    protected string $releasesPath;

    public function __construct()
    {
        $this->releasesPath = __DIR__.'/../../resources/releases';
    }

    /**
     * @return Collection<int, array{slug: string, version: string, title: string, date: string, content: string}>
     */
    public function all(): Collection
    {
        $files = glob($this->releasesPath.'/*.md');

        if (! $files) {
            return collect();
        }

        return collect($files)
            ->map(fn (string $path) => $this->parseFile($path))
            ->filter()
            ->sortByDesc('date')
            ->values();
    }

    /**
     * @return array{slug: string, version: string, title: string, date: string, content: string}|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * @return Collection<int, array{slug: string, version: string, title: string, date: string, content: string}>
     */
    public function getUnreadReleases(Authenticatable $user): Collection
    {
        $viewedSlugs = UserReleaseView::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->pluck('release_slug')
            ->toArray();

        return $this->all()->filter(
            fn (array $release) => ! in_array($release['slug'], $viewedSlugs, true)
        )->values();
    }

    /**
     * @return array{slug: string, version: string, title: string, date: string, content: string}|null
     */
    public function getLatestUnreadRelease(Authenticatable $user): ?array
    {
        $unread = $this->getUnreadReleases($user);

        return $unread->isNotEmpty() ? $unread->first() : null;
    }

    public function markAllAsRead(Authenticatable $user): void
    {
        $userId = $user->getAuthIdentifier();

        $viewedSlugs = UserReleaseView::query()
            ->where('user_id', $userId)
            ->pluck('release_slug')
            ->toArray();

        $toInsert = $this->all()
            ->filter(fn (array $release) => ! in_array($release['slug'], $viewedSlugs, true))
            ->map(fn (array $release) => [
                'user_id' => $userId,
                'release_slug' => $release['slug'],
                'viewed_at' => now(),
            ])
            ->values()
            ->toArray();

        if (! empty($toInsert)) {
            UserReleaseView::insert($toInsert);
        }
    }

    public function hasUnreadReleases(Authenticatable $user): bool
    {
        $viewedCount = UserReleaseView::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->count();

        return $viewedCount < $this->all()->count();
    }

    /**
     * @return array{slug: string, version: string, title: string, date: string, content: string}|null
     */
    protected function parseFile(string $path): ?array
    {
        $raw = file_get_contents($path);

        if (! $raw) {
            return null;
        }

        $parsed = $this->parseFrontmatter($raw);
        $meta = $parsed['meta'];

        if (! isset($meta['slug'], $meta['version'])) {
            return null;
        }

        return [
            'slug' => $meta['slug'],
            'version' => $meta['version'],
            'title' => $meta['title'] ?? 'Version '.$meta['version'],
            'date' => $meta['date'] ?? '',
            'content' => Str::markdown($parsed['content']),
        ];
    }

    /**
     * @return array{meta: array<string, string>, content: string}
     */
    protected function parseFrontmatter(string $raw): array
    {
        if (! str_starts_with(trim($raw), '---')) {
            return ['meta' => [], 'content' => $raw];
        }

        $parts = preg_split('/^---\s*$/m', $raw, 3);

        if (! $parts || count($parts) < 3) {
            return ['meta' => [], 'content' => $raw];
        }

        $meta = [];

        foreach (explode("\n", trim($parts[1])) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $meta[trim($key)] = trim(trim($value), '"\'');
            }
        }

        return ['meta' => $meta, 'content' => trim($parts[2])];
    }
}
