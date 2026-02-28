<?php

namespace Alexisgt01\CmsCore\Filament\Resources\ContactResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
