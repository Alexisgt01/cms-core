<?php

namespace Alexisgt01\CmsCore\Models\States;

class PagePublished extends PageState
{
    public static string $name = 'page_published';

    public function label(): string
    {
        return 'Publie';
    }

    public function color(): string
    {
        return 'success';
    }
}
