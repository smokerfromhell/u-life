<?php

namespace Database\Seeders;

use App\Models\AgeSpecificEvent;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\ProfessionPathEvent;
use Illuminate\Database\Seeder;

class EventChoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add multiple choices to each event with different stat effects
     */
    public function run(): void
    {
        $this->command->info('Adding choices to Age Specific Events...');
        $this->addChoicesToAgeSpecificEvents();
        
        $this->command->info('Adding choices to Daily Events...');
        $this->addChoicesToDailyEvents();
        
        $this->command->info('Adding choices to Cultural Events...');
        $this->addChoicesToCulturalEvents();
        
        $this->command->info('Adding choices to Profession Path Events...');
        $this->addChoicesToProfessionPathEvents();
        
        $this->command->info('All event choices have been added!');
    }
    
    private function addChoicesToAgeSpecificEvents()
    {
        $events = AgeSpecificEvent::all();
        
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }

            $choices = $this->generateChoices($event->event_choice, $event->stat_effects, $event->age_group, 'ageSpecific');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToDailyEvents()
    {
        $events = DailyEvent::all();
        
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }

            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult', 'daily');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToCulturalEvents()
    {
        $events = CulturalEvent::all();
        
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }

            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult', 'cultural');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToProfessionPathEvents()
    {
        $events = ProfessionPathEvent::all();
        
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }

            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult', 'profession');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }

    private function eventHasChoices(mixed $choices): bool
    {
        if (is_array($choices)) {
            return count($choices) > 0;
        }

        if (is_string($choices)) {
            $trimmed = trim($choices);
            if ($trimmed === '' || strtolower($trimmed) === 'null') {
                return false;
            }
            $decoded = json_decode($choices, true);
            if (is_array($decoded)) {
                return count($decoded) > 0;
            }
            return true;
        }

        return false;
    }
    
    /**
     * Generate choices based on event type and original stat effects
     */
    private function generateChoices(string $eventName, ?string $originalEffects, string $ageGroup, string $deckType = 'ageSpecific'): array
    {
        $choices = [
            [
                'text' => 'Lean into it',
                'stat_effects' => $this->adjustEffectsDirectional($originalEffects, 1.0, 0.75),
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => $this->outcomeText($deckType, 'lean_in', 'success', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'lean_in', 'success'),
                    ],
                    [
                        'key' => 'failure',
                        'text' => $this->outcomeText($deckType, 'lean_in', 'failure', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'lean_in', 'failure'),
                    ],
                ],
            ],
            [
                'text' => 'Play it safe',
                'stat_effects' => $this->neutralizeEffects($originalEffects),
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => $this->outcomeText($deckType, 'safe', 'success', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'safe', 'success'),
                    ],
                    [
                        'key' => 'failure',
                        'text' => $this->outcomeText($deckType, 'safe', 'failure', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'safe', 'failure'),
                    ],
                ],
            ],
            [
                'text' => 'Take a risky shortcut',
                'stat_effects' => $this->adjustEffectsDirectional($originalEffects, 1.2, 1.25),
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => $this->outcomeText($deckType, 'shortcut', 'success', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'shortcut', 'success'),
                    ],
                    [
                        'key' => 'failure',
                        'text' => $this->outcomeText($deckType, 'shortcut', 'failure', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'shortcut', 'failure'),
                    ],
                ],
            ],
            [
                'text' => 'Avoid it for now',
                'stat_effects' => $this->mergeEffects($this->neutralizeEffects($originalEffects), $this->avoidBaseEffects($deckType)),
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => $this->outcomeText($deckType, 'avoid', 'success', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'avoid', 'success'),
                    ],
                    [
                        'key' => 'failure',
                        'text' => $this->outcomeText($deckType, 'avoid', 'failure', $eventName),
                        'stat_effects' => $this->outcomeEffects($deckType, 'avoid', 'failure'),
                    ],
                ],
            ]
        ];
        
        return $choices;
    }

    private function mergeEffects(?string ...$effectsTexts): string
    {
        $parts = [];
        foreach ($effectsTexts as $text) {
            $text = trim((string) $text);
            if ($text === '' || strtolower($text) === 'null') {
                continue;
            }
            $parts[] = $text;
        }
        return implode(', ', $parts);
    }

    private function avoidBaseEffects(string $deckType): string
    {
        return match ($deckType) {
            'profession' => '+3 Burnout, +1 Isolation',
            'cultural' => '+2 Isolation',
            default => '+2 Burnout',
        };
    }

    private function outcomeText(string $deckType, string $choiceKey, string $outcomeKey, string $eventName): string
    {
        $prefix = match ($deckType) {
            'daily' => 'Daily life',
            'cultural' => 'Culture',
            'profession' => 'Career',
            default => 'Life',
        };

        $verb = $outcomeKey === 'success' ? 'goes your way' : 'pushes back';

        return match ($choiceKey) {
            'lean_in' => "{$prefix} {$verb} — you get something out of \"{$eventName}\".",
            'safe' => "{$prefix} {$verb} — your cautious approach shapes \"{$eventName}\".",
            'shortcut' => $outcomeKey === 'success'
                ? "{$prefix} goes your way — the shortcut pays off in \"{$eventName}\"."
                : "{$prefix} pushes back — the shortcut backfires in \"{$eventName}\".",
            'avoid' => $outcomeKey === 'success'
                ? "{$prefix} goes your way — you sidestep \"{$eventName}\" without drama."
                : "{$prefix} pushes back — avoiding \"{$eventName}\" has consequences.",
            default => "{$prefix} {$verb}.",
        };
    }

    private function outcomeEffects(string $deckType, string $choiceKey, string $outcomeKey): ?string
    {
        $effects = match ($deckType) {
            'daily' => [
                'lean_in' => ['success' => '+2 Luck, +1 Happiness', 'failure' => '-1 Health, +2 Burnout'],
                'safe' => ['success' => '+1 Health', 'failure' => '+1 Burnout'],
                'shortcut' => ['success' => '+3 Wealth, -1 Morality', 'failure' => '+4 Debt, -2 Reputation'],
                'avoid' => ['success' => '+1 Discipline', 'failure' => '+3 Burnout, +2 Isolation'],
            ],
            'cultural' => [
                'lean_in' => ['success' => '+2 Charisma, +2 Reputation', 'failure' => '+3 Isolation, -1 Reputation'],
                'safe' => ['success' => '+1 Happiness', 'failure' => '+2 Isolation'],
                'shortcut' => ['success' => '+2 Reputation', 'failure' => '-2 Reputation, +2 Burnout'],
                'avoid' => ['success' => '+1 Happiness', 'failure' => '+3 Isolation'],
            ],
            'profession' => [
                'lean_in' => ['success' => '+2 Reputation, +2 Wealth', 'failure' => '-2 Reputation, +3 Burnout'],
                'safe' => ['success' => '+1 Discipline', 'failure' => '+2 Burnout'],
                'shortcut' => ['success' => '+4 Wealth, +2 Reputation, +2 Burnout', 'failure' => '-4 Reputation, +4 Burnout, +2 Debt'],
                'avoid' => ['success' => '+1 Health', 'failure' => '+3 Burnout, -1 Reputation'],
            ],
            default => [
                'lean_in' => ['success' => '+1 Happiness', 'failure' => '+2 Burnout'],
                'safe' => ['success' => '+1 Discipline', 'failure' => '+1 Burnout'],
                'shortcut' => ['success' => '+2 Wealth', 'failure' => '+2 Debt, +2 Burnout'],
                'avoid' => ['success' => '+1 Happiness', 'failure' => '+2 Isolation'],
            ],
        };

        return $effects[$choiceKey][$outcomeKey] ?? null;
    }

    /**
     * Adjust stat effects with separate multipliers for positive vs negative values.
     */
    private function adjustEffectsDirectional(?string $effects, float $positiveMultiplier, float $negativeMultiplier): string
    {
        if (empty($effects)) {
            return '';
        }

        $adjusted = [];
        $parts = explode(',', $effects);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\\d+)\\s+(\\w+)/', $part, $matches)) {
                $value = (int) $matches[1];
                $stat = $matches[2];

                $multiplier = $value >= 0 ? $positiveMultiplier : $negativeMultiplier;
                $newValue = (int) round($value * $multiplier);
                $newValue = $this->capStatValue($stat, $newValue);
                $adjusted[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                // Keep non-stat effects as-is (like explicit "End of game")
                $adjusted[] = $part;
            }
        }

        return implode(', ', $adjusted);
    }
    
    /**
     * Adjust stat effects by a multiplier
     */
    private function adjustEffects(?string $effects, float $multiplier): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $adjusted = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Apply multiplier
                $newValue = (int)($value * $multiplier);
                
                // Ensure we keep the sign
                if ($value < 0) {
                    $newValue = -abs($newValue);
                } else {
                    $newValue = abs($newValue);
                }
                
                // Cap stat values to reasonable ranges for profession balance
                $newValue = $this->capStatValue($stat, $newValue);
                
                $adjusted[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                // Keep non-stat effects as-is (like "End of game")
                $adjusted[] = $part;
            }
        }
        
        return implode(', ', $adjusted);
    }
    
    /**
     * Cap stat values to reasonable ranges for better profession path balance
     */
    private function capStatValue(string $stat, int $value): int
    {
        // Health: allow positive and negative swings, avoid seeder-created instant death.
        if ($stat === 'Health') {
            return max(-20, min(20, $value));
        }
        
        // Burnout and Isolation: negative stats should be capped more strictly
        if (in_array($stat, ['Burnout', 'Isolation', 'Addiction', 'Debt'])) {
            return max(-5, min(15, $value)); // Cap at +15, min -5 (negative is good for these)
        }
        
        // Other stats: balanced range
        return max(-15, min(15, $value)); // -15 to +15
    }
    
    /**
     * Negate the stat effects (make positive negative and vice versa)
     */
    private function negateEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $negated = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Negate the value and apply cap
                $newValue = $this->capStatValue($stat, -$value);
                
                $negated[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $negated[] = $part;
            }
        }
        
        return implode(', ', $negated);
    }
    
    /**
     * Neutralize stat effects (reduce to near zero)
     */
    private function neutralizeEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $neutral = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Reduce significantly and apply cap
                $newValue = $this->capStatValue($stat, (int)($value * 0.1));
                
                $neutral[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $neutral[] = $part;
            }
        }
        
        return implode(', ', $neutral);
    }
    
    /**
     * Worsen the stat effects
     */
    private function worsenEffects(?string $effects): string
    {
        return $this->adjustEffects($effects, 1.5);
    }
    
    /**
     * Get cheating-related effects
     */
    private function getCheatEffects(?string $effects): string
    {
        // Cheating gives positive short-term but negative long-term effects
        return '+15 Intelligence, -10 Morality, -5 Reputation';
    }
}
