<?php
namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\SharedDecisionLog;
use App\Models\LifeStatsSnapshot;
use App\Models\DecisionLog;
use App\Models\DailyAction;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\AgeSpecificEvent;
use App\Models\ProfessionPathEvent;
use App\Models\ProfessionTrigger;
use App\Models\StatTriggerCondition;
use App\Models\User;
use App\Support\Privacy;
use App\Services\AdaptiveNarrativeService;
use App\Services\EventService;
use App\Services\MiniGameService;
use App\Services\LuckService;
use App\Services\AchievementService;
use App\Services\LifeSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class EventController extends Controller
{
    protected EventService $eventService;
    protected AdaptiveNarrativeService $adaptiveNarrativeService;
    protected MiniGameService $miniGameService;
    protected LuckService $luckService;
    protected AchievementService $achievementService;

    // Age progression thresholds - unified with EventService
    // Uses age (not day) based on EventService::getAgeGroupFromAge()
    const AGE_GROUPS = [
        'child' => ['min' => 0, 'max' => 9],       // Age 0-9
        'teenager' => ['min' => 10, 'max' => 17],  // Age 10-17
        'adult' => ['min' => 18, 'max' => 59],     // Age 18-59
        'old' => ['min' => 60, 'max' => 100],     // Age 60+
    ];

    // Days per year for age calculation
    const DAYS_PER_YEAR = 1; // 1 day = 1 year for game simplicity

    // Default maximum days in game (fallback)
    const DEFAULT_MAX_DAYS = 50;

    // Maximum age (day) per age group - must match CharacterController
    const MAX_AGE_BY_GROUP = [
        'child' => 10,
        'teenager' => 18,
        'adult' => 60,
        'old' => 100,
    ];

    // Get max days based on character's starting age group
    private function getMaxDays(Character $character): int
    {
        $state = is_array($character->character_state) ? $character->character_state : [];
        return $state['max_age'] ?? self::DEFAULT_MAX_DAYS;
    }

    public function __construct(EventService $eventService, AdaptiveNarrativeService $adaptiveNarrativeService, MiniGameService $miniGameService, LuckService $luckService, AchievementService $achievementService)
    {
        $this->eventService = $eventService;
        $this->adaptiveNarrativeService = $adaptiveNarrativeService;
        $this->miniGameService = $miniGameService;
        $this->luckService = $luckService;
        $this->achievementService = $achievementService;
    }

    /**
     * Calculate age from current day
     */
    public function calculateAge(int $currentDay): int
    {
        return $this->eventService->calculateAge($currentDay);
    }

    /**
     * Get age group from age
     */
    public function getAgeGroupFromAge(int $age): string
    {
        return $this->eventService->getAgeGroupFromAge($age);
    }

    /**
     * End the current day - call this when user is done making choices
     */
    public function endDay(Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($this->eventService->isDead($character)) {
                return response()->json($this->buildTerminalStatePayload($character));
            }

            // Advance the day
            $character->current_day = ($character->current_day ?? 1) + 1;
            $character->save();

            // Apply age progression
            $this->checkAndApplyAgeProgression($character);

            // Apply deterministic ongoing consequences as time passes
            $this->eventService->applyTimePassage($character, 1);

            // Update health condition based on new state
            $this->eventService->updateHealthCondition($character);

            // Evaluate major consequence thresholds (bankruptcy/cancer/disability/etc.)
            $this->eventService->checkSevereConsequences($character, []);

            // Check if game is over
            $gameOver = false;
            $endingType = null;

            if ($character->current_day >= $this->getMaxDays($character)) {
                $gameOver = true;
                $endingType = $this->determineEndingType($character, 'old_age');
            }

            $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
            $health = isset($effectiveStats['Health']) ? (int)$effectiveStats['Health'] : 100;
            if ($health <= 0) {
                $this->eventService->markCharacterDeath($character, 'health_collapse');
                $gameOver = true;
                $endingType = $this->determineEndingType($character, 'death');
            }

            $response = [
                'current_day' => $character->current_day,
                'age' => $this->calculateAge($character->current_day),
                'age_group' => $character->age_group,
                'game_over' => $gameOver,
            ];

            if ($gameOver && $endingType) {
                $endingDetails = $this->getEndingDetails($endingType, $character);
                $response['ending_type'] = $endingType;
                $response['ending_title'] = $endingDetails['title'];
                $response['ending_description'] = $endingDetails['description'];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error in endDay: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Feature 7: Get enhanced life summary from birth to death
     */
    public function getLifeSummary(Character $character)
    {
        try {
            // Allow if user owns character OR character has no user_id (guest character)
            $userId = Auth::id();
            if ($character->user_id && $userId && $character->user_id !== $userId) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Use the comprehensive LifeSummaryService
            $summaryService = app(LifeSummaryService::class);
            $comprehensiveSummary = $summaryService->generateSummary($character);

            // Get ending details
            $endingType = $this->determineEndingType($character, 
                ($character->current_day >= $this->getMaxDays($character)) ? 'old_age' : 'death');
            $endingDetails = $this->getEndingDetails($endingType, $character);

            return response()->json([
                'success' => true,
                'message' => 'Life summary generated',
                // Basic info
                'character_name' => $character->name,
                'character' => $comprehensiveSummary['character'],
                // Lifespan analysis
                'lifespan' => $comprehensiveSummary['lifespan'],
                'lifespan_years' => $comprehensiveSummary['lifespan']['total_years'] ?? ($comprehensiveSummary['character']['lifespan_years'] ?? ($character->current_day ?? 1)),
                // Stats analysis
                'stats_analysis' => $comprehensiveSummary['statsAnalysis'],
                // Decisions analysis
                'decisions_analysis' => $comprehensiveSummary['decisionsAnalysis'],
                'total_decisions' => $comprehensiveSummary['decisionsAnalysis']['total'] ?? 0,
                'story_decisions' => $comprehensiveSummary['decisionsAnalysis']['story_decisions'] ?? 0,
                'daily_actions' => $comprehensiveSummary['decisionsAnalysis']['daily_actions'] ?? 0,
                // Milestones
                'milestones' => $comprehensiveSummary['milestones'],
                // Personality analysis
                'personality' => $comprehensiveSummary['personality'],
                // Life rating
                'life_rating' => $comprehensiveSummary['lifeRating'],
                // Key moments
                'key_moments' => $comprehensiveSummary['keyMoments'],
                'timeline' => $comprehensiveSummary['keyMoments'] ?? [],
                // Life advice
                'advice' => $comprehensiveSummary['advice'],
                // Ending
                'ending_type' => $endingType,
                'ending_title' => $endingDetails['title'] ?? 'Unknown',
                'ending_description' => $endingDetails['description'] ?? '',
                // Unlocked achievements
                'achievements' => $comprehensiveSummary['achievements'],
                // Comparison data
                'comparison' => method_exists($summaryService, 'getComparisonData') 
                    ? $summaryService->getComparisonData($character) 
                    : [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLifeSummary: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extract milestones from decision logs
     */
    private function extractMilestones($decisions): array
    {
        $milestones = [];
        
        foreach ($decisions as $decision) {
            $eventTitle = strtolower($decision->event_title ?? '');
            $choiceText = strtolower($decision->choice_text ?? '');
            
            // Career milestones
            if (str_contains($eventTitle, 'promotion') || str_contains($choiceText, 'promoted')) {
                $milestones[] = [
                    'day' => $decision->day,
                    'age' => $this->calculateAge($decision->day),
                    'type' => 'career',
                    'title' => 'Promoted!',
                    'description' => $decision->choice_text,
                ];
            }
            
            // Marriage milestones
            if (str_contains($eventTitle, 'marriage') || str_contains($choiceText, 'married')) {
                $milestones[] = [
                    'day' => $decision->day,
                    'age' => $this->calculateAge($decision->day),
                    'type' => 'relationship',
                    'title' => 'Got Married!',
                    'description' => $decision->choice_text,
                ];
            }
            
            // Education milestones
            if (str_contains($eventTitle, 'graduation') || str_contains($choiceText, 'graduated')) {
                $milestones[] = [
                    'day' => $decision->day,
                    'age' => $this->calculateAge($decision->day),
                    'type' => 'education',
                    'title' => 'Graduated!',
                    'description' => $decision->choice_text,
                ];
            }
        }
        
        return $milestones;
    }

    /**
     * Get all available events for a character with narrative branching
     */
    public function getAvailableEvents(Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            if ($this->eventService->isDead($character) || ($character->current_day ?? 1) >= $this->getMaxDays($character)) {
                return response()->json($this->buildTerminalStatePayload($character));
            }

            // Check and apply age progression
            $this->checkAndApplyAgeProgression($character);

            // Check if profession choice is needed (but don't auto-assign)
            $professionChoices = $this->getAvailableProfessions($character);
            
            $ageGroup = $character->age_group ?? 'adult';
            $shownEventIds = $character->shown_event_ids ?? [];
            
            Log::info('EventController:getAvailableEvents START', [
                'character_id' => $character->id,
                'raw_age_group' => $character->getRawOriginal('age_group'),
                'current_day' => $character->current_day,
            ]);
            
            // Get narrative-aware events using NarrativeService
            $narrativeService = app(\App\Services\NarrativeService::class);
            $narrativeData = $narrativeService->getNarrativeEvents($character, $ageGroup, $shownEventIds);
            
Log::info('EventController::getAvailableEvents - Narrative events loaded', [
                'character_id' => $character->id,
                'narrativeData_keys' => array_keys((array)$narrativeData)
            ]);
            
            // Get standard events
            $dailyEvents = $this->getDailyEvents($character, $shownEventIds, $ageGroup);
            $culturalEvents = $this->getCulturalEvents($character, $shownEventIds);
            
// FSM State-aware events (replaces branching logic)
            $statefulEvents = $narrativeData ?? ['daily' => collect([]), 'cultural' => collect([]), 'ageSpecific' => collect([]), 'profession' => collect([])];

            // Stat-trigger events (fold into main story deck)
            $triggerEvents = $this->getTriggeredStatEvents($character, $shownEventIds);
            if (!empty($triggerEvents)) {
                $remainingSlots = max(0, 5 - count($triggerEvents));
                $ageSpecific = $statefulEvents['ageSpecific'] ?? collect([]);
                $sliceAgeSpecific = $ageSpecific->slice(0, $remainingSlots)->values()->toArray();
                $statefulEvents['ageSpecific'] = array_merge($triggerEvents, $sliceAgeSpecific);
            }
            
            $milestone = $this->checkMilestone($character);
            
            // Profession choices are returned as their own deck; milestones stay focused on age transitions.

// Separate system actions into skills_to_learn and daily_actions
            $systemActions = $this->getSystemActions($character);
            $skillsToLearn = [];
            $dailyActions = [];
            
            foreach ($systemActions as $action) {
                if (($action['type'] ?? '') === 'learning') {
                    $skillsToLearn[] = $action;
                } else {
                    $dailyActions[] = $action;
                }
            }

            // Get luck events - check if a random luck event should trigger
            $luckEvents = [];
            if ($this->luckService->shouldTriggerLuckEvent($character)) {
                $luckEvent = $this->luckService->generateLuckEvent($character);
                if ($luckEvent) {
                    $luckEvents = [$this->formatLuckEvent($luckEvent, $character)];
                }
            }

            $events = [
                // Separate decks (same interaction model as life actions)
                'skills_to_learn' => $this->dedupeFormattedEvents($skillsToLearn),
                'daily_actions' => $this->dedupeFormattedEvents($dailyActions),
                'life_actions' => $this->dedupeFormattedEvents($this->getSystemActions($character)), // Backward compat
                'triggers' => $this->dedupeFormattedEvents($triggerEvents),
                'daily' => $this->dedupeFormattedEvents($dailyEvents),
                'cultural' => $this->dedupeFormattedEvents($culturalEvents),
                'ageSpecific' => $this->dedupeFormattedEvents($this->getAgeSpecificEvents($character, $ageGroup, $shownEventIds)),
                'profession' => $this->dedupeFormattedEvents($this->ensureArray($statefulEvents['profession'] ?? [])),
                'profession_choices' => $this->dedupeFormattedEvents($professionChoices),
                // NEW: Luck events
                'luck' => $luckEvents,
                'milestone' => $milestone,
                'current_state' => $character->current_state,
                'character_state' => $character->character_state,
                // Feature 4: Age instead of day
                'age' => $this->calculateAge($character->current_day),
                'current_day' => $character->current_day,
                // Feature 1: Health status instead of health %
                'health_status' => $this->eventService->getHealthStatus($character->health ?? 78),
                'health_percentage' => $character->health ?? 78,
                // Branching system data
                'path_progress' => $this->getPathProgress($character),
                'completed_chains' => $character->completed_event_chains ?? [],
            ];

            // Add profession events if character is adult with profession
            if ($ageGroup === 'adult' && $character->profession) {
                $events['profession'] = $this->dedupeFormattedEvents($this->getProfessionEvents($character, $shownEventIds));
            }

            // Back-compat: keep `actions` as an alias of `life_actions` (frontend should use separated decks).
            $events['actions'] = $events['life_actions'] ?? [];

            return response()->json($events);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getAvailableEvents: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error loading events: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get age-specific events with branching logic - prioritizes narrative chain events
     */
    private function getAgeSpecificEventsWithBranching(Character $character, string $ageGroup, array $shownEventIds = [])
    {
        $normalizedAgeGroup = $this->normalizeAgeGroup($ageGroup);
        $activePaths = $character->active_event_paths ?? [];
        $completedChains = $character->completed_event_chains ?? [];
        
        // Get all age-specific events for this age group
        $events = AgeSpecificEvent::where('age_group', $normalizedAgeGroup)->get();
        
        // Filter out already shown NON-repeatable events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                $isRepeatable = $this->isRepeatableEvent('ageSpecific', $event);
                if ($isRepeatable) return true;
                return !in_array('ageSpecific_' . $event->id, $shownEventIds);
            });
        }
        
        // Separate events into categories:
        // 1. Chain events (events that are part of a narrative chain)
        // 2. Standalone events (random events without prerequisites)
        
        $chainEvents = [];
        $standaloneEvents = [];
        
        foreach ($events as $event) {
            $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'ageSpecific');
            // Check if event has branching prerequisites
            $hasPrerequisites = !empty($event->parent_category);
            
            if ($hasPrerequisites) {
                // Check if prerequisites are met
                if ($this->eventService->checkEventPrerequisites($event, $character)) {
                    $chainEvents[] = $event;
                }
            } else {
                $standaloneEvents[] = $event;
            }
        }
        
        $selectedEvents = [];
        $maxTotal = 10;

        // If we have active paths, bias toward chain events but keep randomness via weights.
        if (!empty($activePaths) && !empty($chainEvents)) {
            $chainCollection = collect($chainEvents)->map(function ($event) use ($activePaths) {
                $boost = 1.0;
                if (!empty($event->event_category) && in_array($event->event_category, $activePaths)) {
                    $boost = 1.6;
                } elseif (!empty($event->parent_category) && in_array($event->parent_category, $activePaths)) {
                    $boost = 1.35;
                }

                $event->weight = ($event->weight ?? 1) * $boost;
                return $event;
            });

            $maxChain = min(4, $chainCollection->count());
            for ($i = 0; $i < $maxChain && !$chainCollection->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($chainCollection);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific', $character);
                    $chainCollection = $chainCollection->reject(fn($e) => $e->id === $picked->id);
                }
            }
        }

        // Fill remaining slots with standalone events (weighted).
        $remaining = $maxTotal - count($selectedEvents);
        if ($remaining > 0 && !empty($standaloneEvents)) {
            $standaloneCollection = collect($standaloneEvents);
            for ($i = 0; $i < $remaining && !$standaloneCollection->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($standaloneCollection);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific', $character);
                    $standaloneCollection = $standaloneCollection->reject(fn($e) => $e->id === $picked->id);
                }
            }
        }

        // If nothing selected yet, do a general weighted draw across all eligible events.
        if (empty($selectedEvents)) {
            $pool = collect(array_merge($chainEvents, $standaloneEvents));
            $maxEvents = min($maxTotal, $pool->count());
            for ($i = 0; $i < $maxEvents && !$pool->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($pool);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific', $character);
                    $pool = $pool->reject(fn($e) => $e->id === $picked->id);
                }
            }
        }

        return $selectedEvents;
    }

    /**
     * Check if character should progress to next age group
     */
    private function checkAndApplyAgeProgression(Character $character): void
    {
        $currentDay = $character->current_day ?? 1;
        $storedAgeGroup = $character->getRawOriginal('age_group') ?? $character->age_group ?? 'child';
        
        Log::info('Age progression check', [
            'character_id' => $character->id,
            'current_day' => $currentDay,
            'stored_age_group' => $storedAgeGroup
        ]);
        
        // Skip progression only for explicitly set non-child age groups in early game (respect character creation choice)
        // But also ensure the age group is still valid for the current day
        $dayBasedAgeGroup = $this->getAgeGroupForDay($currentDay);
        $normalizedStoredAgeGroup = $this->normalizeAgeGroupForCharacter($storedAgeGroup);
        
        // If the stored age group is valid for the current day, keep it
        if ($normalizedStoredAgeGroup === $dayBasedAgeGroup) {
            Log::info('Skipping age progression - stored age_group matches day-based age_group', [
                'age_group' => $storedAgeGroup,
                'day_based' => $dayBasedAgeGroup
            ]);
            return;
        }
        
        // If user explicitly chose adult (or other) at character creation and is in early game, respect that choice
        if ($storedAgeGroup !== 'child' && $currentDay <= 10) {
            Log::info('Skipping age progression - respecting stored age_group in early game', ['age_group' => $storedAgeGroup]);
            return;
        }
        
        $normalizedCurrentAgeGroup = $this->normalizeAgeGroupForCharacter($storedAgeGroup);
        $newAgeGroup = $this->getAgeGroupForDay($currentDay);
        
        Log::info('Age progression calculation', [
            'normalized_current' => $normalizedCurrentAgeGroup,
            'day_based' => $newAgeGroup
        ]);
        
        if ($newAgeGroup !== $normalizedCurrentAgeGroup) {
            Log::info('Age progression applied', ['from' => $normalizedCurrentAgeGroup, 'to' => $newAgeGroup]);
            $character->previous_age_group = $normalizedCurrentAgeGroup;
            $character->age_group = $newAgeGroup;
            
            // Update max_age in character_state to match new age group
            $state = is_array($character->character_state) ? $character->character_state : [];
            $state['max_age'] = self::MAX_AGE_BY_GROUP[$newAgeGroup] ?? self::DEFAULT_MAX_DAYS;
            $character->character_state = $state;
            
            // Update profile picture based on new age_group and gender
            $genderKey = $character->gender;
            $genderMap = [
                'male' => 'male',
                'female' => 'female',
                'non-binary' => 'male',
                'transgender' => 'male',
            ];
            $genderSuffix = $genderMap[$genderKey] ?? 'male';
            
            $ageGroupMap = [
                'child' => 'child',
                'teen' => 'teenager',
                'teenager' => 'teenager',
                'adult' => 'adult',
                'old' => 'old',
            ];
            $ageGroupPrefix = $ageGroupMap[$newAgeGroup] ?? 'adult';
            
            $character->image = "/css/images/profilepicnormal/{$ageGroupPrefix}-{$genderSuffix}.png";
            
            $character->save();
        }
    }

    /**
     * Get age group for a given day - SHORTENED for faster gameplay
     * child: Days 0-9
     * teen: Days 10-19
     * adult: Days 20-35
     * old: Days 36-50 (game ends)
     */
    private function getAgeGroupForDay(int $day): string
    {
        if ($day <= 9) return 'child';
        if ($day <= 19) return 'teenager';
        if ($day <= 35) return 'adult';
        return 'old';
    }

    /**
     * Get path progress for all active story paths
     * Returns progress information for each path the character is on
     * Now counts ALL events from choice_history, not just chain completions
     */
    private function getPathProgress(Character $character): array
    {
        $activePaths = is_array($character->active_event_paths) ? $character->active_event_paths : [];
        $completedChains = is_array($character->completed_event_chains) ? $character->completed_event_chains : [];
        $currentNarrative = $character->current_narrative;
        
        // Define path categories - which event categories belong to each path
        $pathCategories = [
            'education' => ['education', 'study', 'exam', 'school', 'college', 'university', 'homework', 'learning', 'grade'],
            'career' => ['career', 'work', 'job', 'promotion', 'office', 'boss', 'colleague', 'business', 'profession'],
            'family' => ['relationship', 'dating', 'marriage', 'baby', 'family', 'parenting', 'wedding', 'spouse', 'sibling', 'parent'],
            'health' => ['health', 'fitness', 'exercise', 'illness', 'doctor', 'hospital', 'injury', 'accident', 'disease'],
            'wealth' => ['money', 'finance', 'investment', 'wealth', 'rich', 'broke', 'shopping', 'business', 'saving'],
            'social' => ['social', 'friend', 'party', 'network', 'community', 'date', 'bully', 'relationship']
        ];
        
        // Count events per path from choice_history
        $choiceHistory = $character->choice_history ?? [];
        $pathEventCounts = [
            'education' => 0,
            'career' => 0,
            'family' => 0,
            'health' => 0,
            'wealth' => 0,
            'social' => 0
        ];
        
        foreach ($choiceHistory as $choice) {
            $category = $choice['category'] ?? '';
            if (empty($category)) continue;
            
            // Check which path this category belongs to
            foreach ($pathCategories as $path => $categories) {
                if (in_array(strtolower($category), array_map('strtolower', $categories))) {
                    $pathEventCounts[$path]++;
                    break;
                }
            }
        }
        
        $pathStages = [
            'education' => ['school', 'high_school', 'college', 'graduate', 'phd'],
            'career' => ['unemployed', 'entry_level', 'mid_level', 'senior', 'executive', 'retired'],
            'family' => ['single', 'dating', 'engaged', 'married', 'parent', 'empty_nest'],
            'health' => ['healthy', 'fitness', 'injury', 'illness', 'recovery'],
            'wealth' => ['broke', 'saving', 'investing', 'wealthy', 'rich'],
            'social' => ['loner', 'friend', 'popular', 'influencer', 'leader']
        ];
        
        $progress = [];
        
        foreach ($activePaths as $path) {
            $stages = $pathStages[$path] ?? [];
            
            // Use choice_history count + completed_chains for progress
            // This ensures progress shows even for non-chain events
            $historyCount = $pathEventCounts[$path] ?? 0;
            $chainCount = isset($completedChains[$path]) ? (int)$completedChains[$path] : 0;
            
            // Combine both counts, but cap at total stages
            $completedCount = min($historyCount + $chainCount, count($stages));
            $completedCount = max($completedCount, 1); // At least show stage 1 if any events
            
            // Calculate stage index (0-based for display)
            $stageIndex = min($completedCount - 1, count($stages) - 1);
            $stageIndex = max($stageIndex, 0);
            
            $currentStage = $stages[$stageIndex] ?? $stages[0] ?? 'unknown';
            
            // Calculate percentage based on actual event count
            $progressPercent = count($stages) > 0 
                ? min(100, round(($completedCount / count($stages)) * 100)) 
                : 0;
            
            $progress[] = [
                'path' => $path,
                'current_stage' => $currentStage,
                'stage_index' => $stageIndex,
                'total_stages' => count($stages),
                'events_completed' => $completedCount,
                'progress_percent' => $progressPercent,
                'is_active' => true,
                'is_current' => ($currentNarrative === $path)
            ];
        }
        
        // Add inactive paths that haven't been started
        $allPaths = array_keys($pathStages);
        foreach ($allPaths as $path) {
            if (!in_array($path, $activePaths)) {
                $stages = $pathStages[$path] ?? [];
                $progress[] = [
                    'path' => $path,
                    'current_stage' => $stages[0] ?? 'unknown',
                    'stage_index' => 0,
                    'total_stages' => count($stages),
                    'events_completed' => 0,
                    'progress_percent' => 0,
                    'is_active' => false,
                    'is_current' => false
                ];
            }
        }
        
        return $progress;
    }

    /**
     * Check if there's a milestone event (age transition or career milestone)
     */
    private function checkMilestone(Character $character): ?array
    {
        $previousAgeGroup = $character->previous_age_group ?? $character->age_group;
        $currentAgeGroup = $character->age_group;
        
        // Check for age transition milestones
        if ($previousAgeGroup !== $currentAgeGroup) {
            $milestoneMessages = [
                'child_to_teenager' => [
                    'title' => 'Growing Up!',
                    'description' => 'You have grown from a child to a teenager! New adventures await you.',
                    'is_milestone' => true
                ],
                'teenager_to_adult' => [
                    'title' => 'Becoming an Adult!',
                    'description' => 'You have transitioned into adulthood! It is time to find a profession and build your career.',
                    'is_milestone' => true,
                    'is_profession_milestone' => true
                ],
                'adult_to_old' => [
                    'title' => 'Golden Years!',
                    'description' => 'You have entered your golden years. Reflect on your journey and enjoy life.',
                    'is_milestone' => true
                ],
            ];

            $key = $previousAgeGroup . '_to_' . $currentAgeGroup;
            $milestone = $milestoneMessages[$key] ?? null;

            // If transitioning to adulthood and character has no profession, add career choices
            if ($key === 'teenager_to_adult' && empty($character->profession)) {
                $professionChoices = $this->getAvailableProfessions($character);
                if (!empty($professionChoices)) {
                    $milestone['profession_choices'] = $professionChoices;
                    $milestone['title'] = 'Career Time!';
                    $milestone['description'] = 'You have become an adult! Now it is time to choose your career path. Select a profession to begin your working life.';
                }
            }

            // Ensure the milestone is only shown once.
            $character->previous_age_group = $currentAgeGroup;
            $character->save();

            return $milestone;
        }
        
        // Check for career milestone if character is adult but has no profession
        // This handles the case where a character becomes adult but the milestone wasn't shown
        if ($currentAgeGroup === 'adult' && empty($character->profession)) {
            // Check if we've already shown career milestone
            $shownEventIds = $character->shown_event_ids ?? [];
            if (!in_array('career_milestone_shown', $shownEventIds)) {
                $professionChoices = $this->getAvailableProfessions($character);
                if (!empty($professionChoices)) {
                    $milestone = [
                        'title' => 'Career Time!',
                        'description' => 'It is time to choose your career path! Select a profession to begin your working life.',
                        'is_milestone' => true,
                        'is_profession_milestone' => true,
                        'profession_choices' => $professionChoices,
                    ];
                    
                    // Mark career milestone as shown
                    $character->shown_event_ids = array_merge($shownEventIds, ['career_milestone_shown']);
                    $character->save();
                    
                    return $milestone;
                }
            }
        }
        
        return null;
    }

    /**
     * Get available professions for the character (for choice selection)
     * Returns array of profession options instead of auto-assigning
     * 
     * Priority:
     * 1. Stat-based professions (if character meets requirements)
     * 2. Fallback entry-level professions (always available for adults without profession)
     */
    private function getAvailableProfessions(Character $character): array
    {
        if (($character->age_group ?? null) !== 'adult') {
            return [];
        }

        if (!empty($character->profession)) {
            return [];
        }

        // First, try to get stat-based professions
        $unlockedProfessions = $this->eventService->checkProfessionUnlock($character);
        
        // Format profession choices as event cards
        $choices = [];
        foreach ($unlockedProfessions as $profession) {
            if (!$profession instanceof ProfessionTrigger) {
                continue;
            }
            $choices[] = $this->formatProfessionChoiceEvent($profession);
        }
        
        // If no stat-based professions found, return fallback entry-level jobs
        // This ensures players can always find work when they reach adulthood
        if (empty($choices)) {
            $choices = $this->getFallbackProfessions();
        }
        
        return $choices;
    }

    /**
     * Get fallback entry-level professions that are always available for adults
     * These provide a basic career path when the character doesn't meet stat requirements
     */
    private function getFallbackProfessions(): array
    {
        $fallbackProfessions = [
            [
                'profession' => 'Retail Worker',
                'description' => 'Work in a store or supermarket. A great starting point!',
                'stat_effects' => '+3 Discipline, +2 Charisma, +1 Wealth',
                'unlock_condition' => 'None (entry level)',
            ],
            [
                'profession' => 'Food Service',
                'description' => 'Work at a restaurant or cafe. Build customer service skills!',
                'stat_effects' => '+3 Discipline, +2 Happiness, +1 Wealth',
                'unlock_condition' => 'None (entry level)',
            ],
            [
                'profession' => 'Delivery Driver',
                'description' => 'Deliver packages or food. Flexible hours and good exercise!',
                'stat_effects' => '+3 Strength, +2 Luck, +1 Wealth',
                'unlock_condition' => 'None (entry level)',
            ],
            [
                'profession' => 'Office Assistant',
                'description' => 'Help with administrative tasks in an office. Learn business basics!',
                'stat_effects' => '+3 Intelligence, +2 Discipline, +1 Reputation',
                'unlock_condition' => 'None (entry level)',
            ],
            [
                'profession' => 'Warehouse Worker',
                'description' => 'Work in a warehouse moving goods. Physical work with steady pay!',
                'stat_effects' => '+4 Strength, +2 Discipline, +1 Wealth',
                'unlock_condition' => 'None (entry level)',
            ],
            [
                'profession' => 'Intern',
                'description' => 'Gain experience in your field of interest. Unpaid but educational!',
                'stat_effects' => '+3 Intelligence, +2 Reputation, +1 Discipline',
                'unlock_condition' => 'None (entry level)',
            ],
        ];

        $choices = [];
        foreach ($fallbackProfessions as $profession) {
            $choices[] = $this->formatFallbackProfessionChoice($profession);
        }

        return $choices;
    }

    /**
     * Format a fallback profession choice (without needing a ProfessionTrigger record)
     */
    private function formatFallbackProfessionChoice(array $profession): array
    {
        $professionName = $profession['profession'];
        $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $professionName));
        $image = "/css/images/profession/{$titleSlug}.jpg";

        return [
            'id' => 0, // Indicates fallback profession
            'type' => 'profession_choice',
            'deck_label' => 'Career',
            'repeatable' => false,
            'title' => $professionName,
            'description' => $profession['description'],
            'image' => $image,
            'outcome' => $profession['description'],
            'choices' => [
                [
                    'text' => 'Accept this job',
                    'set_profession' => true,
                    'stat_effects' => $profession['stat_effects'],
                    'days_to_advance' => 0,
                ],
                [
                    'text' => 'Look for other options',
                    'set_profession' => false,
                    'stat_effects' => null,
                    'days_to_advance' => 0,
                ],
            ],
            'auto_resolve' => false,
            'days_to_advance' => 0,
            'profession' => $professionName,
            'unlock_condition' => $profession['unlock_condition'],
            'is_fallback' => true, // Flag to identify fallback professions
        ];
    }

    private function formatProfessionChoiceEvent(ProfessionTrigger $profession): array
    {
        $effectsArray = $profession->getStatEffectsArray();
        $effectsText = $this->formatStatEffectsArrayAsText($effectsArray);

        $professionName = (string) ($profession->profession ?? 'Profession');
        $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $professionName));
        $image = "/css/images/profession/{$titleSlug}.jpg";

        $seededChoices = $this->adaptiveNarrativeService->buildChoicesForEvent(
            $professionName,
            $profession->notes ?? "A career path in {$professionName}.",
            $effectsText,
            'profession',
            'adult',
            is_array($profession->choices) ? $profession->choices : []
        );
        $choices = !empty($seededChoices) ? $seededChoices : [
            [
                'text' => 'Commit to this path',
                'set_profession' => true,
                'stat_effects' => $effectsText,
                'days_to_advance' => 0,
            ],
            [
                'text' => 'Not right now',
                'set_profession' => false,
                'stat_effects' => null,
                'days_to_advance' => 0,
            ],
        ];

        return [
            'id' => (int) $profession->id,
            'type' => 'profession_choice',
            'deck_label' => 'Career',
            'repeatable' => false,
            'title' => $professionName,
            'description' => $profession->notes ?? "A career path in {$professionName}.",
            'image' => $image,
            'outcome' => $profession->description ?? null,
            'choices' => $choices,
            'auto_resolve' => false,
            'days_to_advance' => 0,
            // Extra context (optional)
            'profession' => $professionName,
            'unlock_condition' => $profession->unlock_condition,
        ];
    }

    private function formatStatEffectsArrayAsText(array $effects): ?string
    {
        if (empty($effects)) {
            return null;
        }

        ksort($effects);
        $parts = [];
        foreach ($effects as $stat => $value) {
            if (!is_numeric($value)) {
                continue;
            }
            $intValue = (int) $value;
            $parts[] = ($intValue >= 0 ? '+' : '') . $intValue . ' ' . (string) $stat;
        }

        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Parse stat effects from text format (e.g., "+3 Discipline, +2 Charisma, +1 Wealth")
     * Used for fallback professions that don't exist in the database
     */
    private function parseStatEffectsFromText(string $text): array
    {
        $effects = [];
        // Match patterns like "+3 Discipline", "-2 Happiness", "+10 Intelligence"
        preg_match_all('/([+-]?\d+)\s+(\w+)/', $text, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $value = (int) $match[1];
            $stat = ucfirst(strtolower($match[2])); // Normalize stat name
            $effects[$stat] = $value;
        }
        
        return $effects;
    }

    /**
     * Apply stat effects to character
     * Used for both database and fallback professions
     */
    private function applyStatEffectsToCharacter(Character $character, array $statEffects): void
    {
        if (empty($statEffects)) {
            return;
        }
        
        // Apply stat effects to character base stats
        $currentStats = $character->stats ?? [];
        foreach ($statEffects as $stat => $value) {
            $currentStats[$stat] = ($currentStats[$stat] ?? 0) + $value;
        }
        $character->stats = $currentStats;
        
        // Update effective_stats by merging base + hidden stats
        $hiddenStats = $character->hidden_stats ?? [];
        $lifeStatsKeys = ['health', 'happiness', 'finance', 'relationship_status', 'career_level'];
        $filteredHiddenStats = array_diff_key($hiddenStats, array_flip($lifeStatsKeys));
        $effectiveStats = array_merge($filteredHiddenStats, $currentStats);
        
        // Clamp values between 0-100
        foreach ($effectiveStats as $key => $value) {
            $effectiveStats[$key] = max(0, min(100, $value));
        }
        $character->effective_stats = $effectiveStats;
    }

    /**
     * Add profession choice to milestone for user to select
     */
    private function addProfessionChoiceToMilestone(?array $milestone, array $professionChoices): array
    {
        if (empty($professionChoices)) {
            return $milestone;
        }

        // If no existing milestone, create a new one
        if (!$milestone) {
            $milestone = [
                'title' => 'Choose Your Career!',
                'description' => 'You have reached adulthood and must choose a profession path.',
                'is_milestone' => true,
            ];
        }

        // Add profession choices to milestone
        $milestone['profession_choices'] = $professionChoices;
        $milestone['title'] = 'Career Choice!';
        $milestone['description'] = 'Choose your professional path! Your decision will affect future career events.';
        $milestone['is_profession_milestone'] = true;

        return $milestone;
    }

    /**
     * FSM action: unlock a profession when character is adult and meets requirements.
     * Now returns available professions instead of auto-assigning.
     */
    private function maybeUnlockProfession(Character $character): ?string
    {
        // This method is now deprecated - profession is chosen by user
        // Keeping for backward compatibility
        if (($character->age_group ?? null) !== 'adult') {
            return null;
        }

        if (!empty($character->profession)) {
            return null;
        }

        return null; // Don't auto-assign anymore
    }

    private function professionUnlockMilestone(string $profession): array
    {
        return [
            'title' => 'Profession Unlocked!',
            'description' => "You unlocked the {$profession} path. Professional Path cards are now available.",
            'is_milestone' => true,
        ];
    }

    private function mergeMilestones(?array $a, ?array $b): ?array
    {
        if (!$a) return $b;
        if (!$b) return $a;

        return [
            'title' => $a['title'] ?? $b['title'],
            'description' => trim(($a['description'] ?? '') . ' ' . ($b['description'] ?? '')),
            'is_milestone' => true,
        ];
    }

    /**
     * Get stat-triggered events (based on current effective stats)
     * These are formatted as type "trigger" so they can be applied via apply-event.
     */
    private function getTriggeredStatEvents(Character $character, array $shownEventIds = []): array
    {
        $triggered = collect($this->eventService->checkStatTriggers($character));

        if ($triggered->isEmpty()) {
            return [];
        }

        if (!empty($shownEventIds)) {
            $triggered = $triggered->filter(function ($event) use ($shownEventIds) {
                return !in_array('trigger_' . $event->id, $shownEventIds);
            });
        }

        $selected = [];
        $maxEvents = min(2, $triggered->count());

        for ($i = 0; $i < $maxEvents && !$triggered->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($triggered);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'trigger', $character);
                $triggered = $triggered->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $this->dedupeFormattedEvents($selected);
    }

    /**
     * Get random daily events (excluding shown events, filtered by age)
     */
    public function getDailyEvents(Character $character, array $shownEventIds = [], string $ageGroup = 'adult')
    {
        // Normalize age group for daily events
        $normalizedAge = $this->normalizeAgeGroupForDaily($ageGroup);
        
        // Get events matching the age group or "all"
        $events = DailyEvent::whereIn('age_group', [$normalizedAge, 'all'])->get();
        
        // Daily events are repeatable actions: do not exclude by shown_event_ids.

        // Apply branching/stat prerequisites
        $events = $events->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        // Bias toward current narrative/active paths, but keep it weighted.
        $activePaths = $character->active_event_paths ?? [];
        if (!empty($activePaths)) {
            $events = $events->map(function ($event) use ($activePaths) {
                $boost = 1.0;
                if (!empty($event->event_category) && in_array($event->event_category, $activePaths)) {
                    $boost = 1.25;
                } elseif (!empty($event->parent_category) && in_array($event->parent_category, $activePaths)) {
                    $boost = 1.15;
                }
                $event->weight = ((float) ($event->weight ?? 1)) * $boost;
                return $event;
            });
        }

        $events = $events->map(function ($event) use ($character) {
            $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'daily');
            return $event;
        });
        
        $selected = [];
        
        // Deterministic top picks (more options since these are "actions")
        $maxEvents = min(10, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'daily', $character);
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $this->dedupeFormattedEvents($selected);
    }

    /**
     * Normalize age group for daily events (child -> child, teen -> teen, adult -> adult, old -> old)
     */
    private function normalizeAgeGroupForDaily(string $ageGroup): string
    {
        return match($ageGroup) {
            'child', 'children' => 'child',
            'teenager', 'teen', 'adolescent' => 'teen',
            'adult' => 'adult',
            'old', 'elder', 'elderly' => 'old',
            default => 'adult'
        };
    }

    /**
     * Normalize character age_group values for storage in the characters table.
     * (Keeps compatibility with the characters.age_group enum values.)
     */
    private function normalizeAgeGroupForCharacter(?string $ageGroup): string
    {
        $ageGroup = (string) $ageGroup;

        return match ($ageGroup) {
            'child', 'children' => 'child',
            'teen', 'teenager', 'adolescent' => 'teen',
            'adult' => 'adult',
            'old', 'elder', 'elderly' => 'old',
            default => 'adult',
        };
    }

    /**
     * Pick a key by weighted probability (values <= 0 are ignored).
     */
    private function pickWeightedKey(array $weights): string
    {
        $weights = array_filter($weights, fn($w) => is_numeric($w) && $w > 0);
        if (empty($weights)) {
            return 'daily';
        }

        $total = array_sum($weights);
        $roll = random_int(1, max(1, (int) ceil($total * 100)));
        $running = 0;

        foreach ($weights as $key => $weight) {
            $running += (int) ceil($weight * 100);
            if ($roll <= $running) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weights);
    }

    /**
     * Get random cultural events (excluding shown events)
     */
    public function getCulturalEvents(Character $character, array $shownEventIds = [])
    {
        $events = CulturalEvent::all();
        
        // Cultural events are repeatable actions: do not exclude by shown_event_ids.

        // Apply branching/stat prerequisites
        $events = $events->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        // Bias toward current narrative/active paths, but keep it weighted.
        $activePaths = $character->active_event_paths ?? [];
        if (!empty($activePaths)) {
            $events = $events->map(function ($event) use ($activePaths) {
                $boost = 1.0;
                if (!empty($event->event_category) && in_array($event->event_category, $activePaths)) {
                    $boost = 1.25;
                } elseif (!empty($event->parent_category) && in_array($event->parent_category, $activePaths)) {
                    $boost = 1.15;
                }
                $event->weight = ((float) ($event->weight ?? 1)) * $boost;
                return $event;
            });
        }

        $events = $events->map(function ($event) use ($character) {
            $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'cultural');
            return $event;
        });
        
        $selected = [];
        
        // Deterministic top picks (more options since these are "actions")
        $maxEvents = min(10, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'cultural', $character);
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $selected;
    }

    /**
     * Get random age-specific events (excluding shown events)
     */
    public function getAgeSpecificEvents(Character $character, string $ageGroup, array $shownEventIds = [])
    {
        $events = $this->getAgeSpecificEventsWithBranching($character, $ageGroup, $shownEventIds);

        $triggerEvents = $this->getTriggeredStatEvents($character, $shownEventIds);
        if (!empty($triggerEvents)) {
            $remainingSlots = max(0, 5 - count($triggerEvents));
            $events = array_slice($events, 0, $remainingSlots);
            $events = array_merge($triggerEvents, $events);
        }

        return $this->dedupeFormattedEvents($events);
    }

    /**
     * Get profession-specific events (excluding shown events)
     */
    public function getProfessionEvents(Character $character, array $shownEventIds = [])
    {
        $profession = $character->profession;
        if (!$profession) return [];

        $events = ProfessionPathEvent::where('profession', $profession)->get();
        
        // Filter out already shown NON-repeatable events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                $isRepeatable = $this->isRepeatableEvent('profession', $event);
                if ($isRepeatable) return true;
                return !in_array('profession_' . $event->id, $shownEventIds);
            });
        }
        
        // Gate by prerequisites (e.g., required_stat / threshold)
        $events = $events->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        $events = $events->map(function ($event) use ($character) {
            $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'profession');
            return $event;
        });

        $selected = [];
        
        // Deterministic top picks
        $maxEvents = min(10, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'profession', $character);
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $this->dedupeFormattedEvents($selected);
    }

    /**
     * Get a single random event from all available types for character
     */
    public function getRandomEvent(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->checkAndApplyAgeProgression($character);
        $this->maybeUnlockProfession($character);

        $ageGroup = $character->age_group ?? 'adult';
        $shownEventIds = $character->shown_event_ids ?? [];

        $normalizedAge = $this->normalizeAgeGroupForDaily($ageGroup);
        $dailyPool = DailyEvent::whereIn('age_group', [$normalizedAge, 'all'])->get();
        $dailyPool = $dailyPool
            ->filter(fn($event) => !in_array('daily_' . $event->id, $shownEventIds))
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character))
            ->map(function ($event) use ($character) {
                $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'daily');
                return $event;
            });

        $culturalPool = CulturalEvent::all();
        $culturalPool = $culturalPool
            ->filter(fn($event) => !in_array('cultural_' . $event->id, $shownEventIds))
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character))
            ->map(function ($event) use ($character) {
                $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'cultural');
                return $event;
            });

        $ageSpecificPool = AgeSpecificEvent::where('age_group', $this->normalizeAgeGroup($ageGroup))->get();
        $ageSpecificPool = $ageSpecificPool
            ->filter(fn($event) => !in_array('ageSpecific_' . $event->id, $shownEventIds))
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character))
            ->map(function ($event) use ($character) {
                $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'ageSpecific');
                return $event;
            });

        $triggerPool = collect($this->eventService->checkStatTriggers($character));
        $triggerPool = $triggerPool->filter(fn($event) => !in_array('trigger_' . $event->id, $shownEventIds));

        $professionPool = collect();
        if (($character->age_group ?? null) === 'adult' && !empty($character->profession)) {
            $professionPool = ProfessionPathEvent::where('profession', $character->profession)->get();
            $professionPool = $professionPool
                ->filter(fn($event) => !in_array('profession_' . $event->id, $shownEventIds))
                ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character))
                ->map(function ($event) use ($character) {
                    $event->dynamic_weight = $this->adaptiveNarrativeService->scoreEventWeight($character, $event, 'profession');
                    return $event;
                });
        }

        $typeWeights = [
            'daily' => $dailyPool->isEmpty() ? 0 : 0.40,
            'cultural' => $culturalPool->isEmpty() ? 0 : 0.22,
            'ageSpecific' => $ageSpecificPool->isEmpty() ? 0 : 0.22,
            'trigger' => $triggerPool->isEmpty() ? 0 : 0.18,
            'profession' => $professionPool->isEmpty() ? 0 : 0.22,
        ];

        $type = $this->pickWeightedKey($typeWeights);

        $event = match ($type) {
            'daily' => $this->eventService->getRandomEventByWeight($dailyPool),
            'cultural' => $this->eventService->getRandomEventByWeight($culturalPool),
            'ageSpecific' => $this->eventService->getRandomEventByWeight($ageSpecificPool),
            'trigger' => $this->eventService->getRandomEventByWeight($triggerPool),
            'profession' => $this->eventService->getRandomEventByWeight($professionPool),
            default => null,
        };

        return response()->json([
            'event' => $event ? $this->formatEvent($event, $type, $character) : null,
            'type' => $type
        ]);
    }

    /**
     * Get filtered random daily event (filtered by age group)
     */
    private function getFilteredRandomDailyEvent(array $shownEventIds = [], string $ageGroup = 'adult')
    {
        // Normalize age group for daily events
        $normalizedAge = $this->normalizeAgeGroupForDaily($ageGroup);
        
        // Get events matching the age group or "all"
        $events = DailyEvent::whereIn('age_group', [$normalizedAge, 'all'])->get();
        
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('daily_' . $event->id, $shownEventIds);
            });
        }
        
        return $this->eventService->getRandomEventByWeight($events);
    }

    /**
     * Get filtered random cultural event
     */
    private function getFilteredRandomCulturalEvent(array $shownEventIds = [])
    {
        $events = CulturalEvent::all();
        
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('cultural_' . $event->id, $shownEventIds);
            });
        }
        
        return $this->eventService->getRandomEventByWeight($events);
    }

    /**
     * Get filtered random age-specific event
     */
    private function getFilteredRandomAgeSpecificEvent(string $ageGroup, array $shownEventIds = [])
    {
        $events = AgeSpecificEvent::where('age_group', $ageGroup)->get();
        
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('ageSpecific_' . $event->id, $shownEventIds);
            });
        }
        
        return $this->eventService->getRandomEventByWeight($events);
    }

    /**
     * Get filtered random profession event
     */
    private function getFilteredRandomProfessionEvent(string $profession, array $shownEventIds = [])
    {
        $events = ProfessionPathEvent::where('profession', $profession)->get();
        
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('profession_' . $event->id, $shownEventIds);
            });
        }
        
        return $this->eventService->getRandomEventByWeight($events);
    }

    /**
     * Set the character's profession based on user's choice from milestone
     */
    public function setProfession(Request $request, Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'profession' => 'required|string',
            ]);

            $profession = $validated['profession'];

            // Verify the profession is valid (exists in profession_triggers OR is a fallback profession)
            $availableProfessions = $this->getAvailableProfessions($character);
            $validProfessions = array_column($availableProfessions, 'profession');
            
            // Check if this is a fallback profession
            $isFallback = false;
            $statEffects = [];
            foreach ($availableProfessions as $prof) {
                if (($prof['profession'] ?? '') === $profession && ($prof['is_fallback'] ?? false) === true) {
                    $isFallback = true;
                    // Get stat effects from the fallback profession data
                    if (!empty($prof['choices'])) {
                        foreach ($prof['choices'] as $choice) {
                            if (($choice['set_profession'] ?? false) === true && !empty($choice['stat_effects'])) {
                                // Parse stat effects from text format (e.g., "+3 Discipline, +2 Charisma")
                                $statEffects = $this->parseStatEffectsFromText($choice['stat_effects']);
                                $this->applyStatEffectsToCharacter($character, $statEffects);
                            }
                        }
                    }
                    break;
                }
            }
            
            // For non-fallback professions, validate against database
            if (!$isFallback && !empty($availableProfessions) && !in_array($profession, $validProfessions)) {
                return response()->json([
                    'message' => 'Invalid profession choice',
                ], 400);
            }

            // Get the profession trigger to apply stat effects (for non-fallback professions)
            if (!$isFallback) {
                $professionTrigger = \App\Models\ProfessionTrigger::where('profession', $profession)->first();
                
                // Apply stat effects from profession choice
                $statEffects = [];
                if ($professionTrigger && !empty($professionTrigger->stat_effects)) {
                    $statEffects = $professionTrigger->getStatEffectsArray();
                    $this->applyStatEffectsToCharacter($character, $statEffects);
                }
            }

            // Set the profession and career level
            $character->profession = $profession;
            $character->career_level = 'entry_level';
            $character->save();

            Log::info('Character profession set', [
                'character_id' => $character->id,
                'profession' => $profession,
                'stat_effects_applied' => $statEffects,
            ]);

            return response()->json([
                'success' => true,
                'profession' => $profession,
                'stat_effects_applied' => $statEffects,
                'message' => "You have chosen {$profession} as your profession!",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error setting profession: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error setting profession: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply stat effects from an event choice
     */
    public function applyEventOutcome(Request $request, Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'event_type' => 'required|string',
                'event_id' => 'required|integer',
                'choice_index' => 'required|integer|min:0',
                'mini_game_score' => 'nullable|integer|min:0|max:100'
            ]);

            $isSystemEvent = ($validated['event_type'] === 'system');
            $isProfessionChoice = ($validated['event_type'] === 'profession_choice');
            $isLuckEvent = ($validated['event_type'] === 'luck');

            // DEBUG: Log what's being received from frontend
            Log::debug('applyEventOutcome request received', [
                'character_id' => $character->id,
                'event_type' => $validated['event_type'],
                'event_id' => $validated['event_id'],
                'choice_index' => $validated['choice_index'],
                'all_request_data' => $request->all(),
            ]);

            // Get the event FIRST (for logging)
            $event = null;
            $eventData = null;

            if ($isLuckEvent) {
                // Luck events come from LuckService - reconstruct from request data
                $luckEventData = $request->all();
                $eventData = [
                    'id' => $luckEventData['event_id'] ?? 'luck',
                    'title' => $luckEventData['event_title'] ?? 'Lucky Event',
                    'description' => $luckEventData['event_description'] ?? '',
                    'choices' => $luckEventData['choices'] ?? [],
                    'luck_type' => $luckEventData['luck_type'] ?? 'neutral',
                    'category' => $luckEventData['category'] ?? 'random',
                ];
                $event = (object) $eventData;
            } elseif ($isSystemEvent) {
                $eventData = $this->getSystemEventById($character, (int) $validated['event_id']);
                if (!$eventData) {
                    return response()->json(['error' => 'Event not found'], 404);
                }
                $event = (object) $eventData;
            } elseif ($isProfessionChoice) {
                if (($character->age_group ?? null) !== 'adult') {
                    return response()->json(['error' => 'Profession paths unlock in adulthood'], 400);
                }
                if (!empty($character->profession)) {
                    return response()->json(['error' => 'Profession already chosen'], 400);
                }

                $trigger = collect($this->eventService->checkProfessionUnlock($character))
                    ->first(fn($p) => $p instanceof ProfessionTrigger && (int) $p->id === (int) $validated['event_id']);

                if (!$trigger) {
                    return response()->json(['error' => 'Profession path not available'], 404);
                }

                $eventData = $this->formatProfessionChoiceEvent($trigger);
                $event = (object) $eventData;
            } else {
                $event = $this->getEventById($validated['event_type'], $validated['event_id']);
                if (!$event) {
                    return response()->json(['error' => 'Event not found'], 404);
                }
                // Format for API response + logging
                $eventData = $this->formatEvent($event, $validated['event_type'], $character);
            }

            // Get the choice list - use direct choices for luck events
            $choices = $isLuckEvent 
                ? ($event->choices ?? [])
                : $this->resolveEventChoices($event, $validated['event_type'], $character, $character->age_group ?? 'adult');
            
            // Feature 5: Game-decided outcomes - if event has auto_resolve flag, determine outcome automatically
            $autoResolve = $event->auto_resolve ?? false;
            $choiceIndex = $validated['choice_index'];
            
            if ($autoResolve) {
                // Game decides the outcome based on stats
                $outcome = $this->eventService->determineGameOutcome($character, $event);
                // Map outcome to choice index (0 = bad outcome, 1 = good outcome)
                $choiceIndex = ($outcome === 'success') ? 1 : 0;
                Log::info('Game-decided outcome', ['outcome' => $outcome, 'choiceIndex' => $choiceIndex]);
            }
            
            $choice = $choices[$choiceIndex] ?? null;

            if (!$choice) {
                return response()->json(['error' => 'Choice not found'], 404);
            }

            $choiceText = $choice['text'] ?? 'Unknown Choice';

            // Capture BEFORE state - before any character modifications
            $beforeSnapshot = [
                'day' => $character->current_day,
                'stats' => $character->stats,
                'hidden_stats' => $character->hidden_stats,
                'effective_stats' => $character->effective_stats,
                'narrative' => $character->current_narrative,
                'active_event_paths' => $character->active_event_paths,
                'health_status' => $this->eventService->getHealthStatus($character->health ?? 78),
                'health_percentage' => $character->health ?? 78,
                'finance' => $character->finance ?? 20,
                'relationship_status' => $character->relationship_status ?? 'single',
                'profession' => $character->profession ?? null,
                'career_level' => $character->career_level ?? 'unemployed',
            ];

            // Per-choice outcome variants (game decides success/failure deterministically based on stats)
            $resolvedChoiceOutcome = $this->adaptiveNarrativeService->resolveOutcome(
                $character,
                [
                    'title' => $eventData['title'] ?? ($event->event_choice ?? $event->title ?? 'Event'),
                    'description' => $eventData['description'] ?? ($event->outcome ?? $event->description ?? null),
                    'type' => $validated['event_type'],
                    'archetype' => $eventData['archetype'] ?? null,
                ],
                is_array($choice) ? $choice : []
            );
            $choiceOutcomeText = is_array($resolvedChoiceOutcome) ? ($resolvedChoiceOutcome['text'] ?? null) : null;
            $choiceOutcomeEffects = is_array($resolvedChoiceOutcome) ? ($resolvedChoiceOutcome['stat_effects'] ?? null) : null;

            // Apply stat effects with FULL LOGGING (now passes event/choice data)
            $baseStatEffects = $choice['stat_effects'] ?? ($event->stat_effects ?? ($eventData['statEffects'] ?? null));
            $statEffects = $this->mergeStatEffectsText($baseStatEffects, $choiceOutcomeEffects);
            $effectsResult = $this->eventService->applyStatEffects(
                $character, 
                $statEffects,
                $eventData,  // ← NEW: passes formatted event data for logging
                $choiceIndex,
                $choiceText
            );
            
            // Extract effects and random outcome info
            $effects = is_array($effectsResult) ? ($effectsResult['effects'] ?? $effectsResult) : $effectsResult;
            $randomOutcomeInfo = is_array($effectsResult) ? ($effectsResult['random_outcome'] ?? null) : null;

            // Process skill learning (for daily actions with learn_skill)
            // Only learn skill if mini-game score is acceptable (>= 30) or no mini-game was played
            $skillLearningResult = null;
            $talentDiscoveryResult = null;
            $isLearningEvent = ($validated['event_type'] === 'learning');
            
            // Get mini-game score from request or session
            $miniGameScore = $validated['mini_game_score'] ?? null;
            
            // If not provided in request, try to get from session
            if ($miniGameScore === null && isset($validated['event_id'])) {
                $sessionKey = 'mini_game_score_' . $character->id . '_' . $validated['event_id'];
                $miniGameScore = session($sessionKey);
                // Clear the session after retrieving
                session()->forget($sessionKey);
            }
            
            // Minimum score required to successfully learn a skill (30 = 0.3x multiplier, below = fail)
            $minScoreToLearnSkill = 30;
            
            if (($isSystemEvent || $isLearningEvent) && isset($choice['learn_skill'])) {
                $skillName = $choice['learn_skill'];
                
                // Check if mini-game was played and if score is sufficient
                if ($miniGameScore !== null && $miniGameScore < $minScoreToLearnSkill) {
                    // Failed mini-game - skill not learned
                    $skillLearningResult = 'failed_mini_game';
                    Log::info('Skill learning failed due to poor mini-game performance', [
                        'character_id' => $character->id,
                        'skill' => $skillName,
                        'mini_game_score' => $miniGameScore,
                        'required_score' => $minScoreToLearnSkill
                    ]);
                } else {
                    // Either no mini-game or passed - learn the skill
                    $learned = $character->learnSkill($skillName);
                    $skillLearningResult = $learned ? $skillName : false;
                    
                    Log::info('Skill learning attempt', [
                        'character_id' => $character->id,
                        'skill' => $skillName,
                        'learned' => $learned,
                        'event_type' => $validated['event_type'],
                        'mini_game_score' => $miniGameScore
                    ]);
                }
            }
            
            // Process talent discovery (for daily actions with discover_talent)
            if (($isSystemEvent || $isLearningEvent) && isset($choice['discover_talent'])) {
                $talentName = $choice['discover_talent'];
                $discovered = $character->discoverTalent($talentName);
                $talentDiscoveryResult = $discovered ? $talentName : false;
                
                Log::info('Talent discovery attempt', [
                    'character_id' => $character->id,
                    'talent' => $talentName,
                    'discovered' => $discovered
                ]);
            }

            // Process reputation effects (for daily actions with reputation_changes)
            $reputationChanges = [];
            if ($isSystemEvent && isset($choice['reputation_changes'])) {
                $reputationData = $choice['reputation_changes'];
                
                if (is_array($reputationData)) {
                    foreach ($reputationData as $faction => $change) {
                        $character->updateReputation($faction, (int) $change);
                        $reputationChanges[$faction] = $change;
                    }
                    
                    Log::info('Reputation changes applied', [
                        'character_id' => $character->id,
                        'changes' => $reputationChanges
                    ]);
                }
            }

            // Process trauma healing (for daily actions with heal_trauma)
            $traumaHealed = [];
            if ($isSystemEvent && isset($choice['heal_trauma'])) {
                $traumaToHeal = $choice['heal_trauma'];
                $traumasToHeal = is_array($traumaToHeal) ? $traumaToHeal : [$traumaToHeal];
                
                foreach ($traumasToHeal as $traumaType) {
                    $healed = $character->healTrauma($traumaType);
                    if ($healed) {
                        $traumaHealed[] = $traumaType;
                    }
                }
                
                Log::info('Trauma healing attempt', [
                    'character_id' => $character->id,
                    'traumas' => $traumasToHeal,
                    'healed' => $traumaHealed
                ]);
            }

            // Process trauma gain (for daily actions with gain_trauma)
            $traumaGained = [];
            if ($isSystemEvent && isset($choice['gain_trauma'])) {
                $traumaToGain = $choice['gain_trauma'];
                $traumasToGain = is_array($traumaToGain) ? $traumaToGain : [$traumaToGain];
                
                foreach ($traumasToGain as $traumaType) {
                    $character->gainTrauma($traumaType);
                    $traumaGained[] = $traumaType;
                }
                
                Log::info('Trauma gained', [
                    'character_id' => $character->id,
                    'traumas' => $traumaGained
                ]);
            }

            // Process relationship changes (for ALL event types with relationship_status_change)
            $relationshipChanges = [];
            if (isset($choice['relationship_status_change'])) {
                $newStatus = $choice['relationship_status_change'];
                $character->setRelationshipStatus($newStatus);
                $relationshipChanges['status'] = $newStatus;
                
                Log::info('Relationship status changed', [
                    'character_id' => $character->id,
                    'new_status' => $newStatus,
                    'event_type' => $validated['event_type'] ?? 'unknown'
                ]);
            }

            // Process relationship state changes (for ALL event types with relationship_state_change)
            if (isset($choice['relationship_state_change'])) {
                $stateChanges = $choice['relationship_state_change'];
                
                if (is_array($stateChanges)) {
                    foreach ($stateChanges as $group => $newState) {
                        $character->updateRelationshipState($group, $newState);
                        $relationshipChanges[$group] = $newState;
                    }
                    
                    Log::info('Relationship state changed', [
                        'character_id' => $character->id,
                        'changes' => $relationshipChanges
                    ]);
                }
            }

            // Process profession trigger - for ProfessionTrigger events with set_profession => true
            if (isset($choice['set_profession']) && $choice['set_profession'] === true) {
                // Get profession from the choice first (for AgeSpecificEvent career choices)
                $profession = null;
                if (isset($choice['profession'])) {
                    // Direct profession in choice (AgeSpecificEvent career choices)
                    $profession = $choice['profession'];
                } elseif (isset($event->profession)) {
                    // Fallback: get from event (ProfessionTrigger)
                    $profession = $event->profession;
                } elseif (isset($event->event_choice)) {
                    // Fallback: extract profession from event_choice
                    $profession = $event->event_choice;
                }
                
                if ($profession) {
                    $character->setProfession($profession);
                    
                    Log::info('Profession set via trigger', [
                        'character_id' => $character->id,
                        'profession' => $profession,
                        'event_type' => $validated['event_type'] ?? 'unknown'
                    ]);
                }
            }

            // Process social connection (for ALL event types with add_social_connection)
            $socialConnectionsAdded = [];
            if (isset($choice['add_social_connection'])) {
                $connectionData = $choice['add_social_connection'];
                
                if (is_array($connectionData)) {
                    $type = $connectionData['type'] ?? 'friend';
                    $name = $connectionData['name'] ?? 'Unknown';
                    $details = $connectionData['details'] ?? [];
                    
                    $character->addSocialConnection($type, $name, $details);
                    $socialConnectionsAdded[] = ['type' => $type, 'name' => $name];
                    
                    // NEW: Track relationship chain progress when meeting an NPC
                    // Progress 1 = just met, 2 = know name, 3 = friend, 4 = close friend/ally
                    if (isset($connectionData['start_relationship_chain']) && $connectionData['start_relationship_chain'] === true) {
                        $relationshipType = $connectionData['relationship_type'] ?? $type;
                        $startingProgress = $connectionData['starting_progress'] ?? 1;
                        $character->updateRelationshipChainProgress($name, $relationshipType, $startingProgress, $details);
                        
                        Log::info('Relationship chain started', [
                            'character_id' => $character->id,
                            'npc_name' => $name,
                            'relationship_type' => $relationshipType,
                            'progress' => $startingProgress
                        ]);
                    }
                    
                    Log::info('Social connection added', [
                        'character_id' => $character->id,
                        'type' => $type,
                        'name' => $name
                    ]);
                }
            }
            
            // NEW: Process relationship chain progression
            if (isset($choice['advance_relationship'])) {
                $advanceData = $choice['advance_relationship'];
                
                if (is_array($advanceData)) {
                    $npcName = $advanceData['name'] ?? null;
                    $relationshipType = $advanceData['type'] ?? 'friend';
                    $advanceAmount = $advanceData['amount'] ?? 1;
                    
                    if ($npcName) {
                        $currentProgress = $character->getRelationshipProgress($npcName, $relationshipType);
                        $newProgress = $currentProgress + $advanceAmount;
                        $character->updateRelationshipChainProgress($npcName, $relationshipType, $newProgress, [
                            'last_action' => 'choice_advance',
                        ]);
                        
                        Log::info('Relationship chain advanced', [
                            'character_id' => $character->id,
                            'npc_name' => $npcName,
                            'new_progress' => $newProgress
                        ]);
                    }
                }
            }

            // Process location change (for ALL event types with change_location)
            $locationChanged = null;
            if (isset($choice['change_location'])) {
                $newLocation = $choice['change_location'];
                $character->setLocation($newLocation);
                $locationChanged = $newLocation;
                
                Log::info('Location changed', [
                    'character_id' => $character->id,
                    'new_location' => $newLocation
                ]);
            }

            // Process season change (for ALL event types with change_season)
            $seasonChanged = null;
            if (isset($choice['change_season'])) {
                $newSeason = $choice['change_season'];
                $character->setSeason($newSeason);
                $seasonChanged = $newSeason;
                
                Log::info('Season changed', [
                    'character_id' => $character->id,
                    'new_season' => $newSeason
                ]);
            }

            // Process weather change (for daily actions with change_weather)
            $weatherChanged = null;
            if ($isSystemEvent && isset($choice['change_weather'])) {
                $newWeather = $choice['change_weather'];
                $character->setWeather($newWeather);
                $weatherChanged = $newWeather;
                
                Log::info('Weather changed', [
                    'character_id' => $character->id,
                    'new_weather' => $newWeather
                ]);
            }

            // FSM State advance
            $outcomeType = $this->eventService->determineOutcomeType($statEffects);
            if (!$isSystemEvent && !$isProfessionChoice) {
                $this->eventService->advanceState($character, $validated['event_type'], $outcomeType);
            }

            // Track the event as shown (repeatable cards stay available)
            $isRepeatable = $this->isRepeatableEvent($validated['event_type'], $event);
            if (!$isSystemEvent && !$isProfessionChoice && !$isRepeatable) {
                $shownEventIds = $character->shown_event_ids ?? [];
                $eventKey = $validated['event_type'] . '_' . $validated['event_id'];
                
                if (!in_array($eventKey, $shownEventIds)) {
                    $shownEventIds[] = $eventKey;
                    if (count($shownEventIds) > 50) {
                        $shownEventIds = array_slice($shownEventIds, -50);
                    }
                    $character->shown_event_ids = $shownEventIds;
                    $character->save();
                }
            }

            // Game-over evaluation happens after time advancement + consequences.

            // Feature 3: Support days_to_advance - determine how many days to skip
            $explicitDays = $choice['days_to_advance'] ?? $event->days_to_advance ?? null;
            // If not set or 0, default to 1. Otherwise use the explicit value (like 5).
            $daysToAdvance = ($explicitDays === null || $explicitDays == 0) ? 1 : (int) $explicitDays;
            
            // Feature 2: Only advance day if daysToAdvance > 0, otherwise stay on same day for multiple choices
            if ($daysToAdvance > 0) {
                $character->current_day = ($character->current_day ?? 1) + $daysToAdvance;
            }
            // If daysToAdvance is 0, don't advance - user can make more choices this day
            if ($isProfessionChoice) {
                $shouldSetProfession = (bool) ($choice['set_profession'] ?? ($choiceIndex === 0));
                if ($shouldSetProfession) {
                    $character->profession = (string) ($eventData['profession'] ?? $character->profession);
                    $character->career_level = $character->career_level ?: 'entry_level';
                }
            }
            $character->save();

            // Update narrative path based on the event choice (must be after save so narrative is persisted)
            $this->handleEventBranching($character, $event, $effects);

            // Apply any age progression based on the new day
            $this->checkAndApplyAgeProgression($character);

            // FSM action: profession unlock becomes available in adult stage
            $this->maybeUnlockProfession($character);

            // Refresh the character to get updated data
            $character->refresh();

            // Apply deterministic ongoing consequences when time advances
            $timePassageConsequences = $this->eventService->applyTimePassage($character, $daysToAdvance);

            // Feature 1: Update health condition based on health percentage
            $healthStatus = $this->eventService->updateHealthCondition($character);
            
            // Feature 8: Check for severe consequences
            $rawEffects = $this->eventService->parseStatEffects($statEffects);
            $consequences = $this->eventService->checkSevereConsequences($character, $rawEffects);
            $consequences = array_merge($timePassageConsequences ?? [], $consequences ?? []);

            $this->adaptiveNarrativeService->applyDecisionMemory(
                $character,
                [
                    'title' => $eventData['title'] ?? ($event->event_choice ?? $event->title ?? 'Event'),
                    'description' => $eventData['description'] ?? ($event->outcome ?? $event->description ?? null),
                    'type' => $validated['event_type'],
                    'archetype' => $eventData['archetype'] ?? null,
                    'event_category' => $event->event_category ?? $eventData['event_category'] ?? null, // Pass event_category for proper narrative path
                ],
                is_array($choice) ? $choice : [],
                is_array($resolvedChoiceOutcome) ? $resolvedChoiceOutcome : null
            );
            
            // Refresh again after consequences
            $character->refresh();

            // Check for game over condition AFTER time advancement + consequences
            // Either explicit "End of game" in effects OR reached max days (old age) OR health <= 0
            $gameOver = false;
            $currentDay = $character->current_day ?? 1;
            $endingType = null;
            $endingTitle = null;
            $endingDescription = null;

            $deathCause = null;

            if ($statEffects && strpos($statEffects, 'End of game') !== false) {
                $deathCause = 'event_death';
                $this->eventService->markCharacterDeath($character, $deathCause, [
                    'event_title' => $eventData['title'] ?? ($event->event_choice ?? $event->title ?? 'Event'),
                    'choice' => $choiceText,
                ]);
                $gameOver = true;
                $endingType = $this->determineEndingType($character, $deathCause);
            } elseif ($currentDay >= $this->getMaxDays($character)) {
                $gameOver = true;
                $endingType = $this->determineEndingType($character, 'old_age');
            } else {
                $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
                $health = isset($effectiveStats['Health']) ? (int)$effectiveStats['Health'] : 100;
                if ($health <= 0) {
                    $deathCause = 'health_collapse';
                    $this->eventService->markCharacterDeath($character, $deathCause, [
                        'event_title' => $eventData['title'] ?? ($event->event_choice ?? $event->title ?? 'Event'),
                        'choice' => $choiceText,
                    ]);
                    $gameOver = true;
                    $endingType = $this->determineEndingType($character, $deathCause);
                }
            }

            if ($gameOver && $endingType) {
                $endingDetails = $this->getEndingDetails($endingType, $character);
                $endingTitle = $endingDetails['title'];
                $endingDescription = $endingDetails['description'];
            }

            $afterSnapshot = [
                'day' => $character->current_day,
                'stats' => $character->stats,
                'hidden_stats' => $character->hidden_stats,
                'effective_stats' => $character->effective_stats,
                'narrative' => $character->current_narrative,
                'active_event_paths' => $character->active_event_paths,
            ];

            $effectiveDelta = [];
            $beforeEffective = is_array($beforeSnapshot['effective_stats']) ? $beforeSnapshot['effective_stats'] : [];
            $afterEffective = is_array($afterSnapshot['effective_stats']) ? $afterSnapshot['effective_stats'] : [];
            foreach ($afterEffective as $stat => $value) {
                $effectiveDelta[$stat] = $value - ($beforeEffective[$stat] ?? 0);
            }

            try {
                // Check game session first, then fall back to auth
                $gameUserId = session()->get('game_user_id');
                if ($gameUserId) {
                    $authUser = User::find($gameUserId);
                } else {
                    $authUser = Auth::user();
                }
                
                Log::info('SharedDecisionLog check', [
                    'user_id' => $authUser ? $authUser->id : 'none',
                    'is_guest' => $authUser ? ($authUser->is_guest ?? false) : 'N/A',
                    'share_consent' => $authUser ? ($authUser->share_consent ?? null) : 'N/A',
                    'character_id' => $character->id,
                    'event_type' => $validated['event_type'],
                    'event_id' => $validated['event_id'],
                ]);
                
                // Log ALL user decisions for admin analytics (share_consent controls public display, not admin visibility)
                if ($authUser) {
                    Log::info('Creating SharedDecisionLog for user', [
                        'user_id' => $authUser->id,
                        'user_name' => $authUser->name,
                        'is_guest' => $authUser->is_guest ?? false,
                        'share_consent' => $authUser->share_consent ?? null,
                        'character_id' => $character->id,
                    ]);
                    
                    $logData = [
                        'event_title' => $event->event_choice ?? $event->title ?? null,
                        'event_description' => $event->outcome ?? $event->description ?? null,
                        'choice_text' => $choice['text'] ?? null,
                        'stat_effects_text' => $statEffects,
                        'effects' => $effects,
                        'stats_before' => $beforeSnapshot['stats'],
                        'stats_after' => $afterSnapshot['stats'],
                        'hidden_stats_before' => $beforeSnapshot['hidden_stats'],
                        'hidden_stats_after' => $afterSnapshot['hidden_stats'],
                        'effective_stats_before' => $beforeSnapshot['effective_stats'],
                        'effective_stats_after' => $afterSnapshot['effective_stats'],
                        'effective_stats_delta' => $effectiveDelta,
                        'mbti' => $this->eventService->calculateMBTI(
                            $afterSnapshot['effective_stats'] ?? $beforeSnapshot['effective_stats'] ?? [], 
                            $validated['event_type'], 
                            $choiceIndex, 
                            $outcomeType
                        ),
                        'narrative_before' => $beforeSnapshot['narrative'],
                        'narrative_after' => $afterSnapshot['narrative'],
                        'active_event_paths_before' => $beforeSnapshot['active_event_paths'],
                        'active_event_paths_after' => $afterSnapshot['active_event_paths'],
                    ];

                    $userName = ($authUser->is_guest ?? false) ? 'Guest' : ($authUser->name ?? 'Guest');

                    // Ensure character has anon_character_id saved to database
                    // Check using getAttributes to see the current state
                    $currentAnonId = $character->getAttributes()['anon_character_id'] ?? null;
                    if (empty($currentAnonId)) {
                        // Generate and save the anon_character_id directly
                        $anonId = Privacy::anonymize('character', $character->id);
                        $character->update(['anon_character_id' => $anonId]);
                    }
                    
                    // Refresh to get the anon_character_id from database
                    $character->refresh();

                    if (($authUser->share_consent ?? false)) {
                        // Include life stats in the log data
                        $logDataWithLifeStats = array_merge($logData ?? [], [
                            'life_stats' => [
                                'health' => $character->health ?? 78,
                                'happiness' => $character->happiness ?? 72,
                                'finance' => $character->finance ?? 20,
                                'relationship_status' => $character->relationship_status ?? 'single',
                                'profession' => $character->profession ?? null,
                                'career_level' => $character->career_level ?? 'unemployed',
                            ],
                        ]);

                        SharedDecisionLog::create([
                            'anon_user_id' => class_exists(Privacy::class) ? Privacy::anonymize('user', $authUser->id) : 'anon_' . $authUser->id,
                            'anon_character_id' => $character->anon_character_id ?? 'anon_' . $character->id,
                            'is_guest' => (bool) ($authUser->is_guest ?? false),
                            'user_name' => $userName,
                            'day' => $beforeSnapshot['day'],
                            'event_type' => $validated['event_type'],
                            'event_id' => $validated['event_id'],
                            'choice_index' => $choiceIndex,
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_text' => $logData['choice_text'] ?? null,
                            'effects' => isset($logData['effects']) && is_array($logData['effects']) ? json_encode($logData['effects']) : ($logData['effects'] ?? null),
                            'mbti' => $logData['mbti'] ?? null,
                            'profession' => $character->profession ?? null,
                            'data' => $logDataWithLifeStats,
                        ]);
                        
                        Log::info('SharedDecisionLog created successfully', [
                            'log_id' => SharedDecisionLog::latest()->first()?->id,
                        ]);

                        // Also save to LifeStatsSnapshot for dedicated tracking
                        LifeStatsSnapshot::create([
                            'anon_user_id' => class_exists(Privacy::class) ? Privacy::anonymize('user', $authUser->id) : 'anon_' . $authUser->id,
                            'anon_character_id' => $character->anon_character_id ?? 'anon_' . $character->id,
                            'is_guest' => (bool) ($authUser->is_guest ?? false),
                            'user_name' => $userName,
                            'day' => $beforeSnapshot['day'],
                            'event_type' => $validated['event_type'],
                            'event_id' => $validated['event_id'],
                            'choice_index' => $choiceIndex,
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_text' => $logData['choice_text'] ?? null,
                            // Before state
                            'before_health' => $beforeSnapshot['health'] ?? ($character->health ?? 78),
                            'before_happiness' => $beforeSnapshot['happiness'] ?? ($character->happiness ?? 72),
                            'before_finance' => $beforeSnapshot['finance'] ?? ($character->finance ?? 20),
                            'relationship_status' => $character->relationship_status ?? 'single',
                            'profession' => $character->profession ?? null,
                            'career_level' => $character->career_level ?? 'unemployed',
                            'mbti' => $logData['mbti'] ?? null,
                            'data' => $logData,
                        ]);

                        // Also save comprehensive DecisionLog for all decisions (tracks all decisions regardless of share_consent)
                        DecisionLog::create([
                            'character_id' => $character->id,
                            'user_id' => $authUser->id ?? null,
                            'anon_user_id' => class_exists(Privacy::class) ? Privacy::anonymize('user', $authUser->id ?? 0) : 'anon_' . ($authUser->id ?? 0),
                            'anon_character_id' => $character->anon_character_id ?? 'anon_' . $character->id,
                            'is_guest' => (bool) ($authUser->is_guest ?? false),
                            'user_name' => $userName,
                            'day' => $beforeSnapshot['day'],
                            'age_group' => $character->age_group,
                            'profession' => $character->profession ?? null,
                            'event_type' => $validated['event_type'],
                            'event_id' => $validated['event_id'],
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_index' => $choiceIndex,
                            'choice_text' => $logData['choice_text'] ?? null,
                            'outcome' => $logData['event_description'] ?? null,
                            'effects' => json_encode($effects),
                            // Before state
                            'before_health' => $beforeSnapshot['health'] ?? ($character->health ?? 78),
                            'before_happiness' => $beforeSnapshot['happiness'] ?? ($character->happiness ?? 72),
                            'before_finance' => $beforeSnapshot['finance'] ?? ($character->finance ?? 20),
                            'before_relationship_status' => $beforeSnapshot['relationship_status'] ?? ($character->relationship_status ?? 'single'),
                            'before_profession' => $beforeSnapshot['profession'] ?? ($character->profession ?? null),
                            'before_career_level' => $beforeSnapshot['career_level'] ?? ($character->career_level ?? 'unemployed'),
                            // After state
                            'after_health' => $character->health ?? 78,
                            'after_happiness' => $character->happiness ?? 72,
                            'after_finance' => $character->finance ?? 20,
                            'after_relationship_status' => $character->relationship_status ?? 'single',
                            'after_profession' => $character->profession ?? null,
                            'after_career_level' => $character->career_level ?? 'unemployed',
                            // Changes
                            'health_change' => ($character->health ?? 78) - ($beforeSnapshot['health'] ?? ($character->health ?? 78)),
                            'happiness_change' => ($character->happiness ?? 72) - ($beforeSnapshot['happiness'] ?? ($character->happiness ?? 72)),
                            'finance_change' => ($character->finance ?? 20) - ($beforeSnapshot['finance'] ?? ($character->finance ?? 20)),
                            // Additional
                            'mbti' => $logData['mbti'] ?? null,
                            'data' => $logData,
                        ]);
                    } else {
                        Log::info('Skipped SharedDecisionLog - no share_consent', [
                            'user_id' => $authUser->id,
                        ]);
                    }
                }
            } catch (\Throwable $logError) {
                Log::error('Failed to log character decision', [
                    'character_id' => $character->id,
                    'event_type' => $validated['event_type'] ?? null,
                    'event_id' => $validated['event_id'] ?? null,
                    'error' => $logError->getMessage(),
                    'trace' => $logError->getTraceAsString(),
                ]);
            }

            // AUTO-CHECK ACHIEVEMENTS after event outcome is applied
            $newAchievements = [];
            try {
                $newAchievements = $this->achievementService->checkAndUnlockAchievements($character);
                if (!empty($newAchievements)) {
                    Log::info('New achievements unlocked after event', [
                        'character_id' => $character->id,
                        'achievements' => $newAchievements,
                    ]);
                }
            } catch (\Throwable $achievementError) {
                Log::error('Failed to check achievements', [
                    'character_id' => $character->id,
                    'error' => $achievementError->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Event outcome applied',
                'character' => $character,
                'new_achievements' => $newAchievements, // Auto-checked achievements
                'effects' => $effects,
                // Random outcome info - visible to player!
                'random_outcome' => $randomOutcomeInfo ?? null,
                'choice_outcome' => $choiceOutcomeText,
                'game_over' => $gameOver,
                'ending_type' => $endingType,
                'ending_title' => $endingTitle,
                'ending_description' => $endingDescription,
                'character_state' => $character->character_state,
                // Include skills and talents for frontend update
                'skills' => $character->skills->toArray(),
                'talents' => $character->talents->toArray(),
                // Skill/Talent learning results
                'skill_learned' => $skillLearningResult,
                'talent_discovered' => $talentDiscoveryResult,
                'reputation_changes' => $reputationChanges,
                // Trauma healing/gaining results
                'trauma_healed' => $traumaHealed ?? [],
                'trauma_gained' => $traumaGained ?? [],
                // Relationship changes
                'relationship_changes' => $relationshipChanges ?? [],
                'social_connections_added' => $socialConnectionsAdded ?? [],
                // Environment changes
                'location_changed' => $locationChanged ?? null,
                'season_changed' => $seasonChanged ?? null,
                'weather_changed' => $weatherChanged ?? null,
                // Feature 4: Age instead of day
                'age' => $this->calculateAge($character->current_day),
                'age_group' => $character->age_group,
            'health_status' => $this->eventService->getHealthStatus($character->health ?? 78),
            'health_percentage' => $character->health ?? 78,
                // Feature 8: Severe consequences
                'consequences' => $consequences ?? [],
                'current_day' => $character->current_day,
                'narrative_path' => $character->current_narrative,
                'active_paths' => $character->active_event_paths,
                'milestone' => $this->checkMilestone($character)
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in applyEventOutcome: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error applying event: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function suicide(Request $request, Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'method' => 'nullable|string|max:255',
            ]);

            $method = trim((string) ($validated['method'] ?? ''));

            if (!$this->eventService->isDead($character)) {
                $this->eventService->markCharacterDeath($character, 'suicide', [
                    'method' => $method !== '' ? $method : 'unknown',
                ]);
            }

            $character->refresh();
            $endingType = $this->determineEndingType($character, 'suicide');
            $endingDetails = $this->getEndingDetails($endingType, $character);

            return response()->json([
                'message' => 'Your journey has ended.',
                'character' => $character,
                'game_over' => true,
                'ending_type' => $endingType,
                'ending_title' => $endingDetails['title'],
                'ending_description' => $endingDetails['description'],
                'character_state' => $character->character_state,
                'age' => $this->calculateAge((int) ($character->current_day ?? 1)),
                'age_group' => $character->age_group,
                'current_day' => $character->current_day,
                'health_status' => 'dead',
                'health_percentage' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in suicide: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error ending journey: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reincarnate - Start a new life with optional karma bonus
     */
    public function reincarnate(Request $request, Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|string|in:male,female,other',
            ]);

            // Calculate karma bonus (previous life's karma affects new life)
            $karma = $character->karma ?? 0;
            $luckBonus = min(20, max(-20, (int) ($karma / 1000))); // -20 to +20 luck bonus

            // Create new character for the same user
            $newCharacter = Character::create([
                'user_id' => $character->user_id,
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'age_group' => 'child',
                'current_day' => 1,
                'profession' => null,
                'health' => 100 + ($luckBonus > 0 ? $luckBonus : 0), // Positive karma = better health start
                'happiness' => 80,
                'finance' => 0,
                'luck' => 50 + $luckBonus, // Karma affects starting luck
                'karma' => 0, // Reset karma for new life
                'relationship_status' => 'single',
                'career_level' => 1,
                'stats' => [
                    'Health' => 100,
                    'Happiness' => 80,
                    'Finance' => 0,
                    'Ego' => 50,
                    'Discipline' => 50,
                    'Morality' => 50,
                    'Social' => 50,
                    'Intelligence' => 50,
                    'Burnout' => 0,
                    'Addiction' => 0,
                    'Isolation' => 0,
                ],
                'shown_event_ids' => [],
                'completed_event_chains' => [],
                'active_event_paths' => [],
                'current_narrative' => null,
                'choice_history' => [],
                'relationship_state' => [],
                'reputation_by_faction' => [],
                'trauma_flags' => [],
                'achievement_flags' => $character->achievement_flags ?? [], // Carry over achievements
                'pending_events' => [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reincarnation successful! A new journey begins.',
                'character' => $newCharacter,
                'karma_bonus' => $luckBonus,
                'previous_life' => [
                    'name' => $character->name,
                    'karma_earned' => $karma,
                    'achievements_kept' => count($character->achievement_flags ?? []),
                ],
                'new_life' => [
                    'luck_bonus' => $luckBonus,
                    'starting_health' => 100 + ($luckBonus > 0 ? $luckBonus : 0),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in reincarnate: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error during reincarnation: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle event branching - update narrative path and complete chains
     */
    private function handleEventBranching(Character $character, $event, array $effects): void
    {
        // Get the event category if it exists
        $eventCategory = $event->event_category ?? null;
        
        if (empty($eventCategory)) {
            // Try to determine category from event name
            $eventName = $event->event_choice ?? '';
            $eventCategory = $this->inferEventCategory($eventName);
        }
        
        if (empty($eventCategory)) {
            return; // No branching for events without categories
        }
        
        // Get the chain order from the event
        $chainOrder = (int) ($event->chain_order ?? 1);
        
        // Determine outcome type based on stat effects
        $outcomeType = $this->eventService->determineOutcomeType($event->stat_effects ?? '');
        
        // Update the narrative path using NarrativeService
        $narrativeService = app(\App\Services\NarrativeService::class);
        $narrativeService->updateNarrativePath($character, $eventCategory, $outcomeType);
        
        // Complete the event chain and unlock next events (with chain order)
        $this->eventService->completeEventChain($character, $eventCategory, $outcomeType, $chainOrder);
    }

    /**
     * Infer event category from event name
     */
    private function inferEventCategory(string $eventName): ?string
    {
        $eventName = strtolower($eventName);
        
        // Education-related events
        if (preg_match('/(school|exam|college|university|grade|homework|study|learning)/', $eventName)) {
            return 'education';
        }
        
        // Career-related events
        if (preg_match('/(job|career|work|promotion|office|boss|colleague|business)/', $eventName)) {
            return 'career';
        }
        
        // Family-related events
        if (preg_match('/(marriage|wedding|spouse|child|parent|family|sibling|dating|engaged)/', $eventName)) {
            return 'family';
        }
        
        // Health-related events
        if (preg_match('/(health|illness|disease|doctor|hospital|injury|accident|fitness|exercise)/', $eventName)) {
            return 'health';
        }
        
        // Wealth-related events
        if (preg_match('/(money|finance|investment|rich|poor|broke|shopping)/', $eventName)) {
            return 'wealth';
        }
        
        // Social-related events
        if (preg_match('/(friend|date|relationship|party|social|bully|community)/', $eventName)) {
            return 'social';
        }
        
        // Skill-related events
        if (preg_match('/(skill|talent|hobby|practice|training|sport)/', $eventName)) {
            return 'skill';
        }
        
        return null;
    }

    /**
     * Get event by type and ID
     */
    private function getEventById(string $type, int $id)
    {
        return match($type) {
            'daily' => DailyEvent::find($id),
            'cultural' => CulturalEvent::find($id),
            'ageSpecific' => AgeSpecificEvent::find($id),
            'profession' => ProfessionPathEvent::find($id),
            'trigger' => StatTriggerCondition::find($id),
            'learning', 'action', 'system' => DailyAction::find($id),
            default => null
        };
    }

    private function buildTerminalStatePayload(Character $character): array
    {
        $isDead = $this->eventService->isDead($character);
        $cause = $isDead
            ? (string) ((is_array($character->character_state) ? ($character->character_state['death_cause'] ?? null) : null) ?? 'death')
            : 'old_age';
        $endingType = $this->determineEndingType($character, $cause === 'old_age' ? 'old_age' : $cause);
        $endingDetails = $this->getEndingDetails($endingType, $character);

        return [
            'life_actions' => [],
            'triggers' => [],
            'daily' => [],
            'cultural' => [],
            'ageSpecific' => [],
            'profession' => [],
            'profession_choices' => [],
            'milestone' => null,
            'current_state' => $character->current_state,
            'character_state' => $character->character_state,
            'character' => $character,
            'game_over' => true,
            'ending_type' => $endingType,
            'death_cause' => $cause,
            'ending_title' => $endingDetails['title'],
            'ending_description' => $endingDetails['description'],
            'age' => $this->calculateAge((int) ($character->current_day ?? 1)),
            'current_day' => (int) ($character->current_day ?? 1),
            'age_group' => $character->age_group,
            'health_status' => $isDead ? 'dead' : $this->eventService->getHealthStatus($character->health ?? 78),
            'health_percentage' => $isDead ? 0 : ($character->health ?? 78),
            'actions' => [],
        ];
    }

    private function dedupeFormattedEvents(array $events): array
    {
        $seen = [];
        $unique = [];

        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            $title = strtolower(trim((string) ($event['title'] ?? '')));
            $signature = preg_replace('/[^a-z0-9]+/i', ' ', $title) ?: '';
            $signature = trim((string) $signature);

            if ($signature === '') {
                $signature = (string) ($event['type'] ?? 'event') . ':' . (string) ($event['id'] ?? uniqid('event_', true));
            }

            if (isset($seen[$signature])) {
                continue;
            }

            $seen[$signature] = true;
            $unique[] = $event;
        }

        return array_values($unique);
    }

    /**
     * Get daily actions from database based on character conditions.
     */
    private function getSystemActions(Character $character): array
    {
        $state = is_array($character->character_state) ? $character->character_state : [];

        // If already dead/over, hide actions.
        if (($state['health_condition'] ?? null) === 'dead') {
            return [];
        }

        // Fetch all daily actions from database
        $dailyActions = DailyAction::orderBy('display_order')->get();

        // Filter actions based on character conditions
        $actions = [];
        foreach ($dailyActions as $action) {
            if ($action->isAvailable($character)) {
                $actionData = $action->toActionArray();
                $actionData['choices'] = $this->resolveEventChoices($action, 'system', $character, $character->age_group ?? 'adult');
                $actions[] = $actionData;
            }
        }

        $profile = $this->adaptiveNarrativeService->getDecisionProfile($character);
        $flags = is_array($profile['flags'] ?? null) ? $profile['flags'] : [];

        if (($flags['burnout_cycle'] ?? false) === true) {
            $actions[] = $this->buildSystemAction(
                91001,
                'Recovery Plan',
                'Your recent pace is catching up to you. What kind of recovery do you choose?',
                '/css/images/event-placeholder.jpg',
                [
                    ['text' => 'Take a full recovery day', 'stat_effects' => '-8 Burnout, +4 Health, +3 Happiness', 'days_to_advance' => 1],
                    ['text' => 'Scale back and recover slowly', 'stat_effects' => '-4 Burnout, +2 Health, +1 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Ignore the warning signs', 'stat_effects' => '+6 Burnout, -3 Health, -2 Happiness', 'days_to_advance' => 0],
                ]
            );
        }

        if (($flags['relationship_strain'] ?? false) === true) {
            $actions[] = $this->buildSystemAction(
                91002,
                'Repair a Relationship',
                'Distance has built up. Do you try to fix it or keep avoiding it?',
                '/css/images/event-placeholder.jpg',
                [
                    ['text' => 'Have the difficult conversation', 'stat_effects' => '+5 Happiness, -5 Isolation, +3 Morality', 'days_to_advance' => 0],
                    ['text' => 'Send a small peace offering', 'stat_effects' => '+2 Happiness, -2 Isolation, +1 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay distant', 'stat_effects' => '+4 Isolation, -4 Happiness, +2 Burnout', 'days_to_advance' => 0],
                ]
            );
        }

        if (($flags['scandal_marked'] ?? false) === true) {
            $actions[] = $this->buildSystemAction(
                91003,
                'Rebuild Reputation',
                'People are talking. You need to decide how to respond.',
                '/css/images/event-placeholder.jpg',
                [
                    ['text' => 'Own the mistake publicly', 'stat_effects' => '+5 Morality, +4 Reputation, -2 Ego', 'days_to_advance' => 0],
                    ['text' => 'Quietly repair the damage', 'stat_effects' => '+2 Reputation, +1 Discipline, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Double down and fight back', 'stat_effects' => '+4 Ego, -5 Reputation, +3 Burnout', 'days_to_advance' => 0],
                ]
            );
        }

        if (($flags['dependency_flag'] ?? false) === true) {
            $actions[] = $this->buildSystemAction(
                91004,
                'Break the Habit',
                'A coping habit is starting to take over your routine.',
                '/css/images/event-placeholder.jpg',
                [
                    ['text' => 'Ask for structured help', 'stat_effects' => '-6 Addiction, -3 Burnout, +2 Health', 'days_to_advance' => 1],
                    ['text' => 'Manage it alone', 'stat_effects' => '-2 Addiction, -1 Burnout, -1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Give in again', 'stat_effects' => '+5 Addiction, +2 Happiness, -3 Health', 'days_to_advance' => 0],
                ]
            );
        }

        if (($flags['financial_trap'] ?? false) === true) {
            $actions[] = $this->buildSystemAction(
                91005,
                'Financial Recovery',
                'Money pressure is shaping your life. How do you respond?',
                '/css/images/event-placeholder.jpg',
                [
                    ['text' => 'Commit to a recovery budget', 'stat_effects' => '-5 Debt, +3 Discipline, -2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Take an extra shift', 'stat_effects' => '+5 Wealth, +4 Burnout, -2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Take the easy money', 'stat_effects' => '+8 Wealth, +5 Debt, -3 Reputation', 'days_to_advance' => 0],
                ]
            );
        }

        return $this->dedupeFormattedEvents($actions);
    }

    private function buildSystemAction(int $id, string $title, string $description, string $image, array $choices, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'system',
            'deck_label' => 'Action',
            'repeatable' => true,
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'outcome' => $description,
            'statEffects' => null,
            'choices' => $choices,
            'weight' => 0,
            'auto_resolve' => false,
            'days_to_advance' => 0,
        ], $extra);
    }

    private function getSystemEventById(Character $character, int $id): ?array
    {
        // First try to find in available actions (filtered by isAvailable)
        $actions = $this->getSystemActions($character);
        
        Log::debug('getSystemEventById called', [
            'character_id' => $character->id,
            'search_id' => $id,
            'available_actions_count' => count($actions),
            'available_action_ids' => array_column($actions, 'id'),
        ]);
        
        foreach ($actions as $action) {
            if ((int) ($action['id'] ?? 0) === $id) {
                return $action;
            }
        }
        
        // If not found in filtered list, try to find directly in database
        // This ensures the action can be found even if isAvailable() conditions changed
        $dailyAction = DailyAction::find($id);
        if ($dailyAction) {
            Log::debug('getSystemEventById - found in database directly', [
                'action_id' => $dailyAction->id,
                'action_title' => $dailyAction->title,
            ]);
            return [
                'id' => $dailyAction->id,
                'title' => $dailyAction->title,
                'description' => $dailyAction->description,
                'image' => $dailyAction->image,
                'type' => $dailyAction->type,
                'deck_label' => $dailyAction->deck_label,
                'choices' => $dailyAction->choices,
                'conditions' => $dailyAction->conditions,
            ];
        }
        
        Log::warning('getSystemEventById - action not found', [
            'search_id' => $id,
            'character_id' => $character->id,
        ]);
        
        return null;
    }

    private function ensureArray(mixed $value): array
    {
        if ($value instanceof Collection) {
            return $value->values()->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        return [$value];
    }

    private function isRepeatableEvent(string $type, object $event): bool
    {
        // System actions are always repeatable.
        if ($type === 'system') {
            return true;
        }

        // Daily + cultural are meant to be repeatable actions.
        if (in_array($type, ['daily', 'cultural'], true)) {
            return true;
        }

        // Stat triggers should generally be one-time.
        if ($type === 'trigger') {
            return false;
        }

        // Milestones / chain events should be one-time; standalone events can repeat.
        $isMilestone = (bool) ($event->is_milestone ?? false);
        $hasChain = !empty($event->parent_category ?? null) || !empty($event->chain_order ?? null);

        return !$isMilestone && !$hasChain;
    }

    /**
     * Format event for API response
     */
    private function formatEvent($event, string $type, ?Character $character = null): array
    {
        // Determine the correct image path
        $image = $event->image ?? null;
        
        // If no image is set, try to generate one based on event title/type
        if (empty($image)) {
            if ($type === 'ageSpecific') {
                // Use age-group folder with slugified title
                $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $event->event_choice ?? $event->title ?? ''));
                $image = "/css/images/age-group/{$titleSlug}.png";
            } elseif ($type === 'cultural') {
                $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $event->event_choice ?? $event->title ?? ''));
                $image = "/css/images/culturalevents/{$titleSlug}.jpg";
            } elseif ($type === 'daily') {
                $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $event->event_choice ?? $event->title ?? ''));
                $image = "/css/images/dailyevents/{$titleSlug}.jpg";
            } elseif ($type === 'profession') {
                $titleSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $event->event_choice ?? $event->title ?? ''));
                $image = "/css/images/profession/{$titleSlug}.jpg";
            } else {
                $image = '/css/images/event-placeholder.jpg';
            }
        }
        
        $title = $event->event_choice ?? $event->title;
        $description = $event->outcome ?? $event->description;
        $archetype = $this->adaptiveNarrativeService->detectArchetype((string) $title, $description, $type);

        return [
            'id' => $event->id,
            'type' => $type,
            'deck_label' => match($type) {
                'daily' => 'Daily',
                'cultural' => 'Culture',
                'ageSpecific' => 'Story',
                'profession' => 'Career',
                'trigger' => 'Trigger',
                default => 'Event',
            },
            'repeatable' => $this->isRepeatableEvent($type, $event),
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'outcome' => $event->outcome,
            'statEffects' => $event->stat_effects,
            'choices' => $this->resolveEventChoices($event, $type, $character, $character?->age_group),
            'weight' => $event->dynamic_weight ?? $event->calculated_weight ?? $event->weight,
            'archetype' => $archetype,
            // Feature 5: Game-decided outcomes
            'auto_resolve' => $event->auto_resolve ?? false,
            // Feature 3: Days to advance
            'days_to_advance' => $event->days_to_advance ?? 0,
        ];
    }

    /**
     * Format luck event for API response
     */
    private function formatLuckEvent(array $luckEvent, ?Character $character = null): array
    {
        $title = $luckEvent['title'] ?? 'Lucky Event';
        $description = $luckEvent['description'] ?? '';
        
        // Generate image path based on luck type
        $luckType = $luckEvent['luck_type'] ?? 'neutral';
        $image = $luckType === 'fortune' 
            ? '/css/images/luck/fortune.png'
            : ($luckType === 'misfortune' 
                ? '/css/images/luck/misfortune.png'
                : '/css/images/luck/neutral.png');
        
        // Format choices if present
        $choices = [];
        if (!empty($luckEvent['choices'])) {
            foreach ($luckEvent['choices'] as $index => $choice) {
                $choices[] = [
                    'text' => $choice['text'] ?? 'Choice ' . ($index + 1),
                    'effects' => $choice['effects'] ?? [],
                    'weight' => $choice['weight'] ?? 1,
                ];
            }
        }
        
        return [
            'id' => $luckEvent['id'] ?? 'luck_' . uniqid(),
            'type' => 'luck',
            'deck_label' => 'Luck',
            'repeatable' => true,
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'outcome' => null,
            'statEffects' => null,
            'choices' => $choices,
            'luck_type' => $luckType,
            'category' => $luckEvent['category'] ?? 'random',
            'weight' => $luckEvent['weight'] ?? 1,
            'archetype' => 'luck_event',
            'auto_resolve' => empty($choices),
            'days_to_advance' => 0,
        ];
    }

    private function resolveEventChoices($event, string $type, ?Character $character = null, ?string $ageGroup = null): array
    {
        $rawChoices = $event->choices ?? null;

        if (is_string($rawChoices)) {
            $decoded = json_decode($rawChoices, true);
            $rawChoices = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($rawChoices)) {
            $rawChoices = [];
        }

        $choices = $this->adaptiveNarrativeService->buildChoicesForEvent(
            (string) ($event->event_choice ?? $event->title ?? 'Event'),
            $event->outcome ?? $event->description ?? null,
            $event->stat_effects ?? null,
            $type,
            (string) ($ageGroup ?? $event->age_group ?? 'adult'),
            $rawChoices
        );

        if ($character) {
            $choices = $this->adaptiveNarrativeService->adaptChoicesForCharacter(
                $character,
                [
                    'title' => (string) ($event->event_choice ?? $event->title ?? 'Event'),
                    'description' => $event->outcome ?? $event->description ?? null,
                    'type' => $type,
                    'age_group' => (string) ($ageGroup ?? $event->age_group ?? 'adult'),
                    'archetype' => $this->adaptiveNarrativeService->detectArchetype(
                        (string) ($event->event_choice ?? $event->title ?? 'Event'),
                        $event->outcome ?? $event->description ?? null,
                        $type
                    ),
                ],
                $choices
            );
        }

        // Check for locked choices and mark them
        if ($character) {
            $eventChoiceId = $event->event_choice ?? $event->title ?? null;
            foreach ($choices as &$choice) {
                $choiceId = ($eventChoiceId ?? 'unknown') . '_' . ($choice['text'] ?? '');
                $choice['is_locked'] = $character->isChoiceLocked($choiceId);
            }
        }

        return $this->formatChoices($choices);
    }

    /**
     * Format choices with default if not provided
     */
    private function formatChoices($choices): array
    {
        // If choices is null or empty, return 4 diverse choices
        if ($choices === null || $choices === '' || $choices === 'null' || empty($choices)) {
            return [
                [
                    'text' => 'Do it carefully',
                    'stat_effects' => '+2 Happiness, +1 Discipline, -1 Burnout',
                    'days_to_advance' => 0
                ],
                [
                    'text' => 'Take a shortcut',
                    'stat_effects' => '+1 Happiness, +1 Burnout, -1 Discipline',
                    'days_to_advance' => 0
                ]
            ];
        }

        // If choices is a JSON string, decode it
        if (is_string($choices)) {
            $decoded = json_decode($choices, true);
            if (is_array($decoded) && count($decoded) > 0) {
                // Ensure each choice has days_to_advance
                foreach ($decoded as &$choice) {
                    // If explicitly set to > 0, preserve it. Otherwise default to 1.
                    if (!isset($choice['days_to_advance']) || $choice['days_to_advance'] == 0) {
                        $choice['days_to_advance'] = 1;
                    }
                    $choice['text'] = $this->normalizeChoiceText($choice['text'] ?? null);
                }
                return $decoded;
            }
        }

        // If already an array, return it (ensure at least 1)
        if (is_array($choices) && count($choices) > 0) {
            // Ensure each choice has days_to_advance
            foreach ($choices as &$choice) {
                if (!isset($choice['days_to_advance'])) {
                    $choice['days_to_advance'] = 0;
                }
                $choice['text'] = $this->normalizeChoiceText($choice['text'] ?? null);
            }
            return $choices;
        }

        // Fallback to 4 CLEAR choices
        return [
            [
                'text' => 'Take the Opportunity',
                'stat_effects' => '+10 Happiness, +5 Health',
                'days_to_advance' => 0
            ],
            [
                'text' => 'Go with the Flow',
                'stat_effects' => null,
                'days_to_advance' => 0
            ],
            [
                'text' => 'Avoid the Situation',
                'stat_effects' => '-5 Happiness, +5 Burnout',
                'days_to_advance' => 0
            ],
            [
                'text' => 'Do Nothing (Skip)',
                'stat_effects' => '+10 Burnout',
                'days_to_advance' => 0
            ]
        ];
    }

    private function normalizeChoiceText(?string $text): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return 'Choose';
        }

        // Remove explicit outcome labels like "(Good)", "(Neutral)", "(Bad)"
        $text = preg_replace('/\\((good|neutral|bad|skip)\\)/i', '', $text) ?? $text;
        $text = preg_replace('/\\b(good|neutral|bad)\\b\\s*[:\\-]\\s*/i', '', $text) ?? $text;

        $text = trim(preg_replace('/\\s+/', ' ', $text) ?? $text);
        return $text === '' ? 'Choose' : $text;
    }

    private function mergeStatEffectsText(?string ...$effectsTexts): ?string
    {
        $parts = [];
        foreach ($effectsTexts as $text) {
            $text = trim((string) $text);
            if ($text === '' || strtolower($text) === 'null') {
                continue;
            }
            $parts[] = $text;
        }

        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Resolve a choice's outcome variant in a deterministic, game-decided way.
     *
     * Supported structure:
     *  - choice['outcomes'] = [
     *      ['key' => 'success'|'failure', 'text' => '...', 'stat_effects' => '+5 Charisma'],
     *      ['key' => 'failure', ...],
     *    ]
     *  - Optional requirements per outcome: required_stat/stat_threshold, min_age/max_age, requires_state (map)
     */
    private function resolveChoiceOutcomeVariant(Character $character, object $event, array $choice): ?array
    {
        $outcomesRaw = $choice['outcomes'] ?? null;
        if (!is_array($outcomesRaw) || empty($outcomesRaw)) {
            return null;
        }

        $outcomes = array_values(array_filter($outcomesRaw, fn($o) => is_array($o)));
        if (empty($outcomes)) {
            return null;
        }

        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $state = is_array($character->character_state) ? $character->character_state : [];
        $age = $this->calculateAge((int) ($character->current_day ?? 1));

        $hasKeys = false;
        foreach ($outcomes as $o) {
            if (!empty($o['key'])) {
                $hasKeys = true;
                break;
            }
        }

        // If outcomes are keyed, pick success/failure by deterministic stat-based resolution.
        if ($hasKeys) {
            $key = $this->eventService->determineGameOutcome($character, $event) === 'success' ? 'success' : 'failure';

            foreach ($outcomes as $o) {
                if (strtolower((string) ($o['key'] ?? '')) !== $key) {
                    continue;
                }
                if ($this->choiceOutcomeMatches($o, $effectiveStats, $state, $age)) {
                    return $o;
                }
            }

            // Fallback: first matching keyed outcome, else first outcome.
            foreach ($outcomes as $o) {
                if ($this->choiceOutcomeMatches($o, $effectiveStats, $state, $age)) {
                    return $o;
                }
            }
            return $outcomes[0];
        }

        // Otherwise: pick first matching requirement-based outcome.
        foreach ($outcomes as $o) {
            if ($this->choiceOutcomeMatches($o, $effectiveStats, $state, $age)) {
                return $o;
            }
        }

        return $outcomes[0];
    }

    private function choiceOutcomeMatches(array $outcome, array $effectiveStats, array $state, int $age): bool
    {
        if (isset($outcome['min_age']) && $age < (int) $outcome['min_age']) {
            return false;
        }
        if (isset($outcome['max_age']) && $age > (int) $outcome['max_age']) {
            return false;
        }

        if (!empty($outcome['required_stat'])) {
            $requiredStat = (string) $outcome['required_stat'];
            $threshold = isset($outcome['stat_threshold']) ? (int) $outcome['stat_threshold'] : 50;
            $statValue = (int) ($effectiveStats[$requiredStat] ?? 0);
            if ($statValue < $threshold) {
                return false;
            }
        }

        if (!empty($outcome['requires_state']) && is_array($outcome['requires_state'])) {
            foreach ($outcome['requires_state'] as $k => $expected) {
                $actual = $state[$k] ?? null;
                if ((bool) $actual !== (bool) $expected) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Normalize age group names
     */
    private function normalizeAgeGroup(string $ageGroup): string
    {
        return match($ageGroup) {
            'child', 'children' => 'child',
            'teenager', 'teen', 'adolescent' => 'teen',
            'adult' => 'adult',
            'old', 'elder', 'elderly' => 'old',
            default => 'adult'
        };
    }
    
    /**
     * Determine the ending type based on character stats
     */
    private function determineEndingType($character, string $cause): string
    {
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $stats = is_array($character->stats) ? $character->stats : [];
        
        // Merge stats for evaluation
        $allStats = array_merge($stats, $effectiveStats);
        
        // Get key stats (default to character defaults if not set)
        $health = (int)($allStats['Health'] ?? 78);
        $happiness = (int)($allStats['Happiness'] ?? 72);
        $wealth = (int)($allStats['Wealth'] ?? 20);
        $reputation = (int)($allStats['Reputation'] ?? 50);
        $morality = (int)($allStats['Morality'] ?? 50);
        $intelligence = (int)($allStats['Intelligence'] ?? 50);
        $discipline = (int)($allStats['Discipline'] ?? 50);
        $burnout = (int)($allStats['Burnout'] ?? 0);
        $isolation = (int)($allStats['Isolation'] ?? 0);
        
        // If died early (not old age), check what kind of death
        if ($cause === 'death') {
            if ($burnout >= 20 || $isolation >= 20) {
                return 'tragic_end';
            }
            return 'premature_death';
        }
        
        // Old age endings - evaluate based on stats
        $score = 0;
        
        // Wealth score (0-25 points)
        if ($wealth >= 80) $score += 25;
        elseif ($wealth >= 50) $score += 15;
        elseif ($wealth >= 20) $score += 5;
        
        // Happiness score (0-25 points)
        if ($happiness >= 70) $score += 25;
        elseif ($happiness >= 50) $score += 15;
        elseif ($happiness >= 30) $score += 5;
        
        // Reputation score (0-20 points)
        if ($reputation >= 70) $score += 20;
        elseif ($reputation >= 50) $score += 10;
        elseif ($reputation >= 30) $score += 5;
        
        // Health score (0-15 points)
        if ($health >= 60) $score += 15;
        elseif ($health >= 40) $score += 10;
        elseif ($health >= 20) $score += 5;
        
        // Morality score (0-15 points)
        if ($morality >= 70) $score += 15;
        elseif ($morality >= 50) $score += 10;
        elseif ($morality >= 30) $score += 5;
        
        // Determine ending based on score
        if ($score >= 80) {
            return 'legendary_end';
        } elseif ($score >= 60) {
            return 'successful_end';
        } elseif ($score >= 40) {
            return 'peaceful_end';
        } elseif ($score >= 20) {
            return 'modest_end';
        } else {
            return 'humble_end';
        }
    }
    
    /**
     * Get ending details based on ending type
     */
    private function getEndingDetails(string $endingType, $character): array
    {
        $endings = [
            // Good endings (old age)
            'legendary_end' => [
                'title' => '🏆 LEGENDARY LIFE',
                'description' => 'You lived an extraordinary life! Your achievements, wealth, and legacy will be remembered for generations. You departed this world as a legend!'
            ],
            'successful_end' => [
                'title' => '⭐ SUCCESSFUL LIFE',
                'description' => 'You achieved great success in your career and built a comfortable life. Your accomplishments brought you fulfillment and respect from others.'
            ],
            'peaceful_end' => [
                'title' => '🕊️ PEACEFUL REST',
                'description' => 'You lived a balanced life with good health, loving relationships, and inner peace. You passed away serenely, surrounded by cherished memories.'
            ],
            'modest_end' => [
                'title' => '🏠 HUMBLE YET FULFILLING',
                'description' => 'Your life was modest but meaningful. You had your ups and downs, but you found contentment in the simple things.'
            ],
            'humble_end' => [
                'title' => '🌱 SIMPLE BEGINNINGS',
                'description' => 'Your journey was challenging, but you persisted. Life dealt you difficult cards, but you played them with determination.'
            ],
            
            // Bad endings (premature death)
            'tragic_end' => [
                'title' => '💀 TRAGIC END',
                'description' => 'Life became overwhelming, and tragedy struck too soon. Your struggle was real, and your pain is now at peace.'
            ],
            'premature_death' => [
                'title' => '⚡ PREMATURE FATE',
                'description' => 'Your journey ended sooner than expected. Whatever path you chose, it led to an untimely end.'
            ],
        ];
        
        return $endings[$endingType] ?? [
            'title' => 'GAME OVER',
            'description' => 'Your journey has ended.'
        ];
    }

    /**
     * Re-draw all event cards - can only be used once per day
     */
    public function redrawEvents(Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if re-draw was already used today
            $today = now()->toDateString();
            $lastRedrawDate = $character->last_redraw_date;

            if ($lastRedrawDate === $today) {
                return response()->json([
                    'message' => 'Re-draw already used today',
                    'last_redraw_date' => $lastRedrawDate,
                    'available_at' => now()->addDay()->toDateString(),
                    'can_redraw' => false
                ], 400);
            }

            // Clear shown_event_ids to allow previously shown events to appear again
            $character->shown_event_ids = [];
            $character->last_redraw_date = $today;
            $character->save();

            // Fetch fresh events
            $ageGroup = $character->age_group ?? 'adult';
            
            $dailyEvents = $this->getDailyEvents($character, [], $ageGroup);
            $culturalEvents = $this->getCulturalEvents($character, []);
            
            // Get age-specific events
            $ageSpecificEvents = $this->getAgeSpecificEvents($character, $ageGroup, []);
            
            // Get profession events if applicable
            $professionEvents = [];
            if ($ageGroup === 'adult' && $character->profession) {
                $professionEvents = $this->getProfessionEvents($character, []);
            }

            return response()->json([
                'message' => 'Events re-drawn successfully',
                'can_redraw' => false,
                'last_redraw_date' => $today,
                'events' => [
                    'daily' => $dailyEvents,
                    'cultural' => $culturalEvents,
                    'ageSpecific' => $ageSpecificEvents,
                    'profession' => $professionEvents,
                    'milestone' => $this->checkMilestone($character)
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in redrawEvents: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error re-drawing events: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Re-draw a specific type of events - can only be used once per day per type
     */
    public function redrawEventType(Request $request, Character $character)
    {
        try {
            if ($character->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'event_type' => 'required|string|in:daily,cultural,ageSpecific,profession'
            ]);

            $eventType = $validated['event_type'];
            
            // Map event type to database column
            $columnMap = [
                'daily' => 'last_redraw_date_daily',
                'cultural' => 'last_redraw_date_cultural',
                'ageSpecific' => 'last_redraw_date_age_specific',
                'profession' => 'last_redraw_date_profession'
            ];
            $column = $columnMap[$eventType];

            // Check if re-draw was already used today for this specific type
            $today = now()->toDateString();
            $lastRedrawDate = $character->$column;

            if ($lastRedrawDate === $today) {
                return response()->json([
                    'message' => 'Re-draw already used today for this category',
                    'last_redraw_date' => $lastRedrawDate,
                    'available_at' => now()->addDay()->toDateString(),
                    'can_redraw' => false
                ], 400);
            }

            // Clear shown_event_ids for the specific event type to allow re-drawing
            $shownEventIds = $character->shown_event_ids ?? [];
            // Remove events of this type from shown_event_ids
            $shownEventIds = array_filter($shownEventIds, function($id) use ($eventType) {
                return !str_starts_with($id, $eventType . '_');
            });
            $character->shown_event_ids = array_values($shownEventIds);
            
            // Update the specific event type's redraw date
            $character->$column = $today;
            $character->save();

            // Fetch fresh events for the specific type
            $ageGroup = $character->age_group ?? 'adult';
            
            $newEvents = match($eventType) {
                'daily' => $this->getDailyEvents($character, [], $ageGroup),
                'cultural' => $this->getCulturalEvents($character, []),
                'ageSpecific' => $this->getAgeSpecificEvents($character, $ageGroup, []),
                'profession' => $ageGroup === 'adult' && $character->profession 
                    ? $this->getProfessionEvents($character, []) 
                    : [],
                default => []
            };

            return response()->json([
                'message' => ucfirst($eventType) . ' events re-drawn successfully',
                'can_redraw' => true,
                'last_redraw_date' => $today,
                'event_type' => $eventType,
                'events' => $newEvents
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in redrawEventType: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error re-drawing events: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mini-game data for an event
     */
    public function getMiniGame(Request $request, Character $character)
    {
        try {
            $gameType = $request->input('game_type');
            $difficulty = (int) $request->input('difficulty', 1);
            $category = $request->input('category', 'career');
            $eventId = $request->input('event_id');
            $eventType = $request->input('event_type', 'daily');
            
            // Get event's stat effects for the mini-game outcome
            $eventData = $this->getEventStatEffects($eventId, $eventType);
            
            $gameData = match($gameType) {
                'qte' => $this->miniGameService->generateQTESequence($difficulty),
                'memory_match' => $this->miniGameService->generateMemoryMatch($difficulty),
                'choice_chain' => $this->miniGameService->generateChoiceChain($category, $difficulty),
                'timing' => $this->miniGameService->generateTimingGame($difficulty),
                default => null
            };
            
            if (!$gameData) {
                return response()->json([
                    'error' => 'Invalid game type'
                ], 400);
            }
            
            // Add stat effects to game data for outcome calculation
            $gameData['stat_effects'] = $eventData['stat_effects'] ?? [];
            $gameData['event_id'] = $eventId;
            $gameData['event_type'] = $eventType;
            
            return response()->json([
                'game_type' => $gameType,
                'game_data' => $gameData,
                'difficulty' => $difficulty
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating mini-game: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate mini-game'
            ], 500);
        }
    }

    /**
     * Process mini-game result and return modified stat effects
     */
    public function submitMiniGame(Request $request, Character $character)
    {
        try {
            $gameType = $request->input('game_type');
            $score = (int) $request->input('score');
            $gameData = $request->input('game_data', []);
            $eventId = $gameData['event_id'] ?? null;
            
            // Store mini-game score in session for later use when applying the event outcome
            if ($eventId) {
                $sessionKey = 'mini_game_score_' . $character->id . '_' . $eventId;
                session([$sessionKey => $score]);
            }
            
            // Process the game result
            $result = $this->miniGameService->processGameResult(
                $character,
                $gameType,
                $gameData,
                $score
            );
            
            return response()->json([
                'success' => true,
                'effects' => $result['effects'],
                'outcome' => $result['outcome'],
                'score' => $result['score'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing mini-game result: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to process mini-game result'
            ], 500);
        }
    }

    /**
     * Get event stat effects for mini-game outcome calculation
     */
    private function getEventStatEffects($eventId, string $eventType): array
    {
        if (!$eventId) {
            return ['stat_effects' => null];
        }
        
        $model = match($eventType) {
            'daily' => DailyEvent::class,
            'cultural' => CulturalEvent::class,
            'age_specific', 'ageSpecific' => AgeSpecificEvent::class,
            'profession' => ProfessionPathEvent::class,
            'action', 'learning' => DailyAction::class,
            default => null
        };
        
        if (!$model) {
            return ['stat_effects' => null];
        }
        
        $event = $model::find($eventId);
        
        if (!$event) {
            return ['stat_effects' => null];
        }
        
        return [
            'stat_effects' => $event->stat_effects,
            'event' => $event
        ];
    }

    /**
     * Get random luck event for character
     */
    public function getLuckEvent(Character $character)
    {
        try {
            // Check if we can trigger a luck event (limit to once per day)
            $lastLuckEvent = $character->last_luck_event;
            if ($lastLuckEvent) {
                $lastDate = \Carbon\Carbon::parse($lastLuckEvent)->toDateString();
                $today = now()->toDateString();
                if ($lastDate === $today) {
                    return [
                        'available' => false,
                        'message' => 'You already had your daily dose of luck!'
                    ];
                }
            }
            
            // Generate luck event
            $luckEvent = $this->luckService->generateLuckEvent($character);
            
            if (!$luckEvent) {
                return [
                    'available' => false,
                    'message' => 'No luck event triggered this time.'
                ];
            }
            
            // Mark that character had a luck event today
            $character->last_luck_event = now();
            $character->save();
            
            return [
                'available' => true,
                'luck_event' => $luckEvent,
                'luck' => $character->luck,
                'karma' => $character->karma
            ];
        } catch (\Exception $e) {
            Log::error('Error generating luck event: ' . $e->getMessage());
            return [
                'available' => false,
                'error' => 'Failed to generate luck event'
            ];
        }
    }

    /**
     * Apply luck event effects to character
     */
    public function applyLuckEvent(Request $request, Character $character)
    {
        try {
            $validated = $request->validate([
                'event_id' => 'required|string',
                'choice_index' => 'nullable|integer'
            ]);
            
            // Get the event from the request (we need to regenerate it)
            $luckEvent = $this->luckService->generateLuckEvent($character);
            
            if (!$luckEvent || $luckEvent['id'] !== $validated['event_id']) {
                return response()->json([
                    'error' => 'Invalid luck event'
                ], 400);
            }
            
            $effects = $luckEvent['effects'] ?? [];
            
            // Handle choice-based effects
            $choiceIndex = $validated['choice_index'] ?? 0;
            if (isset($luckEvent['choices']) && isset($luckEvent['choices'][$choiceIndex])) {
                $effects = $luckEvent['choices'][$choiceIndex]['effects'] ?? $effects;
            }
            
            // Apply effects
            $effectsResult = $this->eventService->applyStatEffects($character, $effects);
            
            // Extract effects and random outcome info
            $appliedEffects = is_array($effectsResult) ? ($effectsResult['effects'] ?? $effectsResult) : $effectsResult;
            $luckRandomOutcome = is_array($effectsResult) ? ($effectsResult['random_outcome'] ?? null) : null;
            
            // Update luck based on event type
            if ($luckEvent['luck_type'] === 'fortune') {
                $this->luckService->updateLuck($character, 'good_deed');
            } elseif ($luckEvent['luck_type'] === 'misfortune') {
                $this->luckService->updateLuck($character, 'bad_deed');
            }
            
            $character->refresh();
            
            return response()->json([
                'success' => true,
                'message' => $luckEvent['title'] . ' applied!',
                'effects' => $appliedEffects,
                'random_outcome' => $luckRandomOutcome ?? null,
                'character' => $character,
                'luck' => $character->luck,
                'karma' => $character->karma
            ]);
        } catch (\Exception $e) {
            Log::error('Error applying luck event: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to apply luck event'
            ], 500);
        }
    }

    /**
     * Get all achievements for character
     */
    public function getAchievements(Character $character)
    {
        // Use database-backed achievements if available, otherwise fall back to hardcoded
        if ($this->achievementService->hasDatabaseAchievements()) {
            $achievements = $this->achievementService->getAllAchievementsWithStatusFromDatabase($character);
            $stats = $this->achievementService->getAchievementStatsFromDatabase($character);
        } else {
            // Fall back to hardcoded achievements
            $achievements = $this->achievementService->getAllAchievementsWithStatus($character);
            $stats = $this->achievementService->getAchievementStats($character);
        }
        
        return response()->json([
            'achievements' => $achievements,
            'stats' => $stats
        ]);
    }

    /**
     * Check and unlock new achievements
     */
    public function checkAchievements(Character $character)
    {
        // Use database-backed achievements if available
        if ($this->achievementService->hasDatabaseAchievements()) {
            $unlocked = $this->achievementService->checkAndUnlockDatabaseAchievements($character);
            $stats = $this->achievementService->getAchievementStatsFromDatabase($character);
            $unlockedCount = $character->achievements()->count();
        } else {
            // Fall back to hardcoded achievements
            $unlocked = $this->achievementService->checkAndUnlockAchievements($character);
            $stats = $this->achievementService->getAchievementStats($character);
            $unlockedCount = count($character->achievement_flags ?? []);
        }
        
        return response()->json([
            'new_achievements' => $unlocked,
            'unlocked_count' => $unlockedCount,
            'stats' => $stats
        ]);
    }
}
