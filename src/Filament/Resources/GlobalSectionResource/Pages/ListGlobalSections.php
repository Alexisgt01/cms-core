<?php

namespace Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource\Pages;

use Alexisgt01\CmsCore\Filament\Resources\GlobalSectionResource;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListGlobalSections extends ListRecords
{
    protected static string $resource = GlobalSectionResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Creer une section globale')
                ->icon('heroicon-m-plus')
                ->form([
                    Select::make('section_type')
                        ->label('Type de section')
                        ->options(function (): array {
                            $options = [];

                            foreach (app(SectionRegistry::class)->all() as $key => $class) {
                                $options[$key] = $class::label();
                            }

                            return $options;
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->redirect(
                        GlobalSectionResource::getUrl('create') . '?sectionType=' . $data['section_type'],
                    );
                })
                ->visible(fn (): bool => auth()->user()?->can('create pages') ?? false),
        ];
    }
}
