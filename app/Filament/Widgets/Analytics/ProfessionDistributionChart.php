<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProfessionDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Profession Distribution';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $professionCounts = DecisionLog::select('after_profession', DB::raw('count(*) as aggregate'))
            ->whereNotNull('after_profession')
            ->where('after_profession', '!=', '')
            ->groupBy('after_profession')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get();

        $labels = $professionCounts->pluck('after_profession')->all();
        $data = $professionCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Players',
                    'data' => $data,
                    'backgroundColor' => [
                        '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6',
                        '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
