<?php

namespace Alexisgt01\CmsCore\Models\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class RequestState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(RequestNew::class)
            ->allowTransition(RequestNew::class, RequestProcessed::class)
            ->allowTransition(RequestNew::class, RequestArchived::class)
            ->allowTransition(RequestProcessed::class, RequestArchived::class)
            ->allowTransition(RequestProcessed::class, RequestNew::class)
            ->allowTransition(RequestArchived::class, RequestNew::class);
    }
}
