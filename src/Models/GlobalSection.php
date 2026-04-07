<?php

namespace Alexisgt01\CmsCore\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalSection extends Model
{
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
