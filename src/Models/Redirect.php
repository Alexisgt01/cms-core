<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    protected $guarded = ['id'];

    protected $table = 'redirects';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function recordHit(): void
    {
        $this->increment('hit_count');
        $this->update(['last_hit_at' => now()]);
    }

    public static function clearCache(): void
    {
        Cache::forget('cms_redirects');
    }

    /**
     * @return array<string, array{destination_url: string|null, status_code: int, id: int}>
     */
    public static function getCachedRedirects(): array
    {
        /** @var array<string, array{destination_url: string|null, status_code: int, id: int}> */
        return Cache::rememberForever('cms_redirects', function (): array {
            return static::query()
                ->active()
                ->get(['id', 'source_path', 'destination_url', 'status_code'])
                ->keyBy('source_path')
                ->map(fn (self $redirect): array => [
                    'destination_url' => $redirect->destination_url,
                    'status_code' => (int) $redirect->status_code,
                    'id' => $redirect->id,
                ])
                ->all();
        });
    }
}
