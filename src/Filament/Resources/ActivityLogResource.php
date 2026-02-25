<?php

namespace Alexisgt01\CmsCore\Filament\Resources;

use Alexisgt01\CmsCore\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Journal d\'activite';

    protected static ?string $modelLabel = 'Activite';

    protected static ?string $pluralModelLabel = 'Journal d\'activite';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view activity log') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Utilisateur')
                    ->default('Systeme')
                    ->formatStateUsing(function ($record): string {
                        $causer = $record->causer;
                        if (! $causer) {
                            return 'Systeme';
                        }

                        return $causer->first_name
                            ? "{$causer->first_name} {$causer->last_name}"
                            : ($causer->name ?? 'Inconnu');
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modele')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'Alexisgt01\CmsCore\Models\BlogPost' => 'Article',
                        'Alexisgt01\CmsCore\Models\BlogCategory' => 'Categorie',
                        'Alexisgt01\CmsCore\Models\BlogTag' => 'Tag',
                        'Alexisgt01\CmsCore\Models\BlogAuthor' => 'Auteur',
                        'Alexisgt01\CmsCore\Models\Redirect' => 'Redirection',
                        'Alexisgt01\CmsCore\Models\SiteSetting' => 'Parametres du site',
                        'Alexisgt01\CmsCore\Models\BlogSetting' => 'Parametres du blog',
                        'App\Models\User' => 'Utilisateur',
                        default => class_basename($state ?? ''),
                    }),
                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'login' => 'info',
                        'logout' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Cree',
                        'updated' => 'Modifie',
                        'deleted' => 'Supprime',
                        'login' => 'Connexion',
                        'logout' => 'Deconnexion',
                        default => $state ?? '',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Action')
                    ->options([
                        'created' => 'Cree',
                        'updated' => 'Modifie',
                        'deleted' => 'Supprime',
                        'login' => 'Connexion',
                        'logout' => 'Deconnexion',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Modele')
                    ->options([
                        'Alexisgt01\CmsCore\Models\BlogPost' => 'Article',
                        'Alexisgt01\CmsCore\Models\BlogCategory' => 'Categorie',
                        'Alexisgt01\CmsCore\Models\BlogTag' => 'Tag',
                        'Alexisgt01\CmsCore\Models\BlogAuthor' => 'Auteur',
                        'Alexisgt01\CmsCore\Models\Redirect' => 'Redirection',
                        'Alexisgt01\CmsCore\Models\SiteSetting' => 'Parametres du site',
                        'Alexisgt01\CmsCore\Models\BlogSetting' => 'Parametres du blog',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (Activity $record) => view('cms-core::filament.activity-log-detail', [
                        'activity' => $record,
                    ])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
