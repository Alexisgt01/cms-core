<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HookDelivery extends Model
{
    protected $table = 'contact_hook_deliveries';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_retry_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<HookEndpoint, $this>
     */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(HookEndpoint::class, 'hook_endpoint_id');
    }

    /**
     * @return BelongsTo<ContactRequest, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class, 'contact_request_id');
    }
}
