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
    private const EFFECT_STAT_ALIASES = [
        'Stress' => ['stat' => 'Burnout', 'multiplier' => 1],
        'Fatigue' => ['stat' => 'Burnout', 'multiplier' => 1],
        'Peace' => ['stat' => 'Happiness', 'multiplier' => 1],
        'Ethics' => ['stat' => 'Morality', 'multiplier' => 1],
        'Finance' => ['stat' => 'Wealth', 'multiplier' => 1],
        // Lower corruption should improve morality, and higher corruption should hurt it.
        'Corruption' => ['stat' => 'Morality', 'multiplier' => -1],
    ];

    /**
     * Health condition thresholds based on health percentage
     */
    const HEALTH_CONDITIONS = [
        ['min' => 0, 'max' => 10, 'status' => 'dead'],
        ['min' => 11, 'max' => 25, 'status' => 'critical'],
        ['min' => 26, 'max' => 50, 'status' => 'unhealthy'],
        ['min' => 51, 'max' => 75, 'status' => 'sick'],
        ['min' => 76, 'max' => 90, 'status' => 'fever'],
        ['min' => 91, 'max' => 100, 'status' => 'healthy'],
    ];

    /**
     * Get health status text based on health percentage
     */
    public function getHealthStatus(int $healthPercentage): string
    {
        foreach (self::HEALTH_CONDITIONS as $condition) {
            if ($healthPercentage >= $condition['min'] && $healthPercentage <= $condition['max']) {
                return $condition['status'];
            }
        }
        return 'healthy';
    }

    /**
     * Calculate and update health condition in character state
     */
    public function updateHealthCondition(Character $character): string
    {
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $health = isset($effectiveStats['Health']) ? (int)$effectiveStats['Health'] : 100;
        
        $healthStatus = $this->getHealthStatus($health);
        
        $state = $character->character_state ?? [];
        $state['health_condition'] = $healthStatus;
        $character->character_state = $state;
        $character->save();
        
        return $healthStatus;
    }

    /**
     * Calculate age from day (1 day = 1 year for simplicity, or use 365 days per year)
     */
    public function calculateAge(int $currentDay): int
    {
        // Using 1 day = 1 year for game simplicity
        return $currentDay;
    }

    /**
     * Get age group from age
     */
    public function getAgeGroupFromAge(int $age): string
    {
        if ($age < 10) return 'child';
        if ($age < 18) return 'teenager';
        if ($age < 60) return 'adult';
        return 'old';
    }

    /**
     * Calculate success chance based on character stats
     */
    public function calculateSuccessChance(Character $character, object $event): int
    {
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $baseChance = 50; // Base 50% chance
        
        // Adjust based on relevant stats
        if (isset($event->required_stat)) {
            $statValue = $effectiveStats[$event->required_stat] ?? 50;
            $baseChance = $statValue; // Direct correlation
        }
        
        // Apply hidden stats modifiers
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        
        // Addiction reduces success chance
        if (isset($hiddenStats['Addiction'])) {
            $baseChance -= ($hiddenStats['Addiction'] * 0.5);
        }
        
        // Burnout reduces success chance
        if (isset($hiddenStats['Burnout'])) {
            $baseChance -= ($hiddenStats['Burnout'] * 0.3);
        }
        
        return max(5, min(95, $baseChance));
    }

    /**
     * Determine outcome based on stats (game-decided)
     */
    public function determineGameOutcome(Character $character, object $event): string
    {
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];

        if (isset($event->required_stat)) {
            $requiredStat = (string) $event->required_stat;
            $threshold = isset($event->stat_threshold) ? (int) $event->stat_threshold : 50;
            $statValue = (int) ($effectiveStats[$requiredStat] ?? 0);

            return $statValue >= $threshold ? 'success' : 'failure';
        }

        // Deterministic fallback: base success on computed chance (no randomness).
        $successChance = $this->calculateSuccessChance($character, $event);
        return $successChance >= 55 ? 'success' : 'failure';
    }

    /**
     * Check for severe consequences from choices
     */
    public function checkSevereConsequences(Character $character, array $rawEffects): array
    {
        $consequences = [];

        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $state = is_array($character->character_state) ? $character->character_state : [];

        $age = $this->calculateAge((int) ($character->current_day ?? 1));
        $health = (int) ($effectiveStats['Health'] ?? 50);
        $wealth = (int) ($effectiveStats['Wealth'] ?? 50);
        $burnout = (int) ($effectiveStats['Burnout'] ?? 0);
        $debt = (int) ($effectiveStats['Debt'] ?? 0);

        $stateChanged = false;
        $statsChanged = false;

        // Bankruptcy: high debt + low wealth locks expensive actions.
        if (($state['is_bankrupt'] ?? false) !== true && ($debt >= 90 || ($debt >= 70 && $wealth <= 5))) {
            $state['is_bankrupt'] = true;
            $stateChanged = true;
            $consequences[] = [
                'type' => 'bankruptcy',
                'message' => 'You declared bankruptcy. Expensive choices will be restricted until you recover.',
            ];
        }

        // Cancer: persistent condition once very unhealthy at older ages.
        if (($state['has_cancer'] ?? false) !== true && $age >= 35 && $health <= 20) {
            $state['has_cancer'] = true;
            $stateChanged = true;
            $consequences[] = [
                'type' => 'cancer',
                'message' => 'You were diagnosed with cancer. Health will decline faster over time.',
            ];
        }

        // Disability: extreme burnout can lead to a disabling breakdown/accident.
        if (($state['has_disability'] ?? false) !== true && $burnout >= 95) {
            $state['has_disability'] = true;
            $stateChanged = true;

            $effectiveStats['Strength'] = max(5, (int) ($effectiveStats['Strength'] ?? 50) - 30);
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - 10);
            $statsChanged = true;

            $consequences[] = [
                'type' => 'disability',
                'message' => 'A serious breakdown left you disabled. Some career and physical actions become harder.',
            ];
        }

        // Severe addiction (supports numeric addiction stat effects).
        $addictionDelta = (int) ($rawEffects['Addiction'] ?? 0);
        $addictionNow = (int) ($effectiveStats['Addiction'] ?? 0);
        if (($state['has_severe_addiction'] ?? false) !== true && ($addictionNow + max(0, $addictionDelta)) >= 85) {
            $state['has_severe_addiction'] = true;
            $stateChanged = true;
            $consequences[] = [
                'type' => 'severe_addiction',
                'message' => 'Your addiction spirals out of control. Recovery options will start appearing.',
            ];
        }

        if ($stateChanged) {
            $character->character_state = $state;
        }
        if ($statsChanged) {
            $character->effective_stats = $effectiveStats;
            $this->syncLifeStatsFromEffectiveStats($character, $effectiveStats);
        }
        if ($stateChanged || $statsChanged) {
            $character->save();
        }

        return $consequences;
    }

    /**
     * Apply ongoing effects when time advances (deterministic, no randomness).
     * Returns consequences to surface in the life log.
     */
    public function applyTimePassage(Character $character, int $daysAdvanced): array
    {
        $daysAdvanced = max(0, $daysAdvanced);
        if ($daysAdvanced === 0) {
            return [];
        }

        $consequences = [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $state = is_array($character->character_state) ? $character->character_state : [];

        $changed = false;

        if (($state['has_cancer'] ?? false) === true) {
            $delta = 3 * $daysAdvanced;
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $delta);
            $consequences[] = [
                'type' => 'cancer_progression',
                'message' => "Cancer progresses (-{$delta} Health).",
            ];
            $changed = true;
        }

        if (($state['has_severe_addiction'] ?? false) === true) {
            $deltaHealth = 1 * $daysAdvanced;
            $deltaHappiness = 1 * $daysAdvanced;
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $deltaHealth);
            $effectiveStats['Happiness'] = max(0, (int) ($effectiveStats['Happiness'] ?? 50) - $deltaHappiness);
            $changed = true;
        }

        if (($state['has_disability'] ?? false) === true) {
            // Disability increases burnout accumulation over time.
            $delta = 1 * $daysAdvanced;
            $effectiveStats['Burnout'] = min(100, (int) ($effectiveStats['Burnout'] ?? 0) + $delta);
            $changed = true;
        }

        if (($state['is_bankrupt'] ?? false) === true) {
            // Bankruptcy slowly reduces happiness until stabilized.
            $delta = 1 * $daysAdvanced;
            $effectiveStats['Happiness'] = max(0, (int) ($effectiveStats['Happiness'] ?? 50) - $delta);
            $changed = true;
        }

        $profile = app(AdaptiveNarrativeService::class)->getDecisionProfile($character);
        $flags = is_array($profile['flags'] ?? null) ? $profile['flags'] : [];

        if (($flags['study_habit'] ?? false) === true && ($flags['burnout_cycle'] ?? false) !== true) {
            $effectiveStats['Intelligence'] = min(100, (int) ($effectiveStats['Intelligence'] ?? 50) + $daysAdvanced);
            $effectiveStats['Discipline'] = min(100, (int) ($effectiveStats['Discipline'] ?? 50) + $daysAdvanced);
            $consequences[] = [
                'type' => 'study_habit',
                'message' => "Your study routine compounds over time (+{$daysAdvanced} Intelligence, +{$daysAdvanced} Discipline).",
            ];
            $changed = true;
        }

        if (($flags['burnout_cycle'] ?? false) === true) {
            $burnoutDelta = 2 * $daysAdvanced;
            $healthDelta = 1 * $daysAdvanced;
            $effectiveStats['Burnout'] = min(100, (int) ($effectiveStats['Burnout'] ?? 0) + $burnoutDelta);
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $healthDelta);
            $consequences[] = [
                'type' => 'burnout_cycle',
                'message' => "Your burnout pattern keeps draining you (+{$burnoutDelta} Burnout, -{$healthDelta} Health).",
            ];
            $changed = true;
        }

        if (($flags['relationship_strain'] ?? false) === true) {
            $isolationDelta = 1 * $daysAdvanced;
            $happinessDelta = 1 * $daysAdvanced;
            $effectiveStats['Isolation'] = min(100, (int) ($effectiveStats['Isolation'] ?? 0) + $isolationDelta);
            $effectiveStats['Happiness'] = max(0, (int) ($effectiveStats['Happiness'] ?? 50) - $happinessDelta);
            $consequences[] = [
                'type' => 'relationship_strain',
                'message' => "Unresolved distance keeps weighing on you (+{$isolationDelta} Isolation, -{$happinessDelta} Happiness).",
            ];
            $changed = true;
        }

        if (($flags['scandal_marked'] ?? false) === true) {
            $repDelta = 1 * $daysAdvanced;
            $effectiveStats['Reputation'] = max(0, (int) ($effectiveStats['Reputation'] ?? 50) - $repDelta);
            $consequences[] = [
                'type' => 'scandal_marked',
                'message' => "Your reputation keeps taking hits while the scandal lingers (-{$repDelta} Reputation).",
            ];
            $changed = true;
        }

        if (($flags['dependency_flag'] ?? false) === true) {
            $addictionDelta = 1 * $daysAdvanced;
            $healthDelta = 1 * $daysAdvanced;
            $effectiveStats['Addiction'] = min(100, (int) ($effectiveStats['Addiction'] ?? 0) + $addictionDelta);
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $healthDelta);
            $consequences[] = [
                'type' => 'dependency_flag',
                'message' => "The habit tightens its grip over time (+{$addictionDelta} Addiction, -{$healthDelta} Health).",
            ];
            $changed = true;
        }

        if (($flags['financial_trap'] ?? false) === true) {
            $debtDelta = 1 * $daysAdvanced;
            $effectiveStats['Debt'] = min(100, (int) ($effectiveStats['Debt'] ?? 0) + $debtDelta);
            $effectiveStats['Happiness'] = max(0, (int) ($effectiveStats['Happiness'] ?? 50) - $daysAdvanced);
            $consequences[] = [
                'type' => 'financial_trap',
                'message' => "Financial pressure keeps compounding (+{$debtDelta} Debt, -{$daysAdvanced} Happiness).",
            ];
            $changed = true;
        }

        if (($flags['recovery_arc'] ?? false) === true && ($flags['burnout_cycle'] ?? false) !== true) {
            $effectiveStats['Burnout'] = max(0, (int) ($effectiveStats['Burnout'] ?? 0) - $daysAdvanced);
            $effectiveStats['Health'] = min(100, (int) ($effectiveStats['Health'] ?? 50) + $daysAdvanced);
            $consequences[] = [
                'type' => 'recovery_arc',
                'message' => "Your healthier habits keep paying off (-{$daysAdvanced} Burnout, +{$daysAdvanced} Health).",
            ];
            $changed = true;
        }

        if ($changed) {
            $character->effective_stats = $effectiveStats;
            $this->syncLifeStatsFromEffectiveStats($character, $effectiveStats);
            $character->save();
        }

        return $consequences;
    }

    private function syncLifeStatsFromEffectiveStats(Character $character, array $effectiveStats): void
    {
        if (isset($effectiveStats['Health'])) {
            $character->health = (int) $effectiveStats['Health'];
        }
        if (isset($effectiveStats['Happiness'])) {
            $character->happiness = (int) $effectiveStats['Happiness'];
        }
        if (isset($effectiveStats['Wealth']) || isset($effectiveStats['Finance'])) {
            $character->finance = (int) ($effectiveStats['Wealth'] ?? $effectiveStats['Finance']);
        }
    }

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

                // Deterministic transition: move to the highest-weight next state on positive outcomes.
                // Negative/neutral outcomes keep the current state (no randomness).
                if ($outcomeType === 'positive') {
                    $bestState = null;
                    $bestWeight = null;
                    foreach ($possibleTransitions as $newState => $weight) {
                        if ($bestState === null || $weight > $bestWeight) {
                            $bestState = $newState;
                            $bestWeight = $weight;
                        }
                    }
                    if ($bestState !== null) {
                        $state[$stateType] = $bestState;
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

        $weightedEvents = $events->map(function ($event) {
            $weight = (float) ($event->dynamic_weight ?? $event->calculated_weight ?? $event->weight ?? 1);

            return [
                'event' => $event,
                'weight' => max(0.1, $weight),
            ];
        })->values();

        $total = $weightedEvents->sum('weight');
        if ($total <= 0) {
            return $weightedEvents->first()['event'] ?? null;
        }

        $roll = random_int(1, max(1, (int) ceil($total * 100)));
        $running = 0;

        foreach ($weightedEvents as $entry) {
            $running += (int) ceil($entry['weight'] * 100);
            if ($roll <= $running) {
                return $entry['event'];
            }
        }

        return $weightedEvents->last()['event'] ?? null;
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
                [$stat, $value] = $this->normalizeEffectStat($stat, $value);
                $effects[$stat] = ($effects[$stat] ?? 0) + $value;
            }
        }

        return $effects;
    }

    public function normalizeEffectStat(string $stat, int $value): array
    {
        $normalized = trim($stat);
        $alias = self::EFFECT_STAT_ALIASES[$normalized] ?? null;

        if ($alias === null) {
            return [$normalized, $value];
        }

        return [
            (string) ($alias['stat'] ?? $normalized),
            (int) round($value * (int) ($alias['multiplier'] ?? 1)),
        ];
    }

    public function isDead(Character $character): bool
    {
        $state = is_array($character->character_state) ? $character->character_state : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $health = (int) ($effectiveStats['Health'] ?? $character->health ?? 100);

        return ($state['is_dead'] ?? false) === true
            || ($state['health_condition'] ?? null) === 'dead'
            || $health <= 0;
    }

    public function markCharacterDeath(Character $character, string $cause = 'death', array $context = []): void
    {
        $state = is_array($character->character_state) ? $character->character_state : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];

        $effectiveStats['Health'] = 0;
        $state['is_dead'] = true;
        $state['health_condition'] = 'dead';
        $state['death_cause'] = $cause;
        $state['death_day'] = (int) ($character->current_day ?? 1);

        if (!empty($context)) {
            $state['death_context'] = $context;
        }

        $character->effective_stats = $effectiveStats;
        $character->health = 0;
        $character->character_state = $state;
        $character->save();
    }

    /**
     * Get current life stats snapshot from character
     */
    public function getLifeStats(Character $character): array
    {
        return [
            'health' => $character->health ?? 100,
            'happiness' => $character->happiness ?? 100,
            'finance' => $character->finance ?? 0,
            'relationship_status' => $character->relationship_status ?? 'single',
            'career_level' => $character->career_level ?? 'unemployed',
        ];
    }

    /**
     * Apply stat effects to character WITH AUTOMATIC LOGGING (NEW MAIN METHOD)
     */
    public function applyStatEffects(Character $character, ?string $effectsText, array $event = [], int $choiceIndex = 0, string $choiceText = null): array
    {
        // Capture BEFORE life stats
        $beforeLifeStats = $this->getLifeStats($character);
        
        // Apply effects (existing logic)
        $rawEffects = $this->parseStatEffects($effectsText);
        if (!empty($rawEffects)) {
            $baseStats = is_array($character->stats) ? $character->stats : [];
            $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
            $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
            
            if (empty($effectiveStats)) {
                // Priority: baseStats > hiddenStats (but exclude life stats from hiddenStats)
                $lifeStatsKeys = ['health', 'happiness', 'finance', 'relationship_status', 'career_level'];
                $filteredHiddenStats = array_diff_key($hiddenStats, array_flip($lifeStatsKeys));
                $effectiveStats = array_merge($filteredHiddenStats, $baseStats);
            }
            
            foreach ($rawEffects as $stat => $value) {
                if (!isset($effectiveStats[$stat])) {
                    $effectiveStats[$stat] = 0;
                }
                
                $effectiveStats[$stat] += $value;
                $effectiveStats[$stat] = max(0, min(100, $effectiveStats[$stat]));
            }

            $character->effective_stats = $effectiveStats;
            
            // CRITICAL: Sync life stats fields so they can be read by getLifeStats()
            // This ensures the analytics correctly capture before/after stats
            $this->syncLifeStatsFromEffectiveStats($character, $effectiveStats);
        }
        
        $character->save();
        
        // Capture AFTER life stats
        $afterLifeStats = $this->getLifeStats($character);
        $outcomeType = $this->determineOutcomeType($effectsText);
        
        // LOG EVERY STAT CHANGE - MAIN AUDIT TRAIL
        app(\App\Services\DecisionLogService::class)->logFullDecision(
            $character, 
            $event,
            $choiceIndex, 
            $beforeLifeStats, 
            $afterLifeStats, 
            $choiceText, 
            $outcomeType
        );
        
        return $rawEffects;
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
     * Calculate MBTI based on character stats, event type, choice, and outcome
     * Uses cumulative scoring for real life simulation
     */
    public function calculateMBTI(array $characterStats, string $eventType, string $choiceIndex, string $outcomeType): string
    {
        $mbtiScores = [
            'E' => 50, 'I' => 50,
            'N' => 50, 'S' => 50,
            'T' => 50, 'F' => 50,
            'J' => 50, 'P' => 50
        ];

        $choiceIdx = is_numeric($choiceIndex) ? (int) $choiceIndex : 0;

        // E/I: Social stat directly impacts (higher = more extroverted)
        $social = isset($characterStats['Social']) ? (int)$characterStats['Social'] : 50;
        $mbtiScores['E'] = 30 + ($social * 0.4);
        $mbtiScores['I'] = 70 - ($social * 0.4);
        
        if (in_array($eventType, ['cultural', 'social', 'milestone'])) {
            $mbtiScores['E'] += 8;
        } elseif (in_array($eventType, ['profession', 'career', 'ageSpecific'])) {
            $mbtiScores['I'] += 8;
        }

        // N/S: Creativity stat directly impacts
        $creativity = isset($characterStats['Creativity']) ? (int)$characterStats['Creativity'] : 50;
        $mbtiScores['N'] = 30 + ($creativity * 0.4);
        $mbtiScores['S'] = 70 - ($creativity * 0.4);
        
        $intelligence = isset($characterStats['Intelligence']) ? (int)$characterStats['Intelligence'] : 50;
        $mbtiScores['N'] += ($intelligence - 50) * 0.1;

        // T/F: Empathy stat directly impacts
        $empathy = isset($characterStats['Empathy']) ? (int)$characterStats['Empathy'] : 50;
        $mbtiScores['F'] = 30 + ($empathy * 0.4);
        $mbtiScores['T'] = 70 - ($empathy * 0.4);
        
        $charisma = isset($characterStats['Charisma']) ? (int)$characterStats['Charisma'] : 50;
        $mbtiScores['F'] += ($charisma - 50) * 0.1;

        // J/P: Discipline stat directly impacts
        $discipline = isset($characterStats['Discipline']) ? (int)$characterStats['Discipline'] : 50;
        $mbtiScores['J'] = 30 + ($discipline * 0.4);
        $mbtiScores['P'] = 70 - ($discipline * 0.4);

        $strength = isset($characterStats['Strength']) ? (int)$characterStats['Strength'] : 50;
        $mbtiScores['J'] += ($strength - 50) * 0.1;

        // Choice-based modifiers
        if ($choiceIdx === 0) {
            $mbtiScores['J'] += 6;
            $mbtiScores['E'] += 4;
        } elseif ($choiceIdx === 1) {
            $mbtiScores['I'] += 3;
            $mbtiScores['P'] += 3;
        } elseif ($choiceIdx === 2) {
            $mbtiScores['I'] += 6;
            $mbtiScores['P'] += 4;
        } elseif ($choiceIdx >= 3) {
            $mbtiScores['P'] += 8;
            $mbtiScores['I'] += 5;
        }

        // Outcome-based modifiers
        if (stripos($outcomeType, 'success') !== false || stripos($outcomeType, 'positive') !== false) {
            $mbtiScores['J'] += 3;
        }
        if (stripos($outcomeType, 'happy') !== false || stripos($outcomeType, 'love') !== false) {
            $mbtiScores['F'] += 4;
        }
        if (stripos($outcomeType, 'growth') !== false || stripos($outcomeType, 'learn') !== false) {
            $mbtiScores['N'] += 3;
        }

        foreach (['E', 'I', 'N', 'S', 'T', 'F', 'J', 'P'] as $trait) {
            $mbtiScores[$trait] = max(0, min(100, $mbtiScores[$trait]));
        }

        $type = '';
        $type .= $mbtiScores['E'] >= $mbtiScores['I'] ? 'E' : 'I';
        $type .= $mbtiScores['N'] >= $mbtiScores['S'] ? 'N' : 'S';
        $type .= $mbtiScores['T'] >= $mbtiScores['F'] ? 'T' : 'F';
        $type .= $mbtiScores['J'] >= $mbtiScores['P'] ? 'J' : 'P';

        Log::info('MBTI Calculated', [
            'type' => $type,
            'scores' => $mbtiScores,
            'stats' => ['Social' => $social, 'Creativity' => $creativity, 'Empathy' => $empathy, 'Discipline' => $discipline],
        ]);

        return $type;
    }
}
