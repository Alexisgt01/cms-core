<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordCreation(array $data): Model
    {
        $sections = $data['sections'] ?? null;
        unset($data['sections']);

        $record = static::getModel()::create($data);

        if (! empty($sections)) {
            Page::withoutEvents(fn () => Page::withoutTimestamps(
                fn () => Page::query()
                    ->where('id', $record->id)
                    ->update(['sections' => json_encode($sections)])
            ));
        }

        return $record;
    }
}
