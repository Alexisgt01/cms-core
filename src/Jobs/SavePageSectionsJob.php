<?php

namespace Alexisgt01\CmsCore\Jobs;

use Alexisgt01\CmsCore\Models\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SavePageSectionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public int $pageId,
        public string $cacheKey,
    ) {}

    public function handle(): void
    {
        $sections = Cache::pull($this->cacheKey);

        if ($sections === null) {
            return;
        }

        Page::withoutEvents(function () use ($sections) {
            Page::withoutTimestamps(function () use ($sections) {
                Page::query()
                    ->where('id', $this->pageId)
                    ->update(['sections' => $sections]);
            });
        });
    }
}
