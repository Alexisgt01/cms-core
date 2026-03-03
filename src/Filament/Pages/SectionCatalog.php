<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Filament\Resources\SectionTemplateResource;
use Alexisgt01\CmsCore\Models\SectionTemplate;
use Alexisgt01\CmsCore\Sections\SectionRegistry;
use Filament\Pages\Page;

class SectionCatalog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Contenu';

    protected static ?string $navigationLabel = 'Sections';

    protected static ?string $title = 'Sections';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'cms-core::filament.pages.section-catalog';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view pages') ?? false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSectionTypes(): array
    {
        $registry = app(SectionRegistry::class);
        $templateCounts = SectionTemplate::query()
            ->selectRaw('section_type, count(*) as count')
            ->groupBy('section_type')
            ->pluck('count', 'section_type')
            ->toArray();

        $types = [];

        foreach ($registry->all() as $key => $typeClass) {
            $types[] = [
                'key' => $key,
                'label' => $typeClass::label(),
                'icon' => $typeClass::icon(),
                'description' => $typeClass::description(),
                'fields_count' => count($typeClass::fields()),
                'templates_count' => $templateCounts[$key] ?? 0,
                'create_template_url' => SectionTemplateResource::getUrl('create') . '?sectionType=' . $key,
            ];
        }

        return $types;
    }
}
