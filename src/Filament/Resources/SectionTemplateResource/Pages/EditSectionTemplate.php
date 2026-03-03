<?php

namespace Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSectionTemplate extends EditRecord
{
    protected static string $resource = SectionTemplateResource::class;

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
