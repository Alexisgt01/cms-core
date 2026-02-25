<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Alexisgt01\CmsCore\Casts\MediaSelectionCast;

class BlogSetting extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'show_author_on_post' => 'boolean',
            'show_reading_time' => 'boolean',
            'enable_comments' => 'boolean',
            'rss_enabled' => 'boolean',
            'featured_image_required' => 'boolean',
            'og_image_fallback' => MediaSelectionCast::class,
            'twitter_image_fallback' => MediaSelectionCast::class,
            'indexing_default' => 'boolean',
            'default_robots_index' => 'boolean',
            'default_robots_follow' => 'boolean',
            'default_robots_noarchive' => 'boolean',
            'default_robots_nosnippet' => 'boolean',
            'default_h1_from_title' => 'boolean',
            'schema_enabled' => 'boolean',
            'schema_publisher_logo' => MediaSelectionCast::class,
            'schema_custom_json' => 'array',
            'default_schema_types' => 'array',
            'schema_same_as' => 'array',
            'sitemap_enabled' => 'boolean',
            'sitemap_exclude_patterns' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function defaultAuthor(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'default_author_id');
    }

    public static function instance(): static
    {
        /** @var static */
        return static::query()->firstOrCreate([], ['blog_name' => 'Blog']);
    }
}
