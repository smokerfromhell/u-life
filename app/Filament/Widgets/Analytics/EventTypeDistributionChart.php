<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EventTypeDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Event Type Distribution';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $eventTypeCounts = DecisionLog::select('event_type', DB::raw('count(*) as aggregate'))
            ->whereNotNull('event_type')
            ->groupBy('event_type')
            ->orderByDesc('aggregate')
            ->get();

        $labels = $eventTypeCounts->pluck('event_type')->map(fn($label) => ucfirst($label))->all();
        $data = $eventTypeCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Events',
                    'data' => $data,
                    'backgroundColor' => [
                        '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
