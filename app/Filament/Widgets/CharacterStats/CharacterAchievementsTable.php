<?php

namespace App\Filament\Widgets\CharacterStats;

use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CharacterAchievementsTable extends BaseWidget
{
    public ?Character $record = null;

    protected function getStats(): array
    {
        $achievementFlags = $this->record?->achievement_flags ?? [];
        $count = count($achievementFlags);
        
        $achievementNames = [];
        foreach ($achievementFlags as $achievement) {
            $achievementNames[] = $achievement['name'] ?? $achievement['id'] ?? 'Unknown';
        }
        
        return [
            Stat::make('Total Achievements', $count)
                ->description('Milestones unlocked')
                ->color($count > 0 ? 'success' : 'gray'),
            Stat::make('Achievements', implode(', ', array_slice($achievementNames, 0, 3)) ?: 'None')
                ->description($count > 3 ? 'And ' . ($count - 3) . ' more...' : '')
                ->color($count > 0 ? 'info' : 'gray'),
        ];
    }
}
