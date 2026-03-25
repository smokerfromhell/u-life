<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Age Group Progression Widget
 * Shows how players' stats change as they progress through different life stages
 * Helps identify which age groups are thriving or struggling
 */
class AgeGroupProgressionChart extends ChartWidget
{
    protected ?string $heading = 'Stat Progression by Age Group';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Get average stat changes by age group
        $ageGroupStats = DecisionLog::select(
            'age_group',
            DB::raw('AVG(health_change) as avg_health_change'),
            DB::raw('AVG(happiness_change) as avg_happiness_change'),
            DB::raw('AVG(finance_change) as avg_finance_change'),
            DB::raw('COUNT(*) as total_decisions')
        )
            ->whereNotNull('age_group')
            ->where('age_group', '!=', '')
            ->groupBy('age_group')
            ->orderByRaw("CASE 
                WHEN age_group = 'child' THEN 1 
                WHEN age_group = 'teenager' THEN 2 
                WHEN age_group = 'adult' THEN 3 
                WHEN age_group = 'old' THEN 4 
                ELSE 5 
            END")
            ->get();

        $labels = $ageGroupStats->pluck('age_group')->map(function ($age) {
            return ucfirst($age ?? 'Unknown');
        })->toArray();

        $healthData = $ageGroupStats->pluck('avg_health_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $happinessData = $ageGroupStats->pluck('avg_happiness_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $financeData = $ageGroupStats->pluck('avg_finance_change')->map(fn($v) => round((float) $v, 1))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Health',
                    'data' => $healthData,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Happiness',
                    'data' => $happinessData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#f59e0b',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Finance',
                    'data' => $financeData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Average Change per Decision',
                    ],
                ],
            ],
        ];
    }
}