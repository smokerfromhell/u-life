<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\SharedDecisionLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Stats Overview Widget for Shared Decision Logs
 * Shows key metrics above the shared decision logs table
 */
class SharedDecisionLogsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get total shared decisions
        $totalDecisions = SharedDecisionLog::count();
        
        // Get unique players
        $uniquePlayers = SharedDecisionLog::distinct('user_name')->count('user_name');
        
        // Get guest vs registered ratio
        $guestCount = SharedDecisionLog::where('is_guest', true)->count();
        $registeredCount = $totalDecisions - $guestCount;
        $guestPercentage = $totalDecisions > 0 ? round(($guestCount / $totalDecisions) * 100) : 0;
        
        // Get top event type
        $topEventType = SharedDecisionLog::select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->first();
            
        // Get total unique characters
        $uniqueCharacters = SharedDecisionLog::distinct('anon_character_id')->count('anon_character_id');
        
        // Get recent activity (last 24 hours)
        $recentActivity = SharedDecisionLog::where('created_at', '>=', now()->subDay())->count();
        
        // Get popular choices count
        $uniqueChoices = SharedDecisionLog::distinct('choice_text')->count('choice_text');
        
        return [
            Stat::make('Total Decisions', number_format($totalDecisions))
                ->description('All shared decisions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
                
            Stat::make('Active Players', number_format($uniquePlayers))
                ->description('Unique player names')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Guest vs Registered', "{$guestPercentage}% / " . (100 - $guestPercentage) . "%")
                ->description("{$guestCount} guest / {$registeredCount} registered")
                ->descriptionIcon('heroicon-m-user-group')
                ->color($guestPercentage > 50 ? 'warning' : 'info'),
                
            Stat::make('Top Event Type', $this->formatEventType($topEventType?->event_type ?? 'N/A'))
                ->description('Most common event')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('purple'),
                
            Stat::make('Unique Characters', number_format($uniqueCharacters))
                ->description('Anonymized characters')
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),
                
            Stat::make('Last 24 Hours', number_format($recentActivity))
                ->description('Recent decisions')
                ->descriptionIcon('heroicon-m-clock')
                ->color($recentActivity > 50 ? 'success' : 'gray'),
        ];
    }
    
    /**
     * Format event type for display
     */
    protected function formatEventType(?string $type): string
    {
        return match($type) {
            'daily' => '📅 Daily',
            'cultural' => '🎭 Cultural',
            'ageSpecific' => '📖 Story',
            'profession' => '💼 Career',
            'health' => '❤️ Health',
            'family' => '👨‍👩‍👧 Family',
            default => ucfirst($type ?? 'N/A'),
        };
    }
}