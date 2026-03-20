<?php

namespace App\Filament\Widgets\CharacterPersonality;

use App\Models\PersonalityProfile;
use Filament\Widgets\ChartWidget;

class CharacterMbtiChart extends ChartWidget
{
    protected ?string $heading = 'MBTI Dimensions';
    
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
        return 'bar';
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
                        'label' => 'MBTI Dimensions',
                        'data' => [50, 50, 50, 50],
                    ]
                ],
                'labels' => ['E/I', 'S/N', 'F/T', 'J/P'],
            ];
        }

        $profile = PersonalityProfile::where('character_id', $characterId)->first();
        
        $e_i = $profile?->energy_orientation ?? 50;
        $s_n = $profile?->information_gathering ?? 50;
        $f_t = $profile?->decision_forming ?? 50;
        $j_p = $profile?->lifestyle_approach ?? 50;
        
        $mbti = $profile?->current_mbti ?? 'N/A';

        return [
            'datasets' => [
                [
                    'label' => 'Character MBTI: ' . $mbti,
                    'data' => [
                        $e_i,      // E vs I (50 = E, 0 = I)
                        $s_n,      // N vs S (50 = N, 0 = S)
                        $f_t,      // T vs F (50 = T, 0 = F)
                        $j_p,      // J vs P (50 = J, 0 = P)
                    ],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'Extraversion vs Introversion',
                'Intuition vs Sensing',
                'Thinking vs Feeling',
                'Judging vs Perceiving',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Score (0-100)',
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
