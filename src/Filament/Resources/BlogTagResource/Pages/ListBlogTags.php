<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource;

class ListBlogTags extends ListRecords
{
    protected static string $resource = BlogTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
