<?php

namespace Alexisgt01\CmsCore\Filament\Resources\RoleResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
