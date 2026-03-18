<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MbtiDistributionChart extends ChartWidget
{
    protected ?string $heading = 'MBTI Personality Distribution';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $mbtiCounts = DecisionLog::select('mbti', DB::raw('count(*) as aggregate'))
            ->whereNotNull('mbti')
            ->where('mbti', '!=', '')
            ->groupBy('mbti')
            ->orderByDesc('aggregate')
            ->limit(16)
            ->get();

        $labels = $mbtiCounts->pluck('mbti')->all();
        $data = $mbtiCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        $colors = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e',
            '#f97316', '#eab308', '#22c55e', '#14b8a6',
            '#06b6d4', '#3b82f6', '#a855f7', '#d946ef',
            '#f43f5e', '#fb7185', '#fbbf24', '#34d399',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Players',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }
}
