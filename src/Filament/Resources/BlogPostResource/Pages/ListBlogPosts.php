<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogPostResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
