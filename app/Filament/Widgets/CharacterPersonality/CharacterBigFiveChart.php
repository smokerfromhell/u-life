<?php

namespace App\Filament\Widgets\CharacterPersonality;

use App\Models\PersonalityProfile;
use Filament\Widgets\ChartWidget;

class CharacterBigFiveChart extends ChartWidget
{
    protected ?string $heading = 'Big Five Personality Traits';
    
    public ?\App\Models\Character $record = null;
    
    public function mount(): void
    {
        parent::mount();
        
        // Try to get the record from the page context
        if (!$this->record && function_exists(' filament') && filament()->getCurrentPanel()?->getPage()?->getRecord()) {
            $this->record = filament()->getCurrentPanel()->getPage()->getRecord();
        }
    }

    protected function getType(): string
    {
        return 'radar';
    }

    public ?int $recordId = null;

    protected function getData(): array
    {
        // Try to get record from various sources
        $characterId = null;
        
        // First try the mounted record
        if ($this->record) {
            $characterId = $this->record->id;
        }
        // Then try the widget's getRecord() method
        elseif ($this->getRecord()) {
            $characterId = $this->getRecord()->id;
        }
        // Fallback to recordId property
        elseif ($this->recordId) {
            $characterId = $this->recordId;
        }
        // Finally try request
        elseif (request()->has('record')) {
            $characterId = request()->record;
        }
        
        if (!$characterId) {
            return [
                'datasets' => [
                    [
                        'label' => 'Traits',
                        'data' => [50, 50, 50, 50, 50],
                    ]
                ],
                'labels' => ['Openness', 'Conscientiousness', 'Extraversion', 'Agreeableness', 'Neuroticism'],
            ];
        }

        $profile = PersonalityProfile::where('character_id', $characterId)->first();
        
        $openness = $profile?->openness ?? 50;
        $conscientiousness = $profile?->conscientiousness ?? 50;
        $extraversion = $profile?->extraversion ?? 50;
        $agreeableness = $profile?->agreeableness ?? 50;
        $neuroticism = $profile?->neuroticism ?? 50;

        return [
            'datasets' => [
                [
                    'label' => 'Character Traits',
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
