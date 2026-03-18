<?php

namespace Tests\Unit;

use App\Models\Character;
use App\Services\AdaptiveNarrativeService;
use Tests\TestCase;

class AdaptiveNarrativeServiceTest extends TestCase
{
    public function test_it_builds_title_aware_choices_for_study_events(): void
    {
        $service = app(AdaptiveNarrativeService::class);

        $choices = $service->buildChoicesForEvent(
            'Study - Ace exam',
            'Prepare for your exams.',
            '+10 Intelligence, +4 Discipline',
            'system',
            'teen',
            []
        );

        $this->assertCount(3, $choices);
        $this->assertSame('study', $service->detectArchetype('Study - Ace exam', 'Prepare for your exams.', 'system'));
        $this->assertSame('Study hard', $choices[0]['text']);
        $this->assertTrue(collect($choices[0]['outcomes'])->contains(
            fn (array $outcome) => str_contains(strtolower($outcome['text'] ?? ''), 'migraine')
        ));
    }

    public function test_it_scores_related_events_higher_for_matching_character_memory(): void
    {
        $service = app(AdaptiveNarrativeService::class);

        $character = new Character();
        $character->character_state = [
            'decision_profile' => [
                'archetypes' => ['study' => 8],
                'traits' => ['ambition' => 4, 'balance' => 2, 'avoidance' => 0, 'risk' => 0, 'care' => 0, 'discipline' => 3],
                'stress' => ['burnout_pressure' => 1, 'social_strain' => 0, 'financial_pressure' => 0],
                'flags' => ['studious' => true],
            ],
        ];

        $event = (object) [
            'title' => 'Study - Ace exam',
            'description' => 'Prepare for your exams.',
            'weight' => 4,
        ];

        $scoredWeight = $service->scoreEventWeight($character, $event, 'daily');

        $this->assertGreaterThan(4, $scoredWeight);
    }

    public function test_it_resolves_a_valid_outcome_tier(): void
    {
        $service = app(AdaptiveNarrativeService::class);

        $character = new Character();
        $character->effective_stats = [
            'Intelligence' => 82,
            'Discipline' => 74,
            'Burnout' => 8,
        ];
        $character->character_state = [];

        $choices = $service->buildChoicesForEvent(
            'Study - Ace exam',
            'Prepare for your exams.',
            '+10 Intelligence, +4 Discipline',
            'system',
            'teen',
            []
        );

        $outcome = $service->resolveOutcome(
            $character,
            [
                'title' => 'Study - Ace exam',
                'description' => 'Prepare for your exams.',
                'type' => 'system',
                'archetype' => 'study',
            ],
            $choices[0]
        );

        $this->assertNotNull($outcome);
        $this->assertContains($outcome['tier'] ?? null, ['great', 'mixed', 'setback']);
    }

    public function test_it_overlays_future_choices_when_a_long_term_consequence_is_active(): void
    {
        $service = app(AdaptiveNarrativeService::class);

        $character = new Character();
        $character->character_state = [
            'decision_profile' => [
                'archetypes' => ['study' => 7],
                'traits' => ['ambition' => 4, 'balance' => 1, 'avoidance' => 0, 'risk' => 1, 'care' => 0, 'discipline' => 3],
                'stress' => ['burnout_pressure' => 9, 'social_strain' => 0, 'financial_pressure' => 0],
                'consequences' => ['burnout_cycle' => 8],
                'flags' => ['burnout_cycle' => true, 'overextended' => true],
            ],
        ];

        $choices = $service->buildChoicesForEvent(
            'Study - Ace exam',
            'Prepare for your exams.',
            '+10 Intelligence, +4 Discipline',
            'system',
            'teen',
            []
        );

        $adapted = $service->adaptChoicesForCharacter(
            $character,
            [
                'title' => 'Study - Ace exam',
                'description' => 'Prepare for your exams.',
                'type' => 'system',
                'age_group' => 'teen',
                'archetype' => 'study',
            ],
            $choices
        );

        $texts = collect($adapted)->pluck('text')->values()->all();

        $this->assertContains('Take a real recovery break', $texts);
        $this->assertContains('Push through the exhaustion', $texts);
    }
}
