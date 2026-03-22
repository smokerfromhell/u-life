<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterChoiceConsequence extends Model
{
    use HasFactory;

    protected $table = 'character_choice_consequences';

    protected $fillable = [
        'character_id',
        'chain_category',
        'choice_path',
        'chain_order',
        'cumulative_stat_modifiers',
        'locked_choices',
        'unlocked_choices',
        'pending_consequences',
        'last_outcome',
        'consecutive_positive',
        'consecutive_negative',
        // Random outcome fields
        'random_outcome_chance',
        'random_outcome_type',
        'random_outcomes',
        'last_random_outcome',
        'random_outcome_effects',
        'positive_outcome_weight',
        'negative_outcome_weight',
    ];

    protected $casts = [
        'cumulative_stat_modifiers' => 'array',
        'locked_choices' => 'array',
        'unlocked_choices' => 'array',
        'pending_consequences' => 'array',
        'chain_order' => 'integer',
        'consecutive_positive' => 'integer',
        'consecutive_negative' => 'integer',
        // Random outcome casts
        'random_outcome_chance' => 'float',
        'random_outcomes' => 'array',
        'random_outcome_effects' => 'array',
        'positive_outcome_weight' => 'float',
        'negative_outcome_weight' => 'float',
    ];

    /**
     * Get the character this consequence belongs to
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Add a stat modifier to the cumulative effects
     */
    public function addStatModifier(string $stat, int $value): void
    {
        $modifiers = $this->cumulative_stat_modifiers ?? [];
        
        if (isset($modifiers[$stat])) {
            $modifiers[$stat] += $value;
        } else {
            $modifiers[$stat] = $value;
        }
        
        $this->cumulative_stat_modifiers = $modifiers;
        $this->save();
    }

    /**
     * Lock a choice so it's no longer available
     */
    public function lockChoice(string $choiceId): void
    {
        $locked = $this->locked_choices ?? [];
        
        if (!in_array($choiceId, $locked)) {
            $locked[] = $choiceId;
            $this->locked_choices = $locked;
            $this->save();
        }
    }

    /**
     * Unlock new choices based on this decision
     */
    public function unlockChoice(string $choiceId): void
    {
        $unlocked = $this->unlocked_choices ?? [];
        
        if (!in_array($choiceId, $unlocked)) {
            $unlocked[] = $choiceId;
            $this->unlocked_choices = $unlocked;
            $this->save();
        }
    }

    /**
     * Add a pending consequence (future event)
     */
    public function addPendingConsequence(array $consequence): void
    {
        $pending = $this->pending_consequences ?? [];
        $pending[] = $consequence;
        $this->pending_consequences = $pending;
        $this->save();
    }

    /**
     * Update outcome streak
     */
    public function updateOutcomeStreak(string $outcome): void
    {
        if ($outcome === 'positive') {
            $this->consecutive_positive++;
            $this->consecutive_negative = 0;
        } elseif ($outcome === 'negative') {
            $this->consecutive_negative++;
            $this->consecutive_positive = 0;
        } else {
            $this->consecutive_positive = 0;
            $this->consecutive_negative = 0;
        }
        
        $this->last_outcome = $outcome;
        $this->save();
    }

    /**
     * Get or create consequence record for a chain
     */
    public static function getOrCreateForChain(Character $character, string $chainCategory): self
    {
        $consequence = self::where('character_id', $character->id)
            ->where('chain_category', $chainCategory)
            ->first();
        
        if (!$consequence) {
            $consequence = self::create([
                'character_id' => $character->id,
                'chain_category' => $chainCategory,
                'choice_path' => 'neutral',
                'chain_order' => 0,
            ]);
        }
        
        return $consequence;
    }

    /**
     * Configure random outcomes for this choice
     */
    public function setRandomOutcomes(float $chance, string $type, array $outcomes, float $positiveWeight = 50, float $negativeWeight = 50): self
    {
        $this->random_outcome_chance = $chance;
        $this->random_outcome_type = $type;
        $this->random_outcomes = $outcomes;
        $this->positive_outcome_weight = $positiveWeight;
        $this->negative_outcome_weight = $negativeWeight;
        $this->save();
        
        return $this;
    }

    /**
     * Generate a random outcome based on configuration
     * Returns null if no random outcome occurs
     */
    public function generateRandomOutcome(): ?array
    {
        // Check if random outcomes are configured
        if (!$this->random_outcome_chance || !$this->random_outcomes) {
            return null;
        }
        
        // Roll for random outcome
        $roll = rand(1, 100);
        if ($roll > $this->random_outcome_chance) {
            return null; // No random outcome
        }
        
        // Determine if positive or negative outcome
        $totalWeight = $this->positive_outcome_weight + $this->negative_outcome_weight;
        $positiveRoll = rand(1, (int)$totalWeight);
        $isPositive = $positiveRoll <= $this->positive_outcome_weight;
        
        // Filter outcomes by type
        $eligibleOutcomes = array_filter($this->random_outcomes, function($outcome) use ($isPositive) {
            $outcomeType = $outcome['type'] ?? 'neutral';
            if ($isPositive) {
                return in_array($outcomeType, ['positive', 'luck', 'both']);
            } else {
                return in_array($outcomeType, ['negative', 'misfortune', 'both']);
            }
        });
        
        if (empty($eligibleOutcomes)) {
            return null;
        }
        
        // Pick a random outcome from eligible ones
        $outcomeKeys = array_keys($eligibleOutcomes);
        $selectedKey = $outcomeKeys[array_rand($outcomeKeys)];
        $selectedOutcome = $eligibleOutcomes[$selectedKey];
        
        // Record the outcome
        $this->last_random_outcome = $selectedOutcome['name'] ?? 'unknown';
        $this->random_outcome_effects = $selectedOutcome['stat_effects'] ?? [];
        $this->save();
        
        return [
            'name' => $this->last_random_outcome,
            'description' => $selectedOutcome['description'] ?? '',
            'type' => $isPositive ? 'positive' : 'negative',
            'stat_effects' => $this->random_outcome_effects,
        ];
    }

    /**
     * Apply random outcome effects to character
     */
    public function applyRandomOutcomeEffects(Character $character): array
    {
        if (!$this->random_outcome_effects) {
            return [];
        }
        
        $appliedEffects = [];
        
        // Get current effective_stats array (following EventService pattern)
        $effectiveStats = is_array($character->effective_stats) 
            ? $character->effective_stats 
            : [];
        
        foreach ($this->random_outcome_effects as $stat => $value) {
            // Initialize stat if it doesn't exist
            if (!isset($effectiveStats[$stat])) {
                $effectiveStats[$stat] = 0;
            }
            
            // Add the value and clamp between 0-100
            $effectiveStats[$stat] += $value;
            $effectiveStats[$stat] = max(0, min(100, $effectiveStats[$stat]));
            
            $appliedEffects[$stat] = $value;
        }
        
        // Save the updated effective_stats
        $character->effective_stats = $effectiveStats;
        $character->save();
        
        return $appliedEffects;
    }

    /**
     * Check if there are pending random outcomes to apply
     */
    public function hasPendingRandomOutcome(): bool
    {
        return !empty($this->last_random_outcome) && !empty($this->random_outcome_effects);
    }

    /**
     * Clear random outcome state
     */
    public function clearRandomOutcomeState(): void
    {
        $this->last_random_outcome = null;
        $this->random_outcome_effects = null;
        $this->save();
    }
}
