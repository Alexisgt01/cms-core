<?php

namespace Alexisgt01\CmsCore\Models\States;

class PageDraft extends PageState
{
    public static string $name = 'page_draft';

    public function label(): string
    {
        return 'Brouillon';
    }

    public function color(): string
    {
        return 'gray';
    }
}
