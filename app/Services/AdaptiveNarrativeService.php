<?php

namespace App\Services;

use App\Models\Character;

class AdaptiveNarrativeService
{
    private const CONSEQUENCE_DEFAULTS = [
        'study_habit' => 0,
        'burnout_cycle' => 0,
        'relationship_strain' => 0,
        'scandal_pressure' => 0,
        'dependency_risk' => 0,
        'financial_fragility' => 0,
        'recovery_momentum' => 0,
    ];

    private const ARCHETYPE_KEYWORDS = [
        'study' => ['study', 'school', 'exam', 'homework', 'reading', 'learning', 'lecture', 'research', 'graduation', 'class', 'school', 'college'],
        'work' => ['work', 'job', 'career', 'meeting', 'office', 'surgery', 'court', 'classroom', 'award', 'grant', 'promotion', 'business', 'shift', 'profession'],
        'family' => ['family', 'parent', 'child', 'marriage', 'love', 'date', 'crush', 'relationship', 'grand', 'sibling', 'bonding', 'parenthood', 'divorce', 'repair'],
        'health' => ['health', 'doctor', 'illness', 'checkup', 'medication', 'exercise', 'gym', 'meal', 'nutrition', 'accident', 'sleep', 'bedtime', 'rest', 'rehab', 'walk', 'recovery', 'burnout', 'habit'],
        'social' => ['friend', 'party', 'play', 'hang out', 'community', 'friendship', 'playdate', 'bullying', 'social', 'companion', 'reputation', 'rumor'],
        'culture' => ['festival', 'parade', 'concert', 'procession', 'devotion', 'tradition', 'celebration', 'holiday', 'lantern', 'dance', 'ritual'],
        'money' => ['wealth', 'bill', 'debt', 'payment', 'inheritance', 'market', 'finance', 'money', 'earn', 'budget'],
        'creative' => ['art', 'painting', 'music', 'writing', 'mask', 'flower', 'garden', 'memoir', 'lantern', 'cooking', 'craft'],
        'competition' => ['contest', 'competition', 'race', 'sports', 'championship', 'victory', 'rodeo', 'surf', 'team', 'win'],
    ];

    private const ARCHETYPE_CONFIG = [
        'study' => ['primary' => 'Intelligence', 'support' => 'Discipline', 'risk' => 'Burnout', 'vulnerability' => 'Health', 'social' => 'Reputation', 'complication' => 'a pounding migraine'],
        'work' => ['primary' => 'Wealth', 'support' => 'Discipline', 'risk' => 'Burnout', 'vulnerability' => 'Happiness', 'social' => 'Reputation', 'complication' => 'you leave the day mentally spent'],
        'family' => ['primary' => 'Happiness', 'support' => 'Morality', 'risk' => 'Isolation', 'vulnerability' => 'Burnout', 'social' => 'Reputation', 'complication' => 'old tensions quietly resurface'],
        'health' => ['primary' => 'Health', 'support' => 'Discipline', 'risk' => 'Burnout', 'vulnerability' => 'Happiness', 'social' => 'Reputation', 'complication' => 'the recovery is slower than you hoped'],
        'social' => ['primary' => 'Charisma', 'support' => 'Happiness', 'risk' => 'Isolation', 'vulnerability' => 'Reputation', 'social' => 'Reputation', 'complication' => 'an awkward moment follows you home'],
        'culture' => ['primary' => 'Happiness', 'support' => 'Morality', 'risk' => 'Burnout', 'vulnerability' => 'Isolation', 'social' => 'Reputation', 'complication' => 'the crowd leaves you drained'],
        'money' => ['primary' => 'Wealth', 'support' => 'Discipline', 'risk' => 'Debt', 'vulnerability' => 'Happiness', 'social' => 'Reputation', 'complication' => 'unexpected costs hit right after'],
        'creative' => ['primary' => 'Creativity', 'support' => 'Happiness', 'risk' => 'Burnout', 'vulnerability' => 'Reputation', 'social' => 'Reputation', 'complication' => 'self-doubt creeps in after the effort'],
        'competition' => ['primary' => 'Strength', 'support' => 'Discipline', 'risk' => 'Burnout', 'vulnerability' => 'Health', 'social' => 'Reputation', 'complication' => 'you pick up a painful strain'],
        'generic' => ['primary' => 'Happiness', 'support' => 'Discipline', 'risk' => 'Burnout', 'vulnerability' => 'Health', 'social' => 'Reputation', 'complication' => 'the pressure lingers afterward'],
    ];

    public function buildChoicesForEvent(
        string $title,
        ?string $description,
        ?string $baseEffects,
        string $type,
        string $ageGroup = 'adult',
        array $existingChoices = []
    ): array {
        $context = $this->buildContext($title, $description, $type, $ageGroup);
        $choices = $this->normalizeExistingChoices($existingChoices, $context, $baseEffects);

        if (count($choices) < 3) {
            $choices = $this->fillMissingChoices($choices, $context, $baseEffects);
        }

        return array_values(array_map(
            fn (array $choice, int $index) => $this->enrichChoice($choice, $context, $baseEffects, $index),
            array_slice($choices, 0, 3),
            array_keys(array_slice($choices, 0, 3))
        ));
    }

    public function adaptChoicesForCharacter(Character $character, array $eventContext, array $choices): array
    {
        $profile = $this->getDecisionProfile($character);
        $consequence = $this->pickDominantConsequence($profile, $eventContext);

        if ($consequence === null) {
            return array_values(array_slice($choices, 0, 3));
        }

        $choices = array_values(array_slice($choices, 0, 3));
        $overlays = $this->buildConsequenceChoiceOverlay($consequence, $eventContext);

        foreach ($overlays as $index => $overlay) {
            $choices[$index + 1] = $this->enrichChoice($overlay, $eventContext, $overlay['stat_effects'] ?? null, $index + 1);
        }

        return array_values(array_slice($choices, 0, 3));
    }

    public function resolveOutcome(Character $character, array $eventContext, array $choice): ?array
    {
        $outcomes = array_values(array_filter($choice['outcomes'] ?? [], fn ($outcome) => is_array($outcome)));
        if ($outcomes === []) {
            return null;
        }

        $profile = $this->getDecisionProfile($character);
        $archetype = $eventContext['archetype'] ?? $this->detectArchetype(
            $eventContext['title'] ?? '',
            $eventContext['description'] ?? '',
            $eventContext['type'] ?? 'generic'
        );
        $approach = $choice['approach'] ?? $this->classifyChoiceApproach($choice['text'] ?? '', 0, $archetype);
        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];

        $weighted = [];
        foreach ($outcomes as $outcome) {
            $tier = (string) ($outcome['tier'] ?? 'mixed');
            $weight = (int) ($outcome['weight'] ?? 10);
            $weight += $this->outcomeModifierFromStats($effectiveStats, $archetype, $tier, $approach);
            $weight += $this->outcomeModifierFromProfile($profile, $archetype, $tier, $approach);
            $weighted[] = ['weight' => max(1, $weight), 'outcome' => $outcome];
        }

        return $this->pickWeightedOutcome($weighted);
    }

    public function applyDecisionMemory(Character $character, array $eventContext, array $choice, ?array $outcome): void
    {
        $state = is_array($character->character_state) ? $character->character_state : [];
        $profile = $this->initializeProfile($state['decision_profile'] ?? []);

        $archetype = $eventContext['archetype'] ?? $this->detectArchetype(
            $eventContext['title'] ?? '',
            $eventContext['description'] ?? '',
            $eventContext['type'] ?? 'generic'
        );
        $approach = $choice['approach'] ?? $this->classifyChoiceApproach($choice['text'] ?? '', 0, $archetype);
        $tier = (string) ($outcome['tier'] ?? 'mixed');
        $effectsText = implode(', ', array_filter([
            $choice['stat_effects'] ?? null,
            $outcome['stat_effects'] ?? null,
        ]));
        $effects = app(EventService::class)->parseStatEffects($effectsText);

        $profile['archetypes'][$archetype] = min(20, ($profile['archetypes'][$archetype] ?? 0) + $this->focusDelta($approach, $tier));
        $profile['traits']['ambition'] = $this->clampCounter($profile['traits']['ambition'] + ($approach === 'commit' ? 2 : ($approach === 'risky' ? 1 : 0)));
        $profile['traits']['balance'] = $this->clampCounter($profile['traits']['balance'] + ($approach === 'balanced' ? 2 : 0));
        $profile['traits']['avoidance'] = $this->clampCounter($profile['traits']['avoidance'] + ($approach === 'avoid' ? 2 : 0));
        $profile['traits']['risk'] = $this->clampCounter($profile['traits']['risk'] + ($approach === 'risky' ? 2 : 0));
        $profile['traits']['care'] = $this->clampCounter($profile['traits']['care'] + (in_array($archetype, ['family', 'health'], true) && $approach !== 'avoid' ? 1 : 0));
        $profile['traits']['discipline'] = $this->clampCounter(
            $profile['traits']['discipline'] + (in_array($approach, ['commit', 'balanced'], true) ? 1 : 0)
        );

        $burnoutChange = (int) ($effects['Burnout'] ?? 0);
        $isolationChange = (int) ($effects['Isolation'] ?? 0);
        $debtChange = (int) ($effects['Debt'] ?? 0);
        $healthChange = (int) ($effects['Health'] ?? 0);
        $happinessChange = (int) ($effects['Happiness'] ?? 0);
        $reputationChange = (int) ($effects['Reputation'] ?? 0);
        $moralityChange = (int) ($effects['Morality'] ?? 0);
        $egoChange = (int) ($effects['Ego'] ?? 0);
        $addictionChange = (int) ($effects['Addiction'] ?? 0);

        $profile['stress']['burnout_pressure'] = $this->clampCounter(
            $profile['stress']['burnout_pressure'] + max(0, $burnoutChange) + ($tier === 'setback' ? 1 : 0) - ($tier === 'great' ? 1 : 0)
        );
        $profile['stress']['social_strain'] = $this->clampCounter($profile['stress']['social_strain'] + max(0, $isolationChange));
        $profile['stress']['financial_pressure'] = $this->clampCounter($profile['stress']['financial_pressure'] + max(0, $debtChange));

        $profile['consequences']['study_habit'] = $this->shiftCounter(
            (int) ($profile['consequences']['study_habit'] ?? 0),
            match (true) {
                $archetype === 'study' && $approach === 'commit' => 2,
                $archetype === 'study' && $approach === 'balanced' => 1,
                $archetype === 'study' && $approach === 'avoid' => -2,
                $archetype === 'study' && $tier === 'great' => 1,
                default => 0,
            }
        );

        $profile['consequences']['burnout_cycle'] = $this->shiftCounter(
            (int) ($profile['consequences']['burnout_cycle'] ?? 0),
            match (true) {
                // Aggressive recovery when doing health/family activities
                in_array($archetype, ['health', 'family'], true) && in_array($approach, ['commit', 'balanced'], true) => -3,
                // Moderate recovery for social/culture activities
                in_array($archetype, ['social', 'culture'], true) && $approach === 'balanced' => -2,
                // Avoid approach reduces burnout slightly
                $approach === 'avoid' && $archetype !== 'generic' => -1,
                // Main burnout triggers
                in_array($archetype, ['study', 'work', 'competition'], true) && in_array($approach, ['commit', 'risky'], true) => 2 + max(0, (int) floor($burnoutChange / 4)),
                // Health improvement with no burnout = recovery
                $healthChange > 0 && $burnoutChange < 0 => -2,
                // Default: only add if burnout actually increased
                default => max(0, (int) floor($burnoutChange / 6)),
            }
        );

        $profile['consequences']['relationship_strain'] = $this->shiftCounter(
            (int) ($profile['consequences']['relationship_strain'] ?? 0),
            match (true) {
                // Recovery through positive social/family activities
                in_array($archetype, ['family', 'social'], true) && in_array($approach, ['commit', 'balanced'], true) => -3,
                in_array($archetype, ['family', 'social'], true) && $tier === 'great' => -2,
                // Damage from avoidance
                in_array($archetype, ['family', 'social'], true) && $approach === 'avoid' => 2,
                in_array($archetype, ['family', 'social'], true) && $tier === 'setback' => 1,
                $isolationChange > 0 => 1,
                default => 0,
            }
        );

        $profile['consequences']['scandal_pressure'] = $this->shiftCounter(
            (int) ($profile['consequences']['scandal_pressure'] ?? 0),
            match (true) {
                in_array($archetype, ['work', 'social', 'culture'], true) && $approach === 'risky' && ($tier === 'setback' || $reputationChange < 0 || $moralityChange < 0 || $egoChange > 0) => 2,
                in_array($archetype, ['work', 'social', 'culture'], true) && $approach === 'balanced' && $tier === 'great' => -2,
                $reputationChange < 0 => 1,
                default => 0,
            }
        );

        $profile['consequences']['dependency_risk'] = $this->shiftCounter(
            (int) ($profile['consequences']['dependency_risk'] ?? 0),
            match (true) {
                $addictionChange > 0 => 3,
                $burnoutChange > 0 && $approach === 'avoid' => 2,
                in_array($archetype, ['health', 'social'], true) && in_array($approach, ['commit', 'balanced'], true) && $tier === 'great' => -2,
                $happinessChange > 0 && $burnoutChange > 0 && $approach === 'risky' => 1,
                default => 0,
            }
        );

        $profile['consequences']['financial_fragility'] = $this->shiftCounter(
            (int) ($profile['consequences']['financial_fragility'] ?? 0),
            match (true) {
                // Recovery through positive money/work choices
                in_array($archetype, ['money', 'work'], true) && in_array($approach, ['balanced', 'commit'], true) && $tier === 'great' => -3,
                in_array($archetype, ['money', 'work'], true) && in_array($approach, ['balanced', 'commit'], true) && $debtChange < 0 => -2,
                // Damage from debt and risky choices
                $debtChange > 0 => 2,
                in_array($archetype, ['money', 'work'], true) && $approach === 'risky' && $tier === 'setback' => 2,
                default => 0,
            }
        );

        $profile['consequences']['recovery_momentum'] = $this->shiftCounter(
            (int) ($profile['consequences']['recovery_momentum'] ?? 0),
            match (true) {
                $archetype === 'health' && in_array($approach, ['commit', 'balanced'], true) && $tier === 'great' => 2,
                $healthChange > 0 && $burnoutChange < 0 => 1,
                $burnoutChange > 0 && in_array($archetype, ['study', 'work', 'competition'], true) => -1,
                default => 0,
            }
        );

        $profile['recent'][] = [
            'event' => $eventContext['title'] ?? 'Event',
            'archetype' => $archetype,
            'choice' => $choice['text'] ?? 'Choose',
            'approach' => $approach,
            'tier' => $tier,
        ];
        $profile['recent'] = array_slice($profile['recent'], -8);

        $profile['flags']['studious'] = ($profile['archetypes']['study'] ?? 0) >= 6;
        $profile['flags']['career_driven'] = ($profile['archetypes']['work'] ?? 0) >= 6 || ($profile['archetypes']['money'] ?? 0) >= 6;
        $profile['flags']['social_anchor'] = ($profile['archetypes']['family'] ?? 0) >= 5 || ($profile['archetypes']['social'] ?? 0) >= 5;
        $profile['flags']['overextended'] = ($profile['stress']['burnout_pressure'] ?? 0) >= 8;
        $profile['flags']['financially_strained'] = ($profile['stress']['financial_pressure'] ?? 0) >= 6;
        $profile['flags']['study_habit'] = ($profile['consequences']['study_habit'] ?? 0) >= 10;
        $profile['flags']['burnout_cycle'] = ($profile['consequences']['burnout_cycle'] ?? 0) >= 10;
        $profile['flags']['relationship_strain'] = ($profile['consequences']['relationship_strain'] ?? 0) >= 10;
        $profile['flags']['scandal_marked'] = ($profile['consequences']['scandal_pressure'] ?? 0) >= 10;
        $profile['flags']['dependency_flag'] = ($profile['consequences']['dependency_risk'] ?? 0) >= 10;
        $profile['flags']['financial_trap'] = ($profile['consequences']['financial_fragility'] ?? 0) >= 10;
        $profile['flags']['recovery_arc'] = ($profile['consequences']['recovery_momentum'] ?? 0) >= 6;

        $state['decision_profile'] = $profile;
        $state['last_choice_context'] = [
            'title' => $eventContext['title'] ?? 'Event',
            'archetype' => $archetype,
            'choice' => $choice['text'] ?? 'Choose',
            'outcome_tier' => $tier,
        ];

        $character->character_state = $state;

        $pathMap = [
            'study' => 'education',
            'work' => 'career',
            'money' => 'wealth',
            'family' => 'family',
            'health' => 'health',
            'social' => 'social',
            'culture' => 'social',
            'creative' => 'skill',
            'competition' => 'skill',
        ];

        $mappedPath = $pathMap[$archetype] ?? null;
        if ($mappedPath) {
            $activePaths = is_array($character->active_event_paths) ? $character->active_event_paths : [];
            if (!in_array($mappedPath, $activePaths, true)) {
                $activePaths[] = $mappedPath;
            }
            $character->active_event_paths = array_values(array_unique($activePaths));
        }

        $character->save();
    }

    public function scoreEventWeight(Character $character, object|array $event, string $type): float
    {
        $title = (string) ($this->eventValue($event, 'event_choice') ?? $this->eventValue($event, 'title') ?? 'Event');
        $description = (string) ($this->eventValue($event, 'outcome') ?? $this->eventValue($event, 'description') ?? '');
        $archetype = $this->detectArchetype($title, $description, $type);
        $profile = $this->getDecisionProfile($character);

        $baseWeight = (float) (
            $this->eventValue($event, 'dynamic_weight')
            ?? $this->eventValue($event, 'calculated_weight')
            ?? $this->eventValue($event, 'weight')
            ?? 1
        );

        $multiplier = 1.0;
        $focus = (int) ($profile['archetypes'][$archetype] ?? 0);
        $multiplier += min(0.60, $focus * 0.06);

        if ($archetype === 'health' && ($profile['stress']['burnout_pressure'] ?? 0) >= 6) {
            $multiplier += 0.35;
        }

        if ($archetype === 'family' && ($profile['flags']['social_anchor'] ?? false)) {
            $multiplier += 0.20;
        }

        if ($archetype === 'money' && ($profile['flags']['financially_strained'] ?? false)) {
            $multiplier += 0.30;
        }

        if (in_array($archetype, ['study', 'work', 'competition'], true) && ($profile['flags']['overextended'] ?? false)) {
            $multiplier += 0.12;
        }

        if ($archetype === 'study' && ($profile['flags']['study_habit'] ?? false)) {
            $multiplier += 0.35;
        }

        if ($archetype === 'health' && ($profile['flags']['burnout_cycle'] ?? false)) {
            $multiplier += 0.45;
        }

        if (in_array($archetype, ['family', 'social'], true) && ($profile['flags']['relationship_strain'] ?? false)) {
            $multiplier += 0.35;
        }

        if (in_array($archetype, ['work', 'social', 'culture'], true) && ($profile['flags']['scandal_marked'] ?? false)) {
            $multiplier += 0.30;
        }

        if (in_array($archetype, ['health', 'social'], true) && ($profile['flags']['dependency_flag'] ?? false)) {
            $multiplier += 0.28;
        }

        if (in_array($archetype, ['money', 'work'], true) && ($profile['flags']['financial_trap'] ?? false)) {
            $multiplier += 0.32;
        }

        if ($type === 'cultural' && ($profile['archetypes']['culture'] ?? 0) >= 4) {
            $multiplier += 0.25;
        }

        if (($profile['traits']['avoidance'] ?? 0) >= 6 && in_array($archetype, ['health', 'family', 'social'], true)) {
            $multiplier += 0.10;
        }

        return max(0.1, round($baseWeight * $multiplier, 2));
    }

    public function detectArchetype(string $title, ?string $description, string $type): string
    {
        $haystack = strtolower(trim($title . ' ' . ($description ?? '') . ' ' . $type));
        $scores = [];

        foreach (self::ARCHETYPE_KEYWORDS as $archetype => $keywords) {
            $scores[$archetype] = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, strtolower($keyword))) {
                    $scores[$archetype] += strlen($keyword) > 6 ? 2 : 1;
                }
            }
        }

        if ($type === 'cultural') {
            $scores['culture'] += 3;
        } elseif ($type === 'profession') {
            $scores['work'] += 3;
        } elseif ($type === 'system' && str_contains($haystack, 'study')) {
            $scores['study'] += 3;
        }

        arsort($scores);
        $archetype = array_key_first($scores);

        return $archetype && ($scores[$archetype] ?? 0) > 0 ? $archetype : 'generic';
    }

    private function buildContext(string $title, ?string $description, string $type, string $ageGroup): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'age_group' => $ageGroup,
            'archetype' => $this->detectArchetype($title, $description, $type),
        ];
    }

    private function normalizeExistingChoices(array $existingChoices, array $context, ?string $baseEffects): array
    {
        $choices = [];

        foreach ($existingChoices as $choice) {
            if (!is_array($choice)) {
                continue;
            }

            $text = trim((string) ($choice['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $choices[] = array_merge($choice, [
                'text' => $text,
                'stat_effects' => $choice['stat_effects'] ?? $this->defaultChoiceEffects($context['archetype'], 'balanced'),
                'days_to_advance' => (int) ($choice['days_to_advance'] ?? 0),
                'outcomes' => isset($choice['outcomes']) && is_array($choice['outcomes']) ? $choice['outcomes'] : [],
            ]);
        }

        if ($choices === [] && $baseEffects) {
            $choices[] = [
                'text' => $this->defaultChoiceBlueprints($context['archetype'])[0]['text'],
                'stat_effects' => $baseEffects,
                'days_to_advance' => 0,
                'outcomes' => [],
            ];
        }

        return $choices;
    }

    private function fillMissingChoices(array $choices, array $context, ?string $baseEffects): array
    {
        $blueprints = $this->defaultChoiceBlueprints($context['archetype']);
        $existingApproaches = [];

        foreach ($choices as $index => $choice) {
            $existingApproaches[] = $this->classifyChoiceApproach($choice['text'] ?? '', $index, $context['archetype']);
        }

        foreach ($blueprints as $blueprint) {
            if (count($choices) >= 3) {
                break;
            }

            if (in_array($blueprint['approach'], $existingApproaches, true)) {
                continue;
            }

            $choices[] = [
                'text' => $blueprint['text'],
                'stat_effects' => $baseEffects ?: $this->defaultChoiceEffects($context['archetype'], $blueprint['approach']),
                'days_to_advance' => 0,
                'outcomes' => [],
            ];
            $existingApproaches[] = $blueprint['approach'];
        }

        return $choices;
    }

    private function enrichChoice(array $choice, array $context, ?string $baseEffects, int $index): array
    {
        $approach = $choice['approach'] ?? $this->classifyChoiceApproach($choice['text'] ?? '', $index, $context['archetype']);
        $choice['approach'] = $approach;
        $choice['memory_tags'] = [$context['archetype'], $approach];

        if (empty($choice['stat_effects']) && $baseEffects) {
            $choice['stat_effects'] = $baseEffects;
        }
        if (empty($choice['stat_effects'])) {
            $choice['stat_effects'] = $this->defaultChoiceEffects($context['archetype'], $approach);
        }

        if (empty($choice['outcomes'])) {
            $choice['outcomes'] = $this->buildOutcomes($context, $choice);
        }

        return $choice;
    }

    private function buildOutcomes(array $context, array $choice): array
    {
        $archetype = $context['archetype'];
        $approach = $choice['approach'];
        $config = self::ARCHETYPE_CONFIG[$archetype] ?? self::ARCHETYPE_CONFIG['generic'];

        return [
            [
                'tier' => 'great',
                'weight' => $this->baseOutcomeWeight($approach, 'great'),
                'text' => $this->outcomeNarrative($context['title'], $choice['text'] ?? 'Choose', $archetype, $approach, 'great', $config['complication']),
                'stat_effects' => $this->outcomeEffects($config, $approach, 'great'),
            ],
            [
                'tier' => 'mixed',
                'weight' => $this->baseOutcomeWeight($approach, 'mixed'),
                'text' => $this->outcomeNarrative($context['title'], $choice['text'] ?? 'Choose', $archetype, $approach, 'mixed', $config['complication']),
                'stat_effects' => $this->outcomeEffects($config, $approach, 'mixed'),
            ],
            [
                'tier' => 'setback',
                'weight' => $this->baseOutcomeWeight($approach, 'setback'),
                'text' => $this->outcomeNarrative($context['title'], $choice['text'] ?? 'Choose', $archetype, $approach, 'setback', $config['complication']),
                'stat_effects' => $this->outcomeEffects($config, $approach, 'setback'),
            ],
        ];
    }

    private function outcomeNarrative(
        string $title,
        string $choiceText,
        string $archetype,
        string $approach,
        string $tier,
        string $complication
    ): string {
        $title = trim($title);

        return match ($tier) {
            'great' => match ($approach) {
                'commit' => "Because you chose to {$this->lowercaseFirst($choiceText)}, {$title} turns into a real breakthrough for you.",
                'balanced' => "Your measured response to {$title} keeps things steady and quietly pays off.",
                'risky' => "Your bold move around {$title} actually lands, and people notice.",
                default => "Stepping back from {$title} gives you room to recover and reset.",
            },
            'mixed' => match ($approach) {
                'commit' => "Your push on {$title} works, but {$complication}.",
                'balanced' => "You manage {$title} well enough, though {$complication}.",
                'risky' => "The gamble around {$title} partly works, but {$complication}.",
                default => "Avoiding {$title} buys time, but {$complication}.",
            },
            default => match ($approach) {
                'commit' => "You pour yourself into {$title}, but the pressure overwhelms the moment.",
                'balanced' => "{$title} slips out of your control before your careful plan can settle in.",
                'risky' => "Your shortcut around {$title} backfires hard.",
                default => "Putting {$title} aside lets the problem grow on its own.",
            },
        };
    }

    private function outcomeEffects(array $config, string $approach, string $tier): string
    {
        $primary = $config['primary'];
        $support = $config['support'];
        $risk = $config['risk'];
        $vulnerability = $config['vulnerability'];
        $social = $config['social'];

        $effects = match ($approach) {
            'commit' => match ($tier) {
                'great' => ["+8 {$primary}", "+4 {$support}", "+3 {$social}"],
                'mixed' => ["+6 {$primary}", "+2 {$support}", "+8 {$risk}", "-3 {$vulnerability}"],
                default => ["+6 {$risk}", "-4 Happiness", "-2 {$vulnerability}"],
            },
            'balanced' => match ($tier) {
                'great' => ["+5 {$primary}", "+4 {$support}", "-2 {$risk}"],
                'mixed' => ["+4 {$primary}", "+2 {$support}", "+3 {$risk}"],
                default => ["-2 {$primary}", "+4 {$risk}", "-2 Happiness"],
            },
            'risky' => match ($tier) {
                'great' => ["+10 {$primary}", "+4 {$social}", "+4 {$risk}"],
                'mixed' => ["+5 {$primary}", "+7 {$risk}", "-2 {$social}"],
                default => ["+10 {$risk}", "-5 {$social}", "-4 {$vulnerability}"],
            },
            default => match ($tier) {
                'great' => ["+4 Happiness", "-3 {$risk}"],
                'mixed' => ["+2 Happiness", "-2 {$support}", "+2 {$risk}"],
                default => ["-4 {$primary}", "+5 {$risk}", "+3 Isolation"],
            },
        };

        return implode(', ', $effects);
    }

    private function baseOutcomeWeight(string $approach, string $tier): int
    {
        return match ($approach) {
            'commit' => ['great' => 30, 'mixed' => 45, 'setback' => 25][$tier],
            'balanced' => ['great' => 35, 'mixed' => 45, 'setback' => 20][$tier],
            'risky' => ['great' => 35, 'mixed' => 20, 'setback' => 45][$tier],
            default => ['great' => 15, 'mixed' => 45, 'setback' => 40][$tier],
        };
    }

    private function defaultChoiceBlueprints(string $archetype): array
    {
        return match ($archetype) {
            'study' => [
                ['text' => 'Study hard', 'approach' => 'commit'],
                ['text' => 'Study with balance', 'approach' => 'balanced'],
                ['text' => 'Put it off for now', 'approach' => 'avoid'],
            ],
            'work' => [
                ['text' => 'Take the lead', 'approach' => 'commit'],
                ['text' => 'Handle it carefully', 'approach' => 'balanced'],
                ['text' => 'Cut corners', 'approach' => 'risky'],
            ],
            'family' => [
                ['text' => 'Show up sincerely', 'approach' => 'commit'],
                ['text' => 'Keep the peace', 'approach' => 'balanced'],
                ['text' => 'Pull away', 'approach' => 'avoid'],
            ],
            'health' => [
                ['text' => 'Take it seriously', 'approach' => 'commit'],
                ['text' => 'Manage it carefully', 'approach' => 'balanced'],
                ['text' => 'Ignore the warning', 'approach' => 'avoid'],
            ],
            'social' => [
                ['text' => 'Open up and join in', 'approach' => 'commit'],
                ['text' => 'Keep it light', 'approach' => 'balanced'],
                ['text' => 'Stay withdrawn', 'approach' => 'avoid'],
            ],
            'culture' => [
                ['text' => 'Join the celebration', 'approach' => 'commit'],
                ['text' => 'Help from the sidelines', 'approach' => 'balanced'],
                ['text' => 'Skip the crowd', 'approach' => 'avoid'],
            ],
            'money' => [
                ['text' => 'Be disciplined with it', 'approach' => 'balanced'],
                ['text' => 'Take a calculated gamble', 'approach' => 'risky'],
                ['text' => 'Avoid dealing with it', 'approach' => 'avoid'],
            ],
            'creative' => [
                ['text' => 'Go all in', 'approach' => 'commit'],
                ['text' => 'Experiment steadily', 'approach' => 'balanced'],
                ['text' => 'Drop it for now', 'approach' => 'avoid'],
            ],
            'competition' => [
                ['text' => 'Train hard', 'approach' => 'commit'],
                ['text' => 'Play it safe', 'approach' => 'balanced'],
                ['text' => 'Take a risky shortcut', 'approach' => 'risky'],
            ],
            default => [
                ['text' => 'Give it your best', 'approach' => 'commit'],
                ['text' => 'Handle it carefully', 'approach' => 'balanced'],
                ['text' => 'Step back for now', 'approach' => 'avoid'],
            ],
        };
    }

    private function defaultChoiceEffects(string $archetype, string $approach): string
    {
        $config = self::ARCHETYPE_CONFIG[$archetype] ?? self::ARCHETYPE_CONFIG['generic'];
        $primary = $config['primary'];
        $support = $config['support'];
        $risk = $config['risk'];

        return match ($approach) {
            'commit' => "+6 {$primary}, +3 {$support}, +2 {$risk}",
            'balanced' => "+4 {$primary}, +3 {$support}, +1 {$risk}",
            'risky' => "+7 {$primary}, +5 {$risk}",
            default => "+2 Happiness, +1 {$risk}",
        };
    }

    private function classifyChoiceApproach(string $text, int $index, string $archetype): string
    {
        $text = strtolower($text);

        if (preg_match('/shortcut|gamble|rush|break|cut corners|deny|overtime|all day|secret/', $text)) {
            return 'risky';
        }

        if (preg_match('/skip|avoid|ignore|cancel|refuse|decline|delay|stay home|stay in bed|drop|quit|put it off|withdraw/', $text)) {
            return 'avoid';
        }

        if (preg_match('/balance|careful|patient|steady|modest|carefully|light|manage|peace|professional|rest|walk/', $text)) {
            return 'balanced';
        }

        if (preg_match('/hard|lead|join|focus|complete|accept|help|take|go|perform|play|study|work|celebrate|pay|control|open up|train/', $text)) {
            return 'commit';
        }

        return match ($index) {
            0 => 'commit',
            1 => 'balanced',
            2 => $archetype === 'competition' || $archetype === 'money' ? 'risky' : 'avoid',
            default => 'balanced',
        };
    }

    private function outcomeModifierFromStats(array $effectiveStats, string $archetype, string $tier, string $approach): int
    {
        $config = self::ARCHETYPE_CONFIG[$archetype] ?? self::ARCHETYPE_CONFIG['generic'];
        $primary = (int) ($effectiveStats[$config['primary']] ?? 50);
        $support = (int) ($effectiveStats[$config['support']] ?? 50);
        $risk = (int) ($effectiveStats[$config['risk']] ?? 0);

        $competence = (int) floor((($primary + $support) / 2 - 50) / 6);
        $pressure = (int) floor(($risk - 20) / 8);

        return match ($tier) {
            'great' => $competence - max(0, $pressure),
            'mixed' => max(0, $pressure) + ($approach === 'balanced' ? 2 : 0),
            default => max(0, $pressure * 2) - $competence + ($approach === 'avoid' ? 2 : 0),
        };
    }

    private function outcomeModifierFromProfile(array $profile, string $archetype, string $tier, string $approach): int
    {
        $focus = (int) ($profile['archetypes'][$archetype] ?? 0);
        $avoidance = (int) ($profile['traits']['avoidance'] ?? 0);
        $balance = (int) ($profile['traits']['balance'] ?? 0);
        $risk = (int) ($profile['traits']['risk'] ?? 0);
        $burnoutPressure = (int) ($profile['stress']['burnout_pressure'] ?? 0);

        return match ($tier) {
            'great' => (int) floor($focus / 2) + ($approach === 'balanced' ? (int) floor($balance / 3) : 0) + ($approach === 'risky' ? (int) floor($risk / 4) : 0) - (int) floor($burnoutPressure / 3),
            'mixed' => (int) floor($burnoutPressure / 2) + ($approach === 'avoid' ? (int) floor($avoidance / 3) : 0),
            default => (int) floor($burnoutPressure / 2) + ($approach === 'avoid' ? (int) floor($avoidance / 2) : 0) - (int) floor($focus / 3),
        };
    }

    private function pickWeightedOutcome(array $weighted): ?array
    {
        if ($weighted === []) {
            return null;
        }

        $total = array_sum(array_column($weighted, 'weight'));
        $roll = random_int(1, max(1, $total));
        $running = 0;

        foreach ($weighted as $entry) {
            $running += $entry['weight'];
            if ($roll <= $running) {
                return $entry['outcome'];
            }
        }

        return $weighted[array_key_last($weighted)]['outcome'] ?? null;
    }

    private function focusDelta(string $approach, string $tier): int
    {
        $base = match ($approach) {
            'commit' => 2,
            'balanced' => 1,
            'risky' => 1,
            default => 0,
        };

        return $base + match ($tier) {
            'great' => 1,
            'setback' => -1,
            default => 0,
        };
    }

    private function determineNarrativeFromProfile(array $profile): ?string
    {
        $archetypes = $profile['archetypes'] ?? [];
        arsort($archetypes);
        $topArchetype = array_key_first($archetypes);
        $topScore = $topArchetype ? ($archetypes[$topArchetype] ?? 0) : 0;

        if (!$topArchetype || $topScore < 4) {
            if ($profile['flags']['burnout_cycle'] ?? false) {
                return 'survival_arc';
            }
            if ($profile['flags']['relationship_strain'] ?? false) {
                return 'fractured_bonds';
            }
            if ($profile['flags']['dependency_flag'] ?? false) {
                return 'dependency_arc';
            }

            return null;
        }

        return match ($topArchetype) {
            'study' => 'education_driven',
            'work', 'money' => 'career_driven',
            'family' => 'family_centered',
            'health' => 'recovery_arc',
            'social', 'culture' => 'socially_shaped',
            'creative', 'competition' => 'self_mastery',
            default => 'life_in_motion',
        };
    }

    public function getDecisionProfile(Character $character): array
    {
        $state = is_array($character->character_state) ? $character->character_state : [];
        return $this->initializeProfile($state['decision_profile'] ?? []);
    }

    private function initializeProfile(array $profile): array
    {
        $profile['archetypes'] = array_merge(array_fill_keys(array_keys(self::ARCHETYPE_CONFIG), 0), $profile['archetypes'] ?? []);
        $profile['traits'] = array_merge([
            'ambition' => 0,
            'balance' => 0,
            'avoidance' => 0,
            'risk' => 0,
            'care' => 0,
            'discipline' => 0,
        ], $profile['traits'] ?? []);
        $profile['stress'] = array_merge([
            'burnout_pressure' => 0,
            'social_strain' => 0,
            'financial_pressure' => 0,
        ], $profile['stress'] ?? []);
        $profile['consequences'] = array_merge(self::CONSEQUENCE_DEFAULTS, $profile['consequences'] ?? []);
        $profile['flags'] = $profile['flags'] ?? [];
        $profile['recent'] = is_array($profile['recent'] ?? null) ? $profile['recent'] : [];

        return $profile;
    }

    private function clampCounter(int $value): int
    {
        return max(0, min(20, $value));
    }

    private function shiftCounter(int $current, int $delta): int
    {
        return $this->clampCounter($current + $delta);
    }

    private function pickDominantConsequence(array $profile, array $eventContext): ?string
    {
        $archetype = $eventContext['archetype'] ?? 'generic';
        $consequences = $profile['consequences'] ?? [];
        $flags = $profile['flags'] ?? [];

        $candidates = match ($archetype) {
            'study', 'work', 'competition' => ['burnout_cycle', 'study_habit', 'financial_fragility', 'dependency_risk'],
            'family', 'social' => ['relationship_strain', 'dependency_risk', 'scandal_pressure'],
            'culture' => ['scandal_pressure', 'relationship_strain', 'dependency_risk'],
            'health' => ['dependency_risk', 'burnout_cycle', 'recovery_momentum'],
            'money' => ['financial_fragility', 'burnout_cycle', 'scandal_pressure'],
            default => ['burnout_cycle', 'relationship_strain', 'financial_fragility', 'dependency_risk'],
        };

        $bestKey = null;
        $bestScore = 0;

        foreach ($candidates as $candidate) {
            $score = (int) ($consequences[$candidate] ?? 0);
            $flagName = match ($candidate) {
                'scandal_pressure' => 'scandal_marked',
                'financial_fragility' => 'financial_trap',
                'dependency_risk' => 'dependency_flag',
                'recovery_momentum' => 'recovery_arc',
                default => $candidate,
            };

            if (($flags[$flagName] ?? false) === true) {
                $score += 3;
            }

            if ($score > $bestScore) {
                $bestKey = $candidate;
                $bestScore = $score;
            }
        }

        return $bestScore >= 5 ? $bestKey : null;
    }

    private function buildConsequenceChoiceOverlay(string $consequence, array $context): array
    {
        $archetype = $context['archetype'] ?? 'generic';
        $config = self::ARCHETYPE_CONFIG[$archetype] ?? self::ARCHETYPE_CONFIG['generic'];
        $primary = $config['primary'];

        return match ($consequence) {
            'burnout_cycle' => [
                [
                    'text' => 'Take a real recovery break',
                    'approach' => 'balanced',
                    'stat_effects' => "-5 Burnout, +3 Health, +2 Happiness, -2 {$primary}",
                ],
                [
                    'text' => 'Push through the exhaustion',
                    'approach' => 'risky',
                    'stat_effects' => "+5 {$primary}, +9 Burnout, -4 Health",
                    'outcomes' => [
                        ['tier' => 'great', 'weight' => 20, 'text' => "You force your way through {$context['title']} and get the result, but your body pays for it.", 'stat_effects' => "+8 {$primary}, +12 Burnout, -4 Health"],
                        ['tier' => 'mixed', 'weight' => 45, 'text' => "You keep going through {$context['title']}, but the exhaustion follows you home.", 'stat_effects' => "+4 {$primary}, +10 Burnout, -3 Health, -2 Happiness"],
                        ['tier' => 'setback', 'weight' => 35, 'text' => "The exhaustion catches up to you during {$context['title']} and everything slips.", 'stat_effects' => "+12 Burnout, -6 Health, -4 Happiness"],
                    ],
                ],
            ],
            'relationship_strain' => [
                [
                    'text' => 'Try to repair the distance',
                    'approach' => 'commit',
                    'stat_effects' => '+5 Happiness, -4 Isolation, +3 Morality',
                ],
                [
                    'text' => 'Keep them at arm\'s length',
                    'approach' => 'avoid',
                    'stat_effects' => '+5 Isolation, -4 Happiness, +2 Burnout',
                ],
            ],
            'scandal_pressure' => [
                [
                    'text' => 'Address the rumors honestly',
                    'approach' => 'balanced',
                    'stat_effects' => '+4 Morality, +3 Reputation, -2 Ego',
                ],
                [
                    'text' => 'Manage the image aggressively',
                    'approach' => 'risky',
                    'stat_effects' => '+4 Reputation, +4 Ego, +3 Burnout',
                    'outcomes' => [
                        ['tier' => 'great', 'weight' => 20, 'text' => "You control the narrative around {$context['title']} for now.", 'stat_effects' => '+6 Reputation, +4 Ego, +2 Burnout'],
                        ['tier' => 'mixed', 'weight' => 40, 'text' => "The story around {$context['title']} quiets down, but people stay suspicious.", 'stat_effects' => '+2 Reputation, +4 Burnout, -2 Morality'],
                        ['tier' => 'setback', 'weight' => 40, 'text' => "Trying to spin {$context['title']} makes the scandal louder.", 'stat_effects' => '-6 Reputation, +5 Ego, +4 Burnout'],
                    ],
                ],
            ],
            'dependency_risk' => [
                [
                    'text' => 'Ask for help before it gets worse',
                    'approach' => 'balanced',
                    'stat_effects' => '-4 Addiction, -3 Burnout, +2 Health, -2 Ego',
                ],
                [
                    'text' => 'Lean on the habit again',
                    'approach' => 'risky',
                    'stat_effects' => '+6 Addiction, +2 Happiness, -3 Health',
                ],
            ],
            'financial_fragility' => [
                [
                    'text' => 'Choose the stable option',
                    'approach' => 'balanced',
                    'stat_effects' => '-4 Debt, +3 Discipline, -2 Happiness',
                ],
                [
                    'text' => 'Take the cash now, consequences later',
                    'approach' => 'risky',
                    'stat_effects' => '+7 Wealth, +5 Debt, +3 Burnout',
                ],
            ],
            'study_habit' => [
                [
                    'text' => 'Double down on the routine',
                    'approach' => 'commit',
                    'stat_effects' => '+6 Intelligence, +5 Discipline, +3 Burnout',
                ],
                [
                    'text' => 'Teach what you already know',
                    'approach' => 'balanced',
                    'stat_effects' => '+4 Intelligence, +4 Reputation, -1 Isolation',
                ],
            ],
            'recovery_momentum' => [
                [
                    'text' => 'Protect the progress you made',
                    'approach' => 'balanced',
                    'stat_effects' => '+4 Health, -4 Burnout, +2 Discipline',
                ],
            ],
            default => [],
        };
    }

    private function eventValue(object|array $event, string $key): mixed
    {
        if (is_array($event)) {
            return $event[$key] ?? null;
        }

        return $event->{$key} ?? null;
    }

    private function lowercaseFirst(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'respond';
        }

        return strtolower(substr($value, 0, 1)) . substr($value, 1);
    }
}
