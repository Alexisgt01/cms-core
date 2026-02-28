<?php

namespace Alexisgt01\CmsCore\Models\States;

class RequestProcessed extends RequestState
{
    public static string $name = 'processed';

    public function label(): string
    {
        return 'Traite';
    }

    public function color(): string
    {
        return 'success';
    }
}
