<?php

namespace Alexisgt01\CmsCore\Models\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PageState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PageDraft::class)
            ->allowTransition(PageDraft::class, PagePublished::class)
            ->allowTransition(PagePublished::class, PageDraft::class);
    }
}
