<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_choice',
        'title',
        'description',
        'narrative_description',
        'outcome',
        'stat_effects',
        'choices',
        'image',
        'weight',
        'age_group',
        'event_category',
        'chain_id',
        'chain_order',
        'parent_category',
        'required_choice_outcome',
        'next_chain_event',
        'age_transition',
        'next_age_group',
        // New fields from migration
        'type',
        'deck_label',
        'repeatable',
        'auto_resolve',
        'days_to_advance',
        'conditions',
        'display_order',
    ];

    protected $casts = [
        'repeatable' => 'boolean',
        'auto_resolve' => 'boolean',
        'conditions' => 'array',
        'choices' => 'array',
    ];

    /**
     * Get the stat effects as an array
     */
    public function getStatEffectsArray(): array
    {
        if (is_array($this->stat_effects)) {
            return $this->stat_effects;
        }
        
        $effects = [];
        if (!empty($this->stat_effects)) {
            $parts = explode(',', $this->stat_effects);
            foreach ($parts as $part) {
                $part = trim($part);
                if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                    $effects[$matches[2]] = (int)$matches[1];
                }
            }
        }
        return $effects;
    }

    /**
     * Check if this event is part of a chain.
     */
    public function isPartOfChain(): bool
    {
        return !empty($this->chain_id);
    }

    /**
     * Check if this event requires a specific choice outcome to unlock.
     */
    public function hasRequiredOutcome(): bool
    {
        return !empty($this->required_choice_outcome);
    }

    /**
     * Check if the event is available based on character chain completion.
     */
    public function isAvailableForCharacter($character): bool
    {
        // If no required outcome, event is available
        if (!$this->hasRequiredOutcome()) {
            return true;
        }

        // Check if character has the required choice outcome
        $choiceHistory = $character->choice_history ?? [];
        foreach ($choiceHistory as $choice) {
            if (($choice['outcome'] ?? '') === $this->required_choice_outcome) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this is an age transition event.
     */
    public function isAgeTransitionEvent(): bool
    {
        return $this->age_transition === true || $this->age_transition === 'true' || !empty($this->next_age_group);
    }

    /**
     * Get the next event in the chain.
     */
    public function getNextChainEvent()
    {
        if (empty($this->next_chain_event)) {
            return null;
        }

        return DailyEvent::where('event_choice', $this->next_chain_event)->first();
    }

    /**
     * Get all events in the same chain.
     */
    public function getChainEvents()
    {
        if (empty($this->chain_id)) {
            return collect([]);
        }

        return DailyEvent::where('chain_id', $this->chain_id)
            ->orderBy('chain_order', 'asc')
            ->get();
    }

    /**
     * Get the next event in the chain for a character based on their progress.
     */
    public function getNextEventInChainForCharacter($character)
    {
        if (empty($this->chain_id)) {
            return null;
        }

        $currentProgress = $character->getChainProgress($this->chain_id);
        $nextOrder = $currentProgress + 1;

        return DailyEvent::where('chain_id', $this->chain_id)
            ->where('chain_order', $nextOrder)
            ->first();
    }

    /**
     * Check if this event represents an age transition (e.g., teenager -> adult).
     * Returns the target age group if it's a transition event, null otherwise.
     */
    public function getTargetAgeGroup(): ?string
    {
        return $this->next_age_group ?? null;
    }

    /**
     * Get age-appropriate continuation events that bridge from previous age group.
     * This helps ensure continuity when character transitions between age groups.
     */
    public static function getAgeTransitionEvents(string $fromAgeGroup, string $toAgeGroup)
    {
        return DailyEvent::where('next_age_group', $toAgeGroup)
            ->where(function ($query) use ($fromAgeGroup) {
                $query->where('age_group', $fromAgeGroup)
                    ->orWhere('age_group', 'all');
            })
            ->orderBy('weight', 'desc')
            ->get();
    }

    /**
     * Check if this event should trigger a chain completion that bridges age groups.
     * Returns the chain ID if this is a bridge event, null otherwise.
     */
    public function getAgeBridgeChainId(): ?string
    {
        if ($this->isAgeTransitionEvent() && !empty($this->chain_id)) {
            return $this->chain_id;
        }
        return null;
    }
}
