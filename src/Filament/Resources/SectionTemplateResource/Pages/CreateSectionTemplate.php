<?php

namespace Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSectionTemplate extends CreateRecord
{
    protected static string $resource = SectionTemplateResource::class;

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
