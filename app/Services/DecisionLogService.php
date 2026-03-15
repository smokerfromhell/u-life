<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterDecisionLog;
use App\Models\SharedDecisionLog;
use App\Models\DecisionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DecisionLogService
{
    /**
     * Log a decision made by a character
     */
    public function logDecision(Character $character, string $eventId, string $eventType, array $choices, ?string $outcome = null, ?string $mbti = null): bool
    {
        try {
            // Log to character decision log
            CharacterDecisionLog::create([
                'character_id' => $character->id,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'choices' => json_encode($choices),
                'outcome' => $outcome,
                'age_group' => $character->age_group,
                'current_day' => $character->current_day,
            ]);

            // Log to shared decision log for analytics (with anonymized character ID)
            $anonCharacterId = $character->anon_character_id ?? 'unknown';

            SharedDecisionLog::create([
                'anon_character_id' => $anonCharacterId,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'choices' => json_encode($choices),
                'outcome' => $outcome,
                'mbti' => $mbti,
                'user_name' => $character->user->name ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error logging decision: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'event_id' => $eventId,
            ]);
            return false;
        }
    }

    /**
     * Get decision logs for a character
     */
    public function getCharacterDecisionLogs(Character $character, int $limit = 50)
    {
        return CharacterDecisionLog::where('character_id', $character->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get shared decision logs for analytics
     */
    public function getSharedDecisionLogs(?string $eventType = null, int $limit = 100)
    {
        $query = SharedDecisionLog::query();

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get decision statistics for analytics
     */
    public function getDecisionStats(?string $eventType = null): array
    {
        $query = SharedDecisionLog::query();

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        $totalDecisions = $query->count();

        return [
            'total_decisions' => $totalDecisions,
        ];
    }
}
