<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling achievements and badges
 */
class AchievementService
{
    /**
     * Achievement categories
     */
    const CATEGORY_CAREER = 'career';
    const CATEGORY_WEALTH = 'wealth';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_RELATIONSHIP = 'relationship';
    const CATEGORY_SKILLS = 'skills';
    const CATEGORY_LIFESPAN = 'lifespan';
    const CATEGORY_SPECIAL = 'special';
    const CATEGORY_MILESTONE = 'milestone';
    
    /**
     * Rarity levels
     */
    const RARITY_COMMON = 'common';
    const RARITY_UNCOMMON = 'uncommon';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'epic';
    const RARITY_LEGENDARY = 'legendary';
    
    /**
     * All available achievements
     */
    public function getAllAchievements(): array
    {
        return [
            // === CAREER ACHIEVEMENTS ===
            [
                'id' => 'first_job',
                'name' => 'First Steps',
                'description' => 'Get your first job',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-briefcase-outline',
                'condition' => ['has_profession' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'career_master',
                'name' => 'Career Master',
                'description' => 'Reach the highest career level',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-star-outline',
                'condition' => ['career_level' => 'executive'],
                'unlock_once' => true
            ],
            [
                'id' => 'multiple_careers',
                'name' => 'Jack of All Trades',
                'description' => 'Work in 5 different professions',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-account-switch',
                'condition' => ['professions_count' => 5],
                'unlock_once' => true
            ],
            [
                'id' => 'entrepreneur',
                'name' => 'Entrepreneur',
                'description' => 'Start your own business',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-storefront',
                'condition' => ['career_level' => 'entrepreneur'],
                'unlock_once' => true
            ],
            [
                'id' => 'workaholic',
                'name' => 'Workaholic',
                'description' => 'Work for 30+ years',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-clock-outline',
                'condition' => ['total_work_years' => 30],
                'unlock_once' => true
            ],
            
            // === WEALTH ACHIEVEMENTS ===
            [
                'id' => 'first_savings',
                'name' => 'Savings Start',
                'description' => 'Save your first $1,000',
                'category' => self::CATEGORY_WEALTH,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-piggy-bank',
                'condition' => ['min_finance' => 1000],
                'unlock_once' => true
            ],
            [
                'id' => 'millionaire',
                'name' => 'Millionaire',
                'description' => 'Accumulate $1,000,000 in net worth',
                'category' => self::CATEGORY_WEALTH,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-currency-usd',
                'condition' => ['min_finance' => 1000000],
                'unlock_once' => true
            ],
            [
                'id' => 'millionaire_plus',
                'name' => 'Multi-Millionaire',
                'description' => 'Accumulate $10,000,000 in net worth',
                'category' => self::CATEGORY_WEALTH,
                'rarity' => self::RARITY_LEGENDARY,
                'icon' => 'mdi-cash-multiple',
                'condition' => ['min_finance' => 10000000],
                'unlock_once' => true
            ],
            [
                'id' => 'wealthy_retirement',
                'name' => 'Comfortable Retirement',
                'description' => 'Retire with $500,000+ savings',
                'category' => self::CATEGORY_WEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-beach',
                'condition' => ['min_finance' => 500000, 'age_group' => 'old'],
                'unlock_once' => true
            ],
            [
                'id' => 'big_spender',
                'name' => 'Big Spender',
                'description' => 'Spend $100,000 in a single transaction',
                'category' => self::CATEGORY_WEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-cart',
                'condition' => ['max_single_spent' => 100000],
                'unlock_once' => true
            ],
            
            // === HEALTH ACHIEVEMENTS ===
            [
                'id' => 'fitness_fan',
                'name' => 'Fitness Fanatic',
                'description' => 'Maintain 100% health for 10 years',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-heart',
                'condition' => ['health_streak_years' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'marathon_runner',
                'name' => 'Marathon Runner',
                'description' => 'Complete a fitness challenge',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-run',
                'condition' => ['fitness_events_completed' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'healthy_lifestyle',
                'name' => 'Healthy Living',
                'description' => 'Never get sick for 15 years',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-leaf',
                'condition' => ['no_illness_years' => 15],
                'unlock_once' => true
            ],
            [
                'id' => 'survivor',
                'name' => 'Survivor',
                'description' => 'Recover from critical health condition',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-hospital-box',
                'condition' => ['recovered_from_critical' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'longevity',
                'name' => 'Longevity',
                'description' => 'Live to age 80+',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-clock-check',
                'condition' => ['min_age' => 80],
                'unlock_once' => true
            ],
            
            // === RELATIONSHIP ACHIEVEMENTS ===
            [
                'id' => 'first_love',
                'name' => 'First Love',
                'description' => 'Enter your first relationship',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-heart-outline',
                'condition' => ['relationship_status' => ['dating', 'engaged', 'married']],
                'unlock_once' => true
            ],
            [
                'id' => 'marriage',
                'name' => 'Tie the Knot',
                'description' => 'Get married',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-ring',
                'condition' => ['relationship_status' => 'married'],
                'unlock_once' => true
            ],
            [
                'id' => 'family_planning',
                'name' => 'Family Planner',
                'description' => 'Have 3 children',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-account-group',
                'condition' => ['children_count' => 3],
                'unlock_once' => true
            ],
            [
                'id' => 'grandparents',
                'name' => 'Granny/Grandpa',
                'description' => 'Become a grandparent',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-multiple-plus',
                'condition' => ['has_grandchildren' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'social_butterfly',
                'name' => 'Social Butterfly',
                'description' => 'Have 10+ social connections',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-group-outline',
                'condition' => ['social_connections_count' => 10],
                'unlock_once' => true
            ],
            
            // === SKILLS ACHIEVEMENTS ===
            [
                'id' => 'learner',
                'name' => 'Eager Learner',
                'description' => 'Learn your first skill',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-school',
                'condition' => ['skills_count' => 1],
                'unlock_once' => true
            ],
            [
                'id' => 'polymath',
                'name' => 'Polymath',
                'description' => 'Learn 10 different skills',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-brain',
                'condition' => ['skills_count' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'master_skilled',
                'name' => 'Master of Skills',
                'description' => 'Reach max level in 5 skills',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-trophy',
                'condition' => ['max_skills_count' => 5],
                'unlock_once' => true
            ],
            [
                'id' => 'talent_spotted',
                'name' => 'Talent Spotted',
                'description' => 'Discover your first talent',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-star',
                'condition' => ['talents_count' => 1],
                'unlock_once' => true
            ],
            [
                'id' => 'gifted',
                'name' => 'Gifted',
                'description' => 'Discover 5 talents',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-stars',
                'condition' => ['talents_count' => 5],
                'unlock_once' => true
            ],
            
            // === LIFESPAN ACHIEVEMENTS ===
            [
                'id' => 'teenager',
                'name' => 'Teenager',
                'description' => 'Reach age 13',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-cake-variant',
                'condition' => ['min_age' => 13],
                'unlock_once' => true
            ],
            [
                'id' => 'adult',
                'name' => 'Adulthood',
                'description' => 'Reach age 18',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-account-check',
                'condition' => ['min_age' => 18],
                'unlock_once' => true
            ],
            [
                'id' => 'full_life',
                'name' => 'Full Life',
                'description' => 'Experience all life stages',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-timeline-clock',
                'condition' => ['age_groups_experienced' => 4],
                'unlock_once' => true
            ],
            
            // === SPECIAL/MILESTONE ACHIEVEMENTS ===
            [
                'id' => 'luckiest_person',
                'name' => 'Lucky Star',
                'description' => 'Have 80+ luck stat',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-clover',
                'condition' => ['min_luck' => 80],
                'unlock_once' => true
            ],
            [
                'id' => 'karma_whell',
                'name' => 'Karma Whell',
                'description' => 'Have 100+ karma',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-karma',
                'condition' => ['min_karma' => 100],
                'unlock_once' => true
            ],
            [
                'id' => 'narrative_master',
                'name' => 'Story Master',
                'description' => 'Complete a full narrative arc',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-book-open-variant',
                'condition' => ['completed_narratives' => 1],
                'unlock_once' => true
            ],
            [
                'id' => 'perfect_day',
                'name' => 'Perfect Day',
                'description' => 'Make all optimal choices in a day',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-white-balance-sunny',
                'condition' => ['perfect_day' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'comeback_kid',
                'name' => 'Comeback Kid',
                'description' => 'Recover from near-death experience',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-refresh',
                'condition' => ['comeback' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'game_master',
                'name' => 'Game Master',
                'description' => 'Play the game for the first time',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-gamepad-variant',
                'condition' => ['has_played' => true],
                'unlock_once' => true
            ]
        ];
    }
    
    /**
     * Check and unlock achievements for a character
     */
    public function checkAndUnlockAchievements(Character $character): array
    {
        $unlockedAchievements = [];
        $achievements = $this->getAllAchievements();
        $currentUnlocks = $character->achievement_flags ?? [];
        
        foreach ($achievements as $achievement) {
            // Skip if already unlocked
            if (in_array($achievement['id'], $currentUnlocks)) {
                continue;
            }
            
            // Check if unlockable once only
            if (isset($achievement['unlock_once']) && $achievement['unlock_once']) {
                if ($this->checkConditions($achievement['condition'], $character)) {
                    $currentUnlocks[] = $achievement['id'];
                    $unlockedAchievements[] = $achievement;
                    
                    Log::info('Achievement unlocked!', [
                        'character_id' => $character->id,
                        'achievement' => $achievement['id']
                    ]);
                }
            }
        }
        
        // Save if new achievements unlocked
        if (!empty($unlockedAchievements)) {
            $character->achievement_flags = $currentUnlocks;
            $character->save();
        }
        
        return $unlockedAchievements;
    }
    
    /**
     * Check achievement conditions
     */
    private function checkConditions(array $conditions, Character $character): bool
    {
        foreach ($conditions as $condition => $expected) {
            $actual = $this->getConditionValue($condition, $character);
            
            if (is_array($expected)) {
                if (!in_array($actual, $expected)) {
                    return false;
                }
            } else {
                if ($actual != $expected) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get actual value for condition
     */
    private function getConditionValue(string $condition, Character $character): mixed
    {
        switch ($condition) {
            case 'has_profession':
                return !empty($character->profession);
            case 'min_finance':
                return $character->finance ?? 0;
            case 'max_single_spent':
                // Would need to track this separately
                return 0;
            case 'min_age':
                return $character->age ?? 0;
            case 'career_level':
                return $character->career_level ?? 'unemployed';
            case 'relationship_status':
                return $character->relationship_status ?? 'single';
            case 'health_streak_years':
                // Would need to track this
                return 0;
            case 'no_illness_years':
                // Would need to track this
                return 0;
            case 'skills_count':
                return count($character->skills ?? []);
            case 'talents_count':
                return count($character->talents ?? []);
            case 'min_luck':
                return $character->luck ?? 50;
            case 'min_karma':
                return $character->karma ?? 0;
            case 'age_group':
                return $character->age_group ?? 'child';
            case 'has_played':
                return true;
            case 'children_count':
                return 0; // Would need social connections tracking
            case 'social_connections_count':
                $connections = $character->social_connections ?? [];
                return is_array($connections) ? count($connections) : 0;
            case 'has_grandchildren':
                return false; // Would need tracking
            case 'recovered_from_critical':
                return false; // Would need tracking
            case 'comeback':
                return false; // Would need tracking
            case 'perfect_day':
                return false; // Would need tracking
            case 'completed_narratives':
                return 0; // Would need tracking
            case 'fitness_events_completed':
                return 0; // Would need tracking
            case 'max_skills_count':
                return 0; // Would need skill levels
            case 'professions_count':
                return 0; // Would need to track
            case 'total_work_years':
                return 0; // Would need tracking
            case 'age_groups_experienced':
                return 1; // Would need tracking
            default:
                return null;
        }
    }
    
    /**
     * Get all achievements with unlock status for character
     */
    public function getCharacterAchievements(Character $character): array
    {
        $achievements = $this->getAllAchievements();
        $unlocked = $character->achievement_flags ?? [];
        
        $result = [];
        foreach ($achievements as $achievement) {
            $result[] = [
                ...$achievement,
                'unlocked' => in_array($achievement['id'], $unlocked),
                'unlocked_at' => in_array($achievement['id'], $unlocked) ? now()->toIso8601String() : null
            ];
        }
        
        return $result;
    }
    
    /**
     * Get achievement stats for character
     */
    public function getAchievementStats(Character $character): array
    {
        $achievements = $this->getAllAchievements();
        $unlocked = $character->achievement_flags ?? [];
        
        $byCategory = [];
        $byRarity = [];
        
        foreach ($achievements as $achievement) {
            $category = $achievement['category'];
            $rarity = $achievement['rarity'];
            
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = ['total' => 0, 'unlocked' => 0];
            }
            $byCategory[$category]['total']++;
            
            if (!isset($byRarity[$rarity])) {
                $byRarity[$rarity] = ['total' => 0, 'unlocked' => 0];
            }
            $byRarity[$rarity]['total']++;
            
            if (in_array($achievement['id'], $unlocked)) {
                $byCategory[$category]['unlocked']++;
                $byRarity[$rarity]['unlocked']++;
            }
        }
        
        return [
            'total_achievements' => count($achievements),
            'unlocked_count' => count($unlocked),
            'by_category' => $byCategory,
            'by_rarity' => $byRarity
        ];
    }
}
