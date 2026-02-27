<?php

namespace Alexisgt01\CmsCore\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Alexisgt01\CmsCore\ValueObjects\IconSelection;

/**
 * @implements CastsAttributes<IconSelection|null, IconSelection|array<string, mixed>|null>
 */
class IconSelectionCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?IconSelection
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);

        if (! is_array($data) || empty($data['name'])) {
            return null;
        }

        return IconSelection::fromArray($data);
    }

    /**
     * @param  IconSelection|array<string, mixed>|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof IconSelection) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return null;
    }
}
