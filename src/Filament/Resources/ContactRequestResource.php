<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\ContactRequestResource\Pages;
use Alexisgt01\CmsCore\Jobs\DeliverContactHookJob;
use Alexisgt01\CmsCore\Models\ContactRequest;
use Alexisgt01\CmsCore\Models\HookDelivery;
use Alexisgt01\CmsCore\Models\HookEndpoint;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactRequestResource extends Resource
{
    protected static ?string $model = ContactRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Contact';

    protected static ?string $navigationLabel = 'Demandes';

    protected static ?string $modelLabel = 'Demande';

    protected static ?string $pluralModelLabel = 'Demandes';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view contact requests') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit contact requests') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete contact requests') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('state')
                    ->label('Statut')
                    ->options([
                        'new' => 'Nouveau',
                        'processed' => 'Traite',
                        'archived' => 'Archive',
                    ])
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations')
                    ->schema([
                        Infolists\Components\TextEntry::make('type')
                            ->label('Type')
                            ->badge(),
                        Infolists\Components\TextEntry::make('form_id')
                            ->label('Formulaire')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('state')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'new' => 'info',
                                'processed' => 'success',
                                'archived' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'new' => 'Nouveau',
                                'processed' => 'Traite',
                                'archived' => 'Archive',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('contact.email')
                            ->label('Contact')
                            ->placeholder('Anonyme'),
                        Infolists\Components\TextEntry::make('idempotency_key')
                            ->label('Cle d\'idempotence')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Cree le')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Payload')
                    ->schema([
                        Infolists\Components\TextEntry::make('payload')
                            ->label('')
                            ->formatStateUsing(function (mixed $state): string {
                                return self::formatKeyValueHtml($state);
                            })
                            ->html(),
                    ]),
                Infolists\Components\Section::make('Meta')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta')
                            ->label('')
                            ->formatStateUsing(function (mixed $state): string {
                                if (! $state) {
                                    return '—';
                                }

                                return self::formatKeyValueHtml($state);
                            })
                            ->html(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('form_id')
                    ->label('Formulaire')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'processed' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nouveau',
                        'processed' => 'Traite',
                        'archived' => 'Archive',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact.email')
                    ->label('Contact')
                    ->searchable()
                    ->placeholder('Anonyme'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(fn (): array => ContactRequest::query()
                        ->distinct()
                        ->pluck('type', 'type')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('state')
                    ->label('Statut')
                    ->options([
                        'new' => 'Nouveau',
                        'processed' => 'Traite',
                        'archived' => 'Archive',
                    ]),
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
                Actions\ViewAction::make(),
                Actions\Action::make('replay_hooks')
                    ->label('Relancer les hooks')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->can('replay contact hooks') ?? false)
                    ->requiresConfirmation()
                    ->action(function (ContactRequest $record): void {
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
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function formatKeyValueHtml(mixed $data): string
    {
        if (! is_array($data)) {
            return nl2br(e((string) $data));
        }

        $lines = [];

        foreach ($data as $key => $value) {
            $label = e(ucfirst(str_replace('_', ' ', (string) $key)));

            if (is_array($value)) {
                $val = e(json_encode($value, JSON_UNESCAPED_UNICODE));
            } else {
                $val = nl2br(e((string) $value));
            }

            $lines[] = '<div style="margin-bottom: 0.5rem;"><strong>'.$label.' :</strong> '.$val.'</div>';
        }

        return implode('', $lines);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactRequests::route('/'),
            'view' => Pages\ViewContactRequest::route('/{record}'),
        ];
    }
}
