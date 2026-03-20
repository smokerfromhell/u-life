<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\PersonalityProfile;
use Filament\Widgets\ChartWidget;

class BigFiveRadarChart extends ChartWidget
{
    protected ?string $heading = 'Big Five Personality Traits';

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getData(): array
    {
        // Get average Big Five scores across all profiles
        $bigFive = PersonalityProfile::select(
            'openness',
            'conscientiousness', 
            'extraversion',
            'agreeableness',
            'neuroticism'
        )->get();
        
        if ($bigFive->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Average Traits',
                        'data' => [50, 50, 50, 50, 50],
                        'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                        'borderColor' => 'rgb(99, 102, 241)',
                        'pointBackgroundColor' => 'rgb(99, 102, 241)',
                    ]
                ],
                'labels' => ['Openness', 'Conscientiousness', 'Extraversion', 'Agreeableness', 'Neuroticism'],
            ];
        }
        
        $openness = round($bigFive->avg('openness') ?? 50);
        $conscientiousness = round($bigFive->avg('conscientiousness') ?? 50);
        $extraversion = round($bigFive->avg('extraversion') ?? 50);
        $agreeableness = round($bigFive->avg('agreeableness') ?? 50);
        $neuroticism = round($bigFive->avg('neuroticism') ?? 50);
        
        return [
            'datasets' => [
                [
                    'label' => 'Average Traits',
                    'data' => [
                        $openness,
                        $conscientiousness,
                        $extraversion,
                        $agreeableness,
                        $neuroticism
                    ],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'pointBackgroundColor' => 'rgb(99, 102, 241)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(99, 102, 241)',
                ],
                [
                    'label' => 'Population Average',
                    'data' => [50, 50, 50, 50, 50],
                    'backgroundColor' => 'rgba(148, 163, 184, 0.1)',
                    'borderColor' => 'rgb(148, 163, 184)',
                    'pointBackgroundColor' => 'rgb(148, 163, 184)',
                    'pointBorderColor' => '#fff',
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => [
                'Openness',
                'Conscientiousness', 
                'Extraversion',
                'Agreeableness',
                'Neuroticism'
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'r' => [
                    'min' => 0,
                    'max' => 100,
                    'beginAtZero' => true,
                    'pointLabels' => [
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
