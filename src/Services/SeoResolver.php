<?php

namespace Alexisgt01\CmsCore\Services;

use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogCategory;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\BlogSetting;
use Alexisgt01\CmsCore\Models\BlogTag;
use Alexisgt01\CmsCore\Models\CollectionEntry;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Models\SiteSetting;
use Alexisgt01\CmsCore\ValueObjects\MediaSelection;
use Alexisgt01\CmsCore\ValueObjects\SeoMeta;
use Illuminate\Database\Eloquent\Model;

class SeoResolver
{
    private SiteSetting $site;

    private BlogSetting $blog;

    /**
     * Resolve SEO metadata for a page key, model instance, or global defaults.
     */
    public function resolve(Model|string|null $entity = null): SeoMeta
    {
        $this->site = SiteSetting::instance();
        $this->blog = BlogSetting::instance();

        if (is_string($entity)) {
            $entity = Page::findByKey($entity);
        }

        $title = $this->resolveTitle($entity);
        $description = $this->resolveDescription($entity);
        $canonical = $this->resolveCanonical($entity);
        $robots = $this->resolveRobots($entity);
        $og = $this->resolveOg($entity, $title, $description, $canonical);
        $twitter = $this->resolveTwitter($entity, $og);
        $schema = $this->resolveSchema($entity, $title, $description, $canonical);

        return new SeoMeta(
            title: $title,
            description: $description,
            canonicalUrl: $canonical,
            robots: $robots,
            ogType: $og['type'],
            ogTitle: $og['title'],
            ogDescription: $og['description'],
            ogUrl: $og['url'],
            ogSiteName: $og['site_name'],
            ogLocale: $og['locale'],
            ogImageUrl: $og['image_url'],
            ogImageWidth: $og['image_width'],
            ogImageHeight: $og['image_height'],
            twitterCard: $twitter['card'],
            twitterSite: $twitter['site'],
            twitterCreator: $twitter['creator'],
            twitterTitle: $twitter['title'],
            twitterDescription: $twitter['description'],
            twitterImageUrl: $twitter['image_url'],
            schemaJsonLd: $schema,
        );
    }

    private function resolveTitle(?Model $entity): string
    {
        if (! $entity) {
            return $this->site->default_site_title ?? $this->site->site_name ?? '';
        }

        $entityTitle = $this->getAttr($entity, 'meta_title')
            ?: $this->getEntityName($entity);

        if (! $entityTitle) {
            return $this->site->default_site_title ?? $this->site->site_name ?? '';
        }

        $template = $this->site->title_template ?? '%title%';

        return str_replace(
            ['%title%', '%site%'],
            [$entityTitle, $this->site->site_name ?? ''],
            $template,
        );
    }

    private function resolveDescription(?Model $entity): ?string
    {
        if (! $entity) {
            return $this->site->default_meta_description;
        }

        return $this->getAttr($entity, 'meta_description')
            ?: $this->getAttr($entity, 'seo_excerpt')
            ?: $this->getAttr($entity, 'excerpt')
            ?: $this->getAttr($entity, 'description')
            ?: $this->getAttr($entity, 'bio')
            ?: $this->site->default_meta_description;
    }

    private function resolveCanonical(?Model $entity): ?string
    {
        if (! $entity) {
            return $this->site->canonical_base_url;
        }

        $custom = $this->getAttr($entity, 'canonical_url');

        if ($custom) {
            return $custom;
        }

        $baseUrl = $this->site->canonical_base_url;
        $slug = $this->getAttr($entity, 'slug');

        if ($baseUrl && $slug) {
            return rtrim($baseUrl, '/').'/'.ltrim($slug, '/');
        }

        return null;
    }

    private function resolveRobots(?Model $entity): string
    {
        $directives = [];
        $isBlog = $entity && $this->isBlogEntity($entity);

        $index = $entity ? $this->getAttr($entity, 'robots_index') : null;

        if ($index === null && $isBlog) {
            $index = $this->blog->default_robots_index;
        }

        if ($index === null) {
            $index = $this->site->default_robots_index ?? true;
        }

        $directives[] = $index ? 'index' : 'noindex';

        $follow = $entity ? $this->getAttr($entity, 'robots_follow') : null;

        if ($follow === null && $isBlog) {
            $follow = $this->blog->default_robots_follow;
        }

        if ($follow === null) {
            $follow = $this->site->default_robots_follow ?? true;
        }

        $directives[] = $follow ? 'follow' : 'nofollow';

        $noarchive = $entity ? $this->getAttr($entity, 'robots_noarchive') : null;
        if ($noarchive === null && $isBlog) {
            $noarchive = $this->blog->default_robots_noarchive ?? false;
        }
        if ($noarchive) {
            $directives[] = 'noarchive';
        }

        $nosnippet = $entity ? $this->getAttr($entity, 'robots_nosnippet') : null;
        if ($nosnippet === null && $isBlog) {
            $nosnippet = $this->blog->default_robots_nosnippet ?? false;
        }
        if ($nosnippet) {
            $directives[] = 'nosnippet';
        }

        $maxSnippet = $entity ? $this->getAttr($entity, 'robots_max_snippet') : null;
        if ($maxSnippet === null && $isBlog) {
            $maxSnippet = $this->blog->default_robots_max_snippet;
        }
        if ($maxSnippet !== null) {
            $directives[] = "max-snippet:{$maxSnippet}";
        }

        $maxImage = $entity ? $this->getAttr($entity, 'robots_max_image_preview') : null;
        if ($maxImage === null && $isBlog) {
            $maxImage = $this->blog->default_robots_max_image_preview;
        }
        if ($maxImage) {
            $directives[] = "max-image-preview:{$maxImage}";
        }

        $maxVideo = $entity ? $this->getAttr($entity, 'robots_max_video_preview') : null;
        if ($maxVideo === null && $isBlog) {
            $maxVideo = $this->blog->default_robots_max_video_preview;
        }
        if ($maxVideo !== null) {
            $directives[] = "max-video-preview:{$maxVideo}";
        }

        return implode(', ', $directives);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveOg(?Model $entity, string $title, ?string $description, ?string $canonical): array
    {
        $isBlog = $entity && $this->isBlogEntity($entity);

        $ogType = $this->getAttr($entity, 'og_type');
        if (! $ogType && $isBlog) {
            $ogType = $this->blog->og_type_default;
        }
        $ogType = $ogType ?: 'website';

        $ogTitle = $this->getAttr($entity, 'og_title') ?: $title;
        $ogDescription = $this->getAttr($entity, 'og_description') ?: $description;

        $ogSiteName = $this->getAttr($entity, 'og_site_name');
        if (! $ogSiteName && $isBlog) {
            $ogSiteName = $this->blog->og_site_name;
        }
        $ogSiteName = $ogSiteName ?: $this->site->site_name;

        $ogLocale = $this->getAttr($entity, 'og_locale');
        if (! $ogLocale && $isBlog) {
            $ogLocale = $this->blog->og_locale;
        }

        $imageUrl = null;
        $imageWidth = null;
        $imageHeight = null;

        $entityImage = $entity ? $this->getAttr($entity, 'og_image') : null;

        if ($entityImage instanceof MediaSelection && $entityImage->url) {
            $imageUrl = $entityImage->url;
            $imageWidth = $this->getAttr($entity, 'og_image_width');
            $imageHeight = $this->getAttr($entity, 'og_image_height');
        } elseif ($isBlog && $this->blog->og_image_fallback instanceof MediaSelection && $this->blog->og_image_fallback->url) {
            $imageUrl = $this->blog->og_image_fallback->url;
            $imageWidth = $this->blog->og_image_fallback_width;
            $imageHeight = $this->blog->og_image_fallback_height;
        } elseif ($this->site->default_og_image instanceof MediaSelection && $this->site->default_og_image->url) {
            $imageUrl = $this->site->default_og_image->url;
        }

        return [
            'type' => $ogType,
            'title' => $ogTitle,
            'description' => $ogDescription,
            'url' => $canonical,
            'site_name' => $ogSiteName,
            'locale' => $ogLocale,
            'image_url' => $imageUrl,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
        ];
    }

    /**
     * @param  array<string, mixed>  $og
     * @return array<string, mixed>
     */
    private function resolveTwitter(?Model $entity, array $og): array
    {
        $isBlog = $entity && $this->isBlogEntity($entity);

        $card = $this->getAttr($entity, 'twitter_card');
        if (! $card && $isBlog) {
            $card = $this->blog->twitter_card_default;
        }
        $card = $card ?: 'summary_large_image';

        $site = $this->getAttr($entity, 'twitter_site');
        if (! $site && $isBlog) {
            $site = $this->blog->twitter_site;
        }

        $creator = $this->getAttr($entity, 'twitter_creator');
        if (! $creator && $isBlog) {
            $creator = $this->blog->twitter_creator;
        }

        $twitterTitle = $this->getAttr($entity, 'twitter_title') ?: $og['title'];
        $twitterDescription = $this->getAttr($entity, 'twitter_description') ?: $og['description'];

        $imageUrl = null;
        $entityImage = $entity ? $this->getAttr($entity, 'twitter_image') : null;

        if ($entityImage instanceof MediaSelection && $entityImage->url) {
            $imageUrl = $entityImage->url;
        } elseif ($isBlog && $this->blog->twitter_image_fallback instanceof MediaSelection && $this->blog->twitter_image_fallback->url) {
            $imageUrl = $this->blog->twitter_image_fallback->url;
        } else {
            $imageUrl = $og['image_url'];
        }

        return [
            'card' => $card,
            'site' => $site,
            'creator' => $creator,
            'title' => $twitterTitle,
            'description' => $twitterDescription,
            'image_url' => $imageUrl,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveSchema(?Model $entity, string $title, ?string $description, ?string $canonical): ?array
    {
        if (! $entity) {
            return null;
        }

        $entitySchema = $this->getAttr($entity, 'schema_json');

        if (is_array($entitySchema) && ! empty($entitySchema)) {
            return $entitySchema;
        }

        $types = $this->getAttr($entity, 'schema_types');
        $isBlog = $this->isBlogEntity($entity);

        if (empty($types) && $isBlog) {
            $types = $this->blog->default_schema_types;
        }

        if (empty($types)) {
            return null;
        }

        if ($isBlog && ! ($this->blog->schema_enabled ?? true)) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
        ];

        if (count($types) === 1) {
            $schema['@type'] = $types[0];
        } else {
            $schema['@type'] = $types;
        }

        $schema['name'] = $this->getEntityName($entity) ?? $title;

        if ($canonical) {
            $schema['url'] = $canonical;
        }

        if ($description) {
            $schema['description'] = $description;
        }

        if ($entity instanceof BlogPost) {
            $schema['headline'] = $entity->title;

            if ($entity->published_at) {
                $schema['datePublished'] = $entity->published_at->toIso8601String();
            }

            if ($entity->updated_at) {
                $schema['dateModified'] = $entity->updated_at->toIso8601String();
            }
        }

        if ($isBlog && $this->blog->schema_publisher_name) {
            $publisher = [
                '@type' => 'Organization',
                'name' => $this->blog->schema_publisher_name,
            ];

            if ($this->blog->schema_publisher_logo instanceof MediaSelection && $this->blog->schema_publisher_logo->url) {
                $publisher['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => $this->blog->schema_publisher_logo->url,
                ];
            }

            $schema['publisher'] = $publisher;
        }

        if ($isBlog && $this->blog->schema_language) {
            $schema['inLanguage'] = $this->blog->schema_language;
        }

        return $schema;
    }

    private function isBlogEntity(Model $entity): bool
    {
        return $entity instanceof BlogPost
            || $entity instanceof BlogAuthor
            || $entity instanceof BlogCategory
            || $entity instanceof BlogTag;
    }

    private function getEntityName(?Model $entity): ?string
    {
        if (! $entity) {
            return null;
        }

        if ($entity instanceof BlogPost) {
            return $entity->title;
        }

        if ($entity instanceof BlogAuthor) {
            return $entity->display_name;
        }

        if ($entity instanceof CollectionEntry) {
            return $entity->field('title') ?: $entity->field('name');
        }

        return $this->getAttr($entity, 'name');
    }

    private function getAttr(?Model $entity, string $key): mixed
    {
        if (! $entity) {
            return null;
        }

        return $entity->getAttribute($key);
    }
}
