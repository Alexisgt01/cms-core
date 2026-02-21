<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CmsMedia extends Media
{
    /** @var array<int, string> */
    protected $appends = ['url', 'human_readable_size'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(CmsMediaFolder::class, 'folder_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->id . '/' . $this->file_name);
    }
}
