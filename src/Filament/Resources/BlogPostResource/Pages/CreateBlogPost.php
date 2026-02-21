<?php

namespace Vendor\CmsCore\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vendor\CmsCore\Filament\Resources\BlogPostResource;
use Vendor\CmsCore\Models\States\Published;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['state'] ?? null) === Published::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
            $data['first_published_at'] = now();
        }

        if (empty($data['reading_time_minutes']) && ! empty($data['content'])) {
            $wordCount = str_word_count(strip_tags($data['content']));
            $data['reading_time_minutes'] = max(1, (int) ceil($wordCount / 200));
        }

        return $data;
    }
}
