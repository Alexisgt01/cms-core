<?php

namespace Alexisgt01\CmsCore\Filament\Resources\PageResource\Pages;

use Alexisgt01\CmsCore\Filament\Concerns\HasExpandableSections;
use Alexisgt01\CmsCore\Filament\Resources\PageResource;
use Alexisgt01\CmsCore\Jobs\SavePageSectionsJob;
use Alexisgt01\CmsCore\Models\Page;
use Alexisgt01\CmsCore\Models\States\PageDraft;
use Alexisgt01\CmsCore\Models\States\PagePublished;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EditPage extends EditRecord
{
    use HasExpandableSections;

    protected static string $resource = PageResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ReplicateAction::make()
                ->label('Dupliquer')
                ->excludeAttributes(['key', 'slug'])
                ->beforeReplicaSaved(function (Page $replica): void {
                    $replica->slug = Page::generateSlug($replica->name);
                    $replica->state = new PageDraft($replica);
                    $replica->published_at = null;
                    $replica->is_home = false;
                })
                ->successRedirectUrl(fn (Page $replica): string => PageResource::getUrl('edit', ['record' => $replica]))
                ->successNotificationTitle('Page dupliquée')
                ->visible(fn (): bool => auth()->user()?->can('create pages') ?? false),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['state'] ?? null) === PagePublished::getMorphClass() && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $sections = $data['sections'] ?? null;
        unset($data['sections']);

        $record->update($data);

        if ($sections !== null) {
            $cacheKey = "page_sections:{$record->id}:".now()->timestamp;
            Cache::put($cacheKey, json_encode($sections, JSON_UNESCAPED_UNICODE), 300);

            SavePageSectionsJob::dispatch($record->id, $cacheKey);
        }

        return $record;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Page sauvegardée')
            ->success()
            ->send();

        $this->skipRender();
    }

    /**
     * Prevent Filament's default "Saved" notification and redirect.
     */
    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
