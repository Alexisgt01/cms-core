<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Illuminate\Console\Command;

class RetryContactHooksCommand extends Command
{
    protected $signature = 'cms:contact-retry-hooks';

    protected $description = 'Retry pending contact hook deliveries that are due for retry';

    public function handle(): int
    {
        $deliveries = HookDelivery::query()
            ->where('status', '!=', 'success')
            ->where('status', '!=', 'failed')
            ->where('next_retry_at', '<=', now())
            ->get();

        if ($deliveries->isEmpty()) {
            $this->info('No hook deliveries to retry.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($deliveries as $delivery) {
            DeliverContactHookJob::dispatch($delivery->id);
            $count++;
        }

        $this->info("Dispatched {$count} hook delivery retries.");

        return self::SUCCESS;
    }
}
