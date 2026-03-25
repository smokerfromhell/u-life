<?php

namespace App\Services;

use App\Models\DecisionLog;
use Illuminate\Support\Facades\DB;

/**
 * Analytics Statistics Service
 * Provides statistical calculations for game analytics
 * Helps analyze player behavior and game balance
 */
class AnalyticsStatsService
{
    /**
     * Calculate the variance of a dataset
     */
    public function calculateVariance(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        $count = count($values);
        $mean = array_sum($values) / $count;
        
        $squaredDiffs = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        return array_sum($squaredDiffs) / $count;
    }

    /**
     * Calculate the standard deviation of a dataset
     */
    public function calculateStandardDeviation(array $values): float
    {
        return sqrt($this->calculateVariance($values));
    }

    /**
     * Calculate choice effectiveness score
     * A score that considers multiple factors
     */
    public function calculateChoiceEffectiveness(string $choiceText): array
    {
        $decisions = DecisionLog::where('choice_text', $choiceText)->get();
        
        if ($decisions->isEmpty()) {
            return [
                'total_picks' => 0,
                'avg_health_change' => 0,
                'avg_happiness_change' => 0,
                'avg_finance_change' => 0,
                'success_rate' => 0,
                'overall_score' => 0,
            ];
        }

        $healthChanges = $decisions->pluck('health_change')->toArray();
        $happinessChanges = $decisions->pluck('happiness_change')->toArray();
        $financeChanges = $decisions->pluck('finance_change')->toArray();

        $successCount = $decisions->filter(function ($d) {
            return ($d->happiness_change ?? 0) > 0 || ($d->health_change ?? 0) > 0;
        })->count();

        return [
            'total_picks' => $decisions->count(),
            'avg_health_change' => round($decisions->avg('health_change') ?? 0, 2),
            'avg_happiness_change' => round($decisions->avg('happiness_change') ?? 0, 2),
            'avg_finance_change' => round($decisions->avg('finance_change') ?? 0, 2),
            'success_rate' => round($successCount / $decisions->count() * 100, 1),
            'health_variance' => round($this->calculateVariance($healthChanges), 2),
            'happiness_variance' => round($this->calculateVariance($happinessChanges), 2),
            'finance_variance' => round($this->calculateVariance($financeChanges), 2),
            'overall_score' => round((
                ($decisions->avg('health_change') ?? 0) +
                ($decisions->avg('happiness_change') ?? 0) +
                ($decisions->avg('finance_change') ?? 0)
            ) / 3, 2),
        ];
    }

    /**
     * Get overall game statistics
     */
    public function getGameStatistics(): array
    {
        $totalDecisions = DecisionLog::count();
        
        if ($totalDecisions === 0) {
            return [
                'total_decisions' => 0,
                'avg_daily_decisions' => 0,
                'unique_players' => 0,
                'avg_health_impact' => 0,
                'avg_happiness_impact' => 0,
                'avg_finance_impact' => 0,
            ];
        }

        $uniquePlayers = DecisionLog::distinct('user_id')->count('user_id');
        $avgHealth = DecisionLog::avg('health_change') ?? 0;
        $avgHappiness = DecisionLog::avg('happiness_change') ?? 0;
        $avgFinance = DecisionLog::avg('finance_change') ?? 0;

        // Calculate average decisions per day
        $dateRange = DecisionLog::selectRaw('MIN(decision_made_at) as first_date, MAX(decision_made_at) as last_date')->first();
        $days = 1;
        if ($dateRange && $dateRange->first_date && $dateRange->last_date) {
            $days = max(1, $dateRange->first_date->diffInDays($dateRange->last_date));
        }

        return [
            'total_decisions' => $totalDecisions,
            'avg_daily_decisions' => round($totalDecisions / $days, 1),
            'unique_players' => $uniquePlayers,
            'avg_health_impact' => round($avgHealth, 2),
            'avg_happiness_impact' => round($avgHappiness, 2),
            'avg_finance_impact' => round($avgFinance, 2),
        ];
    }

    /**
     * Get session analytics (if time tracking is available)
     */
    public function getSessionAnalytics(): array
    {
        $avgTimeSpent = DecisionLog::whereNotNull('time_spent_on_event')->avg('time_spent_on_event') ?? 0;
        
        $timeDistribution = DecisionLog::select(
            DB::raw('CASE 
                WHEN time_spent_on_event < 5 THEN "Quick (<5s)"
                WHEN time_spent_on_event < 15 THEN "Fast (5-15s)"
                WHEN time_spent_on_event < 30 THEN "Normal (15-30s)"
                WHEN time_spent_on_event < 60 THEN "Long (30-60s)"
                ELSE "Very Long (>60s)"
            END as time_category'),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('time_spent_on_event')
            ->groupBy('time_category')
            ->orderByDesc('count')
            ->get();

        return [
            'avg_time_spent_seconds' => round($avgTimeSpent, 1),
            'time_distribution' => $timeDistribution->pluck('count', 'time_category')->toArray(),
        ];
    }

    /**
     * Get mini-game performance analytics
     */
    public function getMiniGameAnalytics(): array
    {
        $gamesWithResults = DecisionLog::whereNotNull('mini_game_result')->get();
        
        if ($gamesWithResults->isEmpty()) {
            return [
                'total_games' => 0,
                'avg_score' => 0,
            ];
        }

        $scores = [];
        foreach ($gamesWithResults as $game) {
            $result = is_array($game->mini_game_result) 
                ? $game->mini_game_result 
                : json_decode($game->mini_game_result, true);
            
            if (isset($result['score'])) {
                $scores[] = $result['score'];
            }
        }

        return [
            'total_games' => $gamesWithResults->count(),
            'avg_score' => !empty($scores) ? round(array_sum($scores) / count($scores), 1) : 0,
            'min_score' => !empty($scores) ? min($scores) : 0,
            'max_score' => !empty($scores) ? max($scores) : 0,
        ];
    }

    /**
     * Calculate player retention metrics
     */
    public function getRetentionMetrics(): array
    {
        $totalUsers = DecisionLog::distinct('user_id')->count('user_id');
        
        $returningUsers = DecisionLog::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT DATE(decision_made_at)) > 1')
            ->count();

        $oneTimeUsers = $totalUsers - $returningUsers;

        return [
            'total_users' => $totalUsers,
            'returning_users' => $returningUsers,
            'one_time_users' => $oneTimeUsers,
            'retention_rate' => $totalUsers > 0 ? round($returningUsers / $totalUsers * 100, 1) : 0,
        ];
    }
}