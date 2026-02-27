<?php

namespace Alexisgt01\CmsCore\Models\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EntryState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EntryDraft::class)
            ->allowTransition(EntryDraft::class, EntryPublished::class)
            ->allowTransition(EntryPublished::class, EntryDraft::class);
    }
}
