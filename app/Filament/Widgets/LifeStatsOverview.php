<?php

namespace App\Filament\Widgets;

use App\Models\LifeStatsSnapshot;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LifeStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Life Stats Overview';

    protected function getStats(): array
    {
        // Get aggregate stats from LifeStatsSnapshots (like ListDecisionLogs pulls from DecisionLog)
        $snapshotStats = LifeStatsSnapshot::getAggregateStatsFromSnapshots();
        
        // Get event type distribution
        $eventTypeCounts = LifeStatsSnapshot::select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();
        
        // Get relationship distribution from snapshots
        $relationshipCounts = LifeStatsSnapshot::select('relationship_status', DB::raw('count(*) as count'))
            ->groupBy('relationship_status')
            ->pluck('count', 'relationship_status')
            ->toArray();
        
        // Get career distribution from snapshots
        $careerCounts = LifeStatsSnapshot::select('career_level', DB::raw('count(*) as count'))
            ->groupBy('career_level')
            ->pluck('count', 'career_level')
            ->toArray();
        
        $totalSnapshots = LifeStatsSnapshot::count();
        
        // Get MBTI distribution
        $mbtiCounts = LifeStatsSnapshot::select('mbti', DB::raw('count(*) as count'))
            ->whereNotNull('mbti')
            ->where('mbti', '!=', '')
            ->groupBy('mbti')
            ->pluck('count', 'mbti')
            ->toArray();
        
        // Build event type description
        $eventTypeDesc = '';
        if (!empty($eventTypeCounts)) {
            $parts = [];
            foreach ($eventTypeCounts as $type => $count) {
                $parts[] = ucfirst($type) . ': ' . $count;
            }
            $eventTypeDesc = implode(', ', $parts);
        }
        
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
        
        // Build MBTI description
        $mbtiDesc = '';
        if (!empty($mbtiCounts)) {
            $topMbti = array_keys($mbtiCounts, max($mbtiCounts));
            $mbtiDesc = !empty($topMbti) ? $topMbti[0] . ' (' . count($mbtiCounts) . ' types)' : 'No data';
        }

        return [
            // Snapshot-based stats (like ListDecisionLogs pulls from DecisionLog)
            Stat::make('Avg Health', $snapshotStats['avg_health'] . '%')
                ->description($totalSnapshots > 0 ? 'From ' . $totalSnapshots . ' snapshots' : 'No snapshots yet')
                ->color($snapshotStats['avg_health'] >= 70 ? 'success' : ($snapshotStats['avg_health'] >= 40 ? 'warning' : 'danger')),
            Stat::make('Avg Happiness', $snapshotStats['avg_happiness'] . '%')
                ->description($totalSnapshots > 0 ? 'From ' . $totalSnapshots . ' snapshots' : 'No snapshots yet')
                ->color($snapshotStats['avg_happiness'] >= 70 ? 'success' : ($snapshotStats['avg_happiness'] >= 40 ? 'warning' : 'danger')),
            Stat::make('Avg Finance', '$' . number_format($snapshotStats['avg_finance']))
                ->description($totalSnapshots > 0 ? 'From ' . $totalSnapshots . ' snapshots' : 'No snapshots yet')
                ->color($snapshotStats['avg_finance'] >= 0 ? 'success' : 'danger'),
            Stat::make('Total Snapshots', $totalSnapshots)
                ->description($eventTypeDesc ?: 'No data'),
            Stat::make('Relationships', !empty($relationshipCounts) ? array_sum($relationshipCounts) : 0)
                ->description($relationshipDesc ?: 'No data'),
            Stat::make('Career Status', !empty($careerCounts) ? array_sum($careerCounts) : 0)
                ->description($careerDesc ?: 'No data'),
            Stat::make('MBTI Types', !empty($mbtiCounts) ? count($mbtiCounts) : 0)
                ->description($mbtiDesc ?: 'No data'),
        ];
    }
}
