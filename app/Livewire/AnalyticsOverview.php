<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Decisions', \App\Models\SharedDecisionLog::count())
                ->description('All shared choices')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('Guest Sessions', \App\Models\SharedDecisionLog::where('is_guest', true)->count())
                ->description('Anonymous play')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
            Stat::make('INTP Users', \App\Models\SharedDecisionLog::where('mbti', 'INTP')->count())
                ->description('Logician pattern')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('gray'),
            Stat::make('Career Choices', \App\Models\SharedDecisionLog::where('event_type', 'profession')->count())
                ->description('Work decisions')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
        ];
    }
}
