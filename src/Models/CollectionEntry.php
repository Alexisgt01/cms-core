<?php

namespace Alexisgt01\CmsCore\Models;

use Alexisgt01\CmsCore\Casts\MediaSelectionCast;
use Alexisgt01\CmsCore\Collections\CollectionRegistry;
use Alexisgt01\CmsCore\Models\States\EntryPublished;
use Alexisgt01\CmsCore\Models\States\EntryState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

class CollectionEntry extends Model
{
    use HasStates;
    use LogsActivity;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'collection_entries';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => EntryState::class,
            'data' => 'array',
            'published_at' => 'datetime',
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

    /**
     * Get a field value from the data JSON.
     */
    public function field(string $name, mixed $default = null): mixed
    {
        return data_get($this->data, $name, $default);
    }

    /**
     * Resolve the CollectionType class key for this entry.
     *
     * @return class-string<\Alexisgt01\CmsCore\Collections\CollectionType>|null
     */
    public function collectionTypeClass(): ?string
    {
        return app(CollectionRegistry::class)->resolve($this->collection_type);
    }

    /**
     * Scope: filter by collection type key.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('collection_type', $type);
    }

    /**
     * Scope: ordered by position.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    /**
     * Scope: published entries only.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereState('state', EntryPublished::class);
    }

    /**
     * Generate a unique slug within a collection type.
     */
    public static function generateSlug(string $value, string $collectionType): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $i = 1;

        while (static::query()->where('collection_type', $collectionType)->where('slug', $slug)->exists()) {
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
