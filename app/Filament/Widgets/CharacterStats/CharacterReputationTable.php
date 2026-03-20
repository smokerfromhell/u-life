<?php

namespace App\Filament\Widgets\CharacterStats;

use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class CharacterReputationTable extends BaseWidget
{
    public ?Character $record = null;

    protected function getStats(): array
    {
        $reputations = $this->record?->getAllReputations() ?? [];
        
        $stats = [];
        foreach ($reputations as $faction => $value) {
            $level = $this->getReputationLevel($value);
            $color = match ($level) {
                'Allied' => 'success',
                'Friendly' => 'success',
                'Neutral' => 'warning',
                'Known' => 'info',
                'Hostile' => 'danger',
                default => 'gray',
            };
            
            $stats[] = Stat::make(ucfirst($faction), "{$value}/100")
                ->description($level)
                ->color($color);
        }
        
        return $stats;
    }

    private function getReputationLevel(int $value): string
    {
        return match (true) {
            $value >= 80 => 'Allied',
            $value >= 60 => 'Friendly',
            $value >= 40 => 'Neutral',
            $value >= 20 => 'Known',
            default => 'Hostile',
        };
    }
}
