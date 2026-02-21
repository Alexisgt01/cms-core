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
