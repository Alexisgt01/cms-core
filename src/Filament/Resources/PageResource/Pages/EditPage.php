<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

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
        if (($data['state'] ?? null) === PagePublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
