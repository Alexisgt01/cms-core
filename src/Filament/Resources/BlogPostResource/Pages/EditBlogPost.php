<?php

namespace Vendor\CmsCore\Filament\Resources\BlogPostResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Vendor\CmsCore\Filament\Resources\BlogPostResource;
use Vendor\CmsCore\Models\States\Published;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['state'] ?? null) === Published::getMorphClass()) {
            if (empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if (empty($this->record->first_published_at)) {
                $data['first_published_at'] = now();
            }
        }

        $data['updated_content_at'] = now();

        if (empty($data['reading_time_minutes']) && ! empty($data['content'])) {
            $wordCount = str_word_count(strip_tags($data['content']));
            $data['reading_time_minutes'] = max(1, (int) ceil($wordCount / 200));
        }

        return $data;
    }
}
