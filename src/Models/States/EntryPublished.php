<?php

namespace Alexisgt01\CmsCore\Models\States;

class EntryPublished extends EntryState
{
    public function label(): string
    {
        return 'Publie';
    }

    public function color(): string
    {
        return 'success';
    }
}
