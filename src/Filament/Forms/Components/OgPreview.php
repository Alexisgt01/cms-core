<?php

namespace Alexisgt01\CmsCore\Filament\Forms\Components;

use Filament\Forms\Components\Component;

class OgPreview extends Component
{
    protected string $view = 'cms-core::filament.forms.components.og-preview';

    public static function make(): static
    {
        return app(static::class)
            ->columnSpanFull();
    }
}
