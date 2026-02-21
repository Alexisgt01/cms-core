<?php

namespace Vendor\CmsCore\Filament\Resources\PermissionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vendor\CmsCore\Filament\Resources\PermissionResource;

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
