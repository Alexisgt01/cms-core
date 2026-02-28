<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContactSetting extends Model
{
    protected $guarded = ['id'];

    protected static string $cacheKey = 'cms_contact_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_async' => 'boolean',
            'retention_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(static::$cacheKey));
        static::deleted(fn () => Cache::forget(static::$cacheKey));
    }

    public static function instance(): static
    {
        /** @var static */
        return Cache::remember(static::$cacheKey, 3600, function () {
            return static::query()->firstOrCreate([], [
                'default_async' => true,
                'retention_days' => 90,
            ]);
        });
    }
}
