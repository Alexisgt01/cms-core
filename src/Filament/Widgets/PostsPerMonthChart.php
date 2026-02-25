<?php

namespace Alexisgt01\CmsCore\Filament\Widgets;

use Alexisgt01\CmsCore\Models\BlogPost;
use Alexisgt01\CmsCore\Models\States\Published;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PostsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Articles publiés par mois';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $driver = BlogPost::query()->getConnection()->getDriverName();

        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', published_at)",
            default => "DATE_FORMAT(published_at, '%Y-%m')",
        };

        $posts = BlogPost::query()
            ->where('state', Published::class)
            ->whereBetween('published_at', [$start, $end])
            ->selectRaw("{$monthExpression} as month, count(*) as count")
            ->groupBy('month')
            ->pluck('count', 'month');

        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');
            $data[] = $posts->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Articles publiés',
                    'data' => $data,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
