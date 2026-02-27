<?php

namespace Alexisgt01\CmsCore\Models\States;

class PagePublished extends PageState
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
