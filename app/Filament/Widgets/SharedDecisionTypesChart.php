<?php

namespace App\Filament\Widgets;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SharedDecisionTypesChart extends ChartWidget
{
    protected ?string $heading = 'Shared Decisions by Type (7d)';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // No caching - always get fresh data for real-time updates
        $since = Carbon::now()->subDays(7);

        $rows = DecisionLog::query()
            ->select('event_type', DB::raw('count(*) as aggregate'))
            ->where('created_at', '>=', $since)
            ->groupBy('event_type')
            ->orderByDesc('aggregate')
            ->limit(10)  // Top 10 only
            ->get();

        $labels = $rows->pluck('event_type')->all();
        $data = $rows->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Decisions',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
