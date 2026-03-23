<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Skill;
use App\Models\Talent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CharacterController extends Controller
{
    private const VISIBLE_STAT_DEFAULTS = [
        'Intelligence' => 25,
        'Strength' => 25,
        'Charisma' => 25,
        'Creativity' => 25,
        'Wealth' => 20,
        'Luck' => 20,
        'Social' => 50,
        'Empathy' => 50,
    ];

    private const HIDDEN_STAT_DEFAULTS = [
        'Debt' => 0,
        'Health' => 78,
        'Addiction' => 0,
        'Burnout' => 5,
        'Morality' => 45,
        'Happiness' => 72,
        'Reputation' => 35,
        'Discipline' => 40,
        'Isolation' => 6,
        'Ego' => 10,
    ];

    // Starting age (day) based on age group - unified with EventService
    private const START_AGE = [
        'child' => 1,
        'teenager' => 10,
        'adult' => 18,
        'old' => 60,
    ];

    // Maximum age (day) based on starting age group
    private const MAX_AGE = [
        'child' => 10,
        'teenager' => 18,
        'adult' => 60,
        'old' => 100,
    ];

    private const AGE_BONUSES = [
        'child' => ['Luck' => 2, 'Creativity' => 2, 'Happiness' => 4],
        'teenager' => ['Strength' => 2, 'Intelligence' => 1, 'Charisma' => 1],
        'adult' => ['Strength' => 3, 'Intelligence' => 2, 'Wealth' => 2, 'Discipline' => 2],
        'old' => ['Strength' => -2, 'Intelligence' => 4, 'Morality' => 3, 'Reputation' => 2],
    ];

    private const GENDER_BONUSES = [
        'male' => ['Strength' => 2],
        'female' => ['Intelligence' => 2],
        'non-binary' => ['Creativity' => 1, 'Luck' => 1],
        'transgender' => ['Charisma' => 1, 'Luck' => 1],
    ];

    /**
     * Get all characters for the authenticated user
     */
    public function index()
    {
        $characters = Character::where('user_id', Auth::id())
            ->with(['skills', 'talents'])
            ->get();
        return response()->json($characters);
    }

    /**
     * Get a specific character
     */
    public function show(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $character->load(['skills', 'talents']);
        
        // Log for debugging
        Log::info('Character show response:', [
            'id' => $character->id,
            'stats' => $character->stats,
            'hidden_stats' => $character->hidden_stats,
            'effective_stats' => $character->effective_stats,
            'skills' => $character->skills->pluck('name')->toArray(),
            'talents' => $character->talents->pluck('name')->toArray(),
        ]);
        
        return response()->json($character);
    }

    /**
     * Store a newly created character
     */
    public function store(Request $request)
    {
        Log::info('Character creation request:', $request->all());

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_group' => 'required|string|in:child,teenager,adult,old',
            'gender' => 'required|string|in:male,female,non-binary,transgender',
            'stats' => 'required|array',
            'hidden_stats' => 'required|array',
            'skills' => 'array',
            'talents' => 'array',
            'effective_stats' => 'array',
        ]);

        Log::info('Validated data:', $validated);
        Log::info('Auth ID:', ['user_id' => Auth::id()]);

        try {
            // Determine default profile picture based on age_group and gender
            $genderKey = $validated['gender'];
            // Map gender to image filename format
            $genderMap = [
                'male' => 'male',
                'female' => 'female',
                'non-binary' => 'male', // default to male for non-binary
                'transgender' => 'male', // default to male for transgender
            ];
            $genderSuffix = $genderMap[$genderKey] ?? 'male';
            
            // Map age_group to image filename format
            $ageGroupMap = [
                'child' => 'child',
                'teenager' => 'teenage',
                'adult' => 'adult',
                'old' => 'old',
            ];
            $ageGroupPrefix = $ageGroupMap[$validated['age_group']] ?? 'child';
            
            $defaultImage = "/css/images/profilepicnormal/{$ageGroupPrefix}-{$genderSuffix}.png";
            
            $skillSelection = $this->resolveSelectedModels($validated['skills'] ?? [], Skill::class);
            $talentSelection = $this->resolveSelectedModels($validated['talents'] ?? [], Talent::class);
            $statState = $this->buildCharacterStatState(
                $validated,
                $skillSelection['models'],
                $talentSelection['models']
            );

            $character = Character::create([
                'user_id' => $userId,
                'name' => $validated['name'],
                'age_group' => $validated['age_group'],
                'gender' => $validated['gender'],
                'current_day' => self::START_AGE[$validated['age_group']] ?? 1,
                'stats' => $statState['stats'],
                'hidden_stats' => $statState['hidden_stats'],
                'effective_stats' => $statState['effective_stats'],
                'character_state' => $statState['character_state'],
                'health' => $statState['health'],
                'happiness' => $statState['happiness'],
                'finance' => $statState['finance'],
                'image' => $defaultImage,
            ]);

            // Create initial personality profile based on stats
            $this->createInitialPersonalityProfile($character, $statState['stats'], $statState['hidden_stats']);

            Log::info('Character created with start_day', [
                'id' => $character->id,
                'age_group' => $character->age_group,
                'current_day' => $character->current_day
            ]);

            Log::info('Character created with stats:', [
                'id' => $character->id,
                'stats' => $character->stats,
                'hidden_stats' => $character->hidden_stats,
                'effective_stats' => $character->effective_stats,
            ]);

            // Handle skills - look up by name if strings are provided
            $skillIds = $skillSelection['ids'];
            if (!empty($skillIds)) {
                $character->skills()->attach(array_unique($skillIds));
            }

            // Handle talents - look up by name if strings are provided
            $talentIds = $talentSelection['ids'];
            if (!empty($talentIds)) {
                $character->talents()->attach(array_unique($talentIds));
            }

            // Reload the character with skills and talents
            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Character created successfully',
                'character' => $character,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Character creation error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error creating character: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildCharacterStatState(array $validated, array $skillModels = [], array $talentModels = []): array
    {
        $stats = self::VISIBLE_STAT_DEFAULTS;
        $hiddenStats = self::HIDDEN_STAT_DEFAULTS;

        $rawStats = is_array($validated['stats'] ?? null) ? $validated['stats'] : [];
        foreach (['Intelligence', 'Strength', 'Charisma', 'Creativity', 'Wealth', 'Luck'] as $stat) {
            $stats[$stat] = $this->clampStat(($stats[$stat] ?? 0) + (int) ($rawStats[$stat] ?? 0));
        }

        $this->applyNamedEffects($stats, $hiddenStats, self::AGE_BONUSES[$validated['age_group']] ?? []);
        $this->applyNamedEffects($stats, $hiddenStats, self::GENDER_BONUSES[$validated['gender']] ?? []);

        foreach (array_merge($skillModels, $talentModels) as $model) {
            $this->applyEffectString($stats, $hiddenStats, $model->effect ?? null);
            $this->applyEffectString($stats, $hiddenStats, $model->hidden ?? null);
        }

        $stats['Social'] = $this->clampStat((int) round(
            45
            + (($stats['Charisma'] ?? 25) - 25) * 0.6
            + (($hiddenStats['Reputation'] ?? 35) - 35) * 0.15
        ));

        $stats['Empathy'] = $this->clampStat((int) round(
            40
            + (($hiddenStats['Morality'] ?? 45) - 45) * 0.4
            + (($stats['Intelligence'] ?? 25) - 25) * 0.2
        ));

        $effectiveStats = array_merge($hiddenStats, $stats);

        return [
            'stats' => $stats,
            'hidden_stats' => $hiddenStats,
            'effective_stats' => $effectiveStats,
            'health' => (int) ($effectiveStats['Health'] ?? 78),
            'happiness' => (int) ($effectiveStats['Happiness'] ?? 72),
            'finance' => (int) ($effectiveStats['Wealth'] ?? 20),
            'character_state' => [
                'life_stage' => $validated['age_group'],
                'profession_state' => 'unemployed',
                'relationship_status' => 'single',
                'health_condition' => app(\App\Services\EventService::class)->getHealthStatus((int) ($effectiveStats['Health'] ?? 78)),
                'is_dead' => false,
                'max_age' => self::MAX_AGE[$validated['age_group']] ?? 50,
                'starting_age_group' => $validated['age_group'],
            ],
        ];
    }

    private function resolveSelectedModels(array $items, string $modelClass): array
    {
        $ids = [];
        $models = [];

        foreach ($items as $item) {
            $model = null;

            if (is_numeric($item)) {
                $model = $modelClass::find((int) $item);
            } elseif (is_string($item)) {
                $model = $modelClass::where('name', $item)->first();
            } elseif (is_array($item)) {
                $identifier = $item['name'] ?? ($item['id'] ?? null);
                if (is_numeric($identifier)) {
                    $model = $modelClass::find((int) $identifier);
                } elseif (is_string($identifier)) {
                    $model = $modelClass::where('name', $identifier)->first();
                }
            }

            if (!$model) {
                continue;
            }

            $ids[] = (int) $model->id;
            $models[(int) $model->id] = $model;
        }

        return [
            'ids' => array_values(array_unique($ids)),
            'models' => array_values($models),
        ];
    }

    private function applyEffectString(array &$stats, array &$hiddenStats, ?string $effectsText): void
    {
        $effects = app(\App\Services\EventService::class)->parseStatEffects($effectsText);
        $this->applyNamedEffects($stats, $hiddenStats, $effects);
    }

    private function applyNamedEffects(array &$stats, array &$hiddenStats, array $effects): void
    {
        foreach ($effects as $stat => $value) {
            $value = (int) $value;

            if (array_key_exists($stat, $hiddenStats)) {
                $hiddenStats[$stat] = $this->clampStat(($hiddenStats[$stat] ?? 0) + $value);
                continue;
            }

            $stats[$stat] = $this->clampStat(($stats[$stat] ?? 0) + $value);
        }
    }

    /**
     * Create initial personality profile based on character stats
     */
    private function createInitialPersonalityProfile(Character $character, array $stats, array $hiddenStats): void
    {
        // Calculate MBTI dimensions from stats (0-100 scale)
        // E/I: Charisma + Social -> higher = Extraverted
        $energyOrientation = (($stats['Charisma'] ?? 25) + ($stats['Social'] ?? 50)) / 2;
        
        // N/S: Intelligence + Creativity -> higher = Intuitive  
        $informationGathering = (($stats['Intelligence'] ?? 25) + ($stats['Creativity'] ?? 25)) / 2;
        
        // T/F: Empathy + Morality -> higher = Feeling
        $decisionForming = (($stats['Empathy'] ?? 50) + ($hiddenStats['Morality'] ?? 45)) / 2;
        
        // J/P: Discipline + Happiness -> higher = Judging
        $lifestyleApproach = (($hiddenStats['Discipline'] ?? 40) + ($hiddenStats['Happiness'] ?? 72)) / 2;
        
        // Calculate Big Five traits
        $openness = ($stats['Creativity'] ?? 25) + ($stats['Intelligence'] ?? 25);
        $conscientiousness = ($hiddenStats['Discipline'] ?? 40) + ($hiddenStats['Ego'] ?? 10);
        $extraversion = ($stats['Charisma'] ?? 25) + ($stats['Social'] ?? 50);
        $agreeableness = ($stats['Empathy'] ?? 50) + ($hiddenStats['Morality'] ?? 45);
        $neuroticism = ($hiddenStats['Addiction'] ?? 0) + ($hiddenStats['Burnout'] ?? 5);
        
        // Determine MBTI type
        $mbti = '';
        $mbti .= $energyOrientation >= 50 ? 'E' : 'I';
        $mbti .= $informationGathering >= 50 ? 'N' : 'S';
        $mbti .= $decisionForming >= 50 ? 'T' : 'F';
        $mbti .= $lifestyleApproach >= 50 ? 'J' : 'P';
        
        \App\Models\PersonalityProfile::create([
            'character_id' => $character->id,
            'energy_orientation' => (int) $energyOrientation,
            'information_gathering' => (int) $informationGathering,
            'decision_forming' => (int) $decisionForming,
            'lifestyle_approach' => (int) $lifestyleApproach,
            'openness' => min(100, (int) $openness),
            'conscientiousness' => min(100, (int) $conscientiousness),
            'extraversion' => min(100, (int) $extraversion),
            'agreeableness' => min(100, (int) $agreeableness),
            'neuroticism' => min(100, (int) $neuroticism),
            'social_boldness_score' => (int) $extraversion,
            'emotional_stability_score' => 100 - min(100, (int) $neuroticism),
            'agreeableness_score' => (int) $agreeableness,
            'conscientiousness_score' => min(100, (int) $conscientiousness),
            'openness_score' => min(100, (int) $openness),
            'current_mbti' => $mbti,
            'mbti_confidence' => 30, // Initial confidence is low
            'decision_patterns' => [],
            'recent_social_decisions' => [],
            'recent_career_decisions' => [],
            'recent_relationship_decisions' => [],
            'recent_moral_decisions' => [],
        ]);
        
        Log::info('Personality profile created', [
            'character_id' => $character->id,
            'mbti' => $mbti,
        ]);
    }

    private function clampStat(int $value): int
    {
        return max(0, min(100, $value));
    }

    /**
     * Update an existing character
     */
    public function update(Request $request, Character $character)
    {
        // Check if user owns this character
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'age_group' => 'string|in:child,teenager,adult,old',
            'gender' => 'string|in:male,female,non-binary,transgender',
            'stats' => 'array',
            'hidden_stats' => 'array',
            'effective_stats' => 'array',
'current_day' => 'integer|min:1',
            'start_day' => 'integer|min:1|max:120',
            'image' => 'string|max:500',
        ]);

        try {
            // Check if age_group or gender changed and update profile picture accordingly
            $oldAgeGroup = $character->age_group;
            $oldGender = $character->gender;
            
            $character->update($validated);
            
            // Update profile picture if age_group or gender changed and no custom image is set
            $newAgeGroup = $character->age_group;
            $newGender = $character->gender;
            
            if (($oldAgeGroup !== $newAgeGroup || $oldGender !== $newGender) && 
                (empty($character->image) || $character->image === '/css/images/player.jpg')) {
                $genderMap = [
                    'male' => 'male',
                    'female' => 'female',
                    'non-binary' => 'male',
                    'transgender' => 'male',
                ];
                $genderSuffix = $genderMap[$newGender] ?? 'male';
                
                $ageGroupMap = [
                    'child' => 'child',
                    'teenager' => 'teenage',
                    'adult' => 'adult',
                    'old' => 'old',
                ];
                $ageGroupPrefix = $ageGroupMap[$newAgeGroup] ?? 'adult';
                
                $character->image = "/css/images/profilepicnormal/{$ageGroupPrefix}-{$genderSuffix}.png";
                $character->save();
            }

            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Character updated successfully',
                'character' => $character,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Character update error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error updating character: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload character image
     */
    public function uploadImage(Request $request, Character $character)
    {
        // Check if user owns this character
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => 'string|max:255',
        ]);

        try {
            // Store the image
            $imagePath = $request->file('image')->store('character-images', 'public');
            $imageUrl = '/storage/' . $imagePath;

            // Update character with new image and name if provided
            $updateData = ['image' => $imageUrl];
            if ($request->has('name') && $request->name) {
                $updateData['name'] = $request->name;
            }
            $character->update($updateData);

            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'character' => $character,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Image upload error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get decision logs (memory) for character
     */
    public function decisionLogs(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = \App\Models\SharedDecisionLog::where('anon_character_id', $character->anon_character_id)
            ->latest('created_at')
            ->take(50)
            ->get();

        $formattedLogs = $logs->map(function ($log) {
            $data = $log->data ?? [];
            return [
                'event_title' => data_get($data, 'event_title', $log->event_title ?? 'Unknown Event'),
                'choice_text' => data_get($data, 'choice_text', $log->choice_text ?? 'Unknown Choice'),
                'effects' => data_get($data, 'effects', $log->effects ?? []),
                'created_at' => $log->created_at,
            ];
        });

        return response()->json($formattedLogs);
    }

    /**
     * Get player analytics/stats overview
     */
    public function analytics(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'life_stats' => $this->getLifeStatsHistory($character),
            'career' => $this->getCareerProgression($character),
            'relationships' => $this->getRelationshipTimeline($character),
            'skills' => $this->getSkillsProgression($character),
            'decisions' => $this->getDecisionStats($character),
            'summary' => $this->getStatsSummary($character),
            'personality' => $this->getPersonalityProfile($character),
            'timeline' => $this->getLifeTimeline($character),
        ]);
    }

    /**
     * Get personality profile for player
     */
    private function getPersonalityProfile(Character $character): ?array
    {
        $profile = \App\Models\PersonalityProfile::where('character_id', $character->id)->first();
        
        if (!$profile) {
            return null;
        }

        return [
            'mbti' => $profile->current_mbti,
            'big_five' => [
                'openness' => $profile->openness,
                'conscientiousness' => $profile->conscientiousness,
                'extraversion' => $profile->extraversion,
                'agreeableness' => $profile->agreeableness,
                'neuroticism' => $profile->neuroticism,
            ],
            'dimensions' => [
                'energy_orientation' => $profile->energy_orientation,
                'information_gathering' => $profile->information_gathering,
                'decision_forming' => $profile->decision_forming,
                'lifestyle_approach' => $profile->lifestyle_approach,
            ],
            'confidence' => $profile->mbti_confidence,
        ];
    }

    /**
     * Get life events timeline
     */
    private function getLifeTimeline(Character $character): array
    {
        $events = \App\Models\DecisionLog::where('character_id', $character->id)
            ->orderBy('day', 'desc')
            ->limit(20)
            ->get(['day', 'event_title', 'choice_text', 'category', 'outcome']);

        return $events->map(fn($e) => [
            'day' => $e->day,
            'title' => $e->event_title,
            'choice' => $e->choice_text,
            'category' => $e->category,
            'outcome' => $e->outcome,
        ]);
    }

    /**
     * Get life stats history over time
     */
    private function getLifeStatsHistory(Character $character): array
    {
        $snapshots = \App\Models\LifeStatsSnapshot::where('anon_character_id', $character->anon_character_id)
            ->orderBy('day', 'asc')
            ->get(['day', 'health', 'happiness', 'finance', 'career_level', 'profession']);

        return [
            'current' => [
                'day' => $character->current_day,
                'health' => $character->health,
                'happiness' => $character->happiness,
                'finance' => $character->finance,
                'career_level' => $character->career_level,
                'profession' => $character->profession,
            ],
            'history' => $snapshots->map(fn($s) => [
                'day' => $s->day,
                'health' => $s->health,
                'happiness' => $s->happiness,
                'finance' => $s->finance,
                'career_level' => $s->career_level,
            ]),
            'averages' => [
                'health' => round($snapshots->avg('health') ?? $character->health, 1),
                'happiness' => round($snapshots->avg('happiness') ?? $character->happiness, 1),
                'finance' => round($snapshots->avg('finance') ?? $character->finance, 1),
            ],
        ];
    }

    /**
     * Get career progression timeline
     */
    private function getCareerProgression(Character $character): array
    {
        // Get career changes from decision logs
        $careerLogs = \App\Models\DecisionLog::where('character_id', $character->id)
            ->whereNotNull('after_profession')
            ->orderBy('day', 'asc')
            ->get(['day', 'before_profession', 'after_profession', 'event_title']);

        return [
            'current' => [
                'profession' => $character->profession,
                'level' => $character->career_level,
            ],
            'history' => $careerLogs->map(fn($log) => [
                'day' => $log->day,
                'from' => $log->before_profession,
                'to' => $log->after_profession,
                'event' => $log->event_title,
            ]),
            'total_jobs' => $careerLogs->count(),
        ];
    }

    /**
     * Get relationship timeline
     */
    private function getRelationshipTimeline(Character $character): array
    {
        // Get relationship changes from decision logs
        $relationshipLogs = \App\Models\DecisionLog::where('character_id', $character->id)
            ->whereNotNull('after_relationship_status')
            ->orderBy('day', 'asc')
            ->get(['day', 'before_relationship_status', 'after_relationship_status', 'event_title']);

        return [
            'current' => [
                'status' => $character->relationship_status,
            ],
            'history' => $relationshipLogs->map(fn($log) => [
                'day' => $log->day,
                'from' => $log->before_relationship_status,
                'to' => $log->after_relationship_status,
                'event' => $log->event_title,
            ]),
            'total_relationships' => $relationshipLogs->count(),
        ];
    }

    /**
     * Get skills progression
     */
    private function getSkillsProgression(Character $character): array
    {
        $skills = $character->skills()->get();
        
        return [
            'current' => $skills->pluck('name'),
            'count' => $skills->count(),
            'by_category' => $skills->groupBy('category')->map(fn($g) => $g->pluck('name')),
        ];
    }

    /**
     * Get decision statistics
     */
    private function getDecisionStats(Character $character): array
    {
        $logs = \App\Models\DecisionLog::where('character_id', $character->id)->get();
        
        return [
            'total' => $logs->count(),
            'by_type' => $logs->groupBy('decision_type')->map(fn($g) => $g->count()),
            'by_category' => $logs->groupBy('category')->map(fn($g) => $g->count()),
        ];
    }

    /**
     * Get overall stats summary
     */
    private function getStatsSummary(Character $character): array
    {
        $achievementService = app(\App\Services\AchievementService::class);
        
        // Get achievements - database or fallback
        if ($achievementService->hasDatabaseAchievements()) {
            $achievements = $achievementService->getAchievementProgressFromDatabase($character);
        } else {
            $achievements = $achievementService->getAchievementProgress($character);
        }

        return [
            'character' => [
                'name' => $character->name,
                'age' => $character->current_day, // In this game, day = age
                'age_group' => $character->age_group,
                'gender' => $character->gender,
                'days_played' => $character->current_day,
            ],
            'achievements' => [
                'total' => $achievements['total'] ?? 0,
                'unlocked' => $achievements['unlocked'] ?? 0,
                'percentage' => $achievements['percentage'] ?? 0,
                'points' => $achievements['points'] ?? 0,
            ],
            'stats' => [
                'health' => $character->health,
                'happiness' => $character->happiness,
                'finance' => $character->finance,
            ],
            'visible_stats' => $character->stats,
            'hidden_stats' => $character->hidden_stats,
        ];
    }
}
