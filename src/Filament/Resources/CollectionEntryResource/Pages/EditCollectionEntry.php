<?php

namespace Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource;
use Alexisgt01\CmsCore\Models\States\EntryPublished;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Url;

class EditCollectionEntry extends EditRecord
{
    protected static string $resource = CollectionEntryResource::class;

    #[Url(as: 'collectionType')]
    public ?string $collectionType = null;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['state'] ?? null) === EntryPublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $collectionType = $this->record->collection_type ?? $this->collectionType ?? '';

        return static::$resource::getUrl('index').'?collectionType='.$collectionType;
    }
}
