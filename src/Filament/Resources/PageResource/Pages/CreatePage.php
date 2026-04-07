<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Concerns\HasExpandableSections;
use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Jobs\SavePageSectionsJob;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CreatePage extends CreateRecord
{
    use HasExpandableSections;

    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['state'] ?? null) === PagePublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $sections = $data['sections'] ?? null;
        unset($data['sections']);

        $record = static::getModel()::create($data);

        if (! empty($sections)) {
            $cacheKey = "page_sections:{$record->id}:".now()->timestamp;
            Cache::put($cacheKey, json_encode($sections, JSON_UNESCAPED_UNICODE), 300);

            SavePageSectionsJob::dispatch($record->id, $cacheKey);
        }

        return $record;
    }
}
