<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Choice Effectiveness Analysis Widget
 * Shows which choices lead to the best outcomes across different stats
 * Helps game designers balance choices by seeing their average impact
 */
class ChoiceEffectivenessChart extends ChartWidget
{
    protected ?string $heading = 'Choice Effectiveness Analysis';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get choices with their effectiveness scores
        // A choice is "effective" if it improves overall life stats
        $choiceEffectiveness = DecisionLog::select(
            'choice_text',
            DB::raw('COUNT(*) as total_picks'),
            DB::raw('AVG(health_change) as avg_health_change'),
            DB::raw('AVG(happiness_change) as avg_happiness_change'),
            DB::raw('AVG(finance_change) as avg_finance_change'),
            DB::raw('(COALESCE(AVG(health_change), 0) + COALESCE(AVG(happiness_change), 0) + COALESCE(AVG(finance_change), 0)) / 3 as overall_effectiveness')
        )
            ->whereNotNull('choice_text')
            ->where('choice_text', '!=', '')
            ->groupBy('choice_text')
            ->having('total_picks', '>=', 3) // Only show choices picked at least 3 times
            ->orderByDesc('overall_effectiveness')
            ->limit(12)
            ->get();

        $labels = $choiceEffectiveness->pluck('choice_text')->map(function ($text) {
            return strlen($text) > 25 ? substr($text, 0, 25) . '...' : $text;
        })->toArray();

        $healthData = $choiceEffectiveness->pluck('avg_health_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $happinessData = $choiceEffectiveness->pluck('avg_happiness_change')->map(fn($v) => round((float) $v, 1))->toArray();
        $financeData = $choiceEffectiveness->pluck('avg_finance_change')->map(fn($v) => round((float) $v, 1))->toArray();

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