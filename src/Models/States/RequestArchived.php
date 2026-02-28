<?php

namespace Alexisgt01\CmsCore\Models\States;

class RequestArchived extends RequestState
{
    public static string $name = 'archived';

    public function label(): string
    {
        return 'Archive';
    }

    public function color(): string
    {
        return 'gray';
    }
}
