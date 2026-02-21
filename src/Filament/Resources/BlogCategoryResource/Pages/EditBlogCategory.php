<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource;

class EditBlogCategory extends EditRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
