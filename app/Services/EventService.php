<?php

namespace App\Services;

use App\Models\StatTriggerCondition;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\ProfessionPathEvent;
use App\Models\AgeSpecificEvent;
use App\Models\Character;
use Illuminate\Support\Collection;

class EventService
{
    /**
     * Event categories for branching
     */
    const CATEGORIES = [
        // Education chain
        'education_elementary' => ['age_group' => 'child', 'next' => 'education_high_school'],
        'education_high_school' => ['age_group' => 'teen', 'next' => 'education_college'],
        'education_college' => ['age_group' => 'adult', 'next' => null],
        
        // Career chain
        'career_start' => ['age_group' => 'adult', 'next' => 'career_advancement'],
        'career_advancement' => ['age_group' => 'adult', 'next' => 'career_master'],
        'career_master' => ['age_group' => 'adult', 'next' => null],
        
        // Family chain
        'family_relationship' => ['age_group' => 'teen', 'next' => 'family_marriage'],
        'family_marriage' => ['age_group' => 'adult', 'next' => 'family_parenthood'],
        'family_parenthood' => ['age_group' => 'adult', 'next' => null],
        
        // Health chain
        'health_crisis' => ['age_group' => 'adult', 'next' => 'health_recovery'],
        'health_recovery' => ['age_group' => 'adult', 'next' => null],
        
        // Social chain
        'social_friendship' => ['age_group' => 'child', 'next' => 'social_romantic'],
        'social_romantic' => ['age_group' => 'teen', 'next' => 'social_relationship'],
        'social_relationship' => ['age_group' => 'adult', 'next' => null],
        
        // Skills chain
        'skill_learning' => ['age_group' => 'child', 'next' => 'skill_development'],
        'skill_development' => ['age_group' => 'teen', 'next' => 'skill_mastery'],
        'skill_mastery' => ['age_group' => 'adult', 'next' => null],
    ];

    /**
     * Outcome types for branching
     */
    const OUTCOMES = [
        'positive' => ['Happy', 'Great', 'Success', 'Win', 'Best', 'Excellent', 'Joy'],
        'negative' => ['Sad', 'Bad', 'Fail', 'Loss', 'Worst', 'Tragedy', 'Fatal'],
        'neutral' => ['Normal', 'Average', 'Mixed', 'Okay', 'Fine']
    ];

    /**
     * Get a random event by weight probability
     */
    public function getRandomEventByWeight(Collection $events): ?object
    {
        if ($events->isEmpty()) {
            return null;
        }

        $totalWeight = $events->sum('weight');
        $random = rand(1, $totalWeight);
        $current = 0;

        foreach ($events as $event) {
            $current += $event->weight;
            if ($random <= $current) {
                return $event;
            }
        }

        return $events->last();
    }

    /**
     * Get a random daily event
     */
    public function getRandomDailyEvent(): ?DailyEvent
    {
        $events = DailyEvent::all();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random cultural event
     */
    public function getRandomCulturalEvent(): ?CulturalEvent
    {
        $events = CulturalEvent::all();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random event for a specific age group
     */
    public function getRandomAgeSpecificEvent(string $ageGroup): ?AgeSpecificEvent
    {
        $events = AgeSpecificEvent::where('age_group', $ageGroup)->get();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Get a random profession path event
     */
    public function getRandomProfessionEvent(string $profession): ?ProfessionPathEvent
    {
        $events = ProfessionPathEvent::where('profession', $profession)->get();
        return $this->getRandomEventByWeight($events);
    }

    /**
     * Parse stat effects string and return as array
     * Format: "+10 Happiness, -5 Morality, +3 Intelligence"
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
     * Apply stat effects to a character - updates effective stats and saves
     * Returns the effects array for display
     */
    public function applyStatEffects(Character $character, ?string $effectsText): array
    {
        // Handle null or empty effects text
        if (empty($effectsText)) {
            return [];
        }
        
        $effects = $this->parseStatEffects($effectsText);
        
        if (empty($effects)) {
            return [];
        }
        
        // Get current base stats and hidden stats
        $baseStats = is_array($character->stats) ? $character->stats : [];
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        
        // Get existing effective stats or initialize from base + hidden stats
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        
        // If effective_stats is empty, initialize it from base stats and hidden stats
        if (empty($effectiveStats)) {
            $effectiveStats = array_merge($baseStats, $hiddenStats);
        }
        
        // Apply effects to effective stats
        foreach ($effects as $stat => $value) {
            // Initialize stat if it doesn't exist
            if (!isset($effectiveStats[$stat])) {
                $effectiveStats[$stat] = 0;
            }
            
            $effectiveStats[$stat] += $value;
            
            // Ensure stats don't go below 0 or above 100
            $effectiveStats[$stat] = max(0, min(100, $effectiveStats[$stat]));
        }

        // Update character effective_stats
        $character->effective_stats = $effectiveStats;
        
        // Save the character to persist changes
        $character->save();

        return $effects;
    }

    /**
     * Check if any stat trigger conditions are met
     */
    public function checkStatTriggers(Character $character): array
    {
        $triggeredEvents = [];
        $stats = $character->stats ?? [];

        $triggers = StatTriggerCondition::all();

        foreach ($triggers as $trigger) {
            if ($this->evaluateTriggerCondition($trigger->stat_name, $trigger->threshold, $stats)) {
                $triggeredEvents[] = $trigger;
            }
        }

        return $triggeredEvents;
    }

    /**
     * Evaluate if a trigger condition is met
     */
    private function evaluateTriggerCondition(string $stat, int $threshold, array $stats): bool
    {
        return isset($stats[$stat]) && $stats[$stat] >= $threshold;
    }

    /**
     * Check if a character meets profession unlock conditions
     */
    public function checkProfessionUnlock(Character $character): array
    {
        $unlockedProfessions = [];
        $stats = $character->stats ?? [];

        $professions = \App\Models\ProfessionTrigger::all();

        foreach ($professions as $profession) {
            if ($this->evaluateProfessionCondition($profession->unlock_condition, $stats)) {
                $unlockedProfessions[] = $profession;
            }
        }

        return $unlockedProfessions;
    }

    /**
     * Evaluate profession unlock condition
     */
    private function evaluateProfessionCondition(string $condition, array $stats): bool
    {
        $conditions = preg_split('/\s+(AND|OR)\s+/i', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

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

    /**
     * Evaluate a single condition
     */
    private function evaluateSingleCondition(string $condition, array $stats): bool
    {
        $condition = trim($condition);

        if (preg_match('/(\w+)\s*(>=|<=|>|<|===|==|!=)\s*(\d+)/', $condition, $matches)) {
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
     * Determine the outcome type based on stat effects
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
     * Check if event has valid prerequisites for character
     */
    public function checkEventPrerequisites($event, Character $character): bool
    {
        if (!isset($event->parent_category) || empty($event->parent_category)) {
            return true;
        }

        $completedChains = $character->completed_event_chains ?? [];
        
        if (!in_array($event->parent_category, $completedChains)) {
            return false;
        }
        
        if (isset($event->required_choice_outcome) && !empty($event->required_choice_outcome)) {
            $outcomeKey = $event->parent_category . '_outcome';
            $actualOutcome = $character->$outcomeKey ?? null;
            
            if ($actualOutcome !== $event->required_choice_outcome) {
                return false;
            }
        }
        
        if (isset($event->required_stat) && !empty($event->required_stat)) {
            $stats = $character->effective_stats ?? $character->stats ?? [];
            $threshold = $event->stat_threshold ?? 50;
            
            if (($stats[$event->required_stat] ?? 0) < $threshold) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get events for a character's current narrative path
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
     * Determine and set narrative path based on choices made
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
                    $character->current_narrative = $narrative . ($outcomeType === 'positive' ? '_success' : ($outcomeType === 'negative' ? '_struggle' : '_neutral'));
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
     * Complete an event chain and unlock next events
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
}

