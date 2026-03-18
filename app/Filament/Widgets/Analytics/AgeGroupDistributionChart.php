<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AgeGroupDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Age Group Distribution';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $ageGroupCounts = DecisionLog::select('age_group', DB::raw('count(*) as aggregate'))
            ->whereNotNull('age_group')
            ->groupBy('age_group')
            ->orderByDesc('aggregate')
            ->get();

        $labels = $ageGroupCounts->pluck('age_group')->map(fn($label) => ucfirst($label))->all();
        $data = $ageGroupCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Players',
                    'data' => $data,
                    'backgroundColor' => ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444'],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
