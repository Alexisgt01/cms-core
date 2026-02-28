<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\HookDeliveryResource\Pages;
use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HookDeliveryResource extends Resource
{
    protected static ?string $model = HookDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Contact';

    protected static ?string $navigationLabel = 'Deliveries';

    protected static ?string $modelLabel = 'Delivery';

    protected static ?string $pluralModelLabel = 'Deliveries';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view contact hooks') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations')
                    ->schema([
                        Infolists\Components\TextEntry::make('event')
                            ->label('Evenement')
                            ->badge(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'success' => 'success',
                                'failed' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('attempt')
                            ->label('Tentative'),
                        Infolists\Components\TextEntry::make('last_http_code')
                            ->label('Code HTTP')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('endpoint.hook_key')
                            ->label('Webhook'),
                        Infolists\Components\TextEntry::make('request.type')
                            ->label('Type de demande'),
                        Infolists\Components\TextEntry::make('next_retry_at')
                            ->label('Prochain retry')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Cree le')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(4),
                Infolists\Components\Section::make('Erreur')
                    ->schema([
                        Infolists\Components\TextEntry::make('last_error')
                            ->label('')
                            ->placeholder('Aucune erreur'),
                    ])
                    ->collapsible(),
                Infolists\Components\Section::make('Corps de la requete')
                    ->schema([
                        Infolists\Components\TextEntry::make('request_body')
                            ->label('')
                            ->placeholder('—')
                            ->copyable(),
                    ])
                    ->collapsible(),
                Infolists\Components\Section::make('Corps de la reponse')
                    ->schema([
                        Infolists\Components\TextEntry::make('response_body')
                            ->label('')
                            ->placeholder('—'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->label('Evenement')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('attempt')
                    ->label('Tentative')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_http_code')
                    ->label('HTTP')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('endpoint.hook_key')
                    ->label('Webhook')
                    ->searchable(),
                Tables\Columns\TextColumn::make('request.type')
                    ->label('Type')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'success' => 'Succes',
                        'failed' => 'Echoue',
                    ]),
                Tables\Filters\SelectFilter::make('hook_endpoint_id')
                    ->label('Webhook')
                    ->relationship('endpoint', 'hook_key'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('replay')
                    ->label('Relancer')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->can('replay contact hooks') ?? false)
                    ->requiresConfirmation()
                    ->action(function (HookDelivery $record): void {
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHookDeliveries::route('/'),
            'view' => Pages\ViewHookDelivery::route('/{record}'),
        ];
    }
}
