<?php

namespace App\Filament\Widgets\CharacterStats;

use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CharacterLuckStatsWidget extends BaseWidget
{
    public ?Character $record = null;

    protected function getStats(): array
    {
        $luck = $this->record?->luck ?? 50;
        $karma = $this->record?->karma ?? 0;
        $luckHistory = $this->record?->luck_history ?? [];
        
        $luckTrend = $this->getLuckTrend($luck);
        $karmaTrend = $this->getKarmaTrend($karma);
        
        $luckColor = match ($luckTrend) {
            'Very Lucky' => 'success',
            'Lucky' => 'success',
            'Average' => 'gray',
            'Unlucky' => 'warning',
            'Very Unlucky' => 'danger',
            default => 'gray',
        };
        
        $karmaColor = match ($karmaTrend) {
            'Good' => 'success',
            'Neutral+' => 'success',
            'Neutral' => 'gray',
            'Neutral-' => 'warning',
            'Evil' => 'danger',
            default => 'gray',
        };
        
        return [
            Stat::make('Current Luck', "{$luck}/100")
                ->description($luckTrend)
                ->color($luckColor),
            Stat::make('Karma', $karma)
                ->description($karmaTrend)
                ->color($karmaColor),
            Stat::make('Luck Events', count($luckHistory))
                ->description('Total luck changes')
                ->color(count($luckHistory) > 0 ? 'info' : 'gray'),
        ];
    }

    private function getLuckTrend(int $luck): string
    {
        return match (true) {
            $luck >= 70 => 'Very Lucky',
            $luck >= 55 => 'Lucky',
            $luck >= 45 => 'Average',
            $luck >= 30 => 'Unlucky',
            default => 'Very Unlucky',
        };
    }

    private function getKarmaTrend(int $karma): string
    {
        return match (true) {
            $karma >= 50 => 'Good',
            $karma >= 10 => 'Neutral+',
            $karma >= -10 => 'Neutral',
            $karma >= -50 => 'Neutral-',
            default => 'Evil',
        };
    }
}
