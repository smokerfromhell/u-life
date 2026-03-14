<?php

namespace App\Http\Controllers;

use App\Models\SharedDecisionLog;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Get analytics data for professionals
     * Only shared consent decisions
     */
    public function dashboard()
    {
        // Check role
        $user = Auth::user();
if (!$user || (!method_exists($user, 'hasRole') || (!$user->hasRole('Professional') && !$user->hasRole('Super Admin')))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $logs = SharedDecisionLog::with('data')
            ->orderBy('day', 'desc')
            ->limit(1000)
            ->get();

$stats = [
            'total_decisions' => $logs->count(),
            'guest_decisions' => $logs->where('is_guest', true)->count(),
            'mbti_distribution' => [],
            'decision_types' => [],
            'age_groups' => [],
            'trait_tendencies' => [
                'EI' => ['Extrovert' => 0, 'Introvert' => 0],
                'NS' => ['Intuitive' => 0, 'Sensing' => 0],
                'TF' => ['Thinking' => 0, 'Feeling' => 0],
                'JP' => ['Judging' => 0, 'Perceiving' => 0]
            ],
            'recent_mbti_changes' => []
        ];

        foreach ($logs as $log) {
            $data = $log->data ?? [];
            $eventType = $log->event_type;
            // Use dedicated mbti column
            $mbti = $log->mbti ?? 'Analyzing...';
            $traits = str_split($mbti);

            // MBTI distribution
            $stats['mbti_distribution'][$mbti] = ($stats['mbti_distribution'][$mbti] ?? 0) + 1;

            // Decision types
            $stats['decision_types'][$eventType] = ($stats['decision_types'][$eventType] ?? 0) + 1;

            // Age groups
            $ageGroup = $this->estimateAgeGroup($log->day);
            $stats['age_groups'][$ageGroup] = ($stats['age_groups'][$ageGroup] ?? 0) + 1;

            // Trait tendencies (count each dimension)
            if (count($traits) === 4) {
                $stats['trait_tendencies']['EI'][ $traits[0] === 'E' ? 'Extrovert' : 'Introvert' ] += 1;
                $stats['trait_tendencies']['NS'][ $traits[1] === 'N' ? 'Intuitive' : 'Sensing' ] += 1;
                $stats['trait_tendencies']['TF'][ $traits[2] === 'T' ? 'Thinking' : 'Feeling' ] += 1;
                $stats['trait_tendencies']['JP'][ $traits[3] === 'J' ? 'Judging' : 'Perceiving' ] += 1;
            }
        }

        return response()->json($stats);
    }

    private function estimateAgeGroup(int $day): string
    {
        if ($day <= 3650) return 'child';      // 0-10
        if ($day <= 6570) return 'teen';       // 11-18
        if ($day <= 25550) return 'adult';     // 19-70
        return 'senior';                       // 70+
    }
}

