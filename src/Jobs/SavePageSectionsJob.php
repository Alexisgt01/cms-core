<?php

namespace Alexisgt01\CmsCore\Jobs;

use Alexisgt01\CmsCore\Models\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SavePageSectionsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function __construct(
        public int $pageId,
        public array $sections,
    ) {}

    public function handle(): void
    {
        Page::withoutEvents(function () {
            Page::withoutTimestamps(function () {
                Page::query()
                    ->where('id', $this->pageId)
                    ->update(['sections' => json_encode($this->sections)]);
            });
        });
    }
}
