<?php

namespace Alexisgt01\CmsCore\Services;

use BladeUI\Icons\Factory as BladeIconsFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class IconDiscoveryService
{
    public function __construct(
        protected BladeIconsFactory $factory,
    ) {}

    /**
     * @return array<int, array{name: string, prefix: string, label: string, variants: array<string, string>}>
     */
    public function getAvailableSets(): array
    {
        $sets = $this->factory->all();
        $allowedSets = config('cms-icons.sets');
        $labels = config('cms-icons.labels', []);
        $variantsConfig = config('cms-icons.variants', []);

        $result = [];

        foreach ($sets as $name => $options) {
            if (is_array($allowedSets) && ! in_array($name, $allowedSets, true)) {
                continue;
            }

            $result[] = [
                'name' => $name,
                'prefix' => $options['prefix'] ?? $name,
                'label' => $labels[$name] ?? ucfirst($name),
                'variants' => $variantsConfig[$name] ?? [],
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, string>|null  $allowedSets
     * @param  array<int, string>|null  $disallowedSets
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function searchIcons(
        string $query = '',
        string $set = '',
        string $variant = '',
        int $page = 1,
        int $perPage = 60,
        ?array $allowedSets = null,
        ?array $disallowedSets = null,
    ): array {
        $manifest = $this->getManifest();

        $filtered = $manifest;

        if ($allowedSets !== null) {
            $filtered = array_values(array_filter($filtered, fn (array $icon): bool => in_array($icon['set'], $allowedSets, true)));
        }

        if ($disallowedSets !== null) {
            $filtered = array_values(array_filter($filtered, fn (array $icon): bool => ! in_array($icon['set'], $disallowedSets, true)));
        }

        if ($set !== '') {
            $filtered = array_values(array_filter($filtered, fn (array $icon): bool => $icon['set'] === $set));
        }

        if ($variant !== '') {
            $filtered = array_values(array_filter($filtered, fn (array $icon): bool => $icon['variant'] === $variant));
        }

        if ($query !== '') {
            $filtered = array_values(array_filter($filtered, fn (array $icon): bool => Str::contains($icon['label'], $query, true)));
        }

        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($filtered, $offset, $perPage);

        $items = array_map(function (array $icon): array {
            $icon['svg_html'] = $this->getSvgContent($icon['name']);

            return $icon;
        }, $pageItems);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function getSvgContent(string $iconName): string
    {
        try {
            return svg($iconName, ['style' => 'width: 100%; height: 100%;'])->toHtml();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @return array<int, array{name: string, set: string, prefix: string, variant: string|null, label: string}>
     */
    protected function getManifest(): array
    {
        $ttl = (int) config('cms-icons.cache_ttl', 3600);

        if ($ttl > 0) {
            return Cache::remember('cms_icons_manifest', $ttl, fn (): array => $this->buildManifest());
        }

        return $this->buildManifest();
    }

    /**
     * @return array<int, array{name: string, set: string, prefix: string, variant: string|null, label: string}>
     */
    protected function buildManifest(): array
    {
        $sets = $this->factory->all();
        $allowedSets = config('cms-icons.sets');
        $variantsConfig = config('cms-icons.variants', []);
        $manifest = [];

        foreach ($sets as $setName => $options) {
            if (is_array($allowedSets) && ! in_array($setName, $allowedSets, true)) {
                continue;
            }

            $prefix = $options['prefix'] ?? $setName;
            $setVariants = $variantsConfig[$setName] ?? [];
            $variantKeys = array_keys($setVariants);

            foreach ($options['paths'] as $path) {
                $files = $this->getIconFilesFromPath($path);

                foreach ($files as $iconFile) {
                    $canonicalName = $prefix . '-' . $iconFile;
                    [$variant, $label] = $this->parseVariantAndLabel($iconFile, $variantKeys);

                    $manifest[] = [
                        'name' => $canonicalName,
                        'set' => $setName,
                        'prefix' => $prefix,
                        'variant' => $variant,
                        'label' => $label,
                    ];
                }
            }
        }

        usort($manifest, fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return $manifest;
    }

    /**
     * @return array<int, string>
     */
    protected function getIconFilesFromPath(string $path): array
    {
        $filesystem = app(\Illuminate\Filesystem\Filesystem::class);

        if (! $filesystem->isDirectory($path)) {
            return [];
        }

        $icons = [];

        foreach ($filesystem->allFiles($path) as $file) {
            if ($file->getExtension() !== 'svg') {
                continue;
            }

            $icons[] = (string) Str::of($file->getPathname())
                ->after($path . DIRECTORY_SEPARATOR)
                ->replace(DIRECTORY_SEPARATOR, '.')
                ->basename('.svg');
        }

        return $icons;
    }

    /**
     * @param  array<int, string>  $variantKeys
     * @return array{0: string|null, 1: string}
     */
    protected function parseVariantAndLabel(string $iconFile, array $variantKeys): array
    {
        foreach ($variantKeys as $variantKey) {
            $variantPrefix = $variantKey . '-';
            if (str_starts_with($iconFile, $variantPrefix)) {
                return [$variantKey, substr($iconFile, strlen($variantPrefix))];
            }
        }

        $dotPrefix = Str::before($iconFile, '.');
        if ($dotPrefix !== $iconFile && strlen($dotPrefix) <= 4) {
            return [$dotPrefix, Str::after($iconFile, '.')];
        }

        return [null, $iconFile];
    }
}
