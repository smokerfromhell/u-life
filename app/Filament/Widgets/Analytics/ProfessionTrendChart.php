<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Profession Popularity Over Time Widget
 * Shows trending professions by month
 * Helps identify which careers are most popular among players
 */
class ProfessionTrendChart extends ChartWidget
{
    protected ?string $heading = 'Profession Trends Over Time';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        // Check if decision_made_at column exists
        if (!\Illuminate\Support\Facades\Schema::hasColumn('decision_logs', 'decision_made_at')) {
            return [
                'datasets' => [],
                'labels' => ['Migration not run'],
            ];
        }

        // Get profession distribution over time (last 6 months)
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $professionTrends = DecisionLog::select(
            DB::raw('DATE_FORMAT(decision_made_at, "%Y-%m") as month'),
            'profession',
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('profession')
            ->where('profession', '!=', '')
            ->where('decision_made_at', '>=', $sixMonthsAgo)
            ->groupBy('month', 'profession')
            ->orderBy('month')
            ->get();

        // Group by month and get top professions
        $months = $professionTrends->pluck('month')->unique()->sort()->values();
        $professions = $professionTrends->pluck('profession')->unique()->take(5); // Top 5 professions

        $datasets = [];
        $colors = ['#8b5cf6', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444'];

        foreach ($professions as $index => $profession) {
            $data = [];
            foreach ($months as $month) {
                $count = $professionTrends
                    ->where('month', $month)
                    ->where('profession', $profession)
                    ->sum('count');
                $data[] = $count;
            }

            $datasets[] = [
                'label' => ucfirst($profession ?? 'Unknown'),
                'data' => $data,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $months->toArray(),
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
                        'text' => 'Number of Decisions',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
            ],
        ];
    }
}