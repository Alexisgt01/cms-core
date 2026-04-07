<?php

namespace Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGlobalSection extends CreateRecord
{
    protected static string $resource = GlobalSectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['section_type'])) {
            $data['section_type'] = request()->query('sectionType', '');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::$resource::getUrl('index');
    }
}
