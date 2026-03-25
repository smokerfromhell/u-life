<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Luck Impact Analysis Widget
 * Analyzes if players with different luck levels make different choices or have better outcomes
 * Helps balance the luck system by understanding its actual impact
 */
class LuckImpactChart extends ChartWidget
{
    protected ?string $heading = 'Luck Impact on Outcomes';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Check if current_luck column exists
        if (!\Illuminate\Support\Facades\Schema::hasColumn('decision_logs', 'current_luck')) {
            return [
                'datasets' => [],
                'labels' => ['Migration not run - run php artisan migrate'],
            ];
        }

        // Get average outcomes by luck tier (using current_luck field)
        $luckTiers = DecisionLog::select(
            DB::raw('CASE 
                WHEN current_luck >= 70 THEN "High Luck (70+)"
                WHEN current_luck >= 40 THEN "Medium Luck (40-69)"
                ELSE "Low Luck (<40)"
            END as luck_tier'),
            DB::raw('COUNT(*) as total_decisions'),
            DB::raw('AVG(health_change) as avg_health_change'),
            DB::raw('AVG(happiness_change) as avg_happiness_change'),
            DB::raw('AVG(finance_change) as avg_finance_change')
        )
            ->whereNotNull('current_luck')
            ->groupBy('luck_tier')
            ->orderByDesc('luck_tier')
            ->get();

        $labels = $luckTiers->pluck('luck_tier')->toArray();
        $healthData = $luckTiers->pluck('avg_health_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $happinessData = $luckTiers->pluck('avg_happiness_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $financeData = $luckTiers->pluck('avg_finance_change')->map(fn($v) => round((float) $v, 1))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Health',
                    'data' => $healthData,
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Happiness',
                    'data' => $happinessData,
                    'backgroundColor' => '#f59e0b',
                ],
                [
                    'label' => 'Finance',
                    'data' => $financeData,
                    'backgroundColor' => '#3b82f6',
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
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Average Change',
                    ],
                ],
            ],
        ];
    }
}