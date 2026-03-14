<?php

namespace App\Services;

use App\Models\AgeSpecificEvent;
use App\Models\Character;
use App\Models\CulturalEvent;
use App\Models\DailyEvent;
use App\Models\ProfessionPathEvent;
use App\Models\StatTriggerCondition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EventService
{
    /**
     * Event categories for branching
     */
    const CATEGORIES = [
        'education_elementary' => ['age_group' => 'child', 'next' => 'education_high_school'],
        'education_high_school' => ['age_group' => 'teen', 'next' => 'education_college'],
        'education_college' => ['age_group' => 'adult', 'next' => null],
        'career_start' => ['age_group' => 'adult', 'next' => 'career_advancement'],
        'career_advancement' => ['age_group' => 'adult', 'next' => 'career_master'],
        'career_master' => ['age_group' => 'adult', 'next' => null],
        'family_relationship' => ['age_group' => 'teen', 'next' => 'family_marriage'],
        'family_marriage' => ['age_group' => 'adult', 'next' => 'family_parenthood'],
        'family_parenthood' => ['age_group' => 'adult', 'next' => null],
        'health_crisis' => ['age_group' => 'adult', 'next' => 'health_recovery'],
        'health_recovery' => ['age_group' => 'adult', 'next' => null],
        'social_friendship' => ['age_group' => 'child', 'next' => 'social_romantic'],
        'social_romantic' => ['age_group' => 'teen', 'next' => 'social_relationship'],
        'social_relationship' => ['age_group' => 'adult', 'next' => null],
        'skill_learning' => ['age_group' => 'child', 'next' => 'skill_development'],
        'skill_development' => ['age_group' => 'teen', 'next' => 'skill_mastery'],
        'skill_mastery' => ['age_group' => 'adult', 'next' => null],
    ];

    /**
     * State transition table
     */
    const STATE_TRANSITIONS = [
        'life_stage' => [
            'child' => ['teenager' => 0.1, 'adult' => 0.05],
            'teenager' => ['adult' => 0.15],
            'adult' => ['old' => 0.08],
        ],
        'profession_state' => [
            'unemployed' => ['entry_level' => 0.12],
            'entry_level' => ['mid_level' => 0.1],
            'mid_level' => ['senior' => 0.08],
        ],
        'relationship_status' => [
            'single' => ['dating' => 0.15],
            'dating' => ['married' => 0.12, 'single' => 0.05],
            'married' => ['divorced' => 0.03, 'widowed' => 0.02],
        ],
        'health_condition' => [
            'healthy' => ['ill' => 0.07],
            'ill' => ['healthy' => 0.2],
        ],
    ];

    /**
     * Get events filtered by current FSM state (NEW)
     */
    public function getStatefulEvents(Character $character, string $ageGroup): array
    {
        try {
Log::info('EventService::getStatefulEvents called', [
                'character_id' => $character->id,
                'age_group' => $ageGroup,
                'current_state' => $character->current_state
            ]);
            
            $state = $character->current_state ?? ['life_stage' => 'child'];
            
            $eventsByCategory = [
                'daily' => $this->getFilteredDailyEvents($character, $state),
                'cultural' => $this->getFilteredCulturalEvents($character, $state),
                'ageSpecific' => $this->getFilteredAgeSpecificEvents($character, $ageGroup, $state),
                'profession' => $this->getFilteredProfessionEvents($character, $state),
            ];

            $result = $this->weightEventsByState($eventsByCategory, $state);
            
Log::info('EventService::getStatefulEvents complete', [
                'character_id' => $character->id,
                'events_count' => [
                    'daily' => count($result['daily'] ?? []),
                    'cultural' => count($result['cultural'] ?? []),
                    'ageSpecific' => count($result['ageSpecific'] ?? []),
                    'profession' => count($result['profession'] ?? []),
                ]
            ]);
            
            return $result;
        } catch (\Exception $e) {
Log::error('Error in getStatefulEvents', [
                'character_id' => $character->id,
                'age_group' => $ageGroup,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'daily' => [],
                'cultural' => [],
                'ageSpecific' => [],
                'profession' => [],
            ];
        }
    }

    private function getFilteredDailyEvents(Character $character, array $state): Collection
    {
        $query = DailyEvent::query();
        
        if (isset($state['health_condition']) && $state['health_condition'] === 'ill') {
            $query->where('title', 'like', '%Health%')->orWhere('title', 'like', '%hospital%');
        }
        
        if (isset($state['relationship_status']) && $state['relationship_status'] === 'married') {
            $query->where('title', 'like', '%family%')->orWhere('title', 'like', '%marriage%');
        }
        
        $shownIds = $this->extractShownIdsByType($character->shown_event_ids ?? [], 'daily');
        if (!empty($shownIds)) {
            $query->whereNotIn('id', $shownIds);
        }
        
        return $query->get();
    }

    /**
     * Extract numeric event IDs from shown_event_ids by prefix (e.g. 'daily_123' → 123)
     */
    private function extractShownIdsByType(?array $shownEventIds, string $prefix): array
    {
        if (empty($shownEventIds)) {
            return [];
        }

        return collect($shownEventIds)
            ->filter(fn($id) => str_starts_with((string)$id, $prefix . '_'))
            ->map(fn($id) => (int) str_replace($prefix . '_', '', $id))
            ->values()
            ->toArray();
    }

    private function getFilteredCulturalEvents(Character $character, array $state): Collection
    {
        $query = CulturalEvent::query();
        
        if (isset($state['profession_state']) && $state['profession_state'] !== 'unemployed') {
            $query->where('title', 'like', '%career%')->orWhere('title', 'like', '%networking%');
        }
        
        $shownIds = $this->extractShownIdsByType($character->shown_event_ids ?? [], 'cultural');
        if (!empty($shownIds)) {
            $query->whereNotIn('id', $shownIds);
        }
        
        return $query->get();
    }

    private function getFilteredAgeSpecificEvents(Character $character, string $ageGroup, array $state): Collection
    {
        $query = AgeSpecificEvent::where('age_group', $ageGroup);
        
        if (isset($state['life_stage']) && $state['life_stage'] === 'child') {
            $query->where('title', 'like', '%school%')->orWhere('title', 'like', '%family%');
        } elseif (isset($state['life_stage']) && $state['life_stage'] === 'adult') {
            $query->where('title', 'like', '%career%')->orWhere('title', 'like', '%marriage%');
        }
        
        $shownIds = $this->extractShownIdsByType($character->shown_event_ids ?? [], 'ageSpecific');
        if (!empty($shownIds)) {
            $query->whereNotIn('id', $shownIds);
        }
        
        return $query->get();
    }

    private function getFilteredProfessionEvents(Character $character, array $state): Collection
    {
        $profession = $character->profession;
        if (!$profession) return collect();
        
        $query = ProfessionPathEvent::where('profession', $profession);
        
        if (isset($state['profession_state']) && $state['profession_state'] === 'entry_level') {
            $query->where('title', 'like', '%promotion%');
        }
        
        $shownIds = $this->extractShownIdsByType($character->shown_event_ids ?? [], 'profession');
        if (!empty($shownIds)) {
            $query->whereNotIn('id', $shownIds);
        }
        
        return $query->get();
    }

    private function weightEventsByState(array $eventsByCategory, array $state): array
    {
        $weighted = [];
        
        foreach ($eventsByCategory as $category => $events) {
            $categoryWeight = $this->getCategoryWeight($category, $state);
            
            foreach ($events as $event) {
                $eventWeight = ($event->weight ?? 1) * $categoryWeight;
                $event->calculated_weight = $eventWeight;
            }
            
            $weighted[$category] = $events;
        }
        
        return $weighted;
    }

    private function getCategoryWeight(string $category, array $state): float
    {
        $weights = [
            'daily' => 1.0,
            'cultural' => 0.8,
            'ageSpecific' => 1.2,
            'profession' => isset($state['profession_state']) && $state['profession_state'] !== 'unemployed' ? 1.1 : 0.3,
        ];
        
        return $weights[$category] ?? 1.0;
    }

    /**
     * Advance FSM state based on event outcome (NEW)
     */
    public function advanceState(Character $character, string $eventType, string $outcomeType): void
    {
        $state = $character->character_state ?? [];
        
        foreach (self::STATE_TRANSITIONS as $stateType => $transitions) {
            if (isset($state[$stateType]) && isset($transitions[$state[$stateType]])) {
                $possibleTransitions = $transitions[$state[$stateType]];
                
                $totalWeight = array_sum($possibleTransitions);
                $rand = mt_rand() / mt_getrandmax() * $totalWeight;
                $current = 0;
                
                foreach ($possibleTransitions as $newState => $weight) {
                    $current += $weight;
                    if ($rand <= $current) {
                        if ($outcomeType === 'positive') {
                            $state[$stateType] = $newState;
                        } elseif ($outcomeType === 'negative' && rand(0, 100) > 70) {
                            $downgrade = array_keys($transitions);
                            $state[$stateType] = $downgrade[array_rand($downgrade)];
                        }
                        break;
                    }
                }
            }
        }
        
        $character->character_state = $state;
        $character->save();
    }

    /**
     * Get a random event by weight probability (EXISTING)
     */
    public function getRandomEventByWeight(Collection $events): ?object
    {
        if ($events->isEmpty()) {
            return null;
        }

        $totalWeight = 0.0;
        foreach ($events as $event) {
            $weight = (float) ($event->weight ?? 1);
            if ($weight > 0) {
                $totalWeight += $weight;
            }
        }

        if ($totalWeight <= 0) {
            return $events->first();
        }

        $random = (mt_rand() / mt_getrandmax()) * $totalWeight;
        $current = 0.0;

        foreach ($events as $event) {
            $weight = (float) ($event->weight ?? 1);
            if ($weight <= 0) {
                continue;
            }

            $current += $weight;
            if ($random <= $current) {
                return $event;
            }
        }

        return $events->last();
    }

    /**
     * Get a random daily event (EXISTING)
     */
    public function getRandomDailyEvent(): ?DailyEvent
    {
        $events = DailyEvent::all();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random cultural event (EXISTING)
     */
    public function getRandomCulturalEvent(): ?CulturalEvent
    {
        $events = CulturalEvent::all();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random event for a specific age group (EXISTING)
     */
    public function getRandomAgeSpecificEvent(string $ageGroup): ?AgeSpecificEvent
    {
        $events = AgeSpecificEvent::where('age_group', $ageGroup)->get();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random profession path event (EXISTING)
     */
    public function getRandomProfessionEvent(string $profession): ?ProfessionPathEvent
    {
        $events = ProfessionPathEvent::where('profession', $profession)->get();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Parse stat effects string (EXISTING)
     */
    public function parseStatEffects(?string $effectsText): array
    {
        if (empty($effectsText)) {
            return [];
        }
        
        $effects = [];
        $parts = explode(',', $effectsText);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $stat = $matches[2];
                $value = (int)$matches[1];
                $effects[$stat] = ($effects[$stat] ?? 0) + $value;
            }
        }

        return $effects;
    }

    /**
     * Apply stat effects to character (EXISTING)
     */
    public function applyStatEffects(Character $character, ?string $effectsText): array
    {
        if (empty($effectsText)) {
            return [];
        }
        
        $effects = $this->parseStatEffects($effectsText);
        
        if (empty($effects)) {
            return [];
        }
        
        $baseStats = is_array($character->stats) ? $character->stats : [];
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        
        if (empty($effectiveStats)) {
            $effectiveStats = array_merge($baseStats, $hiddenStats);
        }
        
        foreach ($effects as $stat => $value) {
            if (!isset($effectiveStats[$stat])) {
                $effectiveStats[$stat] = 0;
            }
            
            $effectiveStats[$stat] += $value;
            $effectiveStats[$stat] = max(0, min(100, $effectiveStats[$stat]));
        }

        $character->effective_stats = $effectiveStats;
        $character->save();

        return $effects;
    }

    /**
     * Check stat triggers (EXISTING)
     */
    public function checkStatTriggers(Character $character): array
    {
        $triggeredEvents = [];
        $stats = $character->effective_stats ?? $character->stats ?? [];

        $triggers = StatTriggerCondition::all();

        foreach ($triggers as $trigger) {
            if ($this->evaluateTriggerCondition($trigger->stat_name, $trigger->threshold, $stats)) {
                $triggeredEvents[] = $trigger;
            }
        }

        return $triggeredEvents;
    }

    private function evaluateTriggerCondition(string $stat, int $threshold, array $stats): bool
    {
        return isset($stats[$stat]) && $stats[$stat] >= $threshold;
    }

    /**
     * Check profession unlock (EXISTING)
     */
    public function checkProfessionUnlock(Character $character): array
    {
        $unlockedProfessions = [];
        $stats = $character->effective_stats ?? $character->stats ?? [];

        $professions = \App\Models\ProfessionTrigger::all();

        foreach ($professions as $profession) {
            if ($this->evaluateProfessionCondition($profession->unlock_condition, $stats)) {
                $unlockedProfessions[] = $profession;
            }
        }

        return $unlockedProfessions;
    }

    private function evaluateProfessionCondition(string $condition, array $stats): bool
    {
        $conditions = preg_split('/\\s+(AND|OR)\\s+/i', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

        $result = $this->evaluateSingleCondition($conditions[0], $stats);

        for ($i = 1; $i < count($conditions); $i += 2) {
            $operator = strtoupper($conditions[$i]);
            $nextCondition = $this->evaluateSingleCondition($conditions[$i + 1], $stats);

            if ($operator === 'AND') {
                $result = $result && $nextCondition;
            } elseif ($operator === 'OR') {
                $result = $result || $nextCondition;
            }
        }

        return $result;
    }

    private function evaluateSingleCondition(string $condition, array $stats): bool
    {
        $condition = trim($condition);

        if (preg_match('/(\\w+)\\s*(>=|<=|>|<|===|==|!=)\\s*(\\d+)/', $condition, $matches)) {
            $stat = $matches[1];
            $operator = $matches[2];
            $value = (int)$matches[3];

            $statValue = $stats[$stat] ?? 0;

            switch ($operator) {
                case '>=':
                    return $statValue >= $value;
                case '<=':
                    return $statValue <= $value;
                case '>':
                    return $statValue > $value;
                case '<':
                    return $statValue < $value;
                case '==':
                case '===':
                    return $statValue == $value;
                case '!=':
                    return $statValue != $value;
                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * Determine outcome type (EXISTING)
     */
    public function determineOutcomeType(?string $effectsText): string
    {
        if (empty($effectsText)) {
            return 'neutral';
        }
        
        $effects = $this->parseStatEffects($effectsText);
        
        if (empty($effects)) {
            return 'neutral';
        }
        
        $positiveStats = ['Happiness', 'Health', 'Reputation', 'Intelligence', 'Strength', 'Charisma', 'Creativity', 'Wealth', 'Discipline', 'Morality', 'Luck'];
        $negativeStats = ['Burnout', 'Isolation', 'Debt', 'Addiction', 'Ego'];
        
        $positiveScore = 0;
        $negativeScore = 0;
        
        foreach ($effects as $stat => $value) {
            if (in_array($stat, $positiveStats)) {
                $positiveScore += $value;
            } elseif (in_array($stat, $negativeStats)) {
                $negativeScore -= $value;
            }
        }
        
        if ($positiveScore > $negativeScore + 5) {
            return 'positive';
        } elseif ($negativeScore > $positiveScore + 5) {
            return 'negative';
        }
        
        return 'neutral';
    }

    /**
     * Check event prerequisites (EXISTING)
     */
    public function checkEventPrerequisites($event, Character $character): bool
    {
        $completedChains = $character->completed_event_chains ?? [];

        if (!empty($event->parent_category)) {
            if (!in_array($event->parent_category, $completedChains)) {
                return false;
            }

            if (!empty($event->required_choice_outcome)) {
                $outcomeKey = $event->parent_category . '_outcome';
                $actualOutcome = $character->$outcomeKey ?? null;

                if ($actualOutcome !== $event->required_choice_outcome) {
                    return false;
                }
            }
        }

        if (!empty($event->required_stat)) {
            $stats = $character->effective_stats ?? $character->stats ?? [];
            $threshold = $event->stat_threshold ?? 50;

            if (($stats[$event->required_stat] ?? 0) < $threshold) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get events for narrative path (EXISTING)
     */
    public function getEventsForNarrativePath(Character $character, string $ageGroup, array $shownEventIds = []): array
    {
        $currentNarrative = $character->current_narrative ?? null;
        $completedChains = $character->completed_event_chains ?? [];
        $activePaths = $character->active_event_paths ?? [];
        
        $ageSpecificEvents = AgeSpecificEvent::where('age_group', $ageGroup)->get();
        
        $availableEvents = [];
        $milestoneEvents = [];
        
        foreach ($ageSpecificEvents as $event) {
            $eventKey = 'ageSpecific_' . $event->id;
            if (in_array($eventKey, $shownEventIds)) {
                continue;
            }
            
            if (!$this->checkEventPrerequisites($event, $character)) {
                continue;
            }
            
            if ($event->is_milestone ?? false) {
                $milestoneEvents[] = $event;
            } else {
                $availableEvents[] = $event;
            }
        }
        
        return [
            'narrative_events' => $availableEvents,
            'milestone_events' => $milestoneEvents,
            'current_narrative' => $currentNarrative,
            'completed_chains' => $completedChains,
            'active_paths' => $activePaths
        ];
    }

    /**
     * Update narrative path (EXISTING)
     */
    public function updateNarrativePath(Character $character, string $eventCategory, string $outcomeType): void
    {
        $activePaths = $character->active_event_paths ?? [];
        
        $narrativeMappings = [
            'education' => 'education_path',
            'career' => 'career_path',
            'family' => 'family_path',
            'health' => 'health_path',
            'social' => 'social_path',
            'skill' => 'skill_path'
        ];
        
        foreach ($narrativeMappings as $category => $narrative) {
            if (str_contains($eventCategory, $category)) {
                if (empty($character->current_narrative)) {
$character->current_narrative = $narrative . (($outcomeType === 'positive') ? '_success' : (($outcomeType === 'negative') ? '_struggle' : '_neutral'));
                }
                
                if (!in_array($eventCategory, $activePaths)) {
                    $activePaths[] = $eventCategory;
                    $character->active_event_paths = $activePaths;
                }
                
                break;
            }
        }
        
        $character->save();
    }

    /**
     * Complete event chain (EXISTING)
     */
    public function completeEventChain(Character $character, string $category, string $outcomeType): void
    {
        $completedChains = $character->completed_event_chains ?? [];
        
        if (!in_array($category, $completedChains)) {
            $completedChains[] = $category;
            $character->completed_event_chains = $completedChains;
        }
        
        $outcomeKey = $category . '_outcome';
        $character->$outcomeKey = $outcomeType;
        
        if (isset(self::CATEGORIES[$category])) {
            $nextCategory = self::CATEGORIES[$category]['next'];
            if ($nextCategory) {
                $activePaths = $character->active_event_paths ?? [];
                if (!in_array($nextCategory, $activePaths)) {
                    $activePaths[] = $nextCategory;
                    $character->active_event_paths = $activePaths;
                }
            }
        }
        
        $character->save();
    }

    /**
     * Calculate MBTI (EXISTING)
     */
    public function calculateMBTI(array $characterStats, string $eventType, string $choiceIndex, string $outcomeType): string
    {
        $mbtiScores = [
            'E' => 50, 'I' => 50,
            'N' => 50, 'S' => 50,
            'T' => 50, 'F' => 50,
            'J' => 50, 'P' => 50
        ];

        if (in_array($eventType, ['cultural', 'social'])) {
            $mbtiScores['E'] += 10;
            $mbtiScores['I'] -= 5;
        } elseif (in_array($eventType, ['profession', 'daily_routine'])) {
            $mbtiScores['I'] += 12;
            $mbtiScores['E'] -= 6;
        }

        if (strpos($eventType, 'creative') !== false || $characterStats['Creativity'] > 70) {
            $mbtiScores['N'] += 15;
            $mbtiScores['S'] -= 8;
        }

        if (in_array($eventType, ['profession', 'career']) || strpos($outcomeType, 'Success') !== false) {
            $mbtiScores['T'] += 12;
            $mbtiScores['F'] -= 6;
        } elseif (strpos($outcomeType, 'Happy') !== false || strpos($outcomeType, 'Family') !== false) {
            $mbtiScores['F'] += 14;
            $mbtiScores['T'] -= 7;
        }

        if (strpos($eventType, 'planned') !== false || $characterStats['Discipline'] > 70) {
            $mbtiScores['J'] += 10;
            $mbtiScores['P'] -= 5;
        }

        foreach (['E', 'I', 'N', 'S', 'T', 'F', 'J', 'P'] as $trait) {
            $mbtiScores[$trait] = max(0, min(100, $mbtiScores[$trait]));
        }

        $type = '';
        $type .= $mbtiScores['E'] > $mbtiScores['I'] ? 'E' : 'I';
        $type .= $mbtiScores['N'] > $mbtiScores['S'] ? 'N' : 'S';
        $type .= $mbtiScores['T'] > $mbtiScores['F'] ? 'T' : 'F';
        $type .= $mbtiScores['J'] > $mbtiScores['P'] ? 'J' : 'P';

        return $type;
    }
}
