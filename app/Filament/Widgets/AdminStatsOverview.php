<?php

namespace App\Filament\Widgets;

use App\Models\Character;
use App\Models\SharedDecisionLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Platform Overview';

    protected function getStats(): array
    {
        $userId = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : 0;
        $cacheKey = 'admin_stats_' . md5($userId);
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {  // 5min cache
            $now = Carbon::now();
            $since24h = $now->copy()->subDay();
            $since7d = $now->copy()->subDays(7);

            $user = Auth::user();
            if ($user?->hasRole('Professional')) {
                return [
                    Stat::make('Shared Logs (24h)', SharedDecisionLog::query()->where('created_at', '>=', $since24h)->count())
                        ->description('Opt-in analytics')
                        ->color('warning'),
                    Stat::make('Shared Logs (7d)', SharedDecisionLog::query()->where('created_at', '>=', $since7d)->count())
                        ->description('Opt-in analytics')
                        ->color('warning'),
                ];
            }

            return [
                Stat::make('Users', User::query()->where('is_guest', false)->count())
                    ->description('Registered players')
                    ->color('primary'),
                Stat::make('Guests', User::query()->where('is_guest', true)->count())
                    ->description('Guest sessions (accounts)')
                    ->color('gray'),
                Stat::make('Characters', Character::query()->count())
                    ->description('Total created')
                    ->color('success'),
                Stat::make('Shared Logs (24h)', SharedDecisionLog::query()->where('created_at', '>=', $since24h)->count())
                    ->description('Opt-in analytics')
                    ->color('warning'),
                Stat::make('Shared Logs (7d)', SharedDecisionLog::query()->where('created_at', '>=', $since7d)->count())
                    ->description('Opt-in analytics')
                    ->color('warning'),
            ];
        });
    }
}
