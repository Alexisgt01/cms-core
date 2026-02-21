<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Alexisgt01\CmsCore\Filament\Resources\BlogCategoryResource;

class ListBlogCategories extends ListRecords
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
