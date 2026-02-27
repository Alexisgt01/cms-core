<?php

namespace Alexisgt01\CmsCore\Models\States;

class EntryPublished extends EntryState
{
    public static string $name = 'entry_published';

    public function label(): string
    {
        return 'Publie';
    }

    public function color(): string
    {
        return 'success';
    }
}
