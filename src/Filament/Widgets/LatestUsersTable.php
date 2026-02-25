<?php

namespace Alexisgt01\CmsCore\Filament\Widgets;

use App\Models\User;
use Alexisgt01\CmsCore\Filament\Resources\UserResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUsersTable extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Derniers utilisateurs';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->with('roles:id,name')
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nom')
                    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}")
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),

                TextColumn::make('email')
                    ->label('Email'),

                TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}
