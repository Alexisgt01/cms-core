<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PermissionResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
