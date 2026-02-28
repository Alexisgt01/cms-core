<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\HookEndpointResource\Pages;
use Alexisgt01\CmsCore\Models\HookEndpoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HookEndpointResource extends Resource
{
    protected static ?string $model = HookEndpoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Contact';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?string $modelLabel = 'Webhook';

    protected static ?string $pluralModelLabel = 'Webhooks';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view contact hooks') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create contact hooks') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit contact hooks') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete contact hooks') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('hook_key')
                    ->label('Cle')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Identifiant unique du webhook'),
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->url()
                    ->maxLength(2048),
                Forms\Components\TextInput::make('secret')
                    ->label('Secret HMAC')
                    ->required()
                    ->maxLength(255)
                    ->default(fn (): string => Str::random(40))
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generate_secret')
                            ->icon('heroicon-o-arrow-path')
                            ->action(fn (Forms\Set $set) => $set('secret', Str::random(40))),
                    ),
                Forms\Components\Toggle::make('enabled')
                    ->label('Actif')
                    ->default(true),
                Forms\Components\TagsInput::make('events')
                    ->label('Evenements acceptes')
                    ->helperText('Laisser vide pour accepter tous les evenements')
                    ->placeholder('ex: contact, newsletter, quote'),
                Forms\Components\TextInput::make('timeout')
                    ->label('Timeout (s)')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(30),
                Forms\Components\TextInput::make('retries')
                    ->label('Tentatives max')
                    ->numeric()
                    ->default(3)
                    ->minValue(0)
                    ->maxValue(10),
                Forms\Components\TagsInput::make('backoff')
                    ->label('Delais de retry (s)')
                    ->helperText('Delais entre chaque tentative en secondes')
                    ->default([5, 30, 120])
                    ->placeholder('ex: 5, 30, 120'),
                Forms\Components\KeyValue::make('headers')
                    ->label('Headers additionnels')
                    ->keyLabel('Header')
                    ->valueLabel('Valeur'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hook_key')
                    ->label('Cle')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40),
                Tables\Columns\ToggleColumn::make('enabled')
                    ->label('Actif'),
                Tables\Columns\TextColumn::make('deliveries_count')
                    ->label('Deliveries')
                    ->counts('deliveries')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHookEndpoints::route('/'),
            'create' => Pages\CreateHookEndpoint::route('/create'),
            'edit' => Pages\EditHookEndpoint::route('/{record}/edit'),
        ];
    }
}
