<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Alexisgt01\CmsCore\Filament\Resources\BlogAuthorResource;

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
