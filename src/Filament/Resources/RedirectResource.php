<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\RedirectResource\Pages;
use Alexisgt01\CmsCore\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?string $navigationLabel = 'Redirections';

    protected static ?string $modelLabel = 'Redirection';

    protected static ?string $pluralModelLabel = 'Redirections';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view redirects') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create redirects') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit redirects') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete redirects') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('source_path')
                    ->label('Chemin source')
                    ->required()
                    ->maxLength(2048)
                    ->prefix('/')
                    ->placeholder('ancien-article')
                    ->helperText('Chemin relatif sans le domaine (ex: ancien-article)'),
                Forms\Components\Select::make('status_code')
                    ->label('Code HTTP')
                    ->options([
                        301 => '301 — Permanent',
                        302 => '302 — Temporaire',
                        307 => '307 — Temporaire strict',
                        410 => '410 — Supprime (Gone)',
                    ])
                    ->required()
                    ->default(301)
                    ->live(),
                Forms\Components\TextInput::make('destination_url')
                    ->label('URL de destination')
                    ->maxLength(2048)
                    ->placeholder('https://example.com/nouvel-article ou /nouvel-article')
                    ->helperText('URL absolue ou chemin relatif')
                    ->visible(fn (Forms\Get $get): bool => (int) $get('status_code') !== 410)
                    ->required(fn (Forms\Get $get): bool => (int) $get('status_code') !== 410),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Textarea::make('note')
                    ->label('Note')
                    ->rows(2)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_path')
                    ->label('Source')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(60),
                Tables\Columns\TextColumn::make('destination_url')
                    ->label('Destination')
                    ->limit(50)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status_code')
                    ->label('Code')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        301 => 'success',
                        302 => 'warning',
                        307 => 'info',
                        410 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('hit_count')
                    ->label('Hits')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_hit_at')
                    ->label('Dernier hit')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Jamais')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_code')
                    ->label('Code HTTP')
                    ->options([
                        301 => '301 Permanent',
                        302 => '302 Temporaire',
                        307 => '307 Temporaire strict',
                        410 => '410 Supprime',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
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
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
