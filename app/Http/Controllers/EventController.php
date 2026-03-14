<?php
namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\SharedDecisionLog;
use App\Models\LifeStatsSnapshot;
use App\Models\DecisionLog;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\AgeSpecificEvent;
use App\Models\ProfessionPathEvent;
use App\Models\StatTriggerCondition;
use App\Support\Privacy;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventController extends Controller
{
    protected EventService $eventService;

    // Age progression thresholds (in days) - SHORTENED for faster gameplay
    const AGE_GROUPS = [
        'child' => ['min' => 0, 'max' => 30],      // Days 0-30
        'teen' => ['min' => 31, 'max' => 60],      // Days 31-60
        'adult' => ['min' => 61, 'max' => 90],     // Days 61-90
        'old' => ['min' => 91, 'max' => 120],      // Days 91-120 (game ends)
    ];

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
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

            // Check and apply age progression
            $this->checkAndApplyAgeProgression($character);

            // FSM action: unlock profession when adult requirements are met
            $professionUnlocked = $this->maybeUnlockProfession($character);
            
            $ageGroup = $character->age_group ?? 'adult';
            $shownEventIds = $character->shown_event_ids ?? [];
            
            Log::info('EventController:getAvailableEvents START', [
                'character_id' => $character->id,
                'raw_age_group' => $character->getRawOriginal('age_group'),
                'current_day' => $character->current_day,
            ]);
            
            // Get narrative-aware events (disabled for debugging)
            $narrativeData = ['daily' => collect([]), 'cultural' => collect([]), 'ageSpecific' => collect([]), 'profession' => collect([])];
            
Log::info('EventController::getAvailableEvents - Stateful events loaded', [
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
            if ($professionUnlocked) {
                $milestone = $this->mergeMilestones($milestone, $this->professionUnlockMilestone($professionUnlocked));
            }

$events = [
                'daily' => $dailyEvents,
                'cultural' => $culturalEvents,
                'ageSpecific' => $statefulEvents['ageSpecific'] ?? [],
                'profession' => $statefulEvents['profession'] ?? [],
                'milestone' => $milestone,
                'current_state' => $character->current_state,
                'character_state' => $character->character_state,
            ];

            // Add profession events if character is adult with profession
            if ($ageGroup === 'adult' && $character->profession) {
                $events['profession'] = $this->getProfessionEvents($character, $shownEventIds);
            }

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
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('ageSpecific_' . $event->id, $shownEventIds);
            });
        }
        
        // Separate events into categories:
        // 1. Chain events (events that are part of a narrative chain)
        // 2. Standalone events (random events without prerequisites)
        
        $chainEvents = [];
        $standaloneEvents = [];
        
        foreach ($events as $event) {
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

            $maxChain = min(3, $chainCollection->count());
            for ($i = 0; $i < $maxChain && !$chainCollection->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($chainCollection);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific');
                    $chainCollection = $chainCollection->reject(fn($e) => $e->id === $picked->id);
                }
            }
        }

        // Fill remaining slots with standalone events (weighted).
        $remaining = 5 - count($selectedEvents);
        if ($remaining > 0 && !empty($standaloneEvents)) {
            $standaloneCollection = collect($standaloneEvents);
            for ($i = 0; $i < $remaining && !$standaloneCollection->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($standaloneCollection);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific');
                    $standaloneCollection = $standaloneCollection->reject(fn($e) => $e->id === $picked->id);
                }
            }
        }

        // If nothing selected yet, do a general weighted draw across all eligible events.
        if (empty($selectedEvents)) {
            $pool = collect(array_merge($chainEvents, $standaloneEvents));
            $maxEvents = min(5, $pool->count());
            for ($i = 0; $i < $maxEvents && !$pool->isEmpty(); $i++) {
                $picked = $this->eventService->getRandomEventByWeight($pool);
                if ($picked) {
                    $selectedEvents[] = $this->formatEvent($picked, 'ageSpecific');
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
        
        // Skip progression if character has explicit age_group preference (respect character creation choice)
        if ($storedAgeGroup !== 'child' && $currentDay <= 30) {
            Log::info('Skipping age progression - respecting stored age_group', ['age_group' => $storedAgeGroup]);
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
            $character->save();
        }
    }

    /**
     * Get age group for a given day
     */
    private function getAgeGroupForDay(int $day): string
    {
        if ($day <= 30) return 'child';
        if ($day <= 60) return 'teenager';
        if ($day <= 90) return 'adult';
        return 'old';
    }

    /**
     * Check if there's a milestone event (age transition)
     */
    private function checkMilestone(Character $character): ?array
    {
        $previousAgeGroup = $character->previous_age_group ?? $character->age_group;
        $currentAgeGroup = $character->age_group;
        
        if ($previousAgeGroup !== $currentAgeGroup) {
            $milestoneMessages = [
                'child_to_teenager' => [
                    'title' => 'Growing Up!',
                    'description' => 'You have grown from a child to a teenager! New adventures await you.',
                    'is_milestone' => true
                ],
                'teenager_to_adult' => [
                    'title' => 'Becoming an Adult!',
                    'description' => 'You have transitioned into adulthood! Time to face new challenges and opportunities.',
                    'is_milestone' => true
                ],
                'adult_to_old' => [
                    'title' => 'Golden Years!',
                    'description' => 'You have entered your golden years. Reflect on your journey and enjoy life.',
                    'is_milestone' => true
                ],
            ];

            $key = $previousAgeGroup . '_to_' . $currentAgeGroup;
            $milestone = $milestoneMessages[$key] ?? null;

            // Ensure the milestone is only shown once.
            $character->previous_age_group = $currentAgeGroup;
            $character->save();

            return $milestone;
        }
        
        return null;
    }

    /**
     * FSM action: unlock a profession when character is adult and meets requirements.
     */
    private function maybeUnlockProfession(Character $character): ?string
    {
        if (($character->age_group ?? null) !== 'adult') {
            return null;
        }

        if (!empty($character->profession)) {
            return null;
        }

        $unlocked = $this->eventService->checkProfessionUnlock($character);
        if (empty($unlocked)) {
            return null;
        }

        $picked = $unlocked[array_rand($unlocked)];
        $profession = $picked->profession ?? null;
        if (!$profession) {
            return null;
        }

        $character->profession = $profession;
        $character->save();

        return $profession;
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
                $selected[] = $this->formatEvent($event, 'trigger');
                $triggered = $triggered->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $selected;
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
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('daily_' . $event->id, $shownEventIds);
            });
        }

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
        
        $selected = [];
        
        // Get 5 random weighted events (or fewer if not enough available)
        $maxEvents = min(5, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'daily');
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $selected;
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
            'teen', 'teenager', 'adolescent' => 'teenager',
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
        $roll = (mt_rand() / mt_getrandmax()) * $total;

        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
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
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('cultural_' . $event->id, $shownEventIds);
            });
        }

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
        
        $selected = [];
        
        // Get 5 random weighted events
        $maxEvents = min(5, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'cultural');
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

        return $events;
    }

    /**
     * Get profession-specific events (excluding shown events)
     */
    public function getProfessionEvents(Character $character, array $shownEventIds = [])
    {
        $profession = $character->profession;
        if (!$profession) return [];

        $events = ProfessionPathEvent::where('profession', $profession)->get();
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('profession_' . $event->id, $shownEventIds);
            });
        }
        
        // Gate by prerequisites (e.g., required_stat / threshold)
        $events = $events->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        $selected = [];
        
        // Get 5 random weighted events
        $maxEvents = min(5, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'profession');
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $selected;
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
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        $culturalPool = CulturalEvent::all();
        $culturalPool = $culturalPool
            ->filter(fn($event) => !in_array('cultural_' . $event->id, $shownEventIds))
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        $ageSpecificPool = AgeSpecificEvent::where('age_group', $this->normalizeAgeGroup($ageGroup))->get();
        $ageSpecificPool = $ageSpecificPool
            ->filter(fn($event) => !in_array('ageSpecific_' . $event->id, $shownEventIds))
            ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));

        $triggerPool = collect($this->eventService->checkStatTriggers($character));
        $triggerPool = $triggerPool->filter(fn($event) => !in_array('trigger_' . $event->id, $shownEventIds));

        $professionPool = collect();
        if (($character->age_group ?? null) === 'adult' && !empty($character->profession)) {
            $professionPool = ProfessionPathEvent::where('profession', $character->profession)->get();
            $professionPool = $professionPool
                ->filter(fn($event) => !in_array('profession_' . $event->id, $shownEventIds))
                ->filter(fn($event) => $this->eventService->checkEventPrerequisites($event, $character));
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
            'event' => $event ? $this->formatEvent($event, $type) : null,
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
                'choice_index' => 'required|integer|min:0'
            ]);

            $beforeSnapshot = [
                'day' => $character->current_day,
                'stats' => $character->stats,
                'hidden_stats' => $character->hidden_stats,
                'effective_stats' => $character->effective_stats,
                'narrative' => $character->current_narrative,
                'active_event_paths' => $character->active_event_paths,
                // Life stats before
                'health' => $character->health ?? 100,
                'happiness' => $character->happiness ?? 100,
                'finance' => $character->finance ?? 0,
                'relationship_status' => $character->relationship_status ?? 'single',
                'career_level' => $character->career_level ?? 'unemployed',
            ];

            // Get the event
            $event = $this->getEventById($validated['event_type'], $validated['event_id']);
            
            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            // Get the choice
            $choices = $this->formatChoices($event->choices ?? null);
            $choice = $choices[$validated['choice_index']] ?? null;

            if (!$choice) {
                return response()->json(['error' => 'Choice not found'], 404);
            }

            // Apply stat effects from the choice (or default event effects if choice doesn't specify)
            $statEffects = $choice['stat_effects'] ?? $event->stat_effects;
            
            // Apply the stat effects
            $effects = $this->eventService->applyStatEffects($character, $statEffects);

            // FSM State advance
            $outcomeType = $this->eventService->determineOutcomeType($statEffects);
            $this->eventService->advanceState($character, $validated['event_type'], $outcomeType);

            // Track the event as shown
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

            // Check for game over condition
            $gameOver = false;
            if ($statEffects && strpos($statEffects, 'End of game') !== false) {
                $gameOver = true;
            }

            // Advance the simulation by one day (server-authoritative)
            $character->current_day = ($character->current_day ?? 1) + 1;
            $character->save();

            // Apply any age progression based on the new day
            $this->checkAndApplyAgeProgression($character);

            // FSM action: profession unlock becomes available in adult stage
            $this->maybeUnlockProfession($character);

            // Refresh the character to get updated data
            $character->refresh();

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
                $authUser = Auth::user();
                
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
                            $validated['choice_index'], 
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
                                'health' => $character->health ?? 100,
                                'happiness' => $character->happiness ?? 100,
                                'finance' => $character->finance ?? 0,
                                'relationship_status' => $character->relationship_status ?? 'single',
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
                            'choice_index' => $validated['choice_index'],
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_text' => $logData['choice_text'] ?? null,
                            'effects' => $logData['effects'] ?? null,
                            'mbti' => $logData['mbti'] ?? null,
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
                            'choice_index' => $validated['choice_index'],
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_text' => $logData['choice_text'] ?? null,
                            'health' => $character->health ?? 100,
                            'happiness' => $character->happiness ?? 100,
                            'finance' => $character->finance ?? 0,
                            'relationship_status' => $character->relationship_status ?? 'single',
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
                            'event_type' => $validated['event_type'],
                            'event_id' => $validated['event_id'],
                            'event_title' => $logData['event_title'] ?? null,
                            'choice_index' => $validated['choice_index'],
                            'choice_text' => $logData['choice_text'] ?? null,
                            'outcome' => $logData['event_description'] ?? null,
                            'effects' => json_encode($effects),
                            // Before state
                            'before_health' => $beforeSnapshot['health'] ?? ($character->health ?? 100),
                            'before_happiness' => $beforeSnapshot['happiness'] ?? ($character->happiness ?? 100),
                            'before_finance' => $beforeSnapshot['finance'] ?? ($character->finance ?? 0),
                            'before_relationship_status' => $beforeSnapshot['relationship_status'] ?? ($character->relationship_status ?? 'single'),
                            'before_career_level' => $beforeSnapshot['career_level'] ?? ($character->career_level ?? 'unemployed'),
                            // After state
                            'after_health' => $character->health ?? 100,
                            'after_happiness' => $character->happiness ?? 100,
                            'after_finance' => $character->finance ?? 0,
                            'after_relationship_status' => $character->relationship_status ?? 'single',
                            'after_career_level' => $character->career_level ?? 'unemployed',
                            // Changes
                            'health_change' => ($character->health ?? 100) - ($beforeSnapshot['health'] ?? ($character->health ?? 100)),
                            'happiness_change' => ($character->happiness ?? 100) - ($beforeSnapshot['happiness'] ?? ($character->happiness ?? 100)),
                            'finance_change' => ($character->finance ?? 0) - ($beforeSnapshot['finance'] ?? ($character->finance ?? 0)),
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

            return response()->json([
                'message' => 'Event outcome applied',
                'character' => $character,
'effects' => $effects,
                'game_over' => $gameOver,
                'character_state' => $character->character_state,
                'age_group' => $character->age_group,
                'current_day' => $character->current_day,
                'narrative_path' => $character->current_narrative,
                'active_paths' => $character->active_event_paths
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
        
        // Determine outcome type based on stat effects
        $outcomeType = $this->eventService->determineOutcomeType($event->stat_effects ?? '');
        
        // Update the narrative path
        $this->eventService->updateNarrativePath($character, $eventCategory, $outcomeType);
        
        // Complete the event chain and unlock next events
        $this->eventService->completeEventChain($character, $eventCategory, $outcomeType);
    }

    /**
     * Infer event category from event name
     */
    private function inferEventCategory(string $eventName): ?string
    {
        $eventName = strtolower($eventName);
        
        // Education-related events
        if (preg_match('/(school|exam|college|university|grade|homework|study|learning)/', $eventName)) {
            return 'education_elementary';
        }
        
        // Career-related events
        if (preg_match('/(job|career|work|promotion|office|boss|colleague)/', $eventName)) {
            return 'career_start';
        }
        
        // Family-related events
        if (preg_match('/(marriage|wedding|spouse|child|parent|family|sibling)/', $eventName)) {
            return 'family_relationship';
        }
        
        // Health-related events
        if (preg_match('/(health|illness|disease|doctor|hospital|injury|accident)/', $eventName)) {
            return 'health_crisis';
        }
        
        // Social-related events
        if (preg_match('/(friend|date|relationship|party|social|bully)/', $eventName)) {
            return 'social_friendship';
        }
        
        // Skill-related events
        if (preg_match('/(skill|talent|hobby|practice|training|sport)/', $eventName)) {
            return 'skill_learning';
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
            default => null
        };
    }

    /**
     * Format event for API response
     */
    private function formatEvent($event, string $type): array
    {
        return [
            'id' => $event->id,
            'type' => $type,
            'title' => $event->event_choice ?? $event->title,
            'description' => $event->outcome ?? $event->description,
            'image' => $event->image ?? '/css/images/event-placeholder.jpg',
            'outcome' => $event->outcome,
            'statEffects' => $event->stat_effects,
            'choices' => $this->formatChoices($event->choices ?? null),
            'weight' => $event->weight,
        ];
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
                    'text' => 'Embrace Fully (Good)',
                    'stat_effects' => '+10 Happiness, +5 all stats'
                ],
                [
                    'text' => 'Proceed Normally',
                    'stat_effects' => null
                ],
                [
                    'text' => 'Reject/Avoid (Bad)',
                    'stat_effects' => '-5 Happiness, +5 Burnout'
                ],
                [
                    'text' => 'Skip Event',
                    'stat_effects' => '+10 Burnout'
                ]
            ];
        }

        // If choices is a JSON string, decode it
        if (is_string($choices)) {
            $decoded = json_decode($choices, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        // If already an array, return it (ensure at least 1)
        if (is_array($choices) && count($choices) > 0) {
            return $choices;
        }

        // Fallback to 4 CLEAR choices
        return [
            [
                'text' => 'Take the Opportunity',
                'stat_effects' => '+10 Happiness, +5 Health'
            ],
            [
                'text' => 'Go with the Flow',
                'stat_effects' => null
            ],
            [
                'text' => 'Avoid the Situation',
                'stat_effects' => '-5 Happiness, +5 Burnout'
            ],
            [
                'text' => 'Do Nothing (Skip)',
                'stat_effects' => '+10 Burnout'
            ]
        ];
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
}