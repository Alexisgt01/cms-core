<?php

namespace Alexisgt01\CmsCore\Models\States;

class PageDraft extends PageState
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
