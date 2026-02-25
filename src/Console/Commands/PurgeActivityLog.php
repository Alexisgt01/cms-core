<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class PurgeActivityLog extends Command
{
    protected $signature = 'cms:purge-activity {--days=30 : Number of days to keep}';

    protected $description = 'Purge activity log entries older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $count = Activity::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Purged {$count} activity log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
