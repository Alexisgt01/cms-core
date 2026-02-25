<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Alexisgt01\CmsCore\Casts\MediaSelectionCast;

class BlogCategory extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'indexing' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'robots_noarchive' => 'boolean',
            'robots_nosnippet' => 'boolean',
            'secondary_keywords' => 'array',
            'og_image' => MediaSelectionCast::class,
            'twitter_image' => MediaSelectionCast::class,
            'schema_json' => 'array',
            'schema_types' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
