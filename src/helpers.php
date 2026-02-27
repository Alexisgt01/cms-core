<?php

use Alexisgt01\CmsCore\Models\CmsMedia;

if (! function_exists('media_url')) {
    /**
     * Generate a media URL, optionally proxied through imgproxy.
     *
     * @param  array{width?: int, height?: int, resizing_type?: string, gravity?: string, quality?: int, format?: string, blur?: float, sharpen?: float}  $options
     */
    function media_url(string|int|null $pathOrMediaId, array $options = []): string
    {
        if ($pathOrMediaId === null || $pathOrMediaId === '' || $pathOrMediaId === 0) {
            return '';
        }

        if (is_int($pathOrMediaId)) {
            $media = CmsMedia::find($pathOrMediaId);
            if (! $media) {
                return '';
            }
            $sourceUrl = $media->url;
        } else {
            $sourceUrl = $pathOrMediaId;
        }

        if (! config('cms-media.proxy.enabled')) {
            return $sourceUrl;
        }

        $baseUrl = rtrim((string) config('cms-media.proxy.url'), '/');
        if ($baseUrl === '') {
            return $sourceUrl;
        }

        $processingOptions = media_url_build_options($options);
        $optionsPath = $processingOptions !== '' ? $processingOptions : 'raw:true';
        $path = "/{$optionsPath}/plain/{$sourceUrl}";

        $key = (string) config('cms-media.proxy.key');
        $salt = (string) config('cms-media.proxy.salt');

        if ($key !== '' && $salt !== '') {
            $keyBin = hex2bin($key);
            $saltBin = hex2bin($salt);
            $signature = rtrim(strtr(base64_encode(
                hash_hmac('sha256', $saltBin . $path, $keyBin, true)
            ), '+/', '-_'), '=');

            return "{$baseUrl}/{$signature}{$path}";
        }

        return "{$baseUrl}/unsafe{$path}";
    }
}

if (! function_exists('cms_icon')) {
    /**
     * Render an icon from an IconSelection or blade-icons name.
     *
     * @param  array<string, string>  $attributes
     */
    function cms_icon(string|\Alexisgt01\CmsCore\ValueObjects\IconSelection|null $icon, string $class = '', array $attributes = []): string
    {
        if ($icon === null || $icon === '') {
            return '';
        }

        if ($icon instanceof \Alexisgt01\CmsCore\ValueObjects\IconSelection) {
            return $icon->toSvg($class, $attributes);
        }

        return svg($icon, $class, $attributes)->toHtml();
    }
}

if (! function_exists('seo_meta')) {
    /**
     * Resolve SEO metadata for a page key, model instance, or global defaults.
     *
     * Usage:
     *   seo_meta('service')   → looks up Page by key
     *   seo_meta($blogPost)   → uses the model directly
     *   seo_meta()            → global defaults only
     */
    function seo_meta(string|\Illuminate\Database\Eloquent\Model|null $entity = null): \Alexisgt01\CmsCore\ValueObjects\SeoMeta
    {
        return (new \Alexisgt01\CmsCore\Services\SeoResolver)->resolve($entity);
    }
}

if (! function_exists('collection_entries')) {
    /**
     * Get all entries for a collection type.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Alexisgt01\CmsCore\Models\CollectionEntry>
     */
    function collection_entries(string $collectionType, bool $publishedOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = \Alexisgt01\CmsCore\Models\CollectionEntry::query()
            ->forType($collectionType)
            ->ordered();

        if ($publishedOnly) {
            $registry = app(\Alexisgt01\CmsCore\Collections\CollectionRegistry::class);
            $typeClass = $registry->resolve($collectionType);

            if ($typeClass && $typeClass::hasStates()) {
                $query->published();
            }
        }

        return $query->get();
    }
}

if (! function_exists('collection_entry')) {
    /**
     * Get a single collection entry by slug.
     */
    function collection_entry(string $collectionType, string $slug): ?\Alexisgt01\CmsCore\Models\CollectionEntry
    {
        return \Alexisgt01\CmsCore\Models\CollectionEntry::query()
            ->forType($collectionType)
            ->where('slug', $slug)
            ->first();
    }
}

if (! function_exists('media_url_build_options')) {
    /**
     * @param  array<string, mixed>  $options
     */
    function media_url_build_options(array $options): string
    {
        $parts = [];

        $width = (int) ($options['width'] ?? 0);
        $height = (int) ($options['height'] ?? 0);
        $resizingType = $options['resizing_type'] ?? 'fit';

        if ($width > 0 || $height > 0) {
            $parts[] = "rs:{$resizingType}:{$width}:{$height}";
        }

        $gravity = $options['gravity'] ?? '';
        if ($gravity !== '' && $gravity !== 'ce') {
            $parts[] = "g:{$gravity}";
        }

        $quality = (int) ($options['quality'] ?? 0);
        if ($quality > 0 && $quality < 100) {
            $parts[] = "q:{$quality}";
        }

        $format = $options['format'] ?? '';
        if ($format !== '') {
            $parts[] = "f:{$format}";
        }

        $blur = (float) ($options['blur'] ?? 0);
        if ($blur > 0) {
            $parts[] = "bl:{$blur}";
        }

        $sharpen = (float) ($options['sharpen'] ?? 0);
        if ($sharpen > 0) {
            $parts[] = "sh:{$sharpen}";
        }

        return implode('/', $parts);
    }
}
