<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MbtiDimensionChart extends ChartWidget
{
    protected ?string $heading = 'MBTI Dimension Breakdown';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Get MBTI distribution
        $mbtiCounts = DecisionLog::select('mbti', DB::raw('count(*) as count'))
            ->whereNotNull('mbti')
            ->where('mbti', '!=', '')
            ->groupBy('mbti')
            ->pluck('count', 'mbti')
            ->toArray();
        
        // Calculate dimension percentages
        $dimensions = [
            'E' => ['label' => 'Extraversion', 'value' => 0, 'opposite' => 'I'],
            'I' => ['label' => 'Introversion', 'value' => 0, 'opposite' => 'E'],
            'N' => ['label' => 'Intuition', 'value' => 0, 'opposite' => 'S'],
            'S' => ['label' => 'Sensing', 'value' => 0, 'opposite' => 'N'],
            'T' => ['label' => 'Thinking', 'value' => 0, 'opposite' => 'F'],
            'F' => ['label' => 'Feeling', 'value' => 0, 'opposite' => 'T'],
            'J' => ['label' => 'Judging', 'value' => 0, 'opposite' => 'P'],
            'P' => ['label' => 'Perceiving', 'value' => 0, 'opposite' => 'J'],
        ];
        
        $total = 0;
        foreach ($mbtiCounts as $mbti => $count) {
            $traits = str_split($mbti);
            foreach ($traits as $trait) {
                if (isset($dimensions[$trait])) {
                    $dimensions[$trait]['value'] += $count;
                    $total += $count;
                }
            }
        }
        
        // Calculate percentages
        $result = [];
        $labels = [];
        $oppositeLabels = [];
        
        $dimensionPairs = [
            ['E', 'I'],
            ['N', 'S'],
            ['T', 'F'],
            ['J', 'P'],
        ];
        
        foreach ($dimensionPairs as [$primary, $opposite]) {
            $primaryValue = $dimensions[$primary]['value'];
            $oppositeValue = $dimensions[$opposite]['value'];
            $pairTotal = $primaryValue + $oppositeValue;
            
            if ($pairTotal > 0) {
                $result[] = round(($primaryValue / $pairTotal) * 100);
                $result[] = round(($oppositeValue / $pairTotal) * 100);
            } else {
                $result[] = 50;
                $result[] = 50;
            }
            
            $labels[] = $dimensions[$primary]['label'];
            $labels[] = $dimensions[$opposite]['label'];
            $oppositeLabels[] = $dimensions[$opposite]['label'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Trait Percentage',
                    'data' => $result,
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',   // E - Red
                        'rgba(59, 130, 246, 0.8)',  // I - Blue
                        'rgba(168, 85, 247, 0.8)',  // N - Purple
                        'rgba(34, 197, 94, 0.8)',   // S - Green
                        'rgba(249, 115, 22, 0.8)', // T - Orange
                        'rgba(236, 72, 153, 0.8)',  // F - Pink
                        'rgba(234, 179, 8, 0.8)',   // J - Yellow
                        'rgba(20, 184, 166, 0.8)',  // P - Teal
                    ],
                    'borderColor' => [
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(34, 197, 94)',
                        'rgb(249, 115, 22)',
                        'rgb(236, 72, 153)',
                        'rgb(234, 179, 8)',
                        'rgb(20, 184, 166)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'min' => 0,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Percentage (%)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function($context) {
                            return $context->raw . '%';
                        },
                    ],
                ],
            ],
        ];
    }
}
