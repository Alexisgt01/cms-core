<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\ModelStates\HasStates;
use Alexisgt01\CmsCore\Casts\MediaSelectionCast;
use Alexisgt01\CmsCore\Models\States\PostState;

class BlogPost extends Model
{
    use HasStates;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => PostState::class,
            'featured_images' => 'array',
            'indexing' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'robots_noarchive' => 'boolean',
            'robots_nosnippet' => 'boolean',
            'secondary_keywords' => 'array',
            'faq_blocks' => 'array',
            'table_of_contents' => 'boolean',
            'og_image' => MediaSelectionCast::class,
            'twitter_image' => MediaSelectionCast::class,
            'schema_json' => 'array',
            'schema_types' => 'array',
            'published_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'first_published_at' => 'datetime',
            'updated_content_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));

        return max(1, (int) ceil($wordCount / 200));
    }
}
