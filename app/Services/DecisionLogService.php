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

            // SharedDecisionLog removed - using DecisionInsightsResource instead


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

    /**
     * Log FULL decision with before/after life stats - MAIN AUDIT LOG
     * Used by EventService after stat changes
     * Only creates DecisionLog if user has given share_consent
     */
    public function logFullDecision(Character $character, array $event, int $choiceIndex, array $beforeLifeStats, array $afterLifeStats, string $choiceText = null, string $outcomeType = 'neutral'): bool
    {
        try {
            // Get authenticated user via request - more reliable for Intelephense
            $authUser = \request()->user();
            
            // Get event details
            $eventType = $event['type'] ?? 'unknown';
            $eventId = $event['id'] ?? null;
            $rawStatEffects = $event['statEffects'] ?? [];
            // Parse statEffects string to array if needed (fixes type error in calculateEnhancedMBTI)
            $statEffects = [];
            if (is_string($rawStatEffects) && !empty($rawStatEffects)) {
                $statEffects = app(\App\Services\EventService::class)->parseStatEffects($rawStatEffects);
            } elseif (is_array($rawStatEffects)) {
                $statEffects = $rawStatEffects;
            }
            
            // Process choice consequences for branching system
            $eventService = app(\App\Services\EventService::class);
            $eventCategory = $this->determineEventCategory($eventType);
            $choiceId = $eventId . '_choice_' . $choiceIndex;
            
            // Determine next chain category based on event
            $nextChainCategory = $this->determineNextChain($eventCategory, $outcomeType);
            
            // Extract random outcomes from the selected choice
            $choiceRandomOutcomes = [];
            $choices = $event['choices'] ?? [];
            if (isset($choices[$choiceIndex]) && is_array($choices[$choiceIndex])) {
                $selectedChoice = $choices[$choiceIndex];
                $choiceRandomOutcomes = $selectedChoice['random_outcomes'] ?? [];
            }
            
            // Process consequences for branching
            $eventService->processChoiceConsequences(
                $character,
                $eventCategory,
                $choiceId,
                $outcomeType,
                $statEffects,
                $nextChainCategory,
                $choiceRandomOutcomes
            );
            
            // Only log to DecisionLog if user has given share_consent (clicked "PLAY & SHARE")
            if (!$authUser || !($authUser->share_consent ?? false)) {
                Log::info('Skipped DecisionLog - no share_consent', [
                    'character_id' => $character->id,
                    'user_id' => $authUser?->id,
                ]);
                return true;
            }
            
            // Get effective stats from character for personality-based MBTI calculation
            $characterStatsForMBTI = array_merge(
                $afterLifeStats,
                is_array($character->effective_stats) ? $character->effective_stats : [],
                is_array($character->stats) ? $character->stats : [],
                is_array($character->hidden_stats) ? $character->hidden_stats : []
            );

            // Calculate ENHANCED MBTI with cumulative personality profile
            $enhancedMbti = $eventService->calculateEnhancedMBTI(
                $character,
                $eventType,
                $choiceIndex,
                $outcomeType,
                $statEffects
            );
            $mbtiType = $enhancedMbti['mbti'];
            
            DecisionLog::create([
                'character_id' => $character->id,
                'user_id' => $authUser?->id,
                'anon_user_id' => $authUser ? \App\Support\Privacy::anonymize('user', $authUser->id) : null,
                'anon_character_id' => $character->anon_character_id,
                'is_guest' => $authUser?->is_guest ?? false,
                'user_name' => $authUser?->name ?? 'Guest',
                'day' => $character->current_day ?? 0,
                'age_group' => $character->age_group,
                'profession' => $character->profession ?? null,
                'event_type' => $eventType,
                'event_id' => $eventId,
                'event_title' => $event['title'] ?? null,
                'choice_index' => $choiceIndex,
                'choice_text' => $choiceText,
                'outcome' => $outcomeType,
                'effects' => json_encode($statEffects),
                // Before
                'before_health' => $beforeLifeStats['health'] ?? 100,
                'before_happiness' => $beforeLifeStats['happiness'] ?? 100,
                'before_finance' => $beforeLifeStats['finance'] ?? 0,
                'before_relationship_status' => $beforeLifeStats['relationship_status'] ?? 'single',
                'before_profession' => $beforeLifeStats['profession'] ?? ($character->profession ?? null),
                'before_career_level' => $beforeLifeStats['career_level'] ?? 'unemployed',
                // After  
                'after_health' => $afterLifeStats['health'] ?? 100,
                'after_happiness' => $afterLifeStats['happiness'] ?? 100,
                'after_finance' => $afterLifeStats['finance'] ?? 0,
                'after_relationship_status' => $afterLifeStats['relationship_status'] ?? 'single',
                'after_profession' => $afterLifeStats['profession'] ?? ($character->profession ?? null),
                'after_career_level' => $afterLifeStats['career_level'] ?? 'unemployed',
                // Changes
                'health_change' => ($afterLifeStats['health'] ?? 100) - ($beforeLifeStats['health'] ?? 100),
                'happiness_change' => ($afterLifeStats['happiness'] ?? 100) - ($beforeLifeStats['happiness'] ?? 100),
                'finance_change' => ($afterLifeStats['finance'] ?? 0) - ($beforeLifeStats['finance'] ?? 0),
                // MBTI
                'mbti' => $mbtiType,
                'data' => array_merge($event, [
                    'enhanced_mbti' => $enhancedMbti,
                    'personality_insights' => $eventService->getPersonalityInsight($character),
                ]),
            ]);

            // Existing character + shared logs still work
            $this->logDecision($character, $eventId, $eventType, [$choiceText], $outcomeType, $mbtiType);

            return true;
        } catch (\Exception $e) {
            // intelephense ignore
            Log::error('DecisionLogService::logFullDecision failed', [
                'character_id' => $character->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Determine event category for branching
     */
    private function determineEventCategory(string $eventType): string
    {
        return match($eventType) {
            'ageSpecific', 'age_specific' => 'education',
            'profession', 'career' => 'career',
            'cultural', 'social' => 'social',
            'family', 'relationship', 'romantic' => 'family',
            'health' => 'health',
            'daily' => 'daily',
            default => 'general',
        };
    }

    /**
     * Determine next chain category based on current category and outcome
     */
    private function determineNextChain(string $currentCategory, string $outcomeType): ?string
    {
        // Only progress to next chain on positive outcomes
        if ($outcomeType !== 'positive') {
            return null;
        }
        
        return match($currentCategory) {
            'education' => 'career',
            'career' => 'achievement',
            'family' => 'relationships',
            'social' => 'reputation',
            default => null,
        };
    }
}

