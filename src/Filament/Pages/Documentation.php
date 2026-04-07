<?php

namespace Alexisgt01\CmsCore\Filament\Pages;

use Alexisgt01\CmsCore\Services\DocumentationService;
use Filament\Pages\Page;

class Documentation extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Guide d\'utilisation';

    protected static ?string $title = 'Guide d\'utilisation';

    protected static ?string $slug = 'documentation';

    protected static string $view = 'cms-core::filament.pages.documentation';

    protected static bool $shouldRegisterNavigation = false;

    public string $activeSection = '';

    public function mount(): void
    {
        $sections = app(DocumentationService::class)->all();

        if ($sections->isNotEmpty()) {
            $this->activeSection = request()->query('section', $sections->first()['slug']);
        }
    }

    public function getSections(): array
    {
        return app(DocumentationService::class)->all()->toArray();
    }

    public function getActiveContent(): ?array
    {
        if (! $this->activeSection) {
            return null;
        }

        return app(DocumentationService::class)->find($this->activeSection);
    }

    public function setSection(string $slug): void
    {
        $this->activeSection = $slug;
    }
}
