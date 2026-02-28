<?php

namespace Alexisgt01\CmsCore\Filament\Resources\HookEndpointResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\HookEndpointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHookEndpoints extends ListRecords
{
    protected static string $resource = HookEndpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
