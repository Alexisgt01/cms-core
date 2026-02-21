<?php

namespace Vendor\CmsCore\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Vendor\CmsCore\ValueObjects\MediaSelection;

/**
 * @implements CastsAttributes<MediaSelection|null, MediaSelection|array<string, mixed>|null>
 */
class MediaSelectionCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?MediaSelection
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);

        if (! is_array($data) || empty($data['source'])) {
            return null;
        }

        return MediaSelection::fromArray($data);
    }

    /**
     * @param  MediaSelection|array<string, mixed>|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof MediaSelection) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return null;
    }
}
