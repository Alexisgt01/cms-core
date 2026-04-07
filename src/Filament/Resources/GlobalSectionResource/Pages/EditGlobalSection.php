<?php

namespace Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalSection extends EditRecord
{
    protected static string $resource = GlobalSectionResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
