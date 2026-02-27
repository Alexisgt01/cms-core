<?php

namespace Alexisgt01\CmsCore\Models\States;

class Scheduled extends PostState
{
    public static string $name = 'scheduled';

    public function label(): string
    {
        return 'Programmé';
    }

    public function color(): string
    {
        return 'warning';
    }
}
