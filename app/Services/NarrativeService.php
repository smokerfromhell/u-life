<?php

namespace App\Services;

use App\Models\Character;
use App\Models\AgeSpecificEvent;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\ProfessionPathEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NarrativeService
{
    /**
     * Define the main story paths in the game
     * Each path has stages that unlock based on choices and stats
     */
    public const STORY_PATHS = [
        'education' => [
            'name' => 'Education Path',
            'stages' => ['school', 'high_school', 'college', 'graduate', 'phd'],
            'events' => ['education', 'study', 'exam', 'school', 'college']
        ],
        'career' => [
            'name' => 'Career Path',
            'stages' => ['unemployed', 'entry_level', 'mid_level', 'senior', 'executive', 'retired'],
            'events' => ['career', 'work', 'job', 'promotion', 'business']
        ],
        'family' => [
            'name' => 'Family Path',
            'stages' => ['single', 'dating', 'engaged', 'married', 'parent', 'empty_nest'],
            'events' => ['relationship', 'dating', 'marriage', 'baby', 'family', 'parenting']
        ],
        'health' => [
            'name' => 'Health Path',
            'stages' => ['healthy', 'fitness', 'injury', 'illness', 'recovery'],
            'events' => ['health', 'fitness', 'exercise', 'illness', 'doctor', 'hospital']
        ],
        'wealth' => [
            'name' => 'Financial Path',
            'stages' => ['broke', 'saving', 'investing', 'wealthy', 'rich'],
            'events' => ['money', 'finance', 'investment', 'business', 'shopping']
        ],
        'social' => [
            'name' => 'Social Path',
            'stages' => ['loner', 'friend', 'popular', 'influencer', 'leader'],
            'events' => ['social', 'friend', 'party', 'network', 'community']
        ]
    ];

    /**
     * Get narrative-aware events for a character
     * This combines events from all sources and prioritizes based on active narrative
     */
    public function getNarrativeEvents(Character $character, string $ageGroup, array $shownEventIds = []): array
    {
        $currentNarrative = $character->current_narrative ?? null;
        $activePaths = is_array($character->active_event_paths) ? $character->active_event_paths : [];
        $completedChains = $character->getCompletedChainRecords();

        // Determine which story paths are active based on character state
        $activeStoryPaths = $this->determineActivePaths($character);
        $activePaths = array_values(array_unique(array_merge($activePaths, $activeStoryPaths)));

        // Get events from different sources
        $dailyEvents = $this->getDailyEventsWithNarrative($character, $activePaths, $ageGroup);
        $culturalEvents = $this->getCulturalEventsWithNarrative($character, $activePaths);
        $ageSpecificEvents = $this->getAgeSpecificEventsWithNarrative($character, $activePaths, $ageGroup, $shownEventIds);
        $professionEvents = $this->getProfessionEventsWithNarrative($character, $activePaths, $shownEventIds);

        return [
            'daily' => $dailyEvents,
            'cultural' => $culturalEvents,
            'ageSpecific' => $ageSpecificEvents,
            'profession' => $professionEvents,
            'current_narrative' => $currentNarrative,
            'active_paths' => $activePaths,
            'active_story_paths' => $activeStoryPaths,
            'completed_chains' => $completedChains
        ];
    }

    /**
     * Determine which story paths are currently active for a character
     */
    public function determineActivePaths(Character $character): array
    {
        $activePaths = [];
        $stats = $character->effective_stats ?? $character->stats ?? [];
        $characterState = $character->character_state ?? [];
        $relationshipStatus = $characterState['relationship_status'] ?? $character->relationship_status ?? 'single';

        // Prefer JSON state, but fall back to the column when JSON is still at its default.
        $professionState = $characterState['profession_state'] ?? null;
        if (empty($professionState) || ($professionState === 'unemployed' && !empty($character->profession))) {
            $professionState = $character->career_level ?? 'unemployed';
        }
        $health = $character->health ?? 78;
        $wealth = $character->finance ?? $character->wealth ?? 20;
        $intelligence = (int) ($stats['Intelligence'] ?? $stats['intelligence'] ?? 0);
        $social = (int) ($stats['Social'] ?? $stats['social'] ?? 0);
        $ageGroup = $character->age_group ?? $characterState['life_stage'] ?? 'adult';

        // Education path - based on age and int stat
        if ($ageGroup !== 'child') {
            if ($intelligence > 30) {
                $activePaths[] = 'education';
            }
        }

        // Career path - based on profession
        if ($professionState !== 'unemployed') {
            $activePaths[] = 'career';
        }

        // Family path - based on relationship
        if (in_array($relationshipStatus, ['dating', 'engaged', 'married'])) {
            $activePaths[] = 'family';
        }

        // Health path - based on health stat
        if ($health < 70 || $health > 90) {
            $activePaths[] = 'health';
        }

        // Wealth path - based on finance
        if ($wealth > 20) {
            $activePaths[] = 'wealth';
        }

        // Social path - based on social stat
        if ($social > 20) {
            $activePaths[] = 'social';
        }

        return array_values(array_unique($activePaths));
    }

    /**
     * Get daily events with narrative weighting
     * Now supports cross-age chain continuity
     * Now includes interruption prevention for in-progress chains
     */
    protected function getDailyEventsWithNarrative(Character $character, array $activePaths, string $ageGroup): Collection
    {
        $normalizedAge = $this->normalizeAgeGroup($ageGroup);
        
        $events = DailyEvent::whereIn('age_group', [$normalizedAge, 'all'])->get();
        
        // Filter by prerequisites
        $eventService = app(EventService::class);
        $events = $events->filter(fn($event) => $eventService->checkEventPrerequisites($event, $character));

        // Get completed chains for this character
        $completedChains = $character->getCompletedChainIds();
        
        // Get pending chains (chains started but not completed)
        $pendingChains = $character->getPendingChains();
        $pendingChainIds = collect($pendingChains)->pluck('chain_id')->toArray();
        $pendingChainProgress = [];
        foreach ($pendingChains as $chain) {
            $pendingChainProgress[$chain['chain_id']] = $chain['progress'] ?? 0;
        }
        
        // INTERRUPTION PREVENTION: Get chain cooldown tracking
        $characterState = $character->character_state ?? [];
        $chainCooldowns = $characterState['chain_cooldowns'] ?? []; // ['chain_id' => last_day_seen]
        $currentDay = $character->current_day;
        
        // RELATIONSHIP CHAINS: Get NPC relationship info
        $relationshipChains = $character->getRelationshipChains();
        $npcProgress = [];
        foreach ($relationshipChains as $chain) {
            $key = ($chain['npc_name'] ?? '') . '_' . ($chain['relationship_type'] ?? 'default');
            $npcProgress[$key] = $chain['progress'] ?? 0;
        }
        
        // Separate chain events from standalone events
        $chainEvents = [];
        $standaloneEvents = [];
        $continuingChainEvents = []; // Events that continue pending chains from previous age group
        $overdueChainEvents = [];
        $npcRelationshipEvents = []; // Events that continue NPC relationship chains
        
        foreach ($events as $event) {
            $hasChain = !empty($event->parent_category);
            $chainOrder = $event->chain_order ?? 0;
            $chainId = $event->chain_id ?? $event->parent_category;
            
            // Check cooldown status
            $lastSeenDay = $chainCooldowns[$chainId] ?? null;
            $daysSinceSeen = $lastSeenDay ? ($currentDay - $lastSeenDay) : PHP_INT_MAX;
            
            // Check if this event continues a pending chain from a previous age group
            $isContinuingChain = in_array($chainId, $pendingChainIds);
            $currentProgress = $pendingChainProgress[$chainId] ?? 0;
            $isNextStep = $chainOrder == ($currentProgress + 1);
            
            // Mark as overdue if chain in progress but not seen recently
            $isInProgress = in_array($chainId, $pendingChainIds) || in_array($chainId, $completedChains);
            $isOverdue = $isInProgress && $daysSinceSeen >= 3; // 3+ days since chain event = overdue
            
            if ($isContinuingChain && $isNextStep) {
                // This event continues a chain from a previous age group - highest priority
                $event->is_continuing_chain = true;
                $event->is_overdue = $isOverdue;
                $continuingChainEvents[] = $event;
            } elseif ($isOverdue) {
                // Chain event is overdue - force it to appear
                $event->is_overdue = true;
                $event->is_continuing_chain = false;
                $overdueChainEvents[] = $event;
            } elseif ($hasChain) {
                // Check if parent chain was completed
                $parentCompleted = in_array($event->parent_category, $completedChains);
                // Also allow if chain_order is 1 (start of chain)
                $isChainStart = $chainOrder == 1;
                
                if ($parentCompleted || $isChainStart) {
                    $chainEvents[] = $event;
                } else {
                    // Parent not completed, can't show this event yet
                    continue;
                }
            } else {
                // Check for NPC relationship chain events
                $relatedNPC = $event->related_npc ?? null;
                if ($relatedNPC) {
                    $relationshipType = $event->relationship_type ?? 'default';
                    $npcKey = $relatedNPC . '_' . $relationshipType;
                    $currentNPCProgress = $npcProgress[$npcKey] ?? 0;
                    $minLevel = $event->min_relationship_level ?? 1;
                    
                    // Event requires specific NPC relationship level
                    if ($currentNPCProgress >= $minLevel) {
                        $event->is_npc_chain = true;
                        $npcRelationshipEvents[] = $event;
                    } else {
                        // NPC requirement not met yet
                        continue;
                    }
                } else {
                    $standaloneEvents[] = $event;
                }
            }
        }
        
        // Apply narrative weighting - highest for continuing chains and overdue
        $continuingChainEvents = collect($continuingChainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 3.0); // Highest priority
        });
        
        // Overdue chains get very high priority to force appearance
        $overdueChainEvents = collect($overdueChainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 4.0); // Force appearance
        });
        
        // NPC relationship events get high priority
        $npcRelationshipEvents = collect($npcRelationshipEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 2.2); // High priority for relationship chains
        });
        
        // Apply narrative weighting to chain events
        $chainEvents = collect($chainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 1.8); // Stronger boost for chains
        });

        // Apply narrative weighting to standalone events
        $standaloneEvents = collect($standaloneEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 1.5);
        });

        // INTERRUPTION PREVENTION: Guarantee slots for chain events
        // 1. First, guarantee slots for overdue chains (must appear)
        $selectedOverdue = $this->selectWeightedEvents($overdueChainEvents, 3);
        
        // 2. Then ensure continuing chains appear (minimum 2 guaranteed)
        $selectedContinuing = $this->selectWeightedEvents($continuingChainEvents, 2);
        
        // 3. Then NPC relationship events
        $selectedNPC = $this->selectWeightedEvents($npcRelationshipEvents, 2);
        
        // 4. Then regular chain events (minimum 2 guaranteed)
        $selectedChains = $this->selectWeightedEvents($chainEvents, 2);
        
        // 5. Fill remaining slots with standalone
        $remainingSlots = 10 - $selectedOverdue->count() - $selectedContinuing->count() - $selectedNPC->count() - $selectedChains->count();
        $remainingSlots = max(0, $remainingSlots);
        $selectedStandalone = $this->selectWeightedEvents($standaloneEvents, $remainingSlots);
        
        // Update cooldowns for chains that were selected
        $selectedEvents = $selectedOverdue->merge($selectedContinuing)->merge($selectedChains)->merge($selectedNPC);
        $this->updateChainCooldowns($character, $selectedEvents, $currentDay);
        
        return $selectedOverdue->merge($selectedContinuing)->merge($selectedNPC)->merge($selectedChains)->merge($selectedStandalone)->take(10);
    }

    /**
     * Update chain cooldown tracking after event selection
     * This ensures we track which chains have appeared recently
     */
    private function updateChainCooldowns(Character $character, Collection $selectedEvents, int $currentDay): void
    {
        $characterState = $character->character_state ?? [];
        $chainCooldowns = $characterState['chain_cooldowns'] ?? [];
        
        foreach ($selectedEvents as $event) {
            $chainId = $event->chain_id ?? $event->parent_category ?? null;
            if ($chainId) {
                $chainCooldowns[$chainId] = $currentDay;
            }
        }
        
        $characterState['chain_cooldowns'] = $chainCooldowns;
        $character->character_state = $characterState;
        $character->save();
    }

    /**
     * Apply narrative weight boost to an event
     */
    private function applyNarrativeWeight($event, array $activePaths, float $boostMultiplier = 1.5): object
    {
        $weight = (float) ($event->weight ?? 1);
        $eventCategory = $event->event_category ?? '';
        
        // Boost events that match active narrative paths
        foreach ($activePaths as $path) {
            $pathConfig = self::STORY_PATHS[$path] ?? null;
            if ($pathConfig && in_array($eventCategory, $pathConfig['events'])) {
                $weight *= $boostMultiplier;
                break;
            }
        }
        
        $event->narrative_weight = $weight;
        return $event;
    }

    /**
     * Get cultural events with narrative weighting
     * Now supports cross-age chain continuity
     * Now includes interruption prevention
     */
    protected function getCulturalEventsWithNarrative(Character $character, array $activePaths): Collection
    {
        $events = CulturalEvent::all();
        
        // Filter by prerequisites
        $eventService = app(EventService::class);
        $events = $events->filter(fn($event) => $eventService->checkEventPrerequisites($event, $character));

        // Get completed chains
        $completedChains = $character->getCompletedChainIds();
        
        // Get pending chains (chains started but not completed)
        $pendingChains = $character->getPendingChains();
        $pendingChainIds = collect($pendingChains)->pluck('chain_id')->toArray();
        $pendingChainProgress = [];
        foreach ($pendingChains as $chain) {
            $pendingChainProgress[$chain['chain_id']] = $chain['progress'] ?? 0;
        }
        
        // INTERRUPTION PREVENTION: Get chain cooldown tracking
        $characterState = $character->character_state ?? [];
        $chainCooldowns = $characterState['chain_cooldowns'] ?? [];
        $currentDay = $character->current_day;
        
        // RELATIONSHIP CHAINS: Get NPC relationship info
        $relationshipChains = $character->getRelationshipChains();
        $npcProgress = [];
        foreach ($relationshipChains as $chain) {
            $key = ($chain['npc_name'] ?? '') . '_' . ($chain['relationship_type'] ?? 'default');
            $npcProgress[$key] = $chain['progress'] ?? 0;
        }
        
        // Separate chain events from standalone events
        $chainEvents = [];
        $standaloneEvents = [];
        $continuingChainEvents = [];
        $overdueChainEvents = [];
        $npcRelationshipEvents = [];
        
        foreach ($events as $event) {
            $hasChain = !empty($event->parent_category);
            $chainOrder = $event->chain_order ?? 0;
            $chainId = $event->chain_id ?? $event->parent_category;
            
            // Check cooldown status
            $lastSeenDay = $chainCooldowns[$chainId] ?? null;
            $daysSinceSeen = $lastSeenDay ? ($currentDay - $lastSeenDay) : PHP_INT_MAX;
            
            // Check if this event continues a pending chain
            $isContinuingChain = in_array($chainId, $pendingChainIds);
            $currentProgress = $pendingChainProgress[$chainId] ?? 0;
            $isNextStep = $chainOrder == ($currentProgress + 1);
            
            // Check overdue status
            $isInProgress = in_array($chainId, $pendingChainIds) || in_array($chainId, $completedChains);
            $isOverdue = $isInProgress && $daysSinceSeen >= 3;
            
            if ($isContinuingChain && $isNextStep) {
                $event->is_continuing_chain = true;
                $event->is_overdue = $isOverdue;
                $continuingChainEvents[] = $event;
            } elseif ($isOverdue) {
                $event->is_overdue = true;
                $event->is_continuing_chain = false;
                $overdueChainEvents[] = $event;
            } elseif ($hasChain) {
                $parentCompleted = in_array($event->parent_category, $completedChains);
                $isChainStart = $chainOrder == 1;
                
                if ($parentCompleted || $isChainStart) {
                    $chainEvents[] = $event;
                } else {
                    continue;
                }
            } else {
                // Check for NPC relationship chain events
                $relatedNPC = $event->related_npc ?? null;
                if ($relatedNPC) {
                    $relationshipType = $event->relationship_type ?? 'default';
                    $npcKey = $relatedNPC . '_' . $relationshipType;
                    $currentNPCProgress = $npcProgress[$npcKey] ?? 0;
                    $minLevel = $event->min_relationship_level ?? 1;
                    
                    if ($currentNPCProgress >= $minLevel) {
                        $event->is_npc_chain = true;
                        $npcRelationshipEvents[] = $event;
                    } else {
                        continue;
                    }
                } else {
                    $standaloneEvents[] = $event;
                }
            }
        }
        
        // Apply narrative weighting
        $continuingChainEvents = collect($continuingChainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 3.0);
        });
        
        $overdueChainEvents = collect($overdueChainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 4.0);
        });
        
        $npcRelationshipEvents = collect($npcRelationshipEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 2.2);
        });
        
        $chainEvents = collect($chainEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 1.8);
        });
        
        $standaloneEvents = collect($standaloneEvents)->map(function ($event) use ($activePaths) {
            return $this->applyNarrativeWeight($event, $activePaths, 1.5);
        });

        // INTERRUPTION PREVENTION: Guarantee slots
        $selectedOverdue = $this->selectWeightedEvents($overdueChainEvents, 2);
        $selectedContinuing = $this->selectWeightedEvents($continuingChainEvents, 2);
        $selectedNPC = $this->selectWeightedEvents($npcRelationshipEvents, 2);
        $selectedChains = $this->selectWeightedEvents($chainEvents, 2);
        
        $remainingSlots = 5 - $selectedOverdue->count() - $selectedContinuing->count() - $selectedNPC->count() - $selectedChains->count();
        $remainingSlots = max(0, $remainingSlots);
        $selectedStandalone = $this->selectWeightedEvents($standaloneEvents, $remainingSlots);
        
        return $selectedOverdue->merge($selectedContinuing)->merge($selectedNPC)->merge($selectedChains)->merge($selectedStandalone)->take(5);
    }

    /**
     * Get age-specific events with narrative weighting
     */
    protected function getAgeSpecificEventsWithNarrative(Character $character, array $activePaths, string $ageGroup, array $shownEventIds): array
    {
        $normalizedAgeGroup = $this->normalizeAgeGroup($ageGroup);
        $events = AgeSpecificEvent::where('age_group', $normalizedAgeGroup)->get();
        
        // Filter out shown events
        $events = $events->filter(function ($event) use ($shownEventIds) {
            $eventKey = 'ageSpecific_' . $event->id;
            return !in_array($eventKey, $shownEventIds);
        });

        // Filter by prerequisites
        $eventService = app(EventService::class);
        $events = $events->filter(fn($event) => $eventService->checkEventPrerequisites($event, $character));

        // Separate milestone events
        $milestones = [];
        $regular = [];

        foreach ($events as $event) {
            $weight = (float) ($event->weight ?? 1);
            $eventCategory = $event->event_category ?? '';
            
            // Strong boost for narrative-matching age-specific events
            foreach ($activePaths as $path) {
                $pathConfig = self::STORY_PATHS[$path] ?? null;
                if ($pathConfig && in_array($eventCategory, $pathConfig['events'])) {
                    $weight *= 2.0; // 100% boost for milestone narrative events
                    break;
                }
            }
            
            $event->narrative_weight = $weight;

            if ($event->is_milestone ?? false) {
                $milestones[] = $event;
            } else {
                $regular[] = $event;
            }
        }

        // Return milestones first, then regular events
        return array_merge(
            $this->selectWeightedEvents(collect($milestones), 3)->toArray(),
            $this->selectWeightedEvents(collect($regular), 5)->toArray()
        );
    }

    /**
     * Get profession events with narrative weighting
     */
    protected function getProfessionEventsWithNarrative(Character $character, array $activePaths, array $shownEventIds): Collection
    {
        $profession = $character->profession ?? null;
        if (!$profession) {
            return collect([]);
        }

        $events = ProfessionPathEvent::where('profession', $profession)->get();
        
        // Filter by prerequisites
        $eventService = app(EventService::class);
        $events = $events->filter(fn($event) => $eventService->checkEventPrerequisites($event, $character));

        // Apply narrative weighting
        $events = $events->map(function ($event) use ($activePaths) {
            $weight = (float) ($event->weight ?? 1);
            
            // Career path always gets boost for profession events
            if (in_array('career', $activePaths)) {
                $weight *= 1.5;
            }
            
            $event->narrative_weight = $weight;
            return $event;
        });

        return $this->selectWeightedEvents($events, 5);
    }

    /**
     * Select events based on weighted random selection
     */
    protected function selectWeightedEvents(Collection $events, int $max): Collection
    {
        if ($events->isEmpty()) {
            return collect([]);
        }

        $selected = [];
        $available = $events;

        for ($i = 0; $i < $max && !$available->isEmpty(); $i++) {
            $totalWeight = $available->sum('narrative_weight');
            
            if ($totalWeight <= 0) {
                $selected[] = $available->first();
                $available = $available->skip(1);
                continue;
            }

            $roll = rand(1, (int) $totalWeight * 100) / 100;
            $running = 0;

            foreach ($available as $event) {
                $running += $event->narrative_weight;
                if ($roll <= $running) {
                    $selected[] = $event;
                    $available = $available->filter(fn($e) => $e->id !== $event->id);
                    break;
                }
            }
        }

        return collect($selected);
    }

    /**
     * Normalize age group for queries
     */
    protected function normalizeAgeGroup(string $ageGroup): string
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
     * Update the character's narrative path based on an event choice
     */
    public function updateNarrativePath(Character $character, string $eventCategory, string $outcome): void
    {
        $activePaths = is_array($character->active_event_paths) ? $character->active_event_paths : [];
        $matchedPath = $this->resolveStoryPath($eventCategory);

        if (!$matchedPath) {
            return;
        }

        if (!in_array($matchedPath, $activePaths, true)) {
            $activePaths[] = $matchedPath;
        }
        $character->active_event_paths = array_values(array_unique($activePaths));

        $narrativeSuffix = match($outcome) {
            'positive', 'success' => '_success',
            'negative', 'failure' => '_struggle',
            default => '_neutral'
        };

        $newNarrative = $matchedPath . $narrativeSuffix;
        $currentNarrative = $character->normalizeNarrativeToken($character->current_narrative);

        if (empty($currentNarrative) || !$this->isAdvancedOutcome($currentNarrative, $newNarrative)) {
            $character->current_narrative = $newNarrative;
        }

        $character->save();

        Log::info('Narrative path updated', [
            'character_id' => $character->id,
            'event_category' => $eventCategory,
            'outcome' => $outcome,
            'new_narrative' => $newNarrative,
            'active_paths' => $activePaths
        ]);
    }

    /**
     * Check if the new narrative is a more advanced outcome than current
     */
    protected function isAdvancedOutcome(string $current, string $new): bool
    {
        // Extract path and outcome type
        $currentPath = str_replace(['_success', '_struggle', '_neutral'], '', $current);
        $newPath = str_replace(['_success', '_struggle', '_neutral'], '', $new);

        // Only consider "advanced" if same path and new is success while current is struggle
        if ($currentPath !== $newPath) {
            return false;
        }

        $currentOutcome = str_replace($currentPath, '', $current);
        $newOutcome = str_replace($newPath, '', $new);

        // Success is more advanced than struggle or neutral
        if ($newOutcome === '_success' && in_array($currentOutcome, ['_struggle', '_neutral'])) {
            return false; // Allow upgrade to success
        }

        return true; // Block if not an upgrade
    }

    /**
     * Get a human-readable description of the current narrative
     */
    public function getNarrativeDescription(Character $character): string
    {
        $narrative = $character->normalizeNarrativeToken($character->current_narrative) ?? 'none';
        
        if ($narrative === 'none' || empty($narrative)) {
            return "Your story is just beginning. Make choices to shape your path!";
        }

        $descriptions = [
            'education_success' => "You're excelling in your education journey!",
            'education_struggle' => "You're facing challenges in your studies.",
            'education_neutral' => "You're progressing through your education.",
            'career_success' => "Your career is thriving!",
            'career_struggle' => "You're facing career difficulties.",
            'career_neutral' => "You're building your career.",
            'family_success' => "Your family life is flourishing!",
            'family_struggle' => "You're facing family challenges.",
            'family_neutral' => "You're building your family life.",
            'health_success' => "You're in great health!",
            'health_struggle' => "Your health needs attention.",
            'health_neutral' => "You're maintaining your health.",
            'wealth_success' => "You're building wealth!",
            'wealth_struggle' => "You're facing financial challenges.",
            'wealth_neutral' => "You're managing your finances.",
            'social_success' => "You're popular and influential!",
            'social_struggle' => "You're facing social challenges.",
            'social_neutral' => "You're building your social life."
        ];

        return $descriptions[$narrative] ?? "Your story is evolving: {$narrative}";
    }

    private function resolveStoryPath(string $eventCategory): ?string
    {
        $eventCategory = strtolower(trim($eventCategory));
        if ($eventCategory === '') {
            return null;
        }

        foreach (self::STORY_PATHS as $pathKey => $pathConfig) {
            $events = array_map('strtolower', $pathConfig['events'] ?? []);
            if ($eventCategory === $pathKey || in_array($eventCategory, $events, true)) {
                return $pathKey;
            }

            foreach ($events as $eventKey) {
                if (str_contains($eventCategory, $eventKey)) {
                    return $pathKey;
                }
            }
        }

        return match (true) {
            str_contains($eventCategory, 'study'),
            str_contains($eventCategory, 'school'),
            str_contains($eventCategory, 'college') => 'education',
            str_contains($eventCategory, 'work'),
            str_contains($eventCategory, 'job'),
            str_contains($eventCategory, 'profession') => 'career',
            str_contains($eventCategory, 'family'),
            str_contains($eventCategory, 'relationship') => 'family',
            str_contains($eventCategory, 'health'),
            str_contains($eventCategory, 'recovery') => 'health',
            str_contains($eventCategory, 'wealth'),
            str_contains($eventCategory, 'finance'),
            str_contains($eventCategory, 'money') => 'wealth',
            str_contains($eventCategory, 'social'),
            str_contains($eventCategory, 'community'),
            str_contains($eventCategory, 'culture') => 'social',
            default => null,
        };
    }

    /**
     * Get available story paths for a character
     */
    public function getAvailableStoryPaths(Character $character): array
    {
        $paths = [];
        $ageGroup = $character->age_group ?? $character->character_state['life_stage'] ?? 'adult';

        foreach (self::STORY_PATHS as $key => $config) {
            $paths[] = [
                'id' => $key,
                'name' => $config['name'],
                'is_active' => in_array($key, $this->determineActivePaths($character)),
                'stages' => $config['stages']
            ];
        }

        return $paths;
    }
}
