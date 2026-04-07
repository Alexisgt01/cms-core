<?php

namespace Alexisgt01\CmsCore\Filament\Widgets;

use Alexisgt01\CmsCore\Filament\Resources\BlogPostResource;
use Alexisgt01\CmsCore\Models\BlogPost;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPostsTable extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Derniers articles';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BlogPost::query()
                    ->with(['author:id,display_name', 'category:id,name'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(50)
                    ->url(fn (BlogPost $record): string => BlogPostResource::getUrl('edit', ['record' => $record])),

                TextColumn::make('author.display_name')
                    ->label('Auteur')
                    ->placeholder('—'),

                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->placeholder('—'),

                TextColumn::make('state')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->color(fn ($state): string => $state->color()),

                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}
