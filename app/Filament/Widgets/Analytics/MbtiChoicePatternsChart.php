<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * MBTI Choice Patterns Widget
 * Shows which MBTI personality types tend to make what choices
 * Helps understand if the adaptive narrative is working correctly
 */
class MbtiChoicePatternsChart extends ChartWidget
{
    protected ?string $heading = 'MBTI Choice Patterns';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get top choices by MBTI type
        // Show which MBTI types prefer which choice patterns
        $mbtiChoices = DecisionLog::select(
            'mbti',
            'choice_text',
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('mbti')
            ->where('mbti', '!=', '')
            ->whereNotNull('choice_text')
            ->where('choice_text', '!=', '')
            ->groupBy('mbti', 'choice_text')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        // Group by MBTI first letter dimension
        $dimensionChoices = [
            'E' => [], // Extraversion
            'I' => [], // Introversion
            'N' => [], // Intuition
            'S' => [], // Sensing
            'T' => [], // Thinking
            'F' => [], // Feeling
            'J' => [], // Judging
            'P' => [], // Perceiving
        ];

        foreach ($mbtiChoices as $record) {
            if (strlen($record->mbti) >= 1) {
                $firstLetter = $record->mbti[0];
                if (isset($dimensionChoices[$firstLetter])) {
                    if (!isset($dimensionChoices[$firstLetter][$record->choice_text])) {
                        $dimensionChoices[$firstLetter][$record->choice_text] = 0;
                    }
                    $dimensionChoices[$firstLetter][$record->choice_text] += $record->count;
                }
            }
        }

        // Get top 3 choices for each dimension
        $labels = ['E vs I', 'N vs S', 'T vs F', 'J vs P'];
        
        $eVsI = [
            $dimensionChoices['E'] ? max($dimensionChoices['E']) : 0,
            $dimensionChoices['I'] ? max($dimensionChoices['I']) : 0,
        ];
        
        $nVsS = [
            $dimensionChoices['N'] ? max($dimensionChoices['N']) : 0,
            $dimensionChoices['S'] ? max($dimensionChoices['S']) : 0,
        ];
        
        $tVsF = [
            $dimensionChoices['T'] ? max($dimensionChoices['T']) : 0,
            $dimensionChoices['F'] ? max($dimensionChoices['F']) : 0,
        ];
        
        $jVsP = [
            $dimensionChoices['J'] ? max($dimensionChoices['J']) : 0,
            $dimensionChoices['P'] ? max($dimensionChoices['P']) : 0,
        ];

        return [
            'datasets' => [
                [
                    'label' => 'First Letter (E vs I)',
                    'data' => $eVsI,
                    'backgroundColor' => ['#8b5cf6', '#a78bfa'],
                ],
                [
                    'label' => 'Second Letter (N vs S)',
                    'data' => $nVsS,
                    'backgroundColor' => ['#3b82f6', '#60a5fa'],
                ],
                [
                    'label' => 'Third Letter (T vs F)',
                    'data' => $tVsF,
                    'backgroundColor' => ['#22c55e', '#4ade80'],
                ],
                [
                    'label' => 'Fourth Letter (J vs P)',
                    'data' => $jVsP,
                    'backgroundColor' => ['#f59e0b', '#fbbf24'],
                ],
            ],
            'labels' => ['Extraversion', 'Intuition', 'Thinking', 'Judging'],
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
                        'text' => 'Most Popular Choice Count',
                    ],
                ],
            ],
        ];
    }
}