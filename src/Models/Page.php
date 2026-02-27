<?php

namespace Alexisgt01\CmsCore\Models;

use Alexisgt01\CmsCore\Casts\MediaSelectionCast;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Alexisgt01\CmsCore\Models\States\PageState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

class Page extends Model
{
    use HasStates;
    use LogsActivity;
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => PageState::class,
            'meta' => 'array',
            'sections' => 'array',
            'published_at' => 'datetime',
            'is_home' => 'boolean',
            'indexing' => 'boolean',
            'secondary_keywords' => 'array',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'robots_noarchive' => 'boolean',
            'robots_nosnippet' => 'boolean',
            'og_image' => MediaSelectionCast::class,
            'twitter_image' => MediaSelectionCast::class,
            'schema_types' => 'array',
            'schema_json' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereState('state', PagePublished::class);
    }

    public static function findByKey(string $key): ?static
    {
        /** @var static|null */
        return static::query()->where('key', $key)->first();
    }

    public static function home(): ?static
    {
        /** @var static|null */
        return static::query()->where('is_home', true)->first();
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
