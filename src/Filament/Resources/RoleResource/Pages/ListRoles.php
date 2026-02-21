<?php

namespace Vendor\CmsCore\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vendor\CmsCore\Filament\Resources\RoleResource;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
