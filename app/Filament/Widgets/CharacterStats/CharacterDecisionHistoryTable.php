<?php

namespace App\Filament\Widgets\CharacterStats;

use App\Models\PersonalityProfile;
use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CharacterDecisionHistoryTable extends BaseWidget
{
    public ?Character $record = null;

    protected function getStats(): array
    {
        $profile = PersonalityProfile::where('character_id', $this->record?->id)->first();
        
        if (!$profile) {
            return [
                Stat::make('Decisions', 'No data')
                    ->description('No personality profile yet')
                    ->color('gray'),
            ];
        }
        
        $socialCount = count($profile->recent_social_decisions ?? []);
        $careerCount = count($profile->recent_career_decisions ?? []);
        $relationshipCount = count($profile->recent_relationship_decisions ?? []);
        $moralCount = count($profile->recent_moral_decisions ?? []);
        
        $total = $socialCount + $careerCount + $relationshipCount + $moralCount;
        
        return [
            Stat::make('Total Decisions', $total)
                ->description('Recent decisions tracked')
                ->color($total > 0 ? 'info' : 'gray'),
            Stat::make('Social', $socialCount)
                ->description('Social/Cultural decisions')
                ->color('info'),
            Stat::make('Career', $careerCount)
                ->description('Career/Profession decisions')
                ->color('warning'),
            Stat::make('Relationship', $relationshipCount)
                ->description('Relationship/Family decisions')
                ->color('danger'),
            Stat::make('Moral', $moralCount)
                ->description('Moral/Ethics decisions')
                ->color('success'),
        ];
    }
}
