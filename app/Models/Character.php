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
        // Branching system fields
        'choice_history',
        'relationship_state',
        'reputation_by_faction',
        'trauma_flags',
        'achievement_flags',
        'pending_events',
        'stats',
        'hidden_stats',
        'gender_bonus',
        'age_bonus',
        'effective_stats',
        'character_state',
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
        // Branching system casts
        'choice_history' => 'array',
        'relationship_state' => 'array',
        'reputation_by_faction' => 'array',
        'trauma_flags' => 'array',
        'achievement_flags' => 'array',
        'pending_events' => 'array',
    ];

    protected $appends = ['current_state'];

    protected $attributes = [
        'stats' => '{"Intelligence": 25, "Strength": 25, "Charisma": 25, "Creativity": 25, "Wealth": 20, "Luck": 20, "Social": 50, "Empathy": 50}',
        'hidden_stats' => '{"Debt": 0, "Health": 78, "Addiction": 0, "Burnout": 5, "Morality": 45, "Happiness": 72, "Reputation": 35, "Discipline": 40, "Isolation": 6, "Ego": 10}',
        'gender_bonus' => '{}',
        'age_bonus' => '{}',
        'effective_stats' => '{}',
        'character_state' => '{"life_stage": "child", "profession_state": "unemployed", "relationship_status": "single", "health_condition": "healthy", "is_dead": false}',
        'shown_event_ids' => '[]',
        'completed_event_chains' => '[]',
        'active_event_paths' => '[]',
        // Branching system defaults
        'choice_history' => '[]',
        'relationship_state' => '{"family": "neutral", "friends": "neutral", "romantic": "neutral", "career": "neutral", "community": "neutral"}',
        'reputation_by_faction' => '{"family": 50, "friends": 50, "romantic": 50, "career": 50, "community": 50}',
        'trauma_flags' => '[]',
        'achievement_flags' => '[]',
        'pending_events' => '[]',
        // Life Stats defaults
        'health' => 78,
        'happiness' => 72,
        'finance' => 20,
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
     * Learn a new skill.
     * Returns true if skill was learned, false if already known.
     */
    public function learnSkill(string $skillName): bool
    {
        $skill = Skill::where('name', $skillName)->first();
        
        if (!$skill) {
            return false;
        }
        
        // Check if character already has this skill
        $hasSkill = $this->skills()->where('skills.id', $skill->id)->exists();
        
        if ($hasSkill) {
            return false; // Already knows this skill
        }
        
        // Attach the skill to the character
        $this->skills()->attach($skill->id);
        
        return true;
    }

    /**
     * Check if character has a specific skill.
     */
    public function hasSkill(string $skillName): bool
    {
        return $this->skills()->where('skills.name', $skillName)->exists();
    }

    /**
     * Get the talents associated with the character.
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'character_talent');
    }

    /**
     * Discover/unlock a new talent.
     * Returns true if talent was discovered, false if already known.
     */
    public function discoverTalent(string $talentName): bool
    {
        $talent = Talent::where('name', $talentName)->first();
        
        if (!$talent) {
            return false;
        }
        
        // Check if character already has this talent
        $hasTalent = $this->talents()->where('talents.id', $talent->id)->exists();
        
        if ($hasTalent) {
            return false; // Already has this talent
        }
        
        // Attach the talent to the character
        $this->talents()->attach($talent->id);
        
        return true;
    }

    /**
     * Check if character has a specific talent.
     */
    public function hasTalent(string $talentName): bool
    {
        return $this->talents()->where('talents.name', $talentName)->exists();
    }

    // =============================================
    // REPUTATION METHODS
    // =============================================

    /**
     * Get reputation for a specific faction.
     * Returns value between 0-100.
     */
    public function getReputation(string $faction): int
    {
        $reputationData = $this->reputation_by_faction ?? '{}';
        $reputations = is_array($reputationData) 
            ? $reputationData 
            : json_decode($reputationData, true) 
            ?? [];
        
        return (int) ($reputations[$faction] ?? 50);
    }

    /**
     * Get all faction reputations.
     */
    public function getAllReputations(): array
    {
        $reputationData = $this->reputation_by_faction ?? '{}';
        return is_array($reputationData) 
            ? $reputationData 
            : json_decode($reputationData, true) 
            ?? ['family' => 50, 'friends' => 50, 'romantic' => 50, 'career' => 50, 'community' => 50];
    }

    /**
     * Update reputation for a specific faction.
     * Positive values increase reputation, negative decrease.
     * Reputation is clamped between 0-100.
     */
    public function updateReputation(string $faction, int $change): bool
    {
        $reputations = $this->getAllReputations();
        
        // Ensure faction exists
        if (!isset($reputations[$faction])) {
            $reputations[$faction] = 50;
        }
        
        // Apply change and clamp between 0-100
        $reputations[$faction] = max(0, min(100, $reputations[$faction] + $change));
        
        $this->reputation_by_faction = $reputations;
        $this->save();
        
        return true;
    }

    /**
     * Check if reputation meets minimum threshold for a faction.
     */
    public function hasMinReputation(string $faction, int $minimum): bool
    {
        return $this->getReputation($faction) >= $minimum;
    }

    /**
     * Check if reputation is at or below maximum for a faction.
     */
    public function hasMaxReputation(string $faction, int $maximum): bool
    {
        return $this->getReputation($faction) <= $maximum;
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

    /**
     * Get the choice consequences for this character.
     */
    public function choiceConsequences(): HasMany
    {
        return $this->hasMany(CharacterChoiceConsequence::class);
    }

    /**
     * Record a choice in history.
     */
    public function recordChoice(string $category, string $choiceId, string $outcome, array $effects = []): void
    {
        $history = $this->choice_history ?? [];
        $history[] = [
            'category' => $category,
            'choice_id' => $choiceId,
            'outcome' => $outcome,
            'effects' => $effects,
            'day' => $this->current_day,
            'timestamp' => now()->toISOString(),
        ];
        $this->choice_history = $history;
        $this->save();
    }

    /**
     * Update relationship state.
     */
    public function updateRelationshipState(string $group, string $state): void
    {
        $relationships = $this->relationship_state ?? [];
        $relationships[$group] = $state;
        $this->relationship_state = $relationships;
        $this->save();
    }

    /**
     * Get relationship state for a group.
     */
    public function getRelationshipState(string $group): string
    {
        $relationships = $this->relationship_state ?? [];
        return $relationships[$group] ?? 'neutral';
    }

    /**
     * Check if relationship state meets minimum threshold.
     */
    public function hasMinRelationshipState(string $group, string $minimumState): bool
    {
        $stateHierarchy = ['hostile' => 0, 'cold' => 1, 'neutral' => 2, 'warm' => 3, 'close' => 4];
        $currentState = $this->getRelationshipState($group);
        $currentLevel = $stateHierarchy[$currentState] ?? 2;
        $minimumLevel = $stateHierarchy[$minimumState] ?? 2;
        return $currentLevel >= $minimumLevel;
    }

    /**
     * Check if relationship state meets maximum threshold.
     */
    public function hasMaxRelationshipState(string $group, string $maximumState): bool
    {
        $stateHierarchy = ['hostile' => 0, 'cold' => 1, 'neutral' => 2, 'warm' => 3, 'close' => 4];
        $currentState = $this->getRelationshipState($group);
        $currentLevel = $stateHierarchy[$currentState] ?? 2;
        $maximumLevel = $stateHierarchy[$maximumState] ?? 2;
        return $currentLevel <= $maximumLevel;
    }

    /**
     * Get relationship status (single, married, etc.).
     */
    public function getRelationshipStatus(): string
    {
        return $this->relationship_status ?? 'single';
    }

    /**
     * Set relationship status.
     */
    public function setRelationshipStatus(string $status): void
    {
        $validStatuses = ['single', 'dating', 'engaged', 'married', 'divorced', 'widowed'];
        if (in_array($status, $validStatuses)) {
            $this->relationship_status = $status;
            $this->save();
        }
    }

    /**
     * Check if character has specific relationship status.
     */
    public function hasRelationshipStatus(string $status): bool
    {
        return $this->getRelationshipStatus() === $status;
    }

    /**
     * Check if character is in a relationship (dating, engaged, married).
     */
    public function isInRelationship(): bool
    {
        $status = $this->getRelationshipStatus();
        return in_array($status, ['dating', 'engaged', 'married']);
    }

    /**
     * Add a social connection/relationship.
     */
    public function addSocialConnection(string $type, string $name, array $details = []): void
    {
        $connections = $this->social_connections ?? [];
        $connections[] = [
            'type' => $type, // friend, family, colleague, mentor, etc.
            'name' => $name,
            'day_added' => $this->current_day,
            'details' => $details,
        ];
        $this->social_connections = $connections;
        $this->save();
    }

    /**
     * Check if character has a social connection of specific type.
     */
    public function hasSocialConnection(string $type, string $name = null): bool
    {
        $connections = $this->social_connections ?? [];
        foreach ($connections as $connection) {
            if ($connection['type'] === $type) {
                if ($name === null || ($connection['name'] ?? '') === $name) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get all social connections of a specific type.
     */
    public function getSocialConnections(string $type = null): array
    {
        $connections = $this->social_connections ?? [];
        if ($type === null) {
            return $connections;
        }
        return array_filter($connections, fn($c) => ($c['type'] ?? '') === $type);
    }

    /**
     * Get current location.
     */
    public function getLocation(): string
    {
        $state = is_array($this->character_state) ? $this->character_state : [];
        return $state['location'] ?? 'hometown';
    }

    /**
     * Set current location.
     */
    public function setLocation(string $location): void
    {
        $state = is_array($this->character_state) ? $this->character_state : [];
        $state['location'] = $location;
        $this->character_state = $state;
        $this->save();
    }

    /**
     * Check if character is at specific location.
     */
    public function isAtLocation(string $location): bool
    {
        return $this->getLocation() === $location;
    }

    /**
     * Get current season.
     */
    public function getSeason(): string
    {
        $state = is_array($this->character_state) ? $this->character_state : [];
        return $state['season'] ?? 'spring';
    }

    /**
     * Set current season.
     */
    public function setSeason(string $season): void
    {
        $validSeasons = ['spring', 'summer', 'autumn', 'winter'];
        if (!in_array($season, $validSeasons)) {
            return;
        }
        $state = is_array($this->character_state) ? $this->character_state : [];
        $state['season'] = $season;
        $this->character_state = $state;
        $this->save();
    }

    /**
     * Check if current season matches.
     */
    public function isSeason(string $season): bool
    {
        return $this->getSeason() === $season;
    }

    /**
     * Get weather condition.
     */
    public function getWeather(): string
    {
        $state = is_array($this->character_state) ? $this->character_state : [];
        return $state['weather'] ?? 'clear';
    }

    /**
     * Set weather condition.
     */
    public function setWeather(string $weather): void
    {
        $validWeather = ['clear', 'cloudy', 'rainy', 'stormy', 'snowy', 'foggy'];
        if (!in_array($weather, $validWeather)) {
            return;
        }
        $state = is_array($this->character_state) ? $this->character_state : [];
        $state['weather'] = $weather;
        $this->character_state = $state;
        $this->save();
    }

    /**
     * Check if weather matches.
     */
    public function isWeather(string $weather): bool
    {
        return $this->getWeather() === $weather;
    }

    /**
     * Add a trauma flag from negative events.
     */
    public function addTraumaFlag(string $traumaType, string $source): void
    {
        $traumas = $this->trauma_flags ?? [];
        $traumas[] = [
            'type' => $traumaType,
            'source' => $source,
            'day' => $this->current_day,
            'timestamp' => now()->toISOString(),
        ];
        $this->trauma_flags = $traumas;
        $this->save();
    }

    /**
     * Check if character has a specific type of trauma.
     */
    public function hasTrauma(string $traumaType): bool
    {
        $traumas = $this->trauma_flags ?? [];
        foreach ($traumas as $trauma) {
            if (($trauma['type'] ?? '') === $traumaType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all trauma types the character has.
     */
    public function getTraumaTypes(): array
    {
        $traumas = $this->trauma_flags ?? [];
        $types = [];
        foreach ($traumas as $trauma) {
            $types[] = $trauma['type'] ?? '';
        }
        return array_unique(array_filter($types));
    }

    /**
     * Heal a specific type of trauma.
     * Returns true if healing was successful, false if no trauma of that type exists.
     */
    public function healTrauma(string $traumaType): bool
    {
        $traumas = $this->trauma_flags ?? [];
        
        $found = false;
        $newTraumas = [];
        
        foreach ($traumas as $trauma) {
            if (($trauma['type'] ?? '') === $traumaType && !$found) {
                $found = true; // Remove first occurrence only
                continue; // Skip this trauma
            }
            $newTraumas[] = $trauma;
        }
        
        if ($found) {
            $this->trauma_flags = $newTraumas;
            $this->save();
        }
        
        return $found;
    }

    /**
     * Heal all traumas.
     */
    public function healAllTraumas(): void
    {
        $this->trauma_flags = [];
        $this->save();
    }

    /**
     * Gain a trauma (simpler version for DailyActions).
     * Does not track source, just adds the trauma type.
     */
    public function gainTrauma(string $traumaType): void
    {
        // Don't add duplicate trauma of the same type
        if ($this->hasTrauma($traumaType)) {
            return;
        }
        
        $traumas = $this->trauma_flags ?? [];
        $traumas[] = [
            'type' => $traumaType,
            'source' => 'daily_action',
            'day' => $this->current_day,
            'timestamp' => now()->toISOString(),
        ];
        $this->trauma_flags = $traumas;
        $this->save();
    }

    /**
     * Add an achievement.
     */
    public function addAchievement(string $achievementId, string $description): void
    {
        $achievements = $this->achievement_flags ?? [];
        $achievements[] = [
            'id' => $achievementId,
            'description' => $description,
            'day' => $this->current_day,
            'timestamp' => now()->toISOString(),
        ];
        $this->achievement_flags = $achievements;
        $this->save();
    }

    /**
     * Add a pending event.
     */
    public function addPendingEvent(array $event): void
    {
        $pending = $this->pending_events ?? [];
        $pending[] = $event;
        $this->pending_events = $pending;
        $this->save();
    }

    /**
     * Check if a choice is locked.
     */
    public function isChoiceLocked(string $choiceId): bool
    {
        $locked = $this->choiceConsequences()
            ->whereNotNull('locked_choices')
            ->get()
            ->pluck('locked_choices')
            ->flatten()
            ->toArray();
        
        return in_array($choiceId, $locked);
    }
}
