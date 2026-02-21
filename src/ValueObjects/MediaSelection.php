<?php

namespace Alexisgt01\CmsCore\ValueObjects;

class MediaSelection implements \JsonSerializable
{
    public function __construct(
        public readonly string $source,
        public readonly string $url,
        public readonly string $originalUrl,
        public readonly ?int $mediaId = null,
        public readonly ?string $provider = null,
        public readonly ?string $unsplashId = null,
        public readonly ?string $unsplashAuthor = null,
        public readonly ?string $unsplashAuthorUrl = null,
        public readonly ?string $alt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: $data['source'] ?? 'library',
            url: $data['url'] ?? '',
            originalUrl: $data['original_url'] ?? $data['url'] ?? '',
            mediaId: isset($data['media_id']) ? (int) $data['media_id'] : null,
            provider: $data['provider'] ?? null,
            unsplashId: $data['unsplash_id'] ?? null,
            unsplashAuthor: $data['unsplash_author'] ?? null,
            unsplashAuthorUrl: $data['unsplash_author_url'] ?? null,
            alt: $data['alt'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'url' => $this->url,
            'original_url' => $this->originalUrl,
            'media_id' => $this->mediaId,
            'provider' => $this->provider,
            'unsplash_id' => $this->unsplashId,
            'unsplash_author' => $this->unsplashAuthor,
            'unsplash_author_url' => $this->unsplashAuthorUrl,
            'alt' => $this->alt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
