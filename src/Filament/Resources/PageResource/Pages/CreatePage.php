<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Jobs\SavePageSectionsJob;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Notifications\Notification;
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
            SavePageSectionsJob::dispatch($record->id, $sections);

            Notification::make()
                ->title('Sections en cours de sauvegarde')
                ->body('Les sections sont enregistrées en arrière-plan.')
                ->info()
                ->send();
        }

        return $record;
    }
}
