<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['state'] ?? null) === PagePublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
