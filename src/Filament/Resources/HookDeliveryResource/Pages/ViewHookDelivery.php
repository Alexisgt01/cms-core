<?php

namespace Alexisgt01\CmsCore\Filament\Resources\HookDeliveryResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\HookDeliveryResource;
use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewHookDelivery extends ViewRecord
{
    protected static string $resource = HookDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('replay')
                ->label('Relancer')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()?->can('replay contact hooks') ?? false)
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->record;

                    $newDelivery = HookDelivery::create([
                        'hook_endpoint_id' => $record->hook_endpoint_id,
                        'contact_request_id' => $record->contact_request_id,
                        'event' => $record->event,
                        'status' => 'pending',
                        'attempt' => 0,
                    ]);

                    DeliverContactHookJob::dispatch($newDelivery->id);

                    Notification::make()
                        ->title('Hook relance')
                        ->success()
                        ->send();
                }),
        ];
    }
}
