<?php

namespace Vendor\CmsCore\Models\States;

class Draft extends PostState
{
    public function label(): string
    {
        return 'Brouillon';
    }

    public function color(): string
    {
        return 'gray';
    }
}
