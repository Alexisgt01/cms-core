<?php

namespace Alexisgt01\CmsCore\Models;

use Alexisgt01\CmsCore\Casts\MediaSelectionCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SiteSetting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected static string $cacheKey = 'cms_site_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logo_light' => MediaSelectionCast::class,
            'logo_dark' => MediaSelectionCast::class,
            'favicon' => MediaSelectionCast::class,
            'contact_email_recipients' => 'array',
            'restricted_access_enabled' => 'boolean',
            'restricted_access_cookie_ttl' => 'integer',
            'restricted_access_admin_bypass' => 'boolean',
            'default_og_image' => MediaSelectionCast::class,
            'default_robots_index' => 'boolean',
            'default_robots_follow' => 'boolean',
            'show_version_in_footer' => 'boolean',
            'copyright_start_year' => 'integer',
            'meta' => 'array',
        ];
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, $key, $default);
    }

    public function setMeta(string $key, mixed $value): static
    {
        $meta = $this->meta ?? [];
        data_set($meta, $key, $value);
        $this->meta = $meta;

        return $this;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(static::$cacheKey));
        static::deleted(fn () => Cache::forget(static::$cacheKey));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function instance(): static
    {
        /** @var static */
        return Cache::remember(static::$cacheKey, 3600, function () {
            return static::query()->firstOrCreate([], [
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'restricted_access_cookie_ttl' => 1440,
                'title_template' => '%title% · %site%',
                'restricted_access_admin_bypass' => true,
                'default_robots_index' => true,
                'default_robots_follow' => true,
                'company_country' => 'France',
            ]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
    }
}
