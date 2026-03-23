<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Character;
use App\Models\DecisionLog;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling achievements and badges
 * Based on the game's actual mechanics: age system (max 50), career levels, life stats, skills, etc.
 */
class AchievementService
{
    /**
     * Achievement categories
     */
    const CATEGORY_CAREER = 'career';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_RELATIONSHIP = 'relationship';
    const CATEGORY_SKILLS = 'skills';
    const CATEGORY_LIFESPAN = 'lifespan';
    const CATEGORY_SPECIAL = 'special';
    const CATEGORY_MILESTONE = 'milestone';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_LUCK = 'luck';
    
    /**
     * Rarity levels
     */
    const RARITY_COMMON = 'common';
    const RARITY_UNCOMMON = 'uncommon';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'epic';
    const RARITY_LEGENDARY = 'legendary';

    /**
     * Career level progression for promotion checking
     */
    const CAREER_LEVELS = [
        'unemployed',
        'entry',
        'junior',
        'senior',
        'manager',
        'executive',
        'retired'
    ];

    /**
     * Check if achievements table has data.
     */
    public static function hasDatabaseAchievements(): bool
    {
        return Achievement::count() > 0;
    }

    /**
     * Get all achievements from database.
     */
    public function getDatabaseAchievements(): array
    {
        $achievements = Achievement::all();
        
        return $achievements->map(function ($achievement) {
            return [
                'id' => $achievement->achievement_id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'category' => $achievement->category,
                'rarity' => $achievement->rarity,
                'icon' => $achievement->icon,
                'condition' => $achievement->conditions,
                'unlock_once' => $achievement->unlock_once,
                'points' => $achievement->points,
                // Store the DB model for later use
                '_db_model' => $achievement,
            ];
        })->toArray();
    }

    /**
     * Get achievement by ID from database.
     */
    public function getDatabaseAchievementById(string $achievementId): ?Achievement
    {
        return Achievement::where('achievement_id', $achievementId)->first();
    }

    /**
     * Get achievements by category from database.
     */
    public function getDatabaseAchievementsByCategory(string $category): array
    {
        return Achievement::where('category', $category)->get()->map(function ($achievement) {
            return [
                'id' => $achievement->achievement_id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'category' => $achievement->category,
                'rarity' => $achievement->rarity,
                'icon' => $achievement->icon,
                'condition' => $achievement->conditions,
                'unlock_once' => $achievement->unlock_once,
                'points' => $achievement->points,
                '_db_model' => $achievement,
            ];
        })->toArray();
    }

    /**
     * Unlock achievement in database for a character.
     */
    public function unlockAchievementInDatabase(Character $character, string $achievementId, array $metadata = []): bool
    {
        $achievement = $this->getDatabaseAchievementById($achievementId);
        
        if (!$achievement) {
            Log::warning("Achievement not found in database: {$achievementId}");
            return false;
        }

        // Check if already unlocked
        if ($achievement->unlock_once && $character->hasAchievement($achievementId)) {
            return false;
        }

        // Attach to character
        $character->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
            'metadata' => json_encode($metadata),
        ]);

        Log::info("Achievement unlocked: {$achievementId} for character {$character->id}");
        
        return true;
    }

    /**
     * Get character unlocked achievements from database.
     */
    public function getUnlockedAchievementsFromDatabase(Character $character): array
    {
        $achievements = $character->achievements()->get();
        
        return $achievements->map(function ($achievement) {
            return [
                'id' => $achievement->achievement_id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'category' => $achievement->category,
                'rarity' => $achievement->rarity,
                'icon' => $achievement->icon,
                'points' => $achievement->points,
                'unlocked_at' => $achievement->pivot->unlocked_at,
                'metadata' => json_decode($achievement->pivot->metadata, true),
            ];
        })->toArray();
    }

    /**
     * Get achievement progress from database.
     */
    public function getAchievementProgressFromDatabase(Character $character): array
    {
        $totalDb = Achievement::count();
        $unlockedCount = $character->achievements()->count();
        
        return [
            'total' => $totalDb,
            'unlocked' => $unlockedCount,
            'percentage' => $totalDb > 0 ? round(($unlockedCount / $totalDb) * 100, 1) : 0,
            'achievements' => $this->getUnlockedAchievementsFromDatabase($character),
            'points' => $character->getAchievementPoints(),
        ];
    }

    /**
     * Check and unlock achievements using database.
     * Returns array of newly unlocked achievements.
     */
    public function checkAndUnlockDatabaseAchievements(Character $character): array
    {
        $newlyUnlocked = [];
        
        if (!$this->hasDatabaseAchievements()) {
            return $newlyUnlocked;
        }

        $dbAchievements = $this->getDatabaseAchievements();
        
        foreach ($dbAchievements as $achievement) {
            $achievementId = $achievement['id'];
            
            // Skip if already unlocked
            if ($character->hasAchievement($achievementId)) {
                continue;
            }

            // Check if conditions are met using existing isUnlocked method
            if ($this->isUnlocked($character, $achievement)) {
                $metadata = [
                    'day' => $character->current_day,
                    'age' => $character->current_day, // In this game, day = age
                ];
                
                if ($this->unlockAchievementInDatabase($character, $achievementId, $metadata)) {
                    $newlyUnlocked[] = $achievement;
                }
            }
        }
        
        return $newlyUnlocked;
    }

    /**
     * Get all achievements with unlock status from database.
     */
    public function getAllAchievementsWithStatusFromDatabase(Character $character): array
    {
        $allAchievements = $this->getDatabaseAchievements();
        $unlockedAchievements = $character->achievements()->pluck('achievement_id')->toArray();
        
        $result = [];
        foreach ($allAchievements as $achievement) {
            $achievementId = $achievement['id'];
            $isUnlocked = in_array($achievementId, $unlockedAchievements);
            $unlockedAt = null;
            
            if ($isUnlocked) {
                $pivot = $character->achievements()->where('achievement_id', $achievementId)->first();
                $unlockedAt = $pivot ? $pivot->pivot->unlocked_at : null;
            }
            
            $result[] = [
                'id' => $achievementId,
                'name' => $achievement['name'],
                'description' => $achievement['description'],
                'category' => $achievement['category'],
                'rarity' => $achievement['rarity'],
                'icon' => $achievement['icon'],
                'points' => $achievement['points'],
                'unlocked' => $isUnlocked,
                'unlocked_at' => $unlockedAt,
            ];
        }
        
        return $result;
    }

    /**
     * Get achievement stats from database.
     */
    public function getAchievementStatsFromDatabase(Character $character): array
    {
        $progress = $this->getAchievementProgressFromDatabase($character);
        
        // Group by category
        $byCategory = [];
        foreach ($progress['achievements'] as $achievement) {
            $category = $achievement['category'];
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = 0;
            }
            $byCategory[$category]++;
        }
        
        // Group by rarity
        $byRarity = [];
        foreach ($progress['achievements'] as $achievement) {
            $rarity = $achievement['rarity'];
            if (!isset($byRarity[$rarity])) {
                $byRarity[$rarity] = 0;
            }
            $byRarity[$rarity]++;
        }
        
        return [
            'total' => $progress['total'],
            'unlocked' => $progress['unlocked'],
            'percentage' => $progress['percentage'],
            'points' => $progress['points'],
            'by_category' => $byCategory,
            'by_rarity' => $byRarity,
        ];
    }

    /**
     * All available achievements based on the game's mechanics
     */
    public function getAllAchievements(): array
    {
        return [
            // === LIFESPAN ACHIEVEMENTS ===
            [
                'id' => 'born',
                'name' => 'A New Beginning',
                'description' => 'Start your life journey',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-baby-carriage',
                'condition' => ['has_started' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'childhood_complete',
                'name' => 'Growing Up',
                'description' => 'Complete your childhood (reach age 10)',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-child-care',
                'condition' => ['min_age' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'teenager',
                'name' => 'Rebel Without a Cause',
                'description' => 'Become a teenager (reach age 13)',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-account-school',
                'condition' => ['age_group' => 'teenager'],
                'unlock_once' => true
            ],
            [
                'id' => 'adult',
                'name' => 'Adulthood Begins',
                'description' => 'Become an adult (reach age 18)',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-star',
                'condition' => ['age_group' => 'adult'],
                'unlock_once' => true
            ],
            [
                'id' => 'middle_age',
                'name' => 'Mid-Life Crisis?',
                'description' => 'Reach age 35 - middle age begins',
                'category' => self::CATEGORY_LIFESPAN,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-account-clock',
                'condition' => ['min_age' => 35],
                'unlock_once' => true
            ],


            // === CAREER ACHIEVEMENTS ===
            [
                'id' => 'first_job',
                'name' => 'Career Beginnings',
                'description' => 'Start your professional journey',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-briefcase-outline',
                'condition' => ['has_profession' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'career_promotion',
                'name' => 'Moving Up',
                'description' => 'Get your first promotion',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-arrow-up-bold',
                'condition' => ['has_promotion' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'senior_employee',
                'name' => 'Senior Employee',
                'description' => 'Reach senior level in your career',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-tie',
                'condition' => ['career_level' => 'senior'],
                'unlock_once' => true
            ],
            [
                'id' => 'manager',
                'name' => 'The Boss',
                'description' => 'Become a manager',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-account-tie-hat',
                'condition' => ['career_level' => 'manager'],
                'unlock_once' => true
            ],
            [
                'id' => 'executive',
                'name' => 'Executive',
                'description' => 'Reach executive level - the top!',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-star',
                'condition' => ['career_level' => 'executive'],
                'unlock_once' => true
            ],
            [
                'id' => 'retired',
                'name' => 'Retirement',
                'description' => 'Retire from your career',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-palm-tree',
                'condition' => ['career_level' => 'retired'],
                'unlock_once' => true
            ],
            [
                'id' => 'career_struggle',
                'name' => 'Career Struggle',
                'description' => 'Experience unemployment',
                'category' => self::CATEGORY_CAREER,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-off',
                'condition' => ['was_unemployed' => true],
                'unlock_once' => true
            ],

            // === WEALTH ACHIEVEMENTS (Removed - money-related) ===

            // === HEALTH ACHIEVEMENTS ===
            [
                'id' => 'perfect_health',
                'name' => 'Peak Health',
                'description' => 'Reach 100% health',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-heart',
                'condition' => ['min_health' => 100],
                'unlock_once' => true
            ],
            [
                'id' => 'healthy_streak',
                'name' => 'Healthy Streak',
                'description' => 'Maintain 80%+ health for 10 years',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-heart-pulse',
                'condition' => ['health_streak_years' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'survivor',
                'name' => 'Survivor',
                'description' => 'Recover from poor health',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-hospital-box',
                'condition' => ['recovered_from_poor_health' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'burnout',
                'name' => 'Burned Out',
                'description' => 'Experience career burnout',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-fire',
                'condition' => ['had_burnout' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'addiction',
                'name' => 'Temptation',
                'description' => 'Experience addiction',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-bottle-tonic-plus',
                'condition' => ['had_addiction' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'recovered',
                'name' => 'Recovery',
                'description' => 'Overcome addiction',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-medical-bag',
                'condition' => ['recovered_from_addiction' => true],
                'unlock_once' => true
            ],

            // === HAPPINESS ACHIEVEMENTS ===
            [
                'id' => 'blissful',
                'name' => 'Blissful',
                'description' => 'Reach 100% happiness',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-emoticon',
                'condition' => ['min_happiness' => 100],
                'unlock_once' => true
            ],
            [
                'id' => 'happy_streak',
                'name' => 'Joyful Life',
                'description' => 'Maintain 80%+ happiness for 10 years',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-emoticon-happy',
                'condition' => ['happiness_streak_years' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'depressed',
                'name' => 'Dark Times',
                'description' => 'Experience very low happiness',
                'category' => self::CATEGORY_HEALTH,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-emoticon-sad',
                'condition' => ['had_low_happiness' => true],
                'unlock_once' => true
            ],

            // === RELATIONSHIP ACHIEVEMENTS ===
            [
                'id' => 'first_date',
                'name' => 'First Date',
                'description' => 'Go on your first date',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-heart-outline',
                'condition' => ['has_dated' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'dating',
                'name' => 'In a Relationship',
                'description' => 'Enter a relationship',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-heart-multiple',
                'condition' => ['relationship_status' => 'dating'],
                'unlock_once' => true
            ],
            [
                'id' => 'engaged',
                'name' => 'Engaged',
                'description' => 'Get engaged',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-ring',
                'condition' => ['relationship_status' => 'engaged'],
                'unlock_once' => true
            ],
            [
                'id' => 'married',
                'name' => 'Married',
                'description' => 'Get married',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-human-male-female',
                'condition' => ['relationship_status' => 'married'],
                'unlock_once' => true
            ],
            [
                'id' => 'divorced',
                'name' => 'Divorce',
                'description' => 'Experience divorce',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-heart-break',
                'condition' => ['was_divorced' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'widowed',
                'name' => 'Lost Love',
                'description' => 'Lose your spouse',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-candle',
                'condition' => ['was_widowed' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'romance_master',
                'name' => 'Romance Master',
                'description' => 'Have multiple relationships',
                'category' => self::CATEGORY_RELATIONSHIP,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-heart-broken',
                'condition' => ['relationship_count' => 3],
                'unlock_once' => true
            ],

            // === SOCIAL ACHIEVEMENTS ===
            [
                'id' => 'social_butterfly',
                'name' => 'Social Butterfly',
                'description' => 'Make 5 social connections',
                'category' => self::CATEGORY_SOCIAL,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-account-group',
                'condition' => ['social_connections_count' => 5],
                'unlock_once' => true
            ],
            [
                'id' => 'popular',
                'name' => 'Popular',
                'description' => 'Make 10 social connections',
                'category' => self::CATEGORY_SOCIAL,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-star-circle',
                'condition' => ['social_connections_count' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'social_legend',
                'name' => 'Social Legend',
                'description' => 'Make 20 social connections',
                'category' => self::CATEGORY_SOCIAL,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-account-star',
                'condition' => ['social_connections_count' => 20],
                'unlock_once' => true
            ],

            // === SKILLS ACHIEVEMENTS ===
            [
                'id' => 'first_skill',
                'name' => 'Learner',
                'description' => 'Learn your first skill',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-school',
                'condition' => ['skills_count' => 1],
                'unlock_once' => true
            ],
            [
                'id' => 'skilled',
                'name' => 'Skilled',
                'description' => 'Learn 5 skills',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-certificate',
                'condition' => ['skills_count' => 5],
                'unlock_once' => true
            ],
            [
                'id' => 'expert',
                'name' => 'Expert',
                'description' => 'Learn 10 skills',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-medal',
                'condition' => ['skills_count' => 10],
                'unlock_once' => true
            ],
            [
                'id' => 'master',
                'name' => 'Master',
                'description' => 'Learn 20 skills',
                'category' => self::CATEGORY_SKILLS,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-trophy',
                'condition' => ['skills_count' => 20],
                'unlock_once' => true
            ],

            // === DECISIONS / EVENTS ACHIEVEMENTS ===
            [
                'id' => 'first_choice',
                'name' => 'Decision Maker',
                'description' => 'Make your first decision',
                'category' => self::CATEGORY_MILESTONE,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-checkbox-marked-circle',
                'condition' => ['total_decisions' => 1],
                'unlock_once' => true
            ],
            [
                'id' => 'decision_maker',
                'name' => 'Decision Maker',
                'description' => 'Make 50 decisions',
                'category' => self::CATEGORY_MILESTONE,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-vote',
                'condition' => ['total_decisions' => 50],
                'unlock_once' => true
            ],
            [
                'id' => 'life_explorer',
                'name' => 'Life Explorer',
                'description' => 'Make 100 decisions',
                'category' => self::CATEGORY_MILESTONE,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-compass',
                'condition' => ['total_decisions' => 100],
                'unlock_once' => true
            ],
            [
                'id' => 'veteran',
                'name' => 'Veteran',
                'description' => 'Make 200 decisions',
                'category' => self::CATEGORY_MILESTONE,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-history',
                'condition' => ['total_decisions' => 200],
                'unlock_once' => true
            ],

            // === LUCK ACHIEVEMENTS ===
            [
                'id' => 'lucky',
                'name' => 'Lucky',
                'description' => 'Have luck above 80',
                'category' => self::CATEGORY_LUCK,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-clover',
                'condition' => ['min_luck' => 80],
                'unlock_once' => true
            ],
            [
                'id' => 'unlucky',
                'name' => 'Unlucky',
                'description' => 'Have luck below 20',
                'category' => self::CATEGORY_LUCK,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-minecraft',
                'condition' => ['max_luck' => 20],
                'unlock_once' => true
            ],
            [
                'id' => 'balanced_karma',
                'name' => 'Balanced Karma',
                'description' => 'Have karma between 40 and 60',
                'category' => self::CATEGORY_LUCK,
                'rarity' => self::RARITY_COMMON,
                'icon' => 'mdi-scale-balance',
                'condition' => ['karma_range' => [40, 60]],
                'unlock_once' => true
            ],
            [
                'id' => 'good_karma',
                'name' => 'Good Karma',
                'description' => 'Have karma above 80',
                'category' => self::CATEGORY_LUCK,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-angel',
                'condition' => ['min_karma' => 80],
                'unlock_once' => true
            ],
            [
                'id' => 'bad_karma',
                'name' => 'Bad Karma',
                'description' => 'Have karma below 20',
                'category' => self::CATEGORY_LUCK,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-devil',
                'condition' => ['max_karma' => 20],
                'unlock_once' => true
            ],

            // === SPECIAL ACHIEVEMENTS ===
            [
                'id' => 'comeback_kid',
                'name' => 'Comeback Kid',
                'description' => 'Recover from very low stats',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_RARE,
                'icon' => 'mdi-rocket-launch',
                'condition' => ['made_comback' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'rollercoaster',
                'name' => 'Roller Coaster',
                'description' => 'Experience both very high and very low stats',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_EPIC,
                'icon' => 'mdi-rollercoaster',
                'condition' => ['had_extreme_stats' => true],
                'unlock_once' => true
            ],
            [
                'id' => 'perfectionist',
                'name' => 'Perfectionist',
                'description' => 'Max out any stat to 100',
                'category' => self::CATEGORY_SPECIAL,
                'rarity' => self::RARITY_UNCOMMON,
                'icon' => 'mdi-star-circle-outline',
                'condition' => ['maxed_stat' => true],
                'unlock_once' => true
            ],
        ];
    }

    /**
     * Check if an achievement is unlocked for a character
     */
    public function isUnlocked(Character $character, array $achievement): bool
    {
        $condition = $achievement['condition'] ?? [];
        
        if (empty($condition)) {
            return false;
        }

        foreach ($condition as $key => $value) {
            if (!$this->checkCondition($character, $key, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check a single condition
     */
    protected function checkCondition(Character $character, string $condition, $value): bool
    {
        $age = $character->current_day ?? 0;
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $characterState = is_array($character->character_state) ? $character->character_state : [];
        
        // Get finance from effective_stats (Wealth) or direct field
        $finance = (int) ($effectiveStats['Wealth'] ?? ($character->finance ?? 20));
        
        // Get health and happiness
        $health = (int) ($hiddenStats['Health'] ?? ($character->health ?? 78));
        $happiness = (int) ($hiddenStats['Happiness'] ?? ($character->happiness ?? 72));
        
        // Get luck and karma
        $luck = (int) ($character->luck ?? 50);
        $karma = (int) ($character->karma ?? 50);
        
        // Get debt from hidden stats
        $debt = (int) ($hiddenStats['Debt'] ?? 0);
        
        // Get burnout and addiction
        $burnout = (int) ($hiddenStats['Burnout'] ?? 0);
        $addiction = (int) ($hiddenStats['Addiction'] ?? 0);
        
        // Get social connections count
        $socialConnections = is_array($character->social_connections) ? $character->social_connections : [];
        $socialConnectionsCount = count($socialConnections);
        
        // Get skills count
        $skillsCount = $character->skills()->count();
        
        // Get career level
        $careerLevel = $character->career_level ?? 'unemployed';
        
        // Get relationship status
        $relationshipStatus = $character->relationship_status ?? 'single';
        
        // Get age group
        $ageGroup = $character->age_group ?? 'child';

        switch ($condition) {
            // Basic conditions
            case 'has_started':
                return true; // Always true if character exists
            
            case 'min_age':
                return $age >= $value;
            
            case 'age_group':
                return $ageGroup === $value;
            
            case 'has_profession':
                return !empty($character->profession) && $character->profession !== 'none';
            
            // Career conditions
            case 'career_level':
                return $this->compareCareerLevel($careerLevel, $value);
            
            case 'has_promotion':
                // Check if career_level is higher than entry level
                $promotionLevels = ['entry', 'junior', 'senior', 'manager', 'executive', 'retired'];
                return in_array($careerLevel, $promotionLevels, true);
            
            case 'was_unemployed':
                // Check decision logs for past unemployment
                return DecisionLog::where('character_id', $character->id)
                    ->where('before_career_level', 'unemployed')
                    ->exists();
            
            // Wealth conditions
            case 'min_finance':
                return $finance >= $value;
            
            case 'max_debt':
                return $debt <= $value;
            
            case 'max_single_spent':
                // Check decision logs for large expenses
                return DecisionLog::where('character_id', $character->id)
                    ->where('finance_change', '<=', -$value)
                    ->exists();
            
            // Health conditions
            case 'min_health':
                return $health >= $value;
            
            case 'health_streak_years':
                // Check if maintained high health for X years
                return $this->checkStatStreak($character, 'Health', $value, 80);
            
            case 'recovered_from_poor_health':
                // Check if health was ever below 30 and is now above 60
                return $this->checkRecovery($character, 'Health', 30, 60);
            
            case 'had_burnout':
                return $burnout > 50 || ($characterState['had_burnout'] ?? false);
            
            case 'had_addiction':
                return $addiction > 50 || ($characterState['had_addiction'] ?? false);
            
            case 'recovered_from_addiction':
                // Check if had high addiction and now it's low
                return $addiction < 20 && ($characterState['had_addiction'] ?? false);
            
            // Happiness conditions
            case 'min_happiness':
                return $happiness >= $value;
            
            case 'happiness_streak_years':
                return $this->checkStatStreak($character, 'Happiness', $value, 80);
            
            case 'had_low_happiness':
                return $happiness < 20 || ($characterState['had_low_happiness'] ?? false);
            
            // Relationship conditions
            case 'relationship_status':
                return $relationshipStatus === $value;
            
            case 'has_dated':
                return in_array($relationshipStatus, ['dating', 'engaged', 'married', 'divorced', 'widowed'], true);
            
            case 'was_divorced':
                return $relationshipStatus === 'divorced' || ($characterState['was_divorced'] ?? false);
            
            case 'was_widowed':
                return $relationshipStatus === 'widowed' || ($characterState['was_widowed'] ?? false);
            
            case 'relationship_count':
                return $this->getRelationshipCount($character) >= $value;
            
            // Social conditions
            case 'social_connections_count':
                return $socialConnectionsCount >= $value;
            
            // Skills conditions
            case 'skills_count':
                return $skillsCount >= $value;
            
            // Decision conditions
            case 'total_decisions':
                return DecisionLog::where('character_id', $character->id)->count() >= $value;
            
            // Luck conditions
            case 'min_luck':
                return $luck >= $value;
            
            case 'max_luck':
                return $luck <= $value;
            
            case 'min_karma':
                return $karma >= $value;
            
            case 'max_karma':
                return $karma <= $value;
            
            case 'karma_range':
                return is_array($value) && $karma >= $value[0] && $karma <= $value[1];
            
            // Special conditions
            case 'made_comback':
                return $this->checkComeback($character);
            
            case 'had_extreme_stats':
                return $this->checkExtremeStats($character);
            
            case 'maxed_stat':
                return $this->checkMaxedStat($character);
            
            default:
                Log::warning("Unknown achievement condition: {$condition}");
                return false;
        }
    }

    /**
     * Compare career levels
     */
    protected function compareCareerLevel(string $current, string $target): bool
    {
        $levels = self::CAREER_LEVELS;
        $currentIndex = array_search($current, $levels, true);
        $targetIndex = array_search($target, $levels, true);
        
        // Handle entrepreneur specially (not in the progression)
        if ($current === 'entrepreneur') {
            return $target === 'entrepreneur' || $targetIndex === false;
        }
        
        return $currentIndex !== false && $targetIndex !== false && $currentIndex >= $targetIndex;
    }

    /**
     * Check if a stat was maintained at a certain level for X years
     */
    protected function checkStatStreak(Character $character, string $stat, int $years, int $threshold): bool
    {
        // Check life stats snapshots for the streak
        $snapshots = \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
            ->orderBy('day', 'desc')
            ->limit($years) // 1 year = 1 day in this game
            ->get();
        
        if ($snapshots->count() < $years) {
            return false;
        }
        
        foreach ($snapshots as $snapshot) {
            $statValue = (int) ($snapshot->$stat ?? 0);
            if ($statValue < $threshold) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if character recovered from a low stat
     */
    protected function checkRecovery(Character $character, string $stat, int $lowThreshold, int $highThreshold): bool
    {
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $currentValue = (int) ($hiddenStats[$stat] ?? 0);
        
        // Must be above high threshold now
        if ($currentValue < $highThreshold) {
            return false;
        }
        
        // Check if there was ever a low value in snapshots or state
        $snapshots = \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
            ->where($stat, '<', $lowThreshold)
            ->exists();
        
        return $snapshots;
    }

    /**
     * Get relationship count (times relationship status changed)
     */
    protected function getRelationshipCount(Character $character): int
    {
        // Count distinct relationship statuses in decision logs
        return DecisionLog::where('character_id', $character->id)
            ->whereNotNull('after_relationship_status')
            ->distinct()
            ->count('after_relationship_status');
    }

    /**
     * Check if character made a comeback from low stats
     */
    protected function checkComeback(Character $character): bool
    {
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        
        // Check if any major stat was very low and is now high
        $statsToCheck = [
            'Health' => $hiddenStats,
            'Happiness' => $hiddenStats,
            'Wealth' => $effectiveStats
        ];
        
        foreach ($statsToCheck as $stat => $statsArray) {
            $currentValue = (int) ($statsArray[$stat] ?? 0);
            if ($currentValue >= 70) {
                // Check if it was ever below 30
                $wasLow = \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
                    ->where($stat, '<', 30)
                    ->exists();
                if ($wasLow) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if character had extreme stats (both high and low)
     */
    protected function checkExtremeStats(Character $character): bool
    {
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        
        $statsToCheck = [
            'Health' => $hiddenStats,
            'Happiness' => $hiddenStats,
            'Wealth' => $effectiveStats
        ];
        
        $hadHigh = false;
        $hadLow = false;
        
        foreach ($statsToCheck as $stat => $statsArray) {
            $currentValue = (int) ($statsArray[$stat] ?? 0);
            
            // Check history
            $hasHigh = \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
                ->where($stat, '>=', 80)
                ->exists();
            $hasLow = \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
                ->where($stat, '<=', 20)
                ->exists();
            
            if ($hasHigh) $hadHigh = true;
            if ($hasLow) $hadLow = true;
            
            if ($hadHigh && $hadLow) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if any stat was maxed to 100
     */
    protected function checkMaxedStat(Character $character): bool
    {
        $hiddenStats = is_array($character->hidden_stats) ? $character->hidden_stats : [];
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        
        $statsToCheck = array_merge($hiddenStats, $effectiveStats);
        
        foreach ($statsToCheck as $stat => $value) {
            if ((int) $value >= 100) {
                return true;
            }
        }
        
        // Also check snapshots
        return \App\Models\LifeStatsSnapshot::where('character_id', $character->id)
            ->where(function ($query) {
                $query->where('Health', '>=', 100)
                    ->orWhere('Happiness', '>=', 100)
                    ->orWhere('Wealth', '>=', 100);
            })
            ->exists();
    }

    /**
     * Get all unlocked achievements for a character
     */
    public function getUnlockedAchievements(Character $character): array
    {
        $unlocked = [];
        $achievements = $this->getAllAchievements();
        
        foreach ($achievements as $achievement) {
            if ($this->isUnlocked($character, $achievement)) {
                $unlocked[] = $achievement;
            }
        }
        
        return $unlocked;
    }

    /**
     * Get achievement progress for a character
     */
    public function getAchievementProgress(Character $character): array
    {
        $allAchievements = $this->getAllAchievements();
        $unlockedAchievements = $this->getUnlockedAchievements($character);
        
        return [
            'total' => count($allAchievements),
            'unlocked' => count($unlockedAchievements),
            'percentage' => count($allAchievements) > 0 
                ? round((count($unlockedAchievements) / count($allAchievements)) * 100, 1) 
                : 0,
            'achievements' => $unlockedAchievements
        ];
    }

    /**
     * Get character achievements (alias for getUnlockedAchievements)
     */
    public function getCharacterAchievements(Character $character): array
    {
        return $this->getUnlockedAchievements($character);
    }

    /**
     * Get achievement stats for a character
     */
    public function getAchievementStats(Character $character): array
    {
        $progress = $this->getAchievementProgress($character);
        
        // Group by category
        $byCategory = [];
        foreach ($progress['achievements'] as $achievement) {
            $category = $achievement['category'];
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = 0;
            }
            $byCategory[$category]++;
        }
        
        // Group by rarity
        $byRarity = [];
        foreach ($progress['achievements'] as $achievement) {
            $rarity = $achievement['rarity'];
            if (!isset($byRarity[$rarity])) {
                $byRarity[$rarity] = 0;
            }
            $byRarity[$rarity]++;
        }
        
        return [
            'total' => $progress['total'],
            'unlocked' => $progress['unlocked'],
            'percentage' => $progress['percentage'],
            'by_category' => $byCategory,
            'by_rarity' => $byRarity
        ];
    }

    /**
     * Check and unlock achievements (returns newly unlocked ones)
     */
    public function checkAndUnlockAchievements(Character $character): array
    {
        $newlyUnlocked = [];
        $allAchievements = $this->getAllAchievements();
        
        foreach ($allAchievements as $achievement) {
            if ($this->isUnlocked($character, $achievement)) {
                $newlyUnlocked[] = $achievement;
            }
        }
        
        return $newlyUnlocked;
    }

    /**
     * Get all achievements with their unlocked status for a character
     */
    public function getAllAchievementsWithStatus(Character $character): array
    {
        $allAchievements = $this->getAllAchievements();
        $unlockedIds = array_column($this->getUnlockedAchievements($character), 'id');
        
        $result = [];
        foreach ($allAchievements as $achievement) {
            $achievement['unlocked'] = in_array($achievement['id'], $unlockedIds, true);
            $result[] = $achievement;
        }
        
        return $result;
    }
}
