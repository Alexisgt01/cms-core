<?php

namespace Alexisgt01\CmsCore\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentationService
{
    protected string $docsPath;

    public function __construct()
    {
        $this->docsPath = __DIR__.'/../../resources/docs';
    }

    /**
     * @return Collection<int, array{slug: string, title: string, icon: string, order: int, content: string}>
     */
    public function all(): Collection
    {
        $files = glob($this->docsPath.'/*.md');

        if (! $files) {
            return collect();
        }

        return collect($files)
            ->map(fn (string $path) => $this->parseFile($path))
            ->filter()
            ->sortBy('order')
            ->values();
    }

    /**
     * @return array{slug: string, title: string, icon: string, order: int, content: string}|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * @return array{slug: string, title: string, icon: string, order: int, content: string}|null
     */
    protected function parseFile(string $path): ?array
    {
        $raw = file_get_contents($path);

        if (! $raw) {
            return null;
        }

        $parsed = $this->parseFrontmatter($raw);
        $meta = $parsed['meta'];

        if (! isset($meta['title'])) {
            return null;
        }

        $slug = Str::slug(pathinfo($path, PATHINFO_FILENAME));

        return [
            'slug' => $slug,
            'title' => $meta['title'],
            'icon' => $meta['icon'] ?? 'heroicon-o-document-text',
            'order' => (int) ($meta['order'] ?? 99),
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
