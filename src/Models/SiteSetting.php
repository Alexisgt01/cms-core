<?php

namespace Alexisgt01\CmsCore\Models;

use Alexisgt01\CmsCore\Casts\MediaSelectionCast;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SiteSetting extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logo_light' => MediaSelectionCast::class,
            'logo_dark' => MediaSelectionCast::class,
            'favicon' => MediaSelectionCast::class,
            'contact_email_recipients' => 'array',
            'restricted_access_enabled' => 'boolean',
            'restricted_access_cookie_ttl' => 'integer',
            'restricted_access_admin_bypass' => 'boolean',
            'default_og_image' => MediaSelectionCast::class,
            'default_robots_index' => 'boolean',
            'default_robots_follow' => 'boolean',
            'show_version_in_footer' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function instance(): static
    {
        /** @var static */
        return static::query()->firstOrCreate([], [
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'restricted_access_cookie_ttl' => 1440,
            'title_template' => '%title% · %site%',
            'restricted_access_admin_bypass' => true,
            'default_robots_index' => true,
            'default_robots_follow' => true,
        ]);
    }
}
