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
     * Standard stat names used in the game:
     * - Health, Happiness, Wealth, Intelligence, Discipline, Reputation
     * - Strength, Creativity, Charisma, Morality, Luck, Burnout
     * - Confidence, Isolation, Ego, Addiction, Social, Empathy
     */
    private const EFFECT_STAT_ALIASES = [
        // Stats that map to Burnout
        'Stress' => ['stat' => 'Burnout', 'multiplier' => 1],
        'Fatigue' => ['stat' => 'Burnout', 'multiplier' => 1],
        
        // Stats that map to Happiness
        'Peace' => ['stat' => 'Happiness', 'multiplier' => 1],
        'Joy' => ['stat' => 'Happiness', 'multiplier' => 1],
        'Satisfaction' => ['stat' => 'Happiness', 'multiplier' => 1],
        
        // Stats that map to Morality
        'Ethics' => ['stat' => 'Morality', 'multiplier' => 1],
        // Lower corruption should improve morality, and higher corruption should hurt it.
        'Corruption' => ['stat' => 'Morality', 'multiplier' => -1],
        
        // Stats that map to Wealth
        'Finance' => ['stat' => 'Wealth', 'multiplier' => 1],
        'Financial' => ['stat' => 'Wealth', 'multiplier' => 1],
        
        // Stats that map to Intelligence
        'Knowledge' => ['stat' => 'Intelligence', 'multiplier' => 1],
        'Wisdom' => ['stat' => 'Intelligence', 'multiplier' => 1],
        
        // Stats that map to Discipline
        'Self-Control' => ['stat' => 'Discipline', 'multiplier' => 1],
        'Control' => ['stat' => 'Discipline', 'multiplier' => 1],
        
        // Stats that map to Reputation
        'Fame' => ['stat' => 'Reputation', 'multiplier' => 1],
        'Status' => ['stat' => 'Reputation', 'multiplier' => 1],
        
        // Stats that map to Strength
        'Fitness' => ['stat' => 'Strength', 'multiplier' => 1],
        'Athletics' => ['stat' => 'Strength', 'multiplier' => 1],
        
        // Stats that map to Creativity
        'Art' => ['stat' => 'Creativity', 'multiplier' => 1],
        'Imagination' => ['stat' => 'Creativity', 'multiplier' => 1],
        
        // Stats that map to Charisma
        'Social' => ['stat' => 'Charisma', 'multiplier' => 1],
        'Charm' => ['stat' => 'Charisma', 'multiplier' => 1],
    ];

    /**
     * Health condition thresholds based on health percentage
     */
    const HEALTH_CONDITIONS = [
        ['min' => 0, 'max' => 0, 'status' => 'dead'],
        ['min' => 1, 'max' => 10, 'status' => 'critical'],
        ['min' => 11, 'max' => 30, 'status' => 'sick'],
        ['min' => 31, 'max' => 49, 'status' => 'unhealthy'],
        ['min' => 50, 'max' => 69, 'status' => 'fever'],
        ['min' => 70, 'max' => 100, 'status' => 'healthy'],
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

        $effectiveStats = $this->normalizeEffectiveStats($character);
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
                'kind' => 'consequence',
                'message' => 'You declared bankruptcy. Expensive choices will be restricted until you recover.',
            ];
        }

        // Cancer: persistent condition once very unhealthy at older ages.
        if (($state['has_cancer'] ?? false) !== true && $age >= 35 && $health <= 20) {
            $state['has_cancer'] = true;
            $stateChanged = true;
            $consequences[] = [
                'type' => 'cancer',
                'kind' => 'consequence',
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
                'kind' => 'consequence',
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
                'kind' => 'consequence',
                'message' => 'Your addiction spirals out of control. Recovery options will start appearing.',
            ];
        }

        $thresholdResolution = $this->applyStatThresholdMilestones($effectiveStats, $state);
        if (!empty($thresholdResolution['events'])) {
            $consequences = array_merge($consequences, $thresholdResolution['events']);
        }
        $stateChanged = $stateChanged || ($thresholdResolution['state_changed'] ?? false);
        $statsChanged = $statsChanged || ($thresholdResolution['stats_changed'] ?? false);

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
        $effectiveStats = $this->normalizeEffectiveStats($character);
        $state = is_array($character->character_state) ? $character->character_state : [];

        $changed = false;

        if (($state['has_cancer'] ?? false) === true) {
            $delta = 3 * $daysAdvanced;
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $delta);
            $consequences[] = [
                'type' => 'cancer_progression',
                'kind' => 'consequence',
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
                'kind' => 'reward',
                'message' => "Your study routine compounds over time (+{$daysAdvanced} Intelligence, +{$daysAdvanced} Discipline).",
            ];
            $changed = true;
        }

        if (($flags['burnout_cycle'] ?? false) === true) {
            $burnoutDelta = 1 * $daysAdvanced;
            $healthDelta = 1 * $daysAdvanced;
            $effectiveStats['Burnout'] = min(100, (int) ($effectiveStats['Burnout'] ?? 0) + $burnoutDelta);
            $effectiveStats['Health'] = max(0, (int) ($effectiveStats['Health'] ?? 50) - $healthDelta);
            $consequences[] = [
                'type' => 'burnout_cycle',
                'kind' => 'consequence',
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
                'kind' => 'consequence',
                'message' => "Unresolved distance keeps weighing on you (+{$isolationDelta} Isolation, -{$happinessDelta} Happiness).",
            ];
            $changed = true;
        }

        if (($flags['scandal_marked'] ?? false) === true) {
            $repDelta = 1 * $daysAdvanced;
            $effectiveStats['Reputation'] = max(0, (int) ($effectiveStats['Reputation'] ?? 50) - $repDelta);
            $consequences[] = [
                'type' => 'scandal_marked',
                'kind' => 'consequence',
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
                'kind' => 'consequence',
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
                'kind' => 'consequence',
                'message' => "Financial pressure keeps compounding (+{$debtDelta} Debt, -{$daysAdvanced} Happiness).",
            ];
            $changed = true;
        }

        if (($flags['recovery_arc'] ?? false) === true && ($flags['burnout_cycle'] ?? false) !== true) {
            $effectiveStats['Burnout'] = max(0, (int) ($effectiveStats['Burnout'] ?? 0) - $daysAdvanced);
            $effectiveStats['Health'] = min(100, (int) ($effectiveStats['Health'] ?? 50) + $daysAdvanced);
            $consequences[] = [
                'type' => 'recovery_arc',
                'kind' => 'reward',
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

    private function normalizeEffectiveStats(Character $character): array
    {
        $baseStats = is_array($character->stats) ? $character->stats : [];
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];

        $stats = array_merge($hiddenStats, $baseStats, $effectiveStats);

        $stats['Health'] = (int) ($stats['Health'] ?? $character->health ?? 78);
        $stats['Happiness'] = (int) ($stats['Happiness'] ?? $character->happiness ?? 72);
        $stats['Wealth'] = (int) ($stats['Wealth'] ?? $stats['Finance'] ?? $character->finance ?? 20);
        $stats['Finance'] = (int) ($stats['Finance'] ?? $stats['Wealth']);

        foreach ($stats as $stat => $value) {
            $stats[$stat] = max(0, min(100, (int) $value));
        }

        return $stats;
    }

    private function applyStatThresholdMilestones(array &$effectiveStats, array &$state): array
    {
        $events = [];
        $statsChanged = false;
        $stateChanged = false;
        $snapshot = $effectiveStats;

        $milestones = is_array($state['stat_threshold_milestones'] ?? null)
            ? $state['stat_threshold_milestones']
            : [];
        $tracks = is_array($state['stat_threshold_tracks'] ?? null)
            ? $state['stat_threshold_tracks']
            : [];

        foreach ($this->getStatThresholdRules() as $stat => $rules) {
            $value = (int) ($snapshot[$stat] ?? 0);

            foreach ($rules as $rule) {
                $flag = (string) ($rule['flag'] ?? '');
                if ($flag === '' || !empty($milestones[$flag])) {
                    continue;
                }

                $requiredTrack = $rule['requires_track'] ?? null;
                if ($requiredTrack && empty($tracks[$requiredTrack])) {
                    continue;
                }

                $min = $rule['min'] ?? null;
                $max = $rule['max'] ?? null;

                if ($min !== null && $value < (int) $min) {
                    continue;
                }
                if ($max !== null && $value > (int) $max) {
                    continue;
                }

                foreach (($rule['effects'] ?? []) as $affectedStat => $delta) {
                    $applied = $this->applyStatDelta($effectiveStats, (string) $affectedStat, (int) $delta);
                    $statsChanged = $statsChanged || ($applied !== 0);
                }

                foreach (($rule['state_updates'] ?? []) as $key => $valueUpdate) {
                    if (($state[$key] ?? null) !== $valueUpdate) {
                        $state[$key] = $valueUpdate;
                        $stateChanged = true;
                    }
                }

                if (!empty($rule['sets_track'])) {
                    $trackKey = (string) $rule['sets_track'];
                    if (empty($tracks[$trackKey])) {
                        $tracks[$trackKey] = true;
                        $stateChanged = true;
                    }
                }

                if (!empty($rule['clears_track'])) {
                    $trackKey = (string) $rule['clears_track'];
                    if (isset($tracks[$trackKey])) {
                        unset($tracks[$trackKey]);
                        $stateChanged = true;
                    }
                }

                $milestones[$flag] = true;
                $stateChanged = true;

                $events[] = [
                    'type' => $rule['type'] ?? strtolower($flag),
                    'kind' => $rule['kind'] ?? 'consequence',
                    'stat' => $stat,
                    'message' => (string) ($rule['message'] ?? "{$stat} shifted dramatically."),
                ];
            }
        }

        if ($stateChanged) {
            $state['stat_threshold_milestones'] = $milestones;
            $state['stat_threshold_tracks'] = $tracks;
        }

        return [
            'events' => $events,
            'state_changed' => $stateChanged,
            'stats_changed' => $statsChanged,
        ];
    }

    private function applyStatDelta(array &$effectiveStats, string $stat, int $delta): int
    {
        $before = (int) ($effectiveStats[$stat] ?? 0);
        $after = max(0, min(100, $before + $delta));
        $effectiveStats[$stat] = $after;

        if ($stat === 'Wealth') {
            $effectiveStats['Finance'] = $after;
        } elseif ($stat === 'Finance') {
            $effectiveStats['Wealth'] = $after;
        }

        return $after - $before;
    }

    private function getStatThresholdRules(): array
    {
        return [
            'Health' => [
                ['flag' => 'health_collapse', 'kind' => 'consequence', 'type' => 'health_collapse', 'max' => 10, 'message' => 'Health collapsed. Daily life feels brutal until you recover.', 'effects' => ['Happiness' => -5, 'Burnout' => 5], 'sets_track' => 'health_recovery'],
                ['flag' => 'health_mastery', 'kind' => 'reward', 'type' => 'health_mastery', 'min' => 100, 'message' => 'Peak health gives you fresh energy and emotional stability.', 'effects' => ['Happiness' => 5, 'Burnout' => -5]],
                ['flag' => 'health_comeback', 'kind' => 'reward', 'type' => 'health_comeback', 'min' => 80, 'requires_track' => 'health_recovery', 'clears_track' => 'health_recovery', 'message' => 'Your health bounced back in a big way.', 'effects' => ['Happiness' => 5]],
            ],
            'Happiness' => [
                ['flag' => 'happiness_collapse', 'kind' => 'consequence', 'type' => 'happiness_collapse', 'max' => 10, 'message' => 'Despair sets in, making everything feel heavier.', 'effects' => ['Health' => -5, 'Burnout' => 5], 'sets_track' => 'happiness_recovery'],
                ['flag' => 'happiness_mastery', 'kind' => 'reward', 'type' => 'happiness_mastery', 'min' => 100, 'message' => 'Pure happiness fuels resilience and lifts your health.', 'effects' => ['Health' => 5, 'Burnout' => -5]],
                ['flag' => 'happiness_comeback', 'kind' => 'reward', 'type' => 'happiness_comeback', 'min' => 80, 'requires_track' => 'happiness_recovery', 'clears_track' => 'happiness_recovery', 'message' => 'You found your joy again.', 'effects' => ['Discipline' => 5]],
            ],
            'Wealth' => [
                ['flag' => 'wealth_collapse', 'kind' => 'consequence', 'type' => 'wealth_collapse', 'max' => 10, 'message' => 'Poverty adds pressure to every decision.', 'effects' => ['Debt' => 5, 'Happiness' => -5], 'sets_track' => 'wealth_recovery'],
                ['flag' => 'wealth_mastery', 'kind' => 'reward', 'type' => 'wealth_mastery', 'min' => 100, 'message' => 'Financial security cushions your life from stress.', 'effects' => ['Debt' => -10, 'Happiness' => 5]],
                ['flag' => 'wealth_comeback', 'kind' => 'reward', 'type' => 'wealth_comeback', 'min' => 80, 'requires_track' => 'wealth_recovery', 'clears_track' => 'wealth_recovery', 'message' => 'You rebuilt your finances through persistence.', 'effects' => ['Discipline' => 5]],
            ],
            'Intelligence' => [
                ['flag' => 'intelligence_collapse', 'kind' => 'consequence', 'type' => 'intelligence_collapse', 'max' => 10, 'message' => 'Mental fog starts costing you good decisions.', 'effects' => ['Discipline' => -5], 'sets_track' => 'intelligence_recovery'],
                ['flag' => 'intelligence_mastery', 'kind' => 'reward', 'type' => 'intelligence_mastery', 'min' => 100, 'message' => 'Brilliance sharpens both discipline and creativity.', 'effects' => ['Discipline' => 5, 'Creativity' => 5]],
                ['flag' => 'intelligence_comeback', 'kind' => 'reward', 'type' => 'intelligence_comeback', 'min' => 80, 'requires_track' => 'intelligence_recovery', 'clears_track' => 'intelligence_recovery', 'message' => 'Your focus and thinking power return.', 'effects' => ['Confidence' => 5]],
            ],
            'Discipline' => [
                ['flag' => 'discipline_collapse', 'kind' => 'consequence', 'type' => 'discipline_collapse', 'max' => 10, 'message' => 'Your routines break down and stress surges.', 'effects' => ['Burnout' => 8], 'sets_track' => 'discipline_recovery'],
                ['flag' => 'discipline_mastery', 'kind' => 'reward', 'type' => 'discipline_mastery', 'min' => 100, 'message' => 'Exceptional discipline protects you from chaos.', 'effects' => ['Burnout' => -8, 'Health' => 5]],
                ['flag' => 'discipline_comeback', 'kind' => 'reward', 'type' => 'discipline_comeback', 'min' => 80, 'requires_track' => 'discipline_recovery', 'clears_track' => 'discipline_recovery', 'message' => 'You rebuilt your routines and regained control.', 'effects' => ['Happiness' => 5]],
            ],
            'Reputation' => [
                ['flag' => 'reputation_collapse', 'kind' => 'consequence', 'type' => 'reputation_collapse', 'max' => 10, 'message' => 'A damaged reputation leaves you isolated and discouraged.', 'effects' => ['Isolation' => 8, 'Happiness' => -5], 'sets_track' => 'reputation_recovery'],
                ['flag' => 'reputation_mastery', 'kind' => 'reward', 'type' => 'reputation_mastery', 'min' => 100, 'message' => 'A stellar reputation opens doors and lifts your mood.', 'effects' => ['Wealth' => 5, 'Happiness' => 5]],
                ['flag' => 'reputation_comeback', 'kind' => 'reward', 'type' => 'reputation_comeback', 'min' => 80, 'requires_track' => 'reputation_recovery', 'clears_track' => 'reputation_recovery', 'message' => 'You earned people’s trust back.', 'effects' => ['Morality' => 5]],
            ],
            'Strength' => [
                ['flag' => 'strength_collapse', 'kind' => 'consequence', 'type' => 'strength_collapse', 'max' => 10, 'message' => 'Physical weakness makes recovery much harder.', 'effects' => ['Health' => -5, 'Burnout' => 5], 'sets_track' => 'strength_recovery'],
                ['flag' => 'strength_mastery', 'kind' => 'reward', 'type' => 'strength_mastery', 'min' => 100, 'message' => 'Physical mastery keeps both body and stress in check.', 'effects' => ['Health' => 5, 'Burnout' => -5]],
                ['flag' => 'strength_comeback', 'kind' => 'reward', 'type' => 'strength_comeback', 'min' => 80, 'requires_track' => 'strength_recovery', 'clears_track' => 'strength_recovery', 'message' => 'Your body feels strong again.', 'effects' => ['Confidence' => 5]],
            ],
            'Creativity' => [
                ['flag' => 'creativity_collapse', 'kind' => 'consequence', 'type' => 'creativity_collapse', 'max' => 10, 'message' => 'Creative burnout leaves life feeling dull and flat.', 'effects' => ['Happiness' => -5], 'sets_track' => 'creativity_recovery'],
                ['flag' => 'creativity_mastery', 'kind' => 'reward', 'type' => 'creativity_mastery', 'min' => 100, 'message' => 'Creative flow inspires both joy and recognition.', 'effects' => ['Happiness' => 5, 'Reputation' => 5]],
                ['flag' => 'creativity_comeback', 'kind' => 'reward', 'type' => 'creativity_comeback', 'min' => 80, 'requires_track' => 'creativity_recovery', 'clears_track' => 'creativity_recovery', 'message' => 'Your spark returned and ideas are flowing again.', 'effects' => ['Confidence' => 5]],
            ],
            'Charisma' => [
                ['flag' => 'charisma_collapse', 'kind' => 'consequence', 'type' => 'charisma_collapse', 'max' => 10, 'message' => 'Social awkwardness pushes people away.', 'effects' => ['Isolation' => 8, 'Reputation' => -5], 'sets_track' => 'charisma_recovery'],
                ['flag' => 'charisma_mastery', 'kind' => 'reward', 'type' => 'charisma_mastery', 'min' => 100, 'message' => 'Your presence naturally draws people in.', 'effects' => ['Isolation' => -8, 'Reputation' => 5]],
                ['flag' => 'charisma_comeback', 'kind' => 'reward', 'type' => 'charisma_comeback', 'min' => 80, 'requires_track' => 'charisma_recovery', 'clears_track' => 'charisma_recovery', 'message' => 'Your confidence with people returns.', 'effects' => ['Happiness' => 5]],
            ],
            'Morality' => [
                ['flag' => 'morality_collapse', 'kind' => 'consequence', 'type' => 'morality_collapse', 'max' => 10, 'message' => 'Your values slip, and people start feeling the cost.', 'effects' => ['Reputation' => -8, 'Ego' => 5], 'sets_track' => 'morality_recovery'],
                ['flag' => 'morality_mastery', 'kind' => 'reward', 'type' => 'morality_mastery', 'min' => 100, 'message' => 'Living by your values strengthens your name and peace of mind.', 'effects' => ['Reputation' => 8, 'Happiness' => 5]],
                ['flag' => 'morality_comeback', 'kind' => 'reward', 'type' => 'morality_comeback', 'min' => 80, 'requires_track' => 'morality_recovery', 'clears_track' => 'morality_recovery', 'message' => 'You got back to the person you wanted to be.', 'effects' => ['Discipline' => 5]],
            ],
            'Luck' => [
                ['flag' => 'luck_collapse', 'kind' => 'consequence', 'type' => 'luck_collapse', 'max' => 10, 'message' => 'A run of bad luck drags down your optimism.', 'effects' => ['Happiness' => -5], 'sets_track' => 'luck_recovery'],
                ['flag' => 'luck_mastery', 'kind' => 'reward', 'type' => 'luck_mastery', 'min' => 100, 'message' => 'Fortune smiles on you and eases the strain of life.', 'effects' => ['Wealth' => 5, 'Happiness' => 5]],
                ['flag' => 'luck_comeback', 'kind' => 'reward', 'type' => 'luck_comeback', 'min' => 80, 'requires_track' => 'luck_recovery', 'clears_track' => 'luck_recovery', 'message' => 'Your luck finally turns around.', 'effects' => ['Confidence' => 5]],
            ],
            'Social' => [
                ['flag' => 'social_collapse', 'kind' => 'consequence', 'type' => 'social_collapse', 'max' => 10, 'message' => 'Your social energy is depleted and loneliness creeps in.', 'effects' => ['Isolation' => 8, 'Happiness' => -5], 'sets_track' => 'social_recovery'],
                ['flag' => 'social_mastery', 'kind' => 'reward', 'type' => 'social_mastery', 'min' => 100, 'message' => 'Strong social instincts make it easier to stay connected.', 'effects' => ['Isolation' => -8, 'Reputation' => 5]],
                ['flag' => 'social_comeback', 'kind' => 'reward', 'type' => 'social_comeback', 'min' => 80, 'requires_track' => 'social_recovery', 'clears_track' => 'social_recovery', 'message' => 'You reconnect with the world around you.', 'effects' => ['Charisma' => 5]],
            ],
            'Empathy' => [
                ['flag' => 'empathy_collapse', 'kind' => 'consequence', 'type' => 'empathy_collapse', 'max' => 10, 'message' => 'Emotional distance starts damaging your closest bonds.', 'effects' => ['Isolation' => 5, 'Reputation' => -5], 'sets_track' => 'empathy_recovery'],
                ['flag' => 'empathy_mastery', 'kind' => 'reward', 'type' => 'empathy_mastery', 'min' => 100, 'message' => 'Your empathy deepens trust and meaningful happiness.', 'effects' => ['Happiness' => 5, 'Reputation' => 5]],
                ['flag' => 'empathy_comeback', 'kind' => 'reward', 'type' => 'empathy_comeback', 'min' => 80, 'requires_track' => 'empathy_recovery', 'clears_track' => 'empathy_recovery', 'message' => 'You open back up to people again.', 'effects' => ['Morality' => 5]],
            ],
            'Confidence' => [
                ['flag' => 'confidence_collapse', 'kind' => 'consequence', 'type' => 'confidence_collapse', 'max' => 10, 'message' => 'Self-doubt starts shrinking your options.', 'effects' => ['Charisma' => -5, 'Discipline' => -5], 'sets_track' => 'confidence_recovery'],
                ['flag' => 'confidence_mastery', 'kind' => 'reward', 'type' => 'confidence_mastery', 'min' => 100, 'message' => 'Confidence radiates into your presence and follow-through.', 'effects' => ['Charisma' => 5, 'Discipline' => 5]],
                ['flag' => 'confidence_comeback', 'kind' => 'reward', 'type' => 'confidence_comeback', 'min' => 80, 'requires_track' => 'confidence_recovery', 'clears_track' => 'confidence_recovery', 'message' => 'You believe in yourself again.', 'effects' => ['Happiness' => 5]],
            ],
            'Burnout' => [
                ['flag' => 'burnout_crisis', 'kind' => 'consequence', 'type' => 'burnout_crisis', 'min' => 90, 'message' => 'Burnout reaches crisis level and starts damaging the rest of your life.', 'effects' => ['Health' => -8, 'Happiness' => -5], 'sets_track' => 'burnout_recovery'],
                ['flag' => 'burnout_recovery', 'kind' => 'reward', 'type' => 'burnout_recovery', 'max' => 20, 'requires_track' => 'burnout_recovery', 'clears_track' => 'burnout_recovery', 'message' => 'You recovered from deep burnout and feel human again.', 'effects' => ['Health' => 5, 'Happiness' => 5]],
            ],
            'Isolation' => [
                ['flag' => 'isolation_crisis', 'kind' => 'consequence', 'type' => 'isolation_crisis', 'min' => 90, 'message' => 'Extreme isolation starts crushing your emotional stability.', 'effects' => ['Happiness' => -10, 'Charisma' => -5], 'sets_track' => 'isolation_recovery'],
                ['flag' => 'isolation_recovery', 'kind' => 'reward', 'type' => 'isolation_recovery', 'max' => 20, 'requires_track' => 'isolation_recovery', 'clears_track' => 'isolation_recovery', 'message' => 'You broke out of isolation and started reconnecting.', 'effects' => ['Happiness' => 5, 'Charisma' => 5]],
            ],
            'Debt' => [
                ['flag' => 'debt_crisis', 'kind' => 'consequence', 'type' => 'debt_crisis', 'min' => 90, 'message' => 'Debt becomes a constant pressure on your mental state and reputation.', 'effects' => ['Happiness' => -10, 'Reputation' => -5], 'sets_track' => 'debt_recovery'],
                ['flag' => 'debt_recovery', 'kind' => 'reward', 'type' => 'debt_recovery', 'max' => 20, 'requires_track' => 'debt_recovery', 'clears_track' => 'debt_recovery', 'message' => 'You clawed your way back from crushing debt.', 'effects' => ['Happiness' => 5, 'Discipline' => 5]],
            ],
            'Addiction' => [
                ['flag' => 'addiction_crisis', 'kind' => 'consequence', 'type' => 'addiction_crisis', 'min' => 85, 'message' => 'Addiction takes a brutal toll on your health and mood.', 'effects' => ['Health' => -10, 'Happiness' => -5], 'sets_track' => 'addiction_recovery'],
                ['flag' => 'addiction_recovery', 'kind' => 'reward', 'type' => 'addiction_recovery', 'max' => 20, 'requires_track' => 'addiction_recovery', 'clears_track' => 'addiction_recovery', 'message' => 'You are finally pulling away from addiction.', 'effects' => ['Health' => 5, 'Happiness' => 5]],
            ],
            'Ego' => [
                ['flag' => 'ego_crisis', 'kind' => 'consequence', 'type' => 'ego_crisis', 'min' => 90, 'message' => 'Your ego begins damaging trust and pushing people away.', 'effects' => ['Reputation' => -10, 'Isolation' => 5], 'sets_track' => 'ego_recovery'],
                ['flag' => 'ego_recovery', 'kind' => 'reward', 'type' => 'ego_recovery', 'max' => 20, 'requires_track' => 'ego_recovery', 'clears_track' => 'ego_recovery', 'message' => 'Humility steadies your relationships and reputation.', 'effects' => ['Morality' => 5, 'Reputation' => 5]],
            ],
        ];
    }

    /**
     * Event categories for branching
     */
    const CATEGORIES = [
        'education_elementary' => ['age_group' => 'child', 'next' => 'education_high_school'],
        'education_high_school' => ['age_group' => 'teenager', 'next' => 'education_college'],
        'education_college' => ['age_group' => 'adult', 'next' => null],
        'career_start' => ['age_group' => 'adult', 'next' => 'career_advancement'],
        'career_advancement' => ['age_group' => 'adult', 'next' => 'career_master'],
        'career_master' => ['age_group' => 'adult', 'next' => null],
        'family_relationship' => ['age_group' => 'teenager', 'next' => 'family_marriage'],
        'family_marriage' => ['age_group' => 'adult', 'next' => 'family_parenthood'],
        'family_parenthood' => ['age_group' => 'adult', 'next' => null],
        'health_crisis' => ['age_group' => 'adult', 'next' => 'health_recovery'],
        'health_recovery' => ['age_group' => 'adult', 'next' => null],
        'social_friendship' => ['age_group' => 'child', 'next' => 'social_romantic'],
        'social_romantic' => ['age_group' => 'teenager', 'next' => 'social_relationship'],
        'social_relationship' => ['age_group' => 'adult', 'next' => null],
        'skill_learning' => ['age_group' => 'child', 'next' => 'skill_development'],
        'skill_development' => ['age_group' => 'teenager', 'next' => 'skill_mastery'],
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
            'healthy' => ['sick' => 0.07],
            'sick' => ['healthy' => 0.2],
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
        
        if (isset($state['health_condition']) && $state['health_condition'] === 'sick') {
            $query->where('title', 'like', '%Health%')->orWhere('title', 'like', '%hospital%');
        }
        
        if (isset($state['relationship_status']) && $state['relationship_status'] === 'married') {
            $query->where('title', 'like', '%family%')->orWhere('title', 'like', '%marriage%');
        }
        
        $shownIds = $this->extractShownIdsByType($character->shown_event_ids ?? [], 'daily');
        if (!empty($shownIds)) {
            $query->whereNotIn('id', $shownIds);
        }
        
        $events = $query->get();
        
        // Filter out events that require specific choice outcomes not yet made
        return $events->filter(function ($event) use ($character) {
            // If event has no required outcome, it's available
            if (empty($event->required_choice_outcome)) {
                return true;
            }
            
            // Check if character has made the required choice outcome
            $choiceHistory = $character->choice_history ?? [];
            foreach ($choiceHistory as $choice) {
                if (($choice['outcome'] ?? '') === $event->required_choice_outcome) {
                    return true;
                }
            }
            
            return false;
        });
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
     * Advance FSM state based on event outcome (FIXED)
     * Now preserves chain-related data AND decision_profile (for AdaptiveNarrative flags)
     */
    public function advanceState(Character $character, string $eventType, string $outcomeType): void
    {
        $state = $character->character_state ?? [];
        $ageGroup = (string) ($character->age_group ?? 'child');
        $normalizedLifeStage = match ($ageGroup) {
            'teen', 'teenager', 'adolescent' => 'teenager',
            'adult' => 'adult',
            'old', 'elder', 'elderly' => 'old',
            default => 'child',
        };
        $state['life_stage'] = $normalizedLifeStage;

        if (in_array($normalizedLifeStage, ['child', 'teenager'], true) && empty($character->profession)) {
            $state['profession_state'] = 'unemployed';
        }
        
        // Preserve chain-related data that shouldn't be overwritten by FSM transitions
        $preservedData = [
            'chain_progress' => $state['chain_progress'] ?? [],
            'chain_outcomes' => $state['chain_outcomes'] ?? [],
            'completed_chain_steps' => $state['completed_chain_steps'] ?? [],
            'decision_profile' => $state['decision_profile'] ?? [], // FIX: Preserve AdaptiveNarrative flags
        ];
        
        foreach (self::STATE_TRANSITIONS as $stateType => $transitions) {
            if ($stateType === 'life_stage') {
                continue;
            }

            // Relationship status should only change via explicit event choices (e.g. `relationship_status_change`).
            // Auto-advancing this via FSM caused "Single → Dating → Married → Divorced" jumps on unrelated events.
            if ($stateType === 'relationship_status') {
                continue;
            }

            if ($stateType === 'profession_state' && (empty($character->profession) || in_array($normalizedLifeStage, ['child', 'teenager'], true))) {
                continue;
            }

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
        
        // Restore all preserved data
        $state['chain_progress'] = $preservedData['chain_progress'];
        $state['chain_outcomes'] = $preservedData['chain_outcomes'];
        $state['completed_chain_steps'] = $preservedData['completed_chain_steps'];
        $state['decision_profile'] = $preservedData['decision_profile'];
        
        $character->character_state = $state;

        // Keep legacy columns in sync (used by achievements/logging/back-compat checks).
        if (isset($state['relationship_status'])) {
            $character->relationship_status = $state['relationship_status'];
        }
        if (isset($state['profession_state'])) {
            $character->career_level = $state['profession_state'];
        }
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
     * Now handles complex strings with parenthetical explanations like:
     * "+15 Health, +10 Wealth (prevention saves money), -5 Wealth (checkup cost)"
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
            // Updated regex to handle complex strings with parenthetical explanations
            // Matches: +15 Health, +10 Wealth (prevention saves money), -5 Wealth (checkup cost)
            // Captures: sign, number, stat name (ignoring anything in parentheses after)
            if (preg_match('/([+-]\d+)\s+(\w+)(?:\s*\([^)]*\))?/', $part, $matches)) {
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
        $health = (int) ($effectiveStats['Health'] ?? $character->health ?? 78);

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
            'health' => $character->health ?? 78,
            'happiness' => $character->happiness ?? 72,
            'finance' => $character->finance ?? 20,
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
        $logResult = app(\App\Services\DecisionLogService::class)->logFullDecision(
            $character, 
            $event,
            $choiceIndex, 
            $beforeLifeStats, 
            $afterLifeStats, 
            $choiceText, 
            $outcomeType
        );
        
        // Return both raw effects and random outcome info
        return [
            'effects' => $rawEffects,
            'random_outcome' => $logResult['random_outcome'] ?? null,
        ];
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
     * Check event prerequisites (FIXED - now validates sequential chain progression)
     */
    public function checkEventPrerequisites($event, Character $character): bool
    {
        // `completed_event_chains` may be stored as legacy string IDs or as structured records.
        // Always normalize to a list of chain IDs.
        $completedChains = method_exists($character, 'getCompletedChainIds')
            ? $character->getCompletedChainIds()
            : ($character->completed_event_chains ?? []);
        $characterState = $character->character_state ?? [];
        $chainOutcomes = $characterState['chain_outcomes'] ?? [];
        
        // Normalize age_group to match seeder conventions
        $ageGroup = $character->age_group ?? $characterState['life_stage'] ?? 'adult';
        $ageGroup = match($ageGroup) {
            'teen' => 'teenager',
            default => $ageGroup
        };
        
        // FIX: Enhanced parent_category check with chain progression validation (including branch points)
        if (!empty($event->parent_category)) {
            // Check if the parent category was started (step 1 completed)
            if (!in_array($event->parent_category, $completedChains)) {
                return false;
            }
            
            // If event has a chain_order > 1, verify sequential progression (handle floats for branch points)
            $eventChainOrder = (float) ($event->chain_order ?? 1);
            $eventChainOrderInt = (int) floor($eventChainOrder);
            
            if ($eventChainOrder > 1) {
                $completedSteps = $characterState['completed_chain_steps'] ?? [];
                $parentCompletedSteps = $completedSteps[$event->parent_category] ?? [];
                
                // Check if this is a branch point (e.g., 4.1, 4.2)
                $isBranchPoint = fmod($eventChainOrder, 1) > 0;
                
                if ($isBranchPoint) {
                    // For branch points: base step must be completed OR specific branch must be completed
                    $baseCompleted = in_array($eventChainOrderInt, $parentCompletedSteps);
                    $branchCompleted = in_array($eventChainOrder, $parentCompletedSteps);
                    
                    if (!$baseCompleted && !$branchCompleted) {
                        Log::info('Parent chain prerequisite not met (branch point)', [
                            'event' => $event->event_choice ?? $event->title ?? 'unknown',
                            'parent_category' => $event->parent_category,
                            'required_step' => $eventChainOrder,
                            'completed_steps' => $parentCompletedSteps,
                        ]);
                        return false;
                    }
                } else {
                    // For regular steps: verify ALL previous steps are completed
                    for ($i = 1; $i <= $eventChainOrderInt; $i++) {
                        if (!in_array($i, $parentCompletedSteps)) {
                            Log::info('Parent chain prerequisite not met', [
                                'event' => $event->event_choice ?? $event->title ?? 'unknown',
                                'parent_category' => $event->parent_category,
                                'required_step' => $i,
                                'completed_steps' => $parentCompletedSteps,
                            ]);
                            return false;
                        }
                    }
                }
            }

            // Check if previous chain had required outcome (using character_state)
            if (!empty($event->required_choice_outcome)) {
                $actualOutcome = $chainOutcomes[$event->parent_category] ?? null;

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

        // Check the conditions JSON field
        if (!$this->checkEventConditions($event, $character)) {
            return false;
        }

        return true;
    }

    /**
     * Evaluate the conditions JSON field for an event
     * Conditions can include: age_group, has_skill, min_wealth, relationship_status,
     * min_burnout, profession_state, health_status, min_health
     */
    public function checkEventConditions($event, Character $character): bool
    {
        $conditions = $event->conditions ?? null;
        
        // If no conditions, event is available to all
        if (empty($conditions)) {
            return true;
        }
        
        // Get character data
        $characterState = $character->character_state ?? [];
        $ageGroup = $character->age_group ?? $characterState['life_stage'] ?? 'adult';
        
        // Normalize age_group to match seeder conventions (teen -> teenager)
        $ageGroup = match($ageGroup) {
            'teen' => 'teenager',
            default => $ageGroup
        };

        // Prefer JSON state, but fall back to columns when JSON is still at its defaults.
        $relationshipStatus = $characterState['relationship_status'] ?? null;
        if (empty($relationshipStatus) || $relationshipStatus === 'single') {
            $relationshipStatus = $character->relationship_status ?? ($relationshipStatus ?: 'single');
        }

        $professionState = $characterState['profession_state'] ?? null;
        if (empty($professionState) || ($professionState === 'unemployed' && !empty($character->profession))) {
            $professionState = $character->career_level ?? ($professionState ?: 'unemployed');
        }
        $healthCondition = $characterState['health_condition'] ?? $this->getHealthStatus($character->health ?? 78);
        $wealth = $character->finance ?? $character->wealth ?? 20;
        $stats = $character->effective_stats ?? $character->stats ?? [];
        $burnout = $stats['burnout'] ?? 0;
        $health = $character->health ?? 78;
        
        // Get character's skills
        $skills = [];
        if ($character->relationLoaded('skills')) {
            $skills = $character->skills->pluck('name')->toArray();
        } else {
            // Try to get from relationship
            try {
                $skills = $character->skills()->pluck('name')->toArray();
            } catch (\Exception $e) {
                $skills = $character->skills ?? [];
            }
        }
        
        // Get character's talents
        $talents = [];
        if ($character->relationLoaded('talents')) {
            $talents = $character->talents->pluck('name')->toArray();
        } else {
            // Try to get from relationship
            try {
                $talents = $character->talents()->pluck('name')->toArray();
            } catch (\Exception $e) {
                $talents = $character->talents ?? [];
            }
        }
        
        // Check each condition
        foreach ($conditions as $conditionKey => $conditionValue) {
            switch ($conditionKey) {
                case 'age_group':
                    // FIXED: Normalize both character age_group AND condition value for consistency
                    $validAgeGroups = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    
                    // Normalize condition values too (in case seeder uses 'teen')
                    $normalizedValidGroups = array_map(function($g) {
                        return match($g) {
                            'teen' => 'teenager',
                            default => $g
                        };
                    }, $validAgeGroups);
                    
                    if (!in_array($ageGroup, $normalizedValidGroups)) {
                        return false;
                    }
                    break;
                    
                case 'has_skill':
                    // Character must have this skill
                    if (!in_array($conditionValue, $skills)) {
                        return false;
                    }
                    break;
                    
                case 'min_wealth':
                    // Character must have at least this much wealth
                    if ($wealth < $conditionValue) {
                        return false;
                    }
                    break;
                    
                case 'relationship_status':
                    // Character must have this relationship status
                    $validStatuses = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    if (!in_array($relationshipStatus, $validStatuses)) {
                        return false;
                    }
                    break;
                    
                case 'min_burnout':
                    // Character must have at least this burnout level
                    if ($burnout < $conditionValue) {
                        return false;
                    }
                    break;
                    
                case 'profession_state':
                    // Character must have this profession state
                    $validStates = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    if (!in_array($professionState, $validStates)) {
                        return false;
                    }
                    break;
                    
                case 'health_status':
                    // Character must have this health status
                    $validStatuses = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    if (!in_array($healthCondition, $validStatuses)) {
                        return false;
                    }
                    break;
                    
                case 'min_health':
                    // Character must have at least this health percentage
                    if ($health < $conditionValue) {
                        return false;
                    }
                    break;
                    
                case 'lacks_skill':
                    // Character must NOT have this skill
                    $forbiddenSkills = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    foreach ($forbiddenSkills as $forbiddenSkill) {
                        if (in_array($forbiddenSkill, $skills, true)) {
                            return false;
                        }
                    }
                    break;
                    
                case 'has_talent':
                    // Character must have this talent
                    $requiredTalents = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    foreach ($requiredTalents as $requiredTalent) {
                        if (!in_array($requiredTalent, $talents, true)) {
                            return false;
                        }
                    }
                    break;
                    
                case 'lacks_talent':
                    // Character must NOT have this talent
                    $forbiddenTalents = is_array($conditionValue) ? $conditionValue : [$conditionValue];
                    foreach ($forbiddenTalents as $forbiddenTalent) {
                        if (in_array($forbiddenTalent, $talents, true)) {
                            return false;
                        }
                    }
                    break;
                    
                case 'has_completed_chain':
                    // Character must have completed a specific chain step (e.g., 'education_1')
                    // Format: 'category_stepNumber' like 'education_1', 'career_2', 'education_4.1'
                    // FIXED: Now properly validates sequential progression AND handles branch points
                    $requiredChain = $conditionValue; // e.g., 'education_1' or 'education_4.1'
                    
                    // Parse the required chain (e.g., 'education_1' -> category: 'education', step: 1)
                    if (is_string($requiredChain) && strpos($requiredChain, '_') !== false) {
                        // Handle decimal chain orders like '4.1', '4.2' (branch points)
                        $parts = explode('_', $requiredChain, 2);
                        $requiredCategory = $parts[0];
                        $requiredStepRaw = $parts[1] ?? '1';
                        
                        // Handle decimal format (e.g., '4.1' -> base: 4, branch: 1)
                        // Branch points (4.1, 4.2) mean "after completing step 4, choose branch 1 or 2"
                        $isBranchPoint = strpos($requiredStepRaw, '.') !== false;
                        $requiredStep = (int) floor((float) $requiredStepRaw);
                        
                        // Get all completed steps for this category
                        $completedSteps = $character->character_state['completed_chain_steps'] ?? [];
                        $categoryCompletedSteps = $completedSteps[$requiredCategory] ?? [];
                        
                        if ($isBranchPoint) {
                            // For branch points (e.g., education_4.1), check:
                            // 1. Base step (4) is completed
                            // 2. Either the specific branch OR the base step is completed
                            // Branch points are alternatives - completing any branch after base is valid
                            $baseStepCompleted = in_array($requiredStep, $categoryCompletedSteps);
                            $specificBranchCompleted = in_array((float)$requiredStepRaw, array_map('floatval', $categoryCompletedSteps)) 
                                || in_array((int)$requiredStepRaw, $categoryCompletedSteps);
                            
                            // If base step is done OR the specific branch is done, allow access
                            // This supports branching: after step 4, you can do branch 4.1 OR 4.2
                            if (!$baseStepCompleted && !$specificBranchCompleted) {
                                Log::info('Branch point prerequisite not met', [
                                    'category' => $requiredCategory,
                                    'required_step' => $requiredStepRaw,
                                    'completed_steps' => $categoryCompletedSteps,
                                ]);
                                return false;
                            }
                        } else {
                            // For regular steps, verify ALL previous steps (1 to requiredStep) are completed sequentially
                            for ($i = 1; $i <= $requiredStep; $i++) {
                                if (!in_array($i, $categoryCompletedSteps)) {
                                    Log::info('Chain prerequisite not met', [
                                        'category' => $requiredCategory,
                                        'required_step' => $requiredStep,
                                        'completed_steps' => $categoryCompletedSteps,
                                        'missing_step' => $i,
                                    ]);
                                    return false;
                                }
                            }
                        }
                    } else {
                        // Simple format: check if category is in completed_chains
                        // For step 1 or no step specified, just check if category started
                        $completedChains = $character->completed_event_chains ?? [];
                        if (!in_array($requiredChain, $completedChains)) {
                            return false;
                        }
                    }
                    break;
                    
                case 'chain_order':
                    // Character must be at or past a certain chain order in a category
                    $chainProgress = $character->character_state['chain_progress'] ?? [];
                    $requiredCategory = $conditionValue['category'] ?? null;
                    $requiredOrder = $conditionValue['order'] ?? 0;
                    
                    if ($requiredCategory && isset($chainProgress[$requiredCategory])) {
                        if ($chainProgress[$requiredCategory] < $requiredOrder) {
                            return false;
                        }
                    }
                    break;
                    
                // Unknown condition keys are ignored
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
        app(NarrativeService::class)->updateNarrativePath($character, $eventCategory, $outcomeType);
    }

    /**
     * Complete event chain (FIXED)
     * Properly tracks chain progression with step-by-step tracking
     * Now also preserves decision_profile for AdaptiveNarrative flags
     * Handles branch points (e.g., 4.1, 4.2) as floats
     */
    public function completeEventChain(Character $character, string $category, string $outcomeType, int $chainOrder = 1): void
    {
        $characterState = $character->character_state ?? [];
        $preservedDecisionProfile = $characterState['decision_profile'] ?? [];
        
        // Get chain_id from event (use category as fallback)
        $chainId = $category;
        
        // FIX #1: Track chain progress properly - store highest step completed (as float for branch points)
        $chainProgress = $characterState['chain_progress'] ?? [];
        $currentMaxStep = $chainProgress[$category] ?? 0;
        
        // Handle decimal chain orders (e.g., 4.1, 4.2) - convert to float
        $chainOrderFloat = (float) $chainOrder;
        
        // Only update if this step is higher than what we have
        if ($chainOrderFloat > $currentMaxStep) {
            $chainProgress[$category] = $chainOrderFloat;
        }
        $characterState['chain_progress'] = $chainProgress;
        
        // FIX #2: Track all completed steps in an array for granular tracking (store as floats)
        $completedSteps = $characterState['completed_chain_steps'] ?? [];
        if (!isset($completedSteps[$category])) {
            $completedSteps[$category] = [];
        }
        // Store as float to preserve branch point info (4.1 vs 4.2)
        if (!in_array($chainOrderFloat, $completedSteps[$category])) {
            $completedSteps[$category][] = $chainOrderFloat;
            sort($completedSteps[$category]); // Keep sorted for sequential checks
        }
        $characterState['completed_chain_steps'] = $completedSteps;
        
        // FIX #3: Only add to completed_chains if this is the first step (step 1)
        // This ensures sequential progression is enforced via chain_progress
        $character->updateChainProgress($chainId, (int) $chainOrderFloat, [
            'category' => $category,
            'last_outcome' => $outcomeType,
        ]);

        if ($chainOrder === 1 && !$character->hasCompletedChain($chainId)) {
            $character->recordChainCompletion($chainId, [
                'category' => $category,
                'progress' => (int) $chainOrderFloat,
                'last_outcome' => $outcomeType,
            ]);
        }
        
        // NEW: Track pending chains for cross-age continuity
        // When chain step > 1, add to pending_chains if not already there
        if ($chainOrder > 1) {
            $pendingChain = $character->getPendingChain($chainId);
            if (!$pendingChain) {
                // Start tracking this chain in pending (cross-age)
                $character->startChainProgress($chainId, $chainOrder, [
                    'category' => $category,
                    'last_outcome' => $outcomeType,
                ]);
            } else {
                // Update progress
                $character->updatePendingChainProgress($chainId, $chainOrder, [
                    'category' => $category,
                    'last_outcome' => $outcomeType,
                ]);
            }
        } else if ($chainOrder === 1) {
            // First step - start tracking
            $pendingChain = $character->getPendingChain($chainId);
            if (!$pendingChain) {
                $character->startChainProgress($chainId, 1, [
                    'category' => $category,
                    'last_outcome' => $outcomeType,
                ]);
            } else {
                $character->updatePendingChainProgress($chainId, 1, [
                    'category' => $category,
                    'last_outcome' => $outcomeType,
                ]);
            }
        }
        
        // Store outcome in character_state JSON (chain_outcomes)
        $chainOutcomes = $characterState['chain_outcomes'] ?? [];
        $chainOutcomes[$category] = $outcomeType;
        $characterState['chain_outcomes'] = $chainOutcomes;
        
        $characterState['decision_profile'] = $preservedDecisionProfile ?? [];
        
        $character->character_state = $characterState;
        
        // Log chain completion for debugging
        Log::info('Chain completed', [
            'character_id' => $character->id,
            'category' => $category,
            'chain_order' => $chainOrder,
            'chain_order_float' => $chainOrderFloat,
            'current_progress' => $chainProgress[$category] ?? 0,
            'completed_steps' => $completedSteps[$category] ?? [],
        ]);
        
        // Map simple category to detailed category based on age group for cross-category triggers
        $detailedCategory = $this->mapToDetailedCategory($category, $character->age_group ?? 'adult');
        
        if (isset(self::CATEGORIES[$detailedCategory])) {
            $nextCategory = self::CATEGORIES[$detailedCategory]['next'];
            if ($nextCategory) {
                $activePaths = $character->active_event_paths ?? [];
                // Add both the detailed and simple category to active paths
                if (!in_array($nextCategory, $activePaths)) {
                    $activePaths[] = $nextCategory;
                    $character->active_event_paths = $activePaths;
                }
                // Also add the simple category name for narrative weighting
                if (!in_array($category, $activePaths)) {
                    $activePaths[] = $category;
                    $character->active_event_paths = $activePaths;
                }
            }
        } else {
            // Fallback: if no detailed category match, still add simple category to active paths
            $activePaths = $character->active_event_paths ?? [];
            if (!in_array($category, $activePaths)) {
                $activePaths[] = $category;
                $character->active_event_paths = $activePaths;
            }
        }
        
        $character->save();
    }

    /**
     * Map simple category to detailed category based on age group
     */
    private function mapToDetailedCategory(string $category, string $ageGroup): string
    {
        $mapping = [
            'education' => [
                'child' => 'education_elementary',
                'teenager' => 'education_high_school', 
                'adult' => 'education_college',
                'old' => 'education_college',
            ],
            'career' => [
                'child' => 'career_start',
                'teenager' => 'career_start',
                'adult' => 'career_start',
                'old' => 'career_advancement',
            ],
            'family' => [
                'child' => 'family_relationship',
                'teenager' => 'family_relationship',
                'adult' => 'family_marriage',
                'old' => 'family_parenthood',
            ],
            'health' => [
                'child' => 'health_crisis',
                'teenager' => 'health_crisis',
                'adult' => 'health_crisis',
                'old' => 'health_recovery',
            ],
            'social' => [
                'child' => 'social_friendship',
                'teenager' => 'social_romantic',
                'adult' => 'social_relationship',
                'old' => 'social_relationship',
            ],
            'wealth' => [
                'child' => 'career_start',
                'teenager' => 'career_start',
                'adult' => 'career_advancement',
                'old' => 'career_master',
            ],
            'skill' => [
                'child' => 'skill_learning',
                'teenager' => 'skill_development',
                'adult' => 'skill_mastery',
                'old' => 'skill_mastery',
            ],
        ];
        
        return $mapping[$category][$ageGroup] ?? $category;
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

    /**
     * Process choice consequences for branching system
     * This is called when a choice is made to track long-term effects
     */
    public function processChoiceConsequences(Character $character, string $eventCategory, string $choiceId, string $outcomeType, $statEffects, ?string $nextChainCategory = null, array $choiceRandomOutcomes = []): ?array
    {
        // Return value to store random outcome info for API response
        $randomOutcomeInfo = null;
        // Get or create consequence record for this chain
        $consequence = \App\Models\CharacterChoiceConsequence::getOrCreateForChain($character, $eventCategory);
        
        // Update chain order
        $consequence->chain_order = ($consequence->chain_order ?? 0) + 1;
        
        // Update choice path based on outcome
        if ($outcomeType === 'positive' && $consequence->choice_path !== 'positive') {
            $consequence->choice_path = $consequence->choice_path === 'negative' ? 'neutral' : 'positive';
        } elseif ($outcomeType === 'negative' && $consequence->choice_path !== 'negative') {
            $consequence->choice_path = $consequence->choice_path === 'positive' ? 'neutral' : 'negative';
        }
        
        // Update outcome streak
        $consequence->updateOutcomeStreak($outcomeType);
        
        // Set random outcomes if available in the choice
        if (!empty($choiceRandomOutcomes) && is_array($choiceRandomOutcomes)) {
            // Determine outcome type based on profession/category
            $randomType = 'both'; // Default to allowing both positive and negative
            if (in_array($eventCategory, ['education', 'skill'])) {
                $randomType = 'both';
            } elseif (in_array($eventCategory, ['career', 'profession'])) {
                $randomType = 'both';
            }
            
            // Extract configurable values from choice data
            $randomChance = $choiceRandomOutcomes[0]['chance'] ?? 30; // Default 30%, can be overridden per choice
            $positiveWeight = $choiceRandomOutcomes[0]['positive_weight'] ?? 50;
            $negativeWeight = $choiceRandomOutcomes[0]['negative_weight'] ?? 50;
            
            // Clamp the chance value between 0-100
            $randomChance = max(0, min(100, (int)$randomChance));
            
            // Configure random outcomes on the consequence
            $consequence->setRandomOutcomes(
                $randomChance,
                $randomType,
                $choiceRandomOutcomes,
                $positiveWeight,
                $negativeWeight
            );
            
            // Generate and apply random outcome
            $randomResult = $consequence->generateRandomOutcome();
            if ($randomResult) {
                // Apply the random outcome effects to character
                $appliedEffects = $consequence->applyRandomOutcomeEffects($character);
                
                // Store random outcome info for API response
                $randomOutcomeInfo = [
                    'occurred' => true,
                    'name' => $randomResult['name'],
                    'description' => $randomResult['description'] ?? '',
                    'type' => $randomResult['type'],
                    'effects' => $appliedEffects,
                ];
                
                // Log the random outcome in the character history
                $character->recordChoice(
                    $eventCategory . '_random',
                    $choiceId,
                    $randomResult['type'],
                    $appliedEffects
                );
            }
        }
        
        // Add cumulative stat modifiers - convert array to string if needed
        $effectsString = is_array($statEffects) ? json_encode($statEffects) : ($statEffects ?? '');
        $parsedEffects = $this->parseStatEffects($effectsString);
        foreach ($parsedEffects as $stat => $value) {
            $consequence->addStatModifier($stat, $value);
        }
        
        // Handle locked/unlocked choices based on event
        if ($outcomeType === 'positive') {
            // Positive outcomes can unlock new paths
            if ($nextChainCategory) {
                $consequence->unlockChoice($nextChainCategory);
            }
        } elseif ($outcomeType === 'negative') {
            // Negative outcomes can lock certain choices
            if (isset($parsedEffects['Morality']) && $parsedEffects['Morality'] < -10) {
                $consequence->lockChoice('moral_path');
            }
        }
        
        // Record in character choice history
        $character->recordChoice($eventCategory, $choiceId, $outcomeType, $parsedEffects);
        
        // Update relationship state if relevant
        if (in_array($eventCategory, ['family', 'social', 'romantic'])) {
            $newState = $outcomeType === 'positive' ? 'positive' : ($outcomeType === 'negative' ? 'negative' : 'neutral');
            $character->updateRelationshipState($eventCategory, $newState);
        }
        
        // Add trauma flags for negative outcomes
        if ($outcomeType === 'negative') {
            if (isset($parsedEffects['Happiness']) && $parsedEffects['Happiness'] < -15) {
                $character->addTraumaFlag('emotional_harm', $eventCategory);
            }
            if (isset($parsedEffects['Health']) && $parsedEffects['Health'] < -15) {
                $character->addTraumaFlag('physical_harm', $eventCategory);
            }
        }
        
        // Add achievements for positive milestones
        if ($consequence->chain_order >= 3 && $outcomeType === 'positive') {
            $character->addAchievement($eventCategory . '_master', 'Completed major milestone in ' . $eventCategory);
        }
        
        // Return random outcome info if it occurred
        return $randomOutcomeInfo;
    }

    /**
     * Get available choices for an event, considering locked choices
     */
    public function getAvailableChoices(Character $character, array $choices): array
    {
        $lockedChoices = $character->choiceConsequences()
            ->whereNotNull('locked_choices')
            ->get()
            ->pluck('locked_choices')
            ->flatten()
            ->toArray();
        
        if (empty($lockedChoices)) {
            return $choices;
        }
        
        // Filter out locked choices
        return array_filter($choices, function($choice) use ($lockedChoices) {
            $choiceId = is_array($choice) ? ($choice['id'] ?? '') : $choice;
            return !in_array($choiceId, $lockedChoices);
        });
    }

    /**
     * Check if pending events should trigger
     */
    public function checkPendingEvents(Character $character): array
    {
        $pendingEvents = $character->pending_events ?? [];
        $triggeredEvents = [];
        
        $character->pending_events = array_filter($pendingEvents, function($event) use ($character, &$triggeredEvents) {
            $triggerDay = $event['trigger_day'] ?? 0;
            
            if ($character->current_day >= $triggerDay) {
                $triggeredEvents[] = $event;
                return false; // Remove from pending
            }
            return true; // Keep in pending
        });
        
        $character->save();
        
        return $triggeredEvents;
    }

    /**
     * Calculate enhanced MBTI based on cumulative personality profile
     * Uses personality_profiles table for persistent tracking
     */
    public function calculateEnhancedMBTI(Character $character, string $eventType, int $choiceIndex, string $outcomeType, array $statEffects): array
    {
        // Get or create personality profile
        $profile = \App\Models\PersonalityProfile::getOrCreateForCharacter($character);
        
        // Calculate choice-based score (0-100)
        $choiceScore = $this->calculateChoiceScore($choiceIndex, $outcomeType);
        
        // Calculate stat-based dimension scores
        $stats = array_merge(
            $character->effective_stats ?? [],
            $character->hidden_stats ?? []
        );
        
        // E/I: Social stat + Isolation + choice
        $socialScore = isset($stats['Social']) ? (int) (30 + $stats['Social'] * 0.4) : 50;
        $isolationMod = isset($stats['Isolation']) ? -($stats['Isolation'] * 0.2) : 0;
        if (in_array($eventType, ['social', 'cultural'])) {
            $socialScore += $choiceScore * 0.1;
        }
        $energyScore = max(0, min(100, $socialScore + $isolationMod));
        
        // N/S: Creativity + Intelligence + learning outcomes
        $creativityScore = isset($stats['Creativity']) ? (int) (30 + $stats['Creativity'] * 0.4) : 50;
        $intelligenceMod = isset($stats['Intelligence']) ? ($stats['Intelligence'] - 50) * 0.2 : 0;
        if (stripos($outcomeType, 'growth') !== false || stripos($outcomeType, 'learn') !== false) {
            $creativityScore += 5;
        }
        $infoScore = max(0, min(100, $creativityScore + $intelligenceMod));
        
        // T/F: Empathy + Morality + relationship outcomes
        $empathyScore = isset($stats['Empathy']) ? (int) (30 + $stats['Empathy'] * 0.4) : 50;
        $moralityMod = isset($stats['Morality']) ? ($stats['Morality'] - 50) * 0.2 : 0;
        if (stripos($outcomeType, 'love') !== false || stripos($outcomeType, 'happy') !== false) {
            $empathyScore += 5;
        }
        $decisionScore = max(0, min(100, $empathyScore + $moralityMod));
        
        // J/P: Discipline + Burnout + achievement outcomes
        $disciplineScore = isset($stats['Discipline']) ? (int) (30 + $stats['Discipline'] * 0.4) : 50;
        $burnoutMod = isset($stats['Burnout']) ? -($stats['Burnout'] * 0.2) : 0;
        if (stripos($outcomeType, 'success') !== false || stripos($outcomeType, 'achievement') !== false) {
            $disciplineScore += 5;
        }
        $lifestyleScore = max(0, min(100, $disciplineScore + $burnoutMod));
        
        // Apply weighted update to profile (alpha = 0.15 for more responsive)
        $profile->updateDimension('energy_orientation', $energyScore, 0.15);
        $profile->updateDimension('information_gathering', $infoScore, 0.15);
        $profile->updateDimension('decision_forming', $decisionScore, 0.15);
        $profile->updateDimension('lifestyle_approach', $lifestyleScore, 0.15);
        
        // Update Big Five traits
        if (isset($stats['Creativity'])) {
            $profile->updateTrait('openness', $stats['Creativity'], 0.1);
        }
        if (isset($stats['Discipline'])) {
            $profile->updateTrait('conscientiousness', $stats['Discipline'], 0.1);
        }
        if (isset($stats['Social'])) {
            $profile->updateTrait('extraversion', $stats['Social'], 0.1);
        }
        if (isset($stats['Empathy'])) {
            $profile->updateTrait('agreeableness', $stats['Empathy'], 0.1);
        }
        if (isset($stats['Happiness'])) {
            // Invert: high happiness = low neuroticism
            $profile->updateTrait('neuroticism', 100 - $stats['Happiness'], 0.1);
        }
        
        // Add decision to history
        $profile->addDecision($eventType, $choiceScore);
        
        // Update behavioral scores
        if (in_array($eventType, ['social', 'cultural'])) {
            $scoreDelta = $outcomeType === 'positive' ? 2 : ($outcomeType === 'negative' ? -1 : 0);
            $profile->updateBehavioralScore('social_boldness', $scoreDelta);
        }
        if (in_array($eventType, ['career', 'profession'])) {
            $scoreDelta = $outcomeType === 'positive' ? 2 : ($outcomeType === 'negative' ? -1 : 0);
            $profile->updateBehavioralScore('conscientiousness', $scoreDelta);
        }
        if (in_array($eventType, ['moral', 'ethics'])) {
            $scoreDelta = $outcomeType === 'positive' ? 2 : ($outcomeType === 'negative' ? -1 : 0);
            $profile->updateBehavioralScore('agreeableness', $scoreDelta);
        }
        
        // Get final MBTI
        $result = $profile->getMBTIWithConfidence();
        
        Log::info('Enhanced MBTI Calculated', [
            'character_id' => $character->id,
            'mbti' => $result['mbti'],
            'confidence' => $result['confidence'],
            'dimensions' => $result['dimensions'],
            'big_five' => $result['big_five'],
            'event_type' => $eventType,
            'choice_index' => $choiceIndex,
            'outcome' => $outcomeType,
        ]);
        
        return $result;
    }

    /**
     * Calculate choice score (0-100) based on choice index and outcome
     */
    private function calculateChoiceScore(int $choiceIndex, string $outcomeType): int
    {
        // Base score from choice position
        $baseScores = [60, 50, 40, 30]; // First choice = more decisive
        $baseScore = $baseScores[$choiceIndex] ?? 40;
        
        // Adjust for outcome
        $outcomeMod = match($outcomeType) {
            'positive' => 10,
            'negative' => -10,
            default => 0,
        };
        
        return max(0, min(100, $baseScore + $outcomeMod));
    }

    /**
     * Get personality insight - describes the character's personality in detail
     */
    public function getPersonalityInsight(Character $character): array
    {
        $profile = \App\Models\PersonalityProfile::getOrCreateForCharacter($character);
        $result = $profile->getMBTIWithConfidence();
        
        $insights = [];
        
        // MBTI-based insights
        $mbti = $result['mbti'];
        if ($mbti[0] === 'E') {
            $insights[] = 'Extraverted - gains energy from social interactions';
        } else {
            $insights[] = 'Introverted - needs solitude to recharge';
        }
        if ($mbti[1] === 'N') {
            $insights[] = 'Intuitive - focuses on possibilities and patterns';
        } else {
            $insights[] = 'Sensing - focuses on concrete facts and details';
        }
        if ($mbti[2] === 'T') {
            $insights[] = 'Thinking - makes decisions based on logic';
        } else {
            $insights[] = 'Feeling - considers values and impacts on people';
        }
        if ($mbti[3] === 'J') {
            $insights[] = 'Judging - prefers structure and planning';
        } else {
            $insights[] = 'Perceiving - flexible and adaptable';
        }
        
        // Big Five insights
        $bf = $result['big_five'];
        if ($bf['O'] > 60) $insights[] = 'High openness - curious and creative';
        if ($bf['O'] < 40) $insights[] = 'Low openness - traditional and practical';
        if ($bf['C'] > 60) $insights[] = 'High conscientiousness - organized and reliable';
        if ($bf['C'] < 40) $insights[] = 'Low conscientiousness - spontaneous and flexible';
        if ($bf['E'] > 60) $insights[] = 'High extraversion - outgoing and energetic';
        if ($bf['E'] < 40) $insights[] = 'Low extraversion - reserved and independent';
        if ($bf['A'] > 60) $insights[] = 'High agreeableness - trusting and cooperative';
        if ($bf['A'] < 40) $insights[] = 'Low agreeableness - competitive and challenging';
        if ($bf['N'] > 60) $insights[] = 'High neuroticism - emotionally reactive';
        if ($bf['N'] < 40) $insights[] = 'Low neuroticism - emotionally stable';
        
        return [
            'mbti' => $mbti,
            'mbti_confidence' => $result['confidence'],
            'insights' => $insights,
            'dimensions' => $result['dimensions'],
            'big_five' => $bf,
        ];
    }
}
