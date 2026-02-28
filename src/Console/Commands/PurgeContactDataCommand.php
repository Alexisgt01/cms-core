<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use Alexisgt01\CmsCore\Models\ContactRequest;
use Alexisgt01\CmsCore\Models\ContactSetting;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Illuminate\Console\Command;

class PurgeContactDataCommand extends Command
{
    protected $signature = 'cms:contact-purge {--days= : Number of days to keep (overrides settings)}';

    protected $description = 'Purge contact requests and hook deliveries older than the retention period';

    public function handle(): int
    {
        $days = $this->option('days')
            ? (int) $this->option('days')
            : (ContactSetting::instance()->retention_days ?? config('cms-contacts.retention_days', 90));

        $cutoff = now()->subDays($days);

        $requestIds = ContactRequest::query()
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        $deliveriesCount = 0;

        if ($requestIds->isNotEmpty()) {
            $deliveriesCount = HookDelivery::query()
                ->whereIn('contact_request_id', $requestIds)
                ->delete();
        }

        $requestsCount = ContactRequest::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Purged {$requestsCount} contact requests and {$deliveriesCount} hook deliveries older than {$days} days.");

        return self::SUCCESS;
    }
}
