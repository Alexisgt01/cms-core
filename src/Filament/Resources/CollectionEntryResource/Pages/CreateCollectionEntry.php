<?php

namespace Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource\Pages;

use Alexisgt01\CmsCore\Collections\CollectionRegistry;
use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource;
use Alexisgt01\CmsCore\Models\CollectionEntry;
use Alexisgt01\CmsCore\Models\States\EntryPublished;
use Filament\Resources\Pages\CreateRecord;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['state'] ?? null) === EntryPublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Auto-generate slug from the configured source field if empty
        $typeClass = app(CollectionRegistry::class)->resolve($data['collection_type'] ?? '');

        if ($typeClass && $typeClass::hasSlug() && empty($data['slug'])) {
            $sourceField = $typeClass::slugFrom();
            $sourceValue = data_get($data, 'data.' . $sourceField, '');

            if ($sourceValue) {
                $data['slug'] = CollectionEntry::generateSlug($sourceValue, $data['collection_type']);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $collectionType = $this->data['collection_type'] ?? request()->query('collectionType', '');

        return static::$resource::getUrl('index') . '?collectionType=' . $collectionType;
    }
}
