<?php

namespace Alexisgt01\CmsCore\Models\States;

class EntryDraft extends EntryState
{
    public static string $name = 'entry_draft';

    public function label(): string
    {
        return 'Brouillon';
    }

    public function color(): string
    {
        return 'gray';
    }
}
