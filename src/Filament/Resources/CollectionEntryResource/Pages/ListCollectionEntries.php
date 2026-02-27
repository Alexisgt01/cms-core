<?php

namespace Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource\Pages;

use Alexisgt01\CmsCore\Collections\CollectionRegistry;
use Alexisgt01\CmsCore\Filament\Resources\CollectionEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollectionEntries extends ListRecords
{
    protected static string $resource = CollectionEntryResource::class;

    public function getTitle(): string
    {
        $typeClass = $this->resolveTypeClass();

        return $typeClass ? $typeClass::label() : 'Entrees';
    }

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        $collectionType = request()->query('collectionType');
        $typeClass = $this->resolveTypeClass();

        $actions = [];

        if ($collectionType) {
            $action = Actions\CreateAction::make()
                ->url(static::$resource::getUrl('create') . '?collectionType=' . $collectionType);

            if ($typeClass && $typeClass::maxEntries() > 0) {
                $currentCount = static::$resource::getEloquentQuery()->count();
                $action = $action->visible($currentCount < $typeClass::maxEntries());
            }

            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * @return class-string<\Alexisgt01\CmsCore\Collections\CollectionType>|null
     */
    protected function resolveTypeClass(): ?string
    {
        $key = request()->query('collectionType');

        if (! $key) {
            return null;
        }

        return app(CollectionRegistry::class)->resolve($key);
    }
}
