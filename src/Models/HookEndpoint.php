<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HookEndpoint extends Model
{
    protected $table = 'contact_hook_endpoints';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'events' => 'array',
            'backoff' => 'array',
            'headers' => 'array',
        ];
    }

    /**
     * @return HasMany<HookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(HookDelivery::class, 'hook_endpoint_id');
    }

    public function acceptsEvent(string $event): bool
    {
        return $this->events === null || in_array($event, $this->events, true);
    }
}
