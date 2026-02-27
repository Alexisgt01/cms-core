<?php

namespace Alexisgt01\CmsCore\Models\States;

class Draft extends PostState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return 'Brouillon';
    }

    public function color(): string
    {
        return 'gray';
    }
}
