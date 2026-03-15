<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Support\Privacy;

class Character extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Generate and save anon_character_id when creating a new character
        static::creating(function ($character) {
            if (empty($character->anon_character_id)) {
                // We'll generate it after the character is saved to get the ID
                // For now, use a temporary value that will be updated in the created event
            }
        });

        // Update anon_character_id after creation with the actual ID
        static::created(function ($character) {
            if (empty($character->anon_character_id)) {
                $anonId = Privacy::anonymize('character', $character->id);
                $character->update(['anon_character_id' => $anonId]);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'anon_character_id',
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
        // Life Stats
        'health',
        'happiness',
        'finance',
        'relationship_status',
        'career_level',
    ];

    /**
     * Accessor for anon_character_id - generates if not set
     */
    public function getAnonCharacterIdAttribute()
    {
        if (isset($this->attributes['anon_character_id']) && !empty($this->attributes['anon_character_id'])) {
            return $this->attributes['anon_character_id'];
        }

        // Generate anonymized ID if not set (requires id to be loaded)
        if (!$this->exists || !$this->id) {
            return 'pending-' . uniqid();
        }
        $anonId = Privacy::anonymize('character', $this->id);
        $this->setAttribute('anon_character_id', $anonId);
        return $anonId;
    }

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
        'stats' => '{"Intelligence": 0, "Strength": 0, "Charisma": 0, "Creativity": 0, "Wealth": 0, "Luck": 0, "Social": 50, "Empathy": 50}',
        'hidden_stats' => '{"Debt": 0, "Addiction": 0, "Burnout": 0, "Morality": 0, "Reputation": 0, "Discipline": 50, "Isolation": 0, "Ego": 0}',
        'gender_bonus' => '{}',
        'age_bonus' => '{}',
        'effective_stats' => '{}',
        'character_state' => '{"life_stage": "child", "profession_state": "unemployed", "relationship_status": "single", "health_condition": "healthy"}',
        'shown_event_ids' => '[]',
        'completed_event_chains' => '[]',
        'active_event_paths' => '[]',
        // Life Stats defaults
        'health' => 100,
        'happiness' => 100,
        'finance' => 0,
        'relationship_status' => 'single',
        'career_level' => 'unemployed',
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

    /**
     * Get the life stats snapshots for this character.
     */
    public function lifeStatsSnapshots(): HasMany
    {
        return $this->hasMany(LifeStatsSnapshot::class, 'anon_character_id', 'anon_character_id');
    }

    /**
     * Get the latest life stats snapshot for this character.
     */
    public function latestSnapshot(): HasMany
    {
        return $this->hasMany(LifeStatsSnapshot::class, 'anon_character_id', 'anon_character_id')
            ->latest()
            ->limit(1);
    }
}
