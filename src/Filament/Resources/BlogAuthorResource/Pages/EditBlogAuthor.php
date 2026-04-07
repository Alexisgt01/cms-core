<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogAuthor extends EditRecord
{
    protected static string $resource = BlogAuthorResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
