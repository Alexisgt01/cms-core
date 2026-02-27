<?php

namespace Alexisgt01\CmsCore\Models\States;

class Published extends PostState
{
    public static string $name = 'published';

    public function label(): string
    {
        return 'Publié';
    }

    public function color(): string
    {
        return 'success';
    }
}
