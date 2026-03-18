<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatTriggerCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger_stat_condition',
        'stat_name',
        'threshold',
        'event_choice',
        'outcome',
        'stat_effects',
        'weight',
        // New fields from migration
        'title',
        'description',
        'image',
        'type',
        'deck_label',
        'repeatable',
        'auto_resolve',
        'days_to_advance',
        'choices',
        'display_order',
    ];

    protected $casts = [
        'repeatable' => 'boolean',
        'auto_resolve' => 'boolean',
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
}
