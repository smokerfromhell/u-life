<?php

namespace App\Filament\Widgets;

use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LifeStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Life Stats Overview';

    protected function getStats(): array
    {
        // Get average stats across all characters
        $avgHealth = round(Character::avg('health') ?? 0);
        $avgHappiness = round(Character::avg('happiness') ?? 0);
        $avgFinance = round(Character::avg('finance') ?? 0);
        
        // Get counts for relationship status
        $relationshipCounts = Character::select('relationship_status', DB::raw('count(*) as count'))
            ->groupBy('relationship_status')
            ->pluck('count', 'relationship_status')
            ->toArray();
        
        // Get counts for career level
        $careerCounts = Character::select('career_level', DB::raw('count(*) as count'))
            ->groupBy('career_level')
            ->pluck('count', 'career_level')
            ->toArray();
        
        $totalCharacters = Character::count();
        
        // Build relationship description
        $relationshipDesc = '';
        if (!empty($relationshipCounts)) {
            $parts = [];
            foreach ($relationshipCounts as $status => $count) {
                $parts[] = ucfirst($status) . ': ' . $count;
            }
            $relationshipDesc = implode(', ', $parts);
        }
        
        // Build career description
        $careerDesc = '';
        if (!empty($careerCounts)) {
            $parts = [];
            foreach ($careerCounts as $level => $count) {
                $parts[] = ucfirst($level) . ': ' . $count;
            }
            $careerDesc = implode(', ', $parts);
        }

        return [
            Stat::make('Avg Health', $avgHealth . '%')
                ->description($totalCharacters > 0 ? 'Across ' . $totalCharacters . ' characters' : 'No characters yet')
                ->color($avgHealth >= 70 ? 'success' : ($avgHealth >= 40 ? 'warning' : 'danger')),
            Stat::make('Avg Happiness', $avgHappiness . '%')
                ->description($totalCharacters > 0 ? 'Across ' . $totalCharacters . ' characters' : 'No characters yet')
                ->color($avgHappiness >= 70 ? 'success' : ($avgHappiness >= 40 ? 'warning' : 'danger')),
            Stat::make('Avg Finance', '$' . number_format($avgFinance))
                ->description($totalCharacters > 0 ? 'Across ' . $totalCharacters . ' characters' : 'No characters yet')
                ->color($avgFinance >= 0 ? 'success' : 'danger'),
            Stat::make('Relationships', !empty($relationshipCounts) ? array_sum($relationshipCounts) : 0)
                ->description($relationshipDesc ?: 'No data'),
            Stat::make('Career Status', !empty($careerCounts) ? array_sum($careerCounts) : 0)
                ->description($careerDesc ?: 'No data'),
        ];
    }
}
