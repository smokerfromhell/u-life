<?php

namespace Database\Seeders\Concerns;

trait GeneratesOutcomeChoices
{
    protected function eventHasChoices(mixed $choices): bool
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
     * Generate simple 2-choice structure (positive and negative) like DailyActionSeeder.
     */
    protected function generateSimpleChoices(string $eventName, ?string $positiveEffects, ?string $negativeEffects, string $ageGroup = 'all'): array
    {
        return [
            [
                'text' => 'Accept / Go for it',
                'stat_effects' => $positiveEffects ?? '+5 Happiness',
                'days_to_advance' => 0,
            ],
            [
                'text' => 'Decline / Avoid',
                'stat_effects' => $negativeEffects ?? '-2 Happiness',
                'days_to_advance' => 0,
            ],
        ];
    }

    /**
     * Generate negative variant of stat effects (for the decline/avoid choice).
     */
    protected function generateNegativeVariant(?string $effects): string
    {
        if (empty($effects)) {
            return '-2 Happiness';
        }

        $negated = [];
        $parts = explode(',', $effects);

        foreach ($parts as $part) {
            $part = trim($part);
            // Match stat effects like +10 Intelligence or -5 Health
            if (preg_match('/([+-]?\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int) $matches[1];
                $stat = $matches[2];

                // If positive, make negative; if negative, make positive
                $newValue = -$value;
                // Cap the value to reasonable limits
                $newValue = match($stat) {
                    'Health' => max(-15, min(15, $newValue)),
                    'Burnout', 'Isolation', 'Addiction', 'Debt' => max(-10, min(10, $newValue)),
                    default => max(-10, min(10, $newValue)),
                };
                $negated[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $negated[] = $part;
            }
        }

        return implode(', ', $negated);
    }

    /**
     * Generate "life action"-style choices with deterministic success/failure outcome variants.
     */
    protected function generateChoices(string $eventName, ?string $originalEffects, string $ageGroup, string $deckType = 'ageSpecific'): array
    {
        return [
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
            ],
        ];
    }

    protected function mergeEffects(?string ...$effectsTexts): string
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

    protected function avoidBaseEffects(string $deckType): string
    {
        return match ($deckType) {
            'profession' => '+3 Burnout, +1 Isolation',
            'cultural' => '+2 Isolation',
            default => '+2 Burnout',
        };
    }

    protected function outcomeText(string $deckType, string $choiceKey, string $outcomeKey, string $eventName): string
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

    protected function outcomeEffects(string $deckType, string $choiceKey, string $outcomeKey): ?string
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
    protected function adjustEffectsDirectional(?string $effects, float $positiveMultiplier, float $negativeMultiplier): string
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
                $adjusted[] = $part;
            }
        }

        return implode(', ', $adjusted);
    }

    protected function adjustEffects(?string $effects, float $multiplier): string
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

                $newValue = (int) round($value * $multiplier);
                $newValue = $this->capStatValue($stat, $newValue);
                $adjusted[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $adjusted[] = $part;
            }
        }

        return implode(', ', $adjusted);
    }

    protected function capStatValue(string $stat, int $value): int
    {
        if ($stat === 'Health') {
            return max(-20, min(20, $value));
        }

        if (in_array($stat, ['Burnout', 'Isolation', 'Addiction', 'Debt'], true)) {
            return max(-5, min(15, $value));
        }

        return max(-15, min(15, $value));
    }

    protected function negateEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }

        $negated = [];
        $parts = explode(',', $effects);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\\d+)\\s+(\\w+)/', $part, $matches)) {
                $value = (int) $matches[1];
                $stat = $matches[2];

                $newValue = $this->capStatValue($stat, -$value);
                $negated[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $negated[] = $part;
            }
        }

        return implode(', ', $negated);
    }

    protected function neutralizeEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }

        $neutral = [];
        $parts = explode(',', $effects);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\\d+)\\s+(\\w+)/', $part, $matches)) {
                $value = (int) $matches[1];
                $stat = $matches[2];

                $newValue = $this->capStatValue($stat, (int) round($value * 0.1));
                $neutral[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $neutral[] = $part;
            }
        }

        return implode(', ', $neutral);
    }

    protected function worsenEffects(?string $effects): string
    {
        return $this->adjustEffects($effects, 1.5);
    }
}

