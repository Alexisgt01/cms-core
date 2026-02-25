<?php

namespace Alexisgt01\CmsCore\Filament\Widgets;

use Alexisgt01\CmsCore\Models\BlogAuthor;
use Alexisgt01\CmsCore\Models\BlogCategory;
use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\BlogTag;
use Alexisgt01\CmsCore\Models\States\Draft;
use Alexisgt01\CmsCore\Models\States\Published;
use Alexisgt01\CmsCore\Models\States\Scheduled;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BlogStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $postCounts = BlogPost::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when state = ? then 1 else 0 end) as published", [Published::class])
            ->selectRaw("sum(case when state = ? then 1 else 0 end) as scheduled", [Scheduled::class])
            ->selectRaw("sum(case when state = ? then 1 else 0 end) as draft", [Draft::class])
            ->first();

        $publishedLast30 = BlogPost::query()
            ->where('state', Published::class)
            ->where('published_at', '>=', now()->subDays(30))
            ->count();

        $categoriesCount = BlogCategory::query()->count();
        $tagsCount = BlogTag::query()->count();
        $authorsCount = BlogAuthor::query()->count();

        return [
            Stat::make('Articles publiés', $postCounts->published ?? 0)
                ->description($publishedLast30 . ' ces 30 derniers jours')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-document-check'),

            Stat::make('Articles programmés', $postCounts->scheduled ?? 0)
                ->description('En attente de publication')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Brouillons', $postCounts->draft ?? 0)
                ->description('En cours de rédaction')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('gray')
                ->icon('heroicon-o-pencil-square'),

            Stat::make('Catégories', $categoriesCount)
                ->icon('heroicon-o-folder'),

            Stat::make('Tags', $tagsCount)
                ->icon('heroicon-o-tag'),

            Stat::make('Auteurs', $authorsCount)
                ->icon('heroicon-o-users'),
        ];
    }
}
