<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeSpecificEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'age_group',
        'event_choice',
        'title',
        'description',
        'outcome',
        'stat_effects',
        'choices',
        'image',
        'weight',
        'event_category',
        'chain_order',
        'parent_category',
        'required_choice_outcome',
        'required_stat',
        'stat_threshold',
        'is_milestone',
        'alternative_outcomes',
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
        'is_milestone' => 'boolean',
        'choices' => 'array',
        'conditions' => 'array',
        'chain_order' => 'integer',
        'weight' => 'float',
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
     * Get events for a specific age group
     */
    public static function getEventsByAgeGroup(string $ageGroup)
    {
        return self::where('age_group', $ageGroup)->get();
    }
}
