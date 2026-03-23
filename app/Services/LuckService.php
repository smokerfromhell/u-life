<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling random events/luck system
 */
class LuckService
{
    /**
     * Types of luck events
     */
    const LUCK_TYPE_FORTUNE = 'fortune';    // Positive random events
    const LUCK_TYPE_MISFORTUNE = 'misfortune';  // Negative random events
    const LUCK_TYPE_NEUTRAL = 'neutral';    // Mixed/neutral events
    
    /**
     * Luck categories
     */
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_WEALTH = 'wealth';
    const CATEGORY_CAREER = 'career';
    const CATEGORY_RELATIONSHIP = 'relationship';
    const CATEGORY_RANDOM = 'random';
    
    /**
     * Check if a random event should trigger
     * Base chance is 15%, modified by character's luck stat
     */
    public function shouldTriggerLuckEvent(Character $character): bool
    {
        // Base chance 15%
        $baseChance = 15;
        
        // Modify by luck stat (0-100 scale)
        $luckStat = $character->luck ?? 50;
        $luckModifier = ($luckStat - 50) / 2; // -25 to +25
        
        $finalChance = max(5, min(30, $baseChance + $luckModifier));
        
        // Random roll
        $roll = random_int(1, 100);
        
        Log::info('Luck check', [
            'character_id' => $character->id,
            'luck_stat' => $luckStat,
            'chance' => $finalChance,
            'roll' => $roll,
            'triggered' => $roll <= $finalChance
        ]);
        
        return $roll <= $finalChance;
    }
    
    /**
     * Determine the type of luck (fortune vs misfortune)
     * Based on character's overall karma/luck stat
     */
    public function determineLuckType(Character $character): string
    {
        $luckStat = $character->luck ?? 50;
        
        // High luck = more fortune, low luck = more misfortune
        if ($luckStat >= 70) {
            $roll = random_int(1, 100);
            return $roll <= 70 ? self::LUCK_TYPE_FORTUNE : self::LUCK_TYPE_MISFORTUNE;
        } elseif ($luckStat <= 30) {
            $roll = random_int(1, 100);
            return $roll <= 70 ? self::LUCK_TYPE_MISFORTUNE : self::LUCK_TYPE_FORTUNE;
        } else {
            // Balanced luck - more neutral/mixed
            $roll = random_int(1, 100);
            if ($roll <= 30) {
                return self::LUCK_TYPE_FORTUNE;
            } elseif ($roll <= 60) {
                return self::LUCK_TYPE_MISFORTUNE;
            } else {
                return self::LUCK_TYPE_NEUTRAL;
            }
        }
    }
    
    /**
     * Get a random event based on character status and luck type
     */
    public function getRandomEvent(Character $character, string $luckType): ?array
    {
        $events = $this->getEventsForCharacter($character, $luckType);
        
        if (empty($events)) {
            return null;
        }
        
        // Weighted random selection
        $weightedEvents = [];
        foreach ($events as $event) {
            $weight = $event['weight'] ?? 1;
            for ($i = 0; $i < $weight; $i++) {
                $weightedEvents[] = $event;
            }
        }
        
        $selected = $weightedEvents[array_rand($weightedEvents)];
        
        // Apply luck stat multiplier to effects
        if (isset($selected['effects'])) {
            $selected['effects'] = $this->applyLuckMultiplier($selected['effects'], $character->luck ?? 50);
        }
        
        return $selected;
    }
    
    /**
     * Get events filtered by character status
     */
    private function getEventsForCharacter(Character $character, string $luckType): array
    {
        $allEvents = $this->getAllLuckEvents();
        
        $filtered = [];
        
        foreach ($allEvents as $event) {
            // Filter by luck type
            if ($event['luck_type'] !== $luckType && $luckType !== self::LUCK_TYPE_NEUTRAL) {
                if ($event['luck_type'] !== self::LUCK_TYPE_NEUTRAL) {
                    continue;
                }
            }
            
            // Check conditions
            if (isset($event['conditions'])) {
                if (!$this->checkConditions($event['conditions'], $character)) {
                    continue;
                }
            }
            
            $filtered[] = $event;
        }
        
        return $filtered;
    }
    
    /**
     * Check if event conditions are met
     */
    private function checkConditions(array $conditions, Character $character): bool
    {
        foreach ($conditions as $condition => $value) {
            switch ($condition) {
                case 'min_wealth':
                    if (($character->finance ?? 20) < $value) return false;
                    break;
                case 'max_wealth':
                    if (($character->finance ?? 20) > $value) return false;
                    break;
                case 'min_health':
                    if (($character->health ?? 78) < $value) return false;
                    break;
                case 'max_health':
                    if (($character->health ?? 78) > $value) return false;
                    break;
                case 'profession':
                    if ($character->profession !== $value) return false;
                    break;
                case 'has_profession':
                    if (!$character->profession && $value) return false;
                    break;
                case 'relationship_status':
                    if ($character->relationship_status !== $value) return false;
                    break;
                case 'min_age':
                    if (($character->age ?? 0) < $value) return false;
                    break;
                case 'max_age':
                    if (($character->age ?? 0) > $value) return false;
                    break;
                case 'age_group':
                    if ($character->age_group !== $value) return false;
                    break;
                case 'min_happiness':
                    if (($character->happiness ?? 72) < $value) return false;
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Apply luck multiplier to effects
     */
    private function applyLuckMultiplier(array $effects, int $luckStat): array
    {
        // High luck amplifies positive, mitigates negative
        $multiplier = 1.0;
        
        if ($luckStat >= 80) {
            $multiplier = 1.5;
        } elseif ($luckStat >= 60) {
            $multiplier = 1.25;
        } elseif ($luckStat <= 20) {
            $multiplier = 0.5;
        } elseif ($luckStat <= 40) {
            $multiplier = 0.75;
        }
        
        $result = [];
        foreach ($effects as $stat => $value) {
            $result[$stat] = round($value * $multiplier);
        }
        
        return $result;
    }
    
    /**
     * Get all possible luck events
     */
    private function getAllLuckEvents(): array
    {
        return [
            // === FORTUNE EVENTS (Positive) ===
            [
                'id' => 'lucky_discovery',
                'title' => 'Lucky Discovery!',
                'description' => 'You found something valuable or useful!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 3,
                'effects' => [
                    'finance' => [50, 200],
                    'happiness' => [5, 15]
                ],
                'choices' => [
                    ['text' => 'Keep it!', 'effects' => ['finance' => 100]],
                    ['text' => 'Donate to charity', 'effects' => ['happiness' => 20, 'finance' => -50]]
                ]
            ],
            [
                'id' => 'windfall',
                'title' => 'Unexpected Windfall!',
                'description' => 'You received unexpected money!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 2,
                'effects' => [
                    'finance' => [200, 1000]
                ],
                'conditions' => ['min_wealth' => 0]
            ],
            [
                'id' => 'health_boost',
                'title' => 'Feeling Great!',
                'description' => 'Your health has improved mysteriously!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_HEALTH,
                'weight' => 3,
                'effects' => [
                    'health' => [10, 25],
                    'happiness' => [5, 10]
                ]
            ],
            [
                'id' => 'chance_meeting',
                'title' => 'Fateful Encounter',
                'description' => 'You met someone important who could change your life!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_RELATIONSHIP,
                'weight' => 2,
                'effects' => [
                    'happiness' => [10, 20]
                ],
                'conditions' => ['relationship_status' => 'single']
            ],
            [
                'id' => 'opportunity_knocks',
                'title' => 'Golden Opportunity',
                'description' => 'An amazing opportunity has presented itself!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_CAREER,
                'weight' => 2,
                'effects' => [
                    'career_level' => 'promotion',
                    'happiness' => [15, 25]
                ],
                'conditions' => ['has_profession' => true, 'max_age' => 45]
            ],
            [
                'id' => 'lottery_win',
                'title' => 'Lottery Winner!',
                'description' => 'You won the lottery!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 1,
                'effects' => [
                    'finance' => [5000, 50000]
                ],
                'conditions' => ['min_wealth' => 100]
            ],
            [
                'id' => 'inheritance',
                'title' => 'Unexpected Inheritance',
                'description' => 'A relative you barely knew left you something!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 1,
                'effects' => [
                    'finance' => [1000, 10000]
                ],
                'conditions' => ['min_age' => 25]
            ],
            [
                'id' => 'perfect_health',
                'title' => 'Medical Miracle',
                'description' => 'A new treatment has worked wonders for you!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_HEALTH,
                'weight' => 2,
                'effects' => [
                    'health' => [20, 40]
                ],
                'conditions' => ['max_health' => 80]
            ],
            [
                'id' => 'social_viral',
                'title' => 'Went Viral!',
                'description' => 'Your social media post went viral!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'effects' => [
                    'happiness' => [15, 30],
                    'reputation' => [10, 20]
                ]
            ],
            [
                'id' => 'tax_refund',
                'title' => 'Tax Refund!',
                'description' => 'You got a bigger tax refund than expected!',
                'luck_type' => self::LUCK_TYPE_FORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 3,
                'effects' => [
                    'finance' => [200, 1000]
                ]
            ],
            
            // === MISFORTUNE EVENTS (Negative) ===
            [
                'id' => 'accident',
                'title' => 'Accident!',
                'description' => 'You had an accident!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_HEALTH,
                'weight' => 2,
                'effects' => [
                    'health' => [-15, -30],
                    'happiness' => [-10, -20],
                    'finance' => [-100, -500]
                ]
            ],
            [
                'id' => 'theft',
                'title' => 'Pickpocketed!',
                'description' => 'You were robbed!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 2,
                'effects' => [
                    'finance' => [-50, -200],
                    'happiness' => [-5, -15]
                ],
                'conditions' => ['min_wealth' => 50]
            ],
            [
                'id' => 'illness',
                'title' => 'Sudden Illness',
                'description' => 'You fell ill unexpectedly!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_HEALTH,
                'weight' => 3,
                'effects' => [
                    'health' => [-10, -25],
                    'happiness' => [-5, -15]
                ]
            ],
            [
                'id' => 'job_loss',
                'title' => 'Lost Job',
                'description' => 'You were laid off unexpectedly!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_CAREER,
                'weight' => 1,
                'effects' => [
                    'career_level' => 'unemployed',
                    'happiness' => [-20, -30],
                    'finance' => [-200, -500]
                ],
                'conditions' => ['has_profession' => true]
            ],
            [
                'id' => 'breakup',
                'title' => 'Heartbreak',
                'description' => 'Your relationship ended suddenly.',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_RELATIONSHIP,
                'weight' => 2,
                'effects' => [
                    'happiness' => [-20, -35],
                    'relationship_status' => 'single'
                ],
                'conditions' => ['relationship_status' => ['dating', 'engaged', 'married']]
            ],
            [
                'id' => 'scam',
                'title' => 'Scammed!',
                'description' => 'You fell for a scam!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 2,
                'effects' => [
                    'finance' => [-200, -1000],
                    'happiness' => [-10, -20]
                ],
                'conditions' => ['min_wealth' => 100]
            ],
            [
                'id' => 'car_trouble',
                'title' => 'Car Problems',
                'description' => 'Your car broke down!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 3,
                'effects' => [
                    'finance' => [-100, -500],
                    'happiness' => [-5, -10]
                ]
            ],
            [
                'id' => 'home_damage',
                'title' => 'Home Damage',
                'description' => 'Your home was damaged!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_WEALTH,
                'weight' => 2,
                'effects' => [
                    'finance' => [-500, -2000],
                    'happiness' => [-10, -20]
                ]
            ],
            [
                'id' => 'lawsuit',
                'title' => 'Legal Trouble',
                'description' => 'You are facing a lawsuit!',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 1,
                'effects' => [
                    'finance' => [-1000, -5000],
                    'happiness' => [-15, -25]
                ],
                'conditions' => ['min_wealth' => 500]
            ],
            [
                'id' => 'depression',
                'title' => 'Feeling Down',
                'description' => 'You\'ve been feeling depressed lately.',
                'luck_type' => self::LUCK_TYPE_MISFORTUNE,
                'category' => self::CATEGORY_HEALTH,
                'weight' => 2,
                'effects' => [
                    'happiness' => [-15, -25],
                    'health' => [-5, -10]
                ],
                'conditions' => ['max_happiness' => 50]
            ],
            
            // === NEUTRAL EVENTS (Mixed) ===
            [
                'id' => 'stranger_help',
                'title' => 'Kind Stranger',
                'description' => 'A stranger helped you, but asked for a favor.',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'choices' => [
                    ['text' => 'Accept help', 'effects' => ['happiness' => 10, 'finance' => -50]],
                    ['text' => 'Decline', 'effects' => ['happiness' => -5]]
                ]
            ],
            [
                'id' => 'found_pet',
                'title' => 'Found a Pet',
                'description' => 'You found a stray animal!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'choices' => [
                    ['text' => 'Keep it', 'effects' => ['happiness' => 20, 'finance' => -100]],
                    ['text' => 'Find it a home', 'effects' => ['happiness' => 10]],
                    ['text' => 'Leave it', 'effects' => ['happiness' => -5]]
                ]
            ],
            [
                'id' => 'old_friend',
                'title' => 'Old Friend Reappears',
                'description' => 'An old friend reaches out to you!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RELATIONSHIP,
                'weight' => 2,
                'choices' => [
                    ['text' => 'Reconnect', 'effects' => ['happiness' => 15]],
                    ['text' => 'Ignore them', 'effects' => ['happiness' => -5]]
                ]
            ],
            [
                'id' => 'surprise_party',
                'title' => 'Surprise Party',
                'description' => 'Your friends threw you a surprise party!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RELATIONSHIP,
                'weight' => 2,
                'effects' => [
                    'happiness' => [10, 20],
                    'finance' => [-50, -100]
                ]
            ],
            [
                'id' => 'competition',
                'title' => 'Competition Entry',
                'description' => 'You entered a competition!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'choices' => [
                    ['text' => 'Win!', 'effects' => ['happiness' => 25, 'finance' => 500], 'weight' => 1],
                    ['text' => 'Lose', 'effects' => ['happiness' => -10], 'weight' => 3]
                ]
            ],
            [
                'id' => 'traffic_ticket',
                'title' => 'Traffic Ticket',
                'description' => 'You got a traffic ticket!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 3,
                'effects' => [
                    'finance' => [-50, -150],
                    'happiness' => [-5, -10]
                ]
            ],
            [
                'id' => 'promotion_deny',
                'title' => 'Promotion Denied',
                'description' => 'You were passed over for promotion.',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_CAREER,
                'weight' => 2,
                'effects' => [
                    'happiness' => [-10, -20]
                ],
                'conditions' => ['has_profession' => true]
            ],
            [
                'id' => 'free_sample',
                'title' => 'Free Sample',
                'description' => 'You got a free sample!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 3,
                'effects' => [
                    'happiness' => [3, 8]
                ]
            ],
            [
                'id' => 'delayed_flight',
                'title' => 'Flight Delayed',
                'description' => 'Your flight was delayed!',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'effects' => [
                    'happiness' => [-5, -15]
                ]
            ],
            [
                'id' => 'weather_storm',
                'title' => 'Storm Damage',
                'description' => 'A storm caused minor damage.',
                'luck_type' => self::LUCK_TYPE_NEUTRAL,
                'category' => self::CATEGORY_RANDOM,
                'weight' => 2,
                'effects' => [
                    'finance' => [-50, -200],
                    'happiness' => [-3, -8]
                ]
            ]
        ];
    }
    
    /**
     * Generate random event with resolved effects
     */
    public function generateLuckEvent(Character $character): ?array
    {
        if (!$this->shouldTriggerLuckEvent($character)) {
            return null;
        }
        
        $luckType = $this->determineLuckType($character);
        $event = $this->getRandomEvent($character, $luckType);
        
        if (!$event) {
            return null;
        }
        
        // Resolve any random effects
        if (isset($event['effects'])) {
            $event['effects'] = $this->resolveRandomEffects($event['effects']);
        }
        
        // If has choices, resolve choice effects too
        if (isset($event['choices'])) {
            foreach ($event['choices'] as &$choice) {
                if (isset($choice['effects'])) {
                    $choice['effects'] = $this->resolveRandomEffects($choice['effects']);
                }
            }
        }
        
        return $event;
    }
    
    /**
     * Resolve random effect ranges to actual values
     */
    private function resolveRandomEffects(array $effects): array
    {
        $resolved = [];
        
        foreach ($effects as $stat => $value) {
            if (is_array($value) && count($value) === 2) {
                // Range - pick random between min and max
                $resolved[$stat] = random_int($value[0], $value[1]);
            } else {
                $resolved[$stat] = $value;
            }
        }
        
        return $resolved;
    }
    
    /**
     * Update character's luck based on actions
     */
    public function updateLuck(Character $character, string $action): void
    {
        $luckChange = 0;
        
        switch ($action) {
            case 'good_deed':
                $luckChange = random_int(1, 3);
                break;
            case 'bad_deed':
                $luckChange = random_int(-3, -1);
                break;
            case 'healthy_lifestyle':
                $luckChange = random_int(1, 2);
                break;
            case 'unhealthy_lifestyle':
                $luckChange = random_int(-2, -1);
                break;
            case 'generous':
                $luckChange = random_int(2, 5);
                break;
            case 'greedy':
                $luckChange = random_int(-3, -2);
                break;
        }
        
        if ($luckChange !== 0) {
            $currentLuck = $character->luck ?? 50;
            $newLuck = max(0, min(100, $currentLuck + $luckChange));
            $character->luck = $newLuck;
            $character->save();
            
            Log::info('Luck updated', [
                'character_id' => $character->id,
                'action' => $action,
                'change' => $luckChange,
                'new_luck' => $newLuck
            ]);
        }
    }
}
