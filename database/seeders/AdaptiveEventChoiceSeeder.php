<?php

namespace Database\Seeders;

use App\Models\AgeSpecificEvent;
use App\Models\CulturalEvent;
use App\Models\DailyAction;
use App\Models\DailyEvent;
use App\Models\ProfessionPathEvent;
use App\Services\AdaptiveNarrativeService;
use Illuminate\Database\Seeder;

class AdaptiveEventChoiceSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(AdaptiveNarrativeService::class);

        $this->refreshChoices(DailyEvent::all(), $service, 'daily');
        $this->refreshChoices(CulturalEvent::all(), $service, 'cultural');
        $this->refreshChoices(AgeSpecificEvent::all(), $service, 'ageSpecific');
        $this->refreshChoices(ProfessionPathEvent::all(), $service, 'profession');
        $this->refreshChoices(DailyAction::all(), $service, 'system');
    }

    private function refreshChoices(iterable $events, AdaptiveNarrativeService $service, string $type): void
    {
        foreach ($events as $event) {
            $existingChoices = $event->choices;
            if (is_string($existingChoices)) {
                $decoded = json_decode($existingChoices, true);
                $existingChoices = is_array($decoded) ? $decoded : [];
            }

            $event->choices = $service->buildChoicesForEvent(
                (string) ($event->event_choice ?? $event->title ?? 'Event'),
                $event->outcome ?? $event->description ?? null,
                $event->stat_effects ?? null,
                $type,
                (string) ($event->age_group ?? 'adult'),
                is_array($existingChoices) ? $existingChoices : []
            );

            $event->save();
        }
    }
}
