<?php

namespace Vendor\CmsCore\Models\States;

class Scheduled extends PostState
{
    public function label(): string
    {
        return 'Programmé';
    }

    public function color(): string
    {
        return 'warning';
    }
}
