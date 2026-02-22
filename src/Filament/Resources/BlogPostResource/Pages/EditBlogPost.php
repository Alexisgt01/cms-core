<?php

namespace Alexisgt01\CmsCore\Filament\Resources\BlogPostResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\States\Published;
use Alexisgt01\CmsCore\Models\States\Scheduled;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
        $state = $data['state'] ?? null;
        $isPublishing = in_array($state, [Published::getMorphClass(), Scheduled::getMorphClass()], true);

        if ($isPublishing) {
            if (empty($data['meta_title'])) {
                Notification::make()
                    ->title('Le titre meta est requis pour la publication')
                    ->danger()
                    ->send();
                $this->halt();
            }

            if (empty($data['h1']) && empty($data['title'])) {
                Notification::make()
                    ->title('H1 ou titre requis pour la publication')
                    ->danger()
                    ->send();
                $this->halt();
            }

            if (empty($data['meta_description'])) {
                Notification::make()
                    ->title('La description meta est recommandee')
                    ->warning()
                    ->send();
            }
        }

        $this->detectDuplicates($data);

        if ($state === Published::getMorphClass()) {
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function detectDuplicates(array $data): void
    {
        if (! empty($data['meta_title'])) {
            $duplicateTitle = BlogPost::query()
                ->where('meta_title', $data['meta_title'])
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($duplicateTitle) {
                Notification::make()
                    ->title('Un autre article utilise le meme titre meta')
                    ->warning()
                    ->send();
            }
        }

        if (! empty($data['h1'])) {
            $duplicateH1 = BlogPost::query()
                ->where('h1', $data['h1'])
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($duplicateH1) {
                Notification::make()
                    ->title('Un autre article utilise le meme H1')
                    ->warning()
                    ->send();
            }
        }
    }
}
