<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use Illuminate\Console\Command;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\States\Published;
use Alexisgt01\CmsCore\Models\States\Scheduled;

class PublishScheduledPosts extends Command
{
    protected $signature = 'cms:publish-scheduled';

    protected $description = 'Publish all scheduled blog posts whose scheduled_for date has passed';

    public function handle(): int
    {
        $posts = BlogPost::query()
            ->whereState('state', Scheduled::class)
            ->where('scheduled_for', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts to publish.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($posts as $post) {
            $post->state->transitionTo(Published::class);

            if (! $post->published_at) {
                $post->published_at = now();
            }

            if (! $post->first_published_at) {
                $post->first_published_at = now();
            }

            $post->save();
            $count++;
        }

        $this->info("Published {$count} scheduled post(s).");

        return self::SUCCESS;
    }
}
