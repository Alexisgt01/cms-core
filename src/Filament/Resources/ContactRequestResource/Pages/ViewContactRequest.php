<?php

namespace Alexisgt01\CmsCore\Filament\Resources\ContactRequestResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\ContactRequestResource;
use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Alexisgt01\CmsCore\Models\HookEndpoint;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewContactRequest extends ViewRecord
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('replay_hooks')
                ->label('Relancer les hooks')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()?->can('replay contact hooks') ?? false)
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->record;

                    $endpoints = HookEndpoint::query()
                        ->where('enabled', true)
                        ->get()
                        ->filter(fn (HookEndpoint $ep): bool => $ep->acceptsEvent($record->type));

                    $count = 0;

                    foreach ($endpoints as $endpoint) {
                        $delivery = HookDelivery::create([
                            'hook_endpoint_id' => $endpoint->id,
                            'contact_request_id' => $record->id,
                            'event' => $record->type,
                            'status' => 'pending',
                            'attempt' => 0,
                        ]);

                        DeliverContactHookJob::dispatch($delivery->id);
                        $count++;
                    }

                    Notification::make()
                        ->title("{$count} hook(s) relance(s)")
                        ->success()
                        ->send();
                }),
        ];
    }
}
