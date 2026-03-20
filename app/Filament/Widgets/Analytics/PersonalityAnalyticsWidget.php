<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\PersonalityProfile;
use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PersonalityAnalyticsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Personality Analytics';

    protected function getStats(): array
    {
        // Get MBTI distribution
        $mbtiCounts = DecisionLog::select('mbti', DB::raw('count(*) as count'))
            ->whereNotNull('mbti')
            ->where('mbti', '!=', '')
            ->groupBy('mbti')
            ->pluck('count', 'mbti')
            ->toArray();

        // Get total analyzed decisions
        $totalAnalyzed = array_sum($mbtiCounts);
        
        // Get unique MBTI types
        $uniqueTypes = count($mbtiCounts);
        
        // Calculate MBTI dimension distribution
        $dimensions = ['E' => 0, 'I' => 0, 'N' => 0, 'S' => 0, 'T' => 0, 'F' => 0, 'J' => 0, 'P' => 0];
        $dimensionCounts = ['E' => 0, 'I' => 0, 'N' => 0, 'S' => 0, 'T' => 0, 'F' => 0, 'J' => 0, 'P' => 0];
        
        foreach ($mbtiCounts as $mbti => $count) {
            $traits = str_split($mbti);
            foreach ($traits as $trait) {
                if (isset($dimensionCounts[$trait])) {
                    $dimensionCounts[$trait] += $count;
                }
            }
        }
        
        // Calculate percentages
        $total = array_sum($dimensionCounts);
        if ($total > 0) {
            foreach ($dimensionCounts as $trait => $count) {
                $dimensions[$trait] = round(($count / $total) * 100);
            }
        }
        
        // Get Big Five averages from personality profiles
        $bigFiveAvg = PersonalityProfile::select(
            DB::raw('avg(openness) as O'),
            DB::raw('avg(conscientiousness) as C'),
            DB::raw('avg(extraversion) as E'),
            DB::raw('avg(agreeableness) as A'),
            DB::raw('avg(neuroticism) as N')
        )->first();
        
        $totalProfiles = PersonalityProfile::count();

        // Most common MBTI
        $topMbti = !empty($mbtiCounts) ? array_keys($mbtiCounts, max($mbtiCounts))[0] : 'N/A';
        $topMbtiCount = !empty($mbtiCounts) ? max($mbtiCounts) : 0;
        
        return [
            Stat::make('Total Analyzed Decisions', $totalAnalyzed)
                ->description('Decisions with MBTI calculated')
                ->color('info'),
            Stat::make('Unique MBTI Types', $uniqueTypes)
                ->description('Different personality types found')
                ->color('purple'),
            Stat::make('Most Common MBTI', $topMbti)
                ->description($topMbtiCount . ' occurrences')
                ->color('warning'),
            Stat::make('Personality Profiles', $totalProfiles)
                ->description('Tracked over time')
                ->color('success'),
            // MBTI Dimensions
            Stat::make('Extraversion', ($dimensions['E'] ?? 0) . '%')
                ->description(($dimensions['I'] ?? 0) . '% Introversion'),
            Stat::make('Intuition', ($dimensions['N'] ?? 0) . '%')
                ->description(($dimensions['S'] ?? 0) . '% Sensing'),
            Stat::make('Thinking', ($dimensions['T'] ?? 0) . '%')
                ->description(($dimensions['F'] ?? 0) . '% Feeling'),
            Stat::make('Judging', ($dimensions['J'] ?? 0) . '%')
                ->description(($dimensions['P'] ?? 0) . '% Perceiving'),
            // Big Five
            Stat::make('Big Five - Openness', round($bigFiveAvg->O ?? 50) . '%')
                ->description('Curiosity & creativity'),
            Stat::make('Big Five - Conscientiousness', round($bigFiveAvg->C ?? 50) . '%')
                ->description('Organization & discipline'),
            Stat::make('Big Five - Extraversion', round($bigFiveAvg->extraversion ?? 50) . '%')
                ->description('Sociability & energy'),
            Stat::make('Big Five - Agreeableness', round($bigFiveAvg->A ?? 50) . '%')
                ->description('Trust & cooperation'),
            Stat::make('Big Five - Neuroticism', round($bigFiveAvg->N ?? 50) . '%')
                ->description('Emotional stability'),
        ];
    }
}
