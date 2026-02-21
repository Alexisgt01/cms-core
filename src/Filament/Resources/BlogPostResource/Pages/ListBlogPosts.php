<?php

namespace Vendor\CmsCore\Filament\Resources\BlogPostResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vendor\CmsCore\Filament\Resources\BlogPostResource;

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
