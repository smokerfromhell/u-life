<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'age_group',
        'previous_age_group',
        'gender',
        'profession',
        'current_day',
        'shown_event_ids',
        'completed_event_chains',
        'active_event_paths',
        'current_narrative',
        'stats',
        'hidden_stats',
        'gender_bonus',
        'age_bonus',
        'effective_stats',
        'image',
    ];

    protected $casts = [
        'stats' => 'array',
        'hidden_stats' => 'array',
        'gender_bonus' => 'array',
        'age_bonus' => 'array',
        'effective_stats' => 'array',
        'character_state' => 'array',
        'completed_event_chains' => 'array',
        'active_event_paths' => 'array',
        'shown_event_ids' => 'array',
    ];

    protected $appends = ['current_state'];

    protected $attributes = [
        'stats' => '{"Intelligence": 0, "Strength": 0, "Charisma": 0, "Creativity": 0, "Wealth": 0, "Luck": 0}',
        'hidden_stats' => '{"Debt": 0, "Health": 0, "Addiction": 0, "Burnout": 0, "Morality": 0, "Happiness": 0, "Reputation": 0, "Discipline": 0, "Isolation": 0, "Ego": 0}',
        'gender_bonus' => '{}',
        'age_bonus' => '{}',
        'effective_stats' => '{}',
        'character_state' => '{"life_stage": "child", "profession_state": "unemployed", "relationship_status": "single", "health_condition": "healthy"}',
        'shown_event_ids' => '[]',
        'completed_event_chains' => '[]',
        'active_event_paths' => '[]',
    ];

    public function getCurrentStateAttribute()
    {
        return $this->character_state ?? ['life_stage' => 'child', 'profession_state' => 'unemployed', 'relationship_status' => 'single', 'health_condition' => 'healthy'];
    }

    /**
     * Get the user that owns the character.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the skills associated with the character.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'character_skill');
    }

    /**
     * Get the talents associated with the character.
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'character_talent');
    }
}
