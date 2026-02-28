<?php

namespace Alexisgt01\CmsCore\Models\States;

class RequestNew extends RequestState
{
    public static string $name = 'new';

    public function label(): string
    {
        return 'Nouveau';
    }

    public function color(): string
    {
        return 'info';
    }
}
