<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Filament\Forms\Components\Component;

class SerpPreview extends Component
{
    protected string $view = 'cms-core::filament.forms.components.serp-preview';

    public static function make(): static
    {
        return app(static::class)
            ->columnSpanFull();
    }
}
