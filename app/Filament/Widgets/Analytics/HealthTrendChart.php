<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class HealthTrendChart extends ChartWidget
{
    protected ?string $heading = 'Health Impact by Choice';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get average health change grouped by choice text
        $healthImpact = DecisionLog::select(
            'choice_text',
            DB::raw('AVG(health_change) as avg_health_change'),
            DB::raw('count(*) as total_count')
        )
            ->whereNotNull('choice_text')
            ->where('choice_text', '!=', '')
            ->groupBy('choice_text')
            ->having('total_count', '>=', 2) // Only choices with at least 2 selections
            ->orderByDesc('avg_health_change')
            ->limit(10)
            ->get();

        $labels = $healthImpact->pluck('choice_text')->map(fn($text) => substr($text, 0, 25) . (strlen($text) > 25 ? '...' : ''))->all();
        $data = $healthImpact->pluck('avg_health_change')->map(fn($v) => round((float) $v, 1))->all();

        return [
            'datasets' => [
                [
                    'label' => 'Avg Health Change',
                    'data' => $data,
                    'backgroundColor' => array_map(fn($v) => $v >= 0 ? '#22c55e' : '#ef4444', $data),
                ],
            ],
            'labels' => $labels,
        ];
    }
}
