<?php

namespace Alexisgt01\CmsCore\Models\States;

class EntryDraft extends EntryState
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
