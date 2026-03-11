<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\AgeSpecificEvent;
use App\Models\ProfessionPathEvent;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            
            $ageGroup = $character->age_group ?? 'adult';
            $shownEventIds = $character->shown_event_ids ?? [];
            
            // Get narrative-aware events
            $narrativeData = $this->eventService->getEventsForNarrativePath($character, $ageGroup, $shownEventIds);
            
            // Get standard events
            $dailyEvents = $this->getDailyEvents($shownEventIds, $ageGroup);
            $culturalEvents = $this->getCulturalEvents($shownEventIds);
            
            // Get age-specific events with branching logic
            $ageSpecificEvents = $this->getAgeSpecificEventsWithBranching($character, $ageGroup, $shownEventIds);
            
            $events = [
                'daily' => $dailyEvents,
                'cultural' => $culturalEvents,
                'ageSpecific' => $ageSpecificEvents,
                'milestone' => $this->checkMilestone($character),
                // Add narrative events from branching system
                'narrative' => $narrativeData['narrative_events'] ?? [],
                'activePaths' => $narrativeData['active_paths'] ?? [],
                'currentNarrative' => $narrativeData['current_narrative'] ?? null,
            ];

            // Add profession events if character is adult with profession
            if ($ageGroup === 'adult' && $character->profession) {
                $events['profession'] = $this->getProfessionEvents($character->profession, $shownEventIds);
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
        
        // Prioritize chain events if there are active paths
        $prioritizedEvents = [];
        if (!empty($activePaths)) {
            // Add chain events first
            foreach ($chainEvents as $event) {
                $prioritizedEvents[] = $this->formatEvent($event, 'ageSpecific');
            }
            
            // Fill remaining slots with standalone events (multiple events)
            $remainingSlots = 5 - count($prioritizedEvents);
            if ($remainingSlots > 0 && !empty($standaloneEvents)) {
                $standaloneCollection = collect($standaloneEvents);
                for ($i = 0; $i < $remainingSlots && !$standaloneCollection->isEmpty(); $i++) {
                    $standaloneSelected = $this->eventService->getRandomEventByWeight($standaloneCollection);
                    if ($standaloneSelected) {
                        $prioritizedEvents[] = $this->formatEvent($standaloneSelected, 'ageSpecific');
                        $standaloneCollection = $standaloneCollection->reject(fn($e) => $e->id === $standaloneSelected->id);
                    }
                }
            }
        } else {
            // No active chains - use weighted random selection for standalone events
            $maxEvents = min(5, $events->count());
            for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
                $event = $this->eventService->getRandomEventByWeight($events);
                if ($event) {
                    $prioritizedEvents[] = $this->formatEvent($event, 'ageSpecific');
                    $events = $events->reject(fn($e) => $e->id === $event->id);
                }
            }
        }
        
        return $prioritizedEvents;
    }

    /**
     * Check if character should progress to next age group
     */
    private function checkAndApplyAgeProgression(Character $character): void
    {
        $currentDay = $character->current_day ?? 1;
        $currentAgeGroup = $character->age_group;
        
        // Normalize the current age group to ensure consistency
        $normalizedCurrentAgeGroup = $this->normalizeAgeGroup($currentAgeGroup);
        
        // Determine the appropriate age group based on current day
        $newAgeGroup = $this->getAgeGroupForDay($currentDay);
        
        // Only update if the normalized current age group is different from the calculated age group
        if ($newAgeGroup !== $normalizedCurrentAgeGroup) {
            // Age progression detected - save previous age group for milestone event
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
        if ($day <= 60) return 'teen';
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
                'child_to_teen' => [
                    'title' => 'Growing Up!',
                    'description' => 'You have grown from a child to a teenager! New adventures await you.',
                    'is_milestone' => true
                ],
                'teen_to_adult' => [
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
            return $milestoneMessages[$key] ?? null;
        }
        
        return null;
    }

    /**
     * Get random daily events (excluding shown events, filtered by age)
     */
    public function getDailyEvents(array $shownEventIds = [], string $ageGroup = 'adult')
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
     * Get random cultural events (excluding shown events)
     */
    public function getCulturalEvents(array $shownEventIds = [])
    {
        $events = CulturalEvent::all();
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('cultural_' . $event->id, $shownEventIds);
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
    public function getAgeSpecificEvents(string $ageGroup, array $shownEventIds = [])
    {
        // Normalize age_group
        $normalizedAgeGroup = $this->normalizeAgeGroup($ageGroup);
        
        $events = AgeSpecificEvent::where('age_group', $normalizedAgeGroup)->get();
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('ageSpecific_' . $event->id, $shownEventIds);
            });
        }
        
        $selected = [];
        
        // Get 5 random weighted events
        $maxEvents = min(5, $events->count());
        for ($i = 0; $i < $maxEvents && !$events->isEmpty(); $i++) {
            $event = $this->eventService->getRandomEventByWeight($events);
            if ($event) {
                $selected[] = $this->formatEvent($event, 'ageSpecific');
                $events = $events->reject(fn($e) => $e->id === $event->id);
            }
        }

        return $selected;
    }

    /**
     * Get profession-specific events (excluding shown events)
     */
    public function getProfessionEvents(string $profession, array $shownEventIds = [])
    {
        $events = ProfessionPathEvent::where('profession', $profession)->get();
        
        // Filter out already shown events
        if (!empty($shownEventIds)) {
            $events = $events->filter(function($event) use ($shownEventIds) {
                return !in_array('profession_' . $event->id, $shownEventIds);
            });
        }
        
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

        // Check age progression
        $this->checkAndApplyAgeProgression($character);

        $ageGroup = $character->age_group;
        $shownEventIds = $character->shown_event_ids ?? [];
        
        // Randomly pick from available event types
        $eventTypes = ['daily', 'cultural', 'ageSpecific'];
        if ($ageGroup === 'adult' && $character->profession) {
            $eventTypes[] = 'profession';
        }

        $type = $eventTypes[array_rand($eventTypes)];
        
        $event = match($type) {
            'daily' => $this->getFilteredRandomDailyEvent($shownEventIds, $ageGroup),
            'cultural' => $this->getFilteredRandomCulturalEvent($shownEventIds),
            'ageSpecific' => $this->getFilteredRandomAgeSpecificEvent($this->normalizeAgeGroup($ageGroup), $shownEventIds),
            'profession' => $this->getFilteredRandomProfessionEvent($character->profession, $shownEventIds),
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

            // Get the event
            $event = $this->getEventById($validated['event_type'], $validated['event_id']);
            
            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            // Get the choice
            $choices = $this->formatChoices($event->choices);
            $choice = $choices[$validated['choice_index']] ?? null;

            if (!$choice) {
                return response()->json(['error' => 'Choice not found'], 404);
            }

            // Apply stat effects from the choice (or default event effects if choice doesn't specify)
            $statEffects = $choice['stat_effects'] ?? $event->stat_effects;
            
            // Apply the stat effects
            $effects = $this->eventService->applyStatEffects($character, $statEffects);

            // Track the event as shown
            $shownEventIds = $character->shown_event_ids ?? [];
            $eventKey = $validated['event_type'] . '_' . $validated['event_id'];
            
            if (!in_array($eventKey, $shownEventIds)) {
                $shownEventIds[] = $eventKey;
                // Keep only last 50 events to prevent array from growing too large
                if (count($shownEventIds) > 50) {
                    $shownEventIds = array_slice($shownEventIds, -50);
                }
                $character->shown_event_ids = $shownEventIds;
                $character->save();
            }

            // Handle branching logic - update narrative path and complete event chains
            $this->handleEventBranching($character, $event, $effects);

            // Check for game over condition
            $gameOver = false;
            if ($statEffects && strpos($statEffects, 'End of game') !== false) {
                $gameOver = true;
            }

            // Refresh the character to get updated data
            $character->refresh();

            return response()->json([
                'message' => 'Event outcome applied',
                'character' => $character,
                'effects' => $effects,
                'game_over' => $gameOver,
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
            'choices' => $this->formatChoices($event->choices),
            'weight' => $event->weight,
        ];
    }

    /**
     * Format choices with default if not provided
     */
    private function formatChoices($choices): array
    {
        // If choices is null or empty, return default choice
        if ($choices === null || $choices === '' || $choices === 'null') {
            return [
                [
                    'text' => 'Accept Event',
                    'stat_effects' => null
                ]
            ];
        }

        // If choices is a JSON string, decode it
        if (is_string($choices)) {
            $decoded = json_decode($choices, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // If already an array, return it
        if (is_array($choices) && !empty($choices)) {
            return $choices;
        }

        // Default choice if none specified
        return [
            [
                'text' => 'Accept Event',
                'stat_effects' => null
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
}

