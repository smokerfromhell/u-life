<?php

namespace App\Http\Controllers;

use App\Models\DecisionLog;
use App\Services\AnalyticsStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Analytics Export Controller
 * Provides export functionality for admin analytics
 * Allows exporting decision data to CSV/Excel for external analysis
 */
class AnalyticsExportController extends Controller
{
    protected AnalyticsStatsService $statsService;

    public function __construct(AnalyticsStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Check if user has admin privileges
     */
    private function checkAdminAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // Check for admin role orFilament admin access
        return $user->hasRole('admin') || $user->hasPermissionTo('view-analytics');
    }

    /**
     * Export decision logs to CSV
     */
    public function exportToCsv(Request $request): StreamedResponse
    {
        if (!$this->checkAdminAccess()) {
            abort(403, 'Unauthorized access');
        }

        $query = $this->buildFilteredQuery($request);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, [
                'ID',
                'Character ID',
                'User ID',
                'Day',
                'Age Group',
                'Profession',
                'Event Type',
                'Event Title',
                'Choice Index',
                'Choice Text',
                'Outcome',
                'Before Health',
                'Before Happiness',
                'Before Finance',
                'After Health',
                'After Happiness',
                'After Finance',
                'Health Change',
                'Happiness Change',
                'Finance Change',
                'MBTI',
                'Current Luck',
                'Time Spent (s)',
                'Created At',
            ]);

            $query->chunk(1000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->character_id,
                        $log->user_id,
                        $log->day,
                        $log->age_group,
                        $log->profession,
                        $log->event_type,
                        $log->event_title,
                        $log->choice_index,
                        $log->choice_text,
                        $log->outcome,
                        $log->before_health,
                        $log->before_happiness,
                        $log->before_finance,
                        $log->after_health,
                        $log->after_happiness,
                        $log->after_finance,
                        $log->health_change,
                        $log->happiness_change,
                        $log->finance_change,
                        $log->mbti,
                        $log->current_luck,
                        $log->time_spent_on_event,
                        $log->created_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'decision_logs_export_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="decision_logs_export.csv"',
        ]);
    }

    /**
     * Export analytics summary to CSV
     */
    public function exportAnalyticsSummary(Request $request): StreamedResponse
    {
        if (!$this->checkAdminAccess()) {
            abort(403, 'Unauthorized access');
        }

        $stats = $this->statsService->getGameStatistics();
        $retention = $this->statsService->getRetentionMetrics();
        $sessions = $this->statsService->getSessionAnalytics();
        $minigames = $this->statsService->getMiniGameAnalytics();

        return response()->streamDownload(function () use ($stats, $retention, $sessions, $minigames) {
            $handle = fopen('php://output', 'w');

            // Summary Stats
            fputcsv($handle, ['Analytics Summary Report']);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);
            
            fputcsv($handle, ['Game Statistics']);
            foreach ($stats as $key => $value) {
                fputcsv($handle, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($handle, []);
            
            fputcsv($handle, ['Retention Metrics']);
            foreach ($retention as $key => $value) {
                fputcsv($handle, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($handle, []);
            
            fputcsv($handle, ['Session Analytics']);
            fputcsv($handle, ['Average Time Spent (seconds)', $sessions['avg_time_spent_seconds']]);
            foreach ($sessions['time_distribution'] as $category => $count) {
                fputcsv($handle, [$category, $count]);
            }
            fputcsv($handle, []);
            
            fputcsv($handle, ['Mini-Game Analytics']);
            foreach ($minigames as $key => $value) {
                fputcsv($handle, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }

            fclose($handle);
        }, 'analytics_summary_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export choice effectiveness analysis
     */
    public function exportChoiceEffectiveness(Request $request): StreamedResponse
    {
        if (!$this->checkAdminAccess()) {
            abort(403, 'Unauthorized access');
        }

        // Get top choices with their effectiveness
        $choices = DecisionLog::select('choice_text')
            ->whereNotNull('choice_text')
            ->where('choice_text', '!=', '')
            ->groupBy('choice_text')
            ->get();

        $effectivenessData = [];
        foreach ($choices as $choice) {
            $effectiveness = $this->statsService->calculateChoiceEffectiveness($choice->choice_text);
            if ($effectiveness['total_picks'] > 0) {
                $effectivenessData[] = array_merge(
                    ['choice_text' => $choice->choice_text],
                    $effectiveness
                );
            }
        }

        // Sort by overall score
        usort($effectivenessData, function ($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });

        return response()->streamDownload(function () use ($effectivenessData) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Choice Text',
                'Total Picks',
                'Avg Health Change',
                'Avg Happiness Change',
                'Avg Finance Change',
                'Success Rate (%)',
                'Overall Score',
            ]);

            foreach ($effectivenessData as $row) {
                fputcsv($handle, [
                    $row['choice_text'],
                    $row['total_picks'],
                    $row['avg_health_change'],
                    $row['avg_happiness_change'],
                    $row['avg_finance_change'],
                    $row['success_rate'],
                    $row['overall_score'],
                ]);
            }

            fclose($handle);
        }, 'choice_effectiveness_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Build filtered query based on request parameters
     */
    private function buildFilteredQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = DecisionLog::query();

        // Date range filter
        if ($request->date_from) {
            $query->where('decision_made_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('decision_made_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Age group filter
        if ($request->age_group) {
            $query->where('age_group', $request->age_group);
        }

        // Profession filter
        if ($request->profession) {
            $query->where('profession', $request->profession);
        }

        // Event type filter
        if ($request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        // Limit results for safety
        return $query->orderBy('created_at', 'desc')->limit(100000);
    }
}