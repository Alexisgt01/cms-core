<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Alexisgt01\CmsCore\Filament\Resources\BlogTagResource;

class EditBlogTag extends EditRecord
{
    protected static string $resource = BlogTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
