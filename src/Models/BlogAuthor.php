<?php

namespace Vendor\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Vendor\CmsCore\Casts\MediaSelectionCast;

class BlogAuthor extends Model
{
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'avatar' => MediaSelectionCast::class,
            'indexing' => 'boolean',
            'og_image' => MediaSelectionCast::class,
            'twitter_image' => MediaSelectionCast::class,
            'schema_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
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
