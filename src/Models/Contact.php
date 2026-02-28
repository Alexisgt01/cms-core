<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attribs' => 'array',
            'tags' => 'array',
            'consents' => 'array',
        ];
    }

    /**
     * @return HasMany<ContactRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ContactRequest::class);
    }

    public static function upsertByEmail(string $email, array $data = []): static
    {
        /** @var static */
        return static::updateOrCreate(
            ['email' => $email],
            array_filter([
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]),
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
