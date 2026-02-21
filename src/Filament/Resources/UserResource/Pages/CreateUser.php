<?php

namespace Vendor\CmsCore\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vendor\CmsCore\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
