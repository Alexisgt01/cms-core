<?php

namespace Vendor\CmsCore\Models\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PostState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Scheduled::class)
            ->allowTransition(Draft::class, Published::class)
            ->allowTransition(Scheduled::class, Draft::class)
            ->allowTransition(Scheduled::class, Published::class)
            ->allowTransition(Published::class, Draft::class);
    }
}
