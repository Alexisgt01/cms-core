<?php

namespace Alexisgt01\CmsCore\ValueObjects;

class SeoMeta implements \JsonSerializable, \Stringable
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $canonicalUrl = null,
        public readonly string $robots = 'index, follow',
        public readonly ?string $ogType = null,
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly ?string $ogUrl = null,
        public readonly ?string $ogSiteName = null,
        public readonly ?string $ogLocale = null,
        public readonly ?string $ogImageUrl = null,
        public readonly ?int $ogImageWidth = null,
        public readonly ?int $ogImageHeight = null,
        public readonly ?string $twitterCard = null,
        public readonly ?string $twitterSite = null,
        public readonly ?string $twitterCreator = null,
        public readonly ?string $twitterTitle = null,
        public readonly ?string $twitterDescription = null,
        public readonly ?string $twitterImageUrl = null,
        public readonly ?array $schemaJsonLd = null,
    ) {}

    /**
     * Render all SEO tags as HTML for the <head>.
     */
    public function toHtml(): string
    {
        $tags = [];

        $tags[] = '<title>'.e($this->title).'</title>';

        if ($this->description) {
            $tags[] = '<meta name="description" content="'.e($this->description).'">';
        }

        if ($this->canonicalUrl) {
            $tags[] = '<link rel="canonical" href="'.e($this->canonicalUrl).'">';
        }

        $tags[] = '<meta name="robots" content="'.e($this->robots).'">';

        if ($this->ogType) {
            $tags[] = '<meta property="og:type" content="'.e($this->ogType).'">';
        }

        if ($this->ogTitle) {
            $tags[] = '<meta property="og:title" content="'.e($this->ogTitle).'">';
        }

        if ($this->ogDescription) {
            $tags[] = '<meta property="og:description" content="'.e($this->ogDescription).'">';
        }

        if ($this->ogUrl) {
            $tags[] = '<meta property="og:url" content="'.e($this->ogUrl).'">';
        }

        if ($this->ogSiteName) {
            $tags[] = '<meta property="og:site_name" content="'.e($this->ogSiteName).'">';
        }

        if ($this->ogLocale) {
            $tags[] = '<meta property="og:locale" content="'.e($this->ogLocale).'">';
        }

        if ($this->ogImageUrl) {
            $tags[] = '<meta property="og:image" content="'.e($this->ogImageUrl).'">';

            if ($this->ogImageWidth) {
                $tags[] = '<meta property="og:image:width" content="'.$this->ogImageWidth.'">';
            }

            if ($this->ogImageHeight) {
                $tags[] = '<meta property="og:image:height" content="'.$this->ogImageHeight.'">';
            }
        }

        if ($this->twitterCard) {
            $tags[] = '<meta name="twitter:card" content="'.e($this->twitterCard).'">';
        }

        if ($this->twitterSite) {
            $tags[] = '<meta name="twitter:site" content="'.e($this->twitterSite).'">';
        }

        if ($this->twitterCreator) {
            $tags[] = '<meta name="twitter:creator" content="'.e($this->twitterCreator).'">';
        }

        if ($this->twitterTitle) {
            $tags[] = '<meta name="twitter:title" content="'.e($this->twitterTitle).'">';
        }

        if ($this->twitterDescription) {
            $tags[] = '<meta name="twitter:description" content="'.e($this->twitterDescription).'">';
        }

        if ($this->twitterImageUrl) {
            $tags[] = '<meta name="twitter:image" content="'.e($this->twitterImageUrl).'">';
        }

        if ($this->schemaJsonLd) {
            $json = json_encode($this->schemaJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $tags[] = '<script type="application/ld+json">'.$json.'</script>';
        }

        return implode("\n", $tags);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical_url' => $this->canonicalUrl,
            'robots' => $this->robots,
            'og_type' => $this->ogType,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_url' => $this->ogUrl,
            'og_site_name' => $this->ogSiteName,
            'og_locale' => $this->ogLocale,
            'og_image_url' => $this->ogImageUrl,
            'og_image_width' => $this->ogImageWidth,
            'og_image_height' => $this->ogImageHeight,
            'twitter_card' => $this->twitterCard,
            'twitter_site' => $this->twitterSite,
            'twitter_creator' => $this->twitterCreator,
            'twitter_title' => $this->twitterTitle,
            'twitter_description' => $this->twitterDescription,
            'twitter_image_url' => $this->twitterImageUrl,
            'schema_json_ld' => $this->schemaJsonLd,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
