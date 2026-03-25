<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'pending_chains',
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
        // Social Connections
        'social_connections',
        // Luck System
        'luck',
        'karma',
        'luck_history',
        'last_luck_event',
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
        'pending_chains' => 'array',
        'active_event_paths' => 'array',
        'shown_event_ids' => 'array',
        // Branching system casts
        'choice_history' => 'array',
        'relationship_state' => 'array',
        'reputation_by_faction' => 'array',
        'trauma_flags' => 'array',
        'achievement_flags' => 'array',
        'pending_events' => 'array',
        // Social Connections
        'social_connections' => 'array',
    ];

    protected $appends = ['current_state'];

    protected $attributes = [
        'stats' => '{"Intelligence": 25, "Strength": 25, "Charisma": 25, "Creativity": 25, "Wealth": 20, "Luck": 20, "Social": 50, "Empathy": 50}',
        'hidden_stats' => '{"Debt": 0, "Addiction": 0, "Burnout": 5, "Morality": 45, "Reputation": 35, "Discipline": 40, "Isolation": 6, "Ego": 10}',
        'gender_bonus' => '{}',
        'age_bonus' => '{}',
        'effective_stats' => '{}',
        'character_state' => '{"life_stage": "child", "profession_state": "unemployed", "relationship_status": "single", "health_condition": "healthy", "is_dead": false}',
        'shown_event_ids' => '[]',
        'completed_event_chains' => '[]',
        'pending_chains' => '[]',
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
        // Social Connections defaults
        'social_connections' => '[]',
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
     * Get the personality profile for this character.
     */
    public function personalityProfile(): HasOne
    {
        return $this->hasOne(PersonalityProfile::class);
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
     * JSON is source of truth, falls back to column for backwards compatibility.
     */
    public function getRelationshipStatus(): string
    {
        // Check JSON first (source of truth)
        $state = is_array($this->character_state) ? $this->character_state : [];
        if (isset($state['relationship_status']) && !empty($state['relationship_status'])) {
            return $state['relationship_status'];
        }
        // Fallback to column
        return $this->relationship_status ?? 'single';
    }

    /**
     * Mutator for relationship_status - syncs to JSON when directly assigned.
     */
    public function setRelationshipStatusAttribute($value)
    {
        $validStatuses = ['single', 'dating', 'engaged', 'married', 'divorced', 'widowed'];
        if (in_array($value, $validStatuses)) {
            // Update JSON (source of truth)
            $state = is_array($this->character_state) ? $this->character_state : [];
            $state['relationship_status'] = $value;
            $this->character_state = $state;
            // Also set the column for backwards compatibility
            $this->attributes['relationship_status'] = $value;
        }
    }

    // =============================================
    // LIFE STATS ACCESSORS/MUTATORS (effective_stats based)
    // =============================================

    /**
     * Get health from effective_stats (source of truth).
     */
    public function getHealthAttribute(): int
    {
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        if (isset($effectiveStats['Health'])) {
            return (int) $effectiveStats['Health'];
        }
        // Fallback to column
        return (int) ($this->attributes['health'] ?? 78);
    }

    /**
     * Set health - updates effective_stats and syncs to column.
     */
    public function setHealthAttribute($value)
    {
        $value = (int) $value;
        // Update effective_stats (source of truth)
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        $effectiveStats['Health'] = $value;
        $this->effective_stats = $effectiveStats;
        // Also sync to column for backwards compatibility
        $this->attributes['health'] = $value;
    }

    /**
     * Get happiness from effective_stats (source of truth).
     */
    public function getHappinessAttribute(): int
    {
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        if (isset($effectiveStats['Happiness'])) {
            return (int) $effectiveStats['Happiness'];
        }
        // Fallback to column
        return (int) ($this->attributes['happiness'] ?? 72);
    }

    /**
     * Set happiness - updates effective_stats and syncs to column.
     */
    public function setHappinessAttribute($value)
    {
        $value = (int) $value;
        // Update effective_stats (source of truth)
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        $effectiveStats['Happiness'] = $value;
        $this->effective_stats = $effectiveStats;
        // Also sync to column for backwards compatibility
        $this->attributes['happiness'] = $value;
    }

    /**
     * Get finance from effective_stats (source of truth).
     */
    public function getFinanceAttribute(): int
    {
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        if (isset($effectiveStats['Wealth'])) {
            return (int) $effectiveStats['Wealth'];
        }
        if (isset($effectiveStats['Finance'])) {
            return (int) $effectiveStats['Finance'];
        }
        // Fallback to column
        return (int) ($this->attributes['finance'] ?? 20);
    }

    /**
     * Set finance - updates effective_stats and syncs to column.
     */
    public function setFinanceAttribute($value)
    {
        $value = (int) $value;
        // Update effective_stats (source of truth)
        $effectiveStats = is_array($this->effective_stats) ? $this->effective_stats : [];
        $effectiveStats['Wealth'] = $value;
        $effectiveStats['Finance'] = $value;
        $this->effective_stats = $effectiveStats;
        // Also sync to column for backwards compatibility
        $this->attributes['finance'] = $value;
    }

    /**
     * Set relationship status.
     * Updates both JSON (source of truth) and column (backwards compatibility).
     */
    public function setRelationshipStatus(string $status): void
    {
        $validStatuses = ['single', 'dating', 'engaged', 'married', 'divorced', 'widowed'];
        if (in_array($status, $validStatuses)) {
            // Update JSON (source of truth)
            $state = is_array($this->character_state) ? $this->character_state : [];
            $state['relationship_status'] = $status;
            $this->character_state = $state;
            
            // Also update column for backwards compatibility
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
     * Get character profession.
     */
    public function getProfession(): ?string
    {
        return $this->profession;
    }

    /**
     * Set profession.
     */
    public function setProfession(?string $profession): void
    {
        $this->profession = $profession;
        $this->save();
    }

    /**
     * Check if character has specific profession.
     */
    public function hasProfession(string $profession): bool
    {
        return $this->getProfession() === $profession;
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
     * Relationship Chain Tracking
     * Enables NPC-based narrative chains (e.g., Meet → Befriend → Help → Ally)
     */

    /**
     * Start or update progress in a relationship chain with an NPC.
     * @param string $npcName The name of the NPC
     * @param string $relationshipType Type of relationship (friend, mentor, rival, etc.)
     * @param int $progress Current progress step (1 = just met, higher = more established)
     * @param array $metadata Additional data about the relationship
     */
    public function updateRelationshipChainProgress(string $npcName, string $relationshipType, int $progress, array $metadata = []): void
    {
        $characterState = is_array($this->character_state) ? $this->character_state : [];
        $relationshipChains = $characterState['relationship_chains'] ?? [];
        
        // Find existing or create new
        $found = false;
        foreach ($relationshipChains as &$chain) {
            if ($chain['npc_name'] === $npcName && $chain['relationship_type'] === $relationshipType) {
                $chain['progress'] = $progress;
                $chain['last_updated'] = now()->toISOString();
                $chain['metadata'] = array_merge($chain['metadata'] ?? [], $metadata);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $relationshipChains[] = [
                'npc_name' => $npcName,
                'relationship_type' => $relationshipType,
                'progress' => $progress,
                'started_at' => $this->current_day,
                'started_age_group' => $this->age_group,
                'last_updated' => now()->toISOString(),
                'metadata' => $metadata,
            ];
        }
        
        $characterState['relationship_chains'] = $relationshipChains;
        $this->character_state = $characterState;
        $this->save();
    }

    /**
     * Get all relationship chain progress for a character.
     * @return array Array of relationship chains with their progress
     */
    public function getRelationshipChains(): array
    {
        $characterState = is_array($this->character_state) ? $this->character_state : [];
        return $characterState['relationship_chains'] ?? [];
    }

    /**
     * Get the current progress with a specific NPC.
     * @param string $npcName The name of the NPC
     * @param string $relationshipType Optional type of relationship
     * @return int Progress level (0 if no relationship)
     */
    public function getRelationshipProgress(string $npcName, string $relationshipType = null): int
    {
        $relationshipChains = $this->getRelationshipChains();
        
        foreach ($relationshipChains as $chain) {
            if ($chain['npc_name'] === $npcName) {
                if ($relationshipType === null || ($chain['relationship_type'] ?? '') === $relationshipType) {
                    return $chain['progress'] ?? 0;
                }
            }
        }
        
        return 0;
    }

    /**
     * Check if character has met a specific NPC.
     * @param string $npcName The name of the NPC
     * @return bool True if character has any relationship with this NPC
     */
    public function hasMetNPC(string $npcName): bool
    {
        return $this->getRelationshipProgress($npcName) > 0;
    }

    /**
     * Get all NPCs the character has a relationship with.
     * @param string|null $relationshipType Filter by type (friend, mentor, etc.)
     * @return array List of NPC names
     */
    public function getKnownNPCs(string $relationshipType = null): array
    {
        $relationshipChains = $this->getRelationshipChains();
        $npcs = [];
        
        foreach ($relationshipChains as $chain) {
            if ($relationshipType === null || ($chain['relationship_type'] ?? '') === $relationshipType) {
                $npcs[] = $chain['npc_name'];
            }
        }
        
        return array_unique($npcs);
    }

    /**
     * Get the relationship level with an NPC.
     * This is derived from progress and can be used for event prerequisites.
     * @param string $npcName The name of the NPC
     * @return string Level: 'stranger', 'acquaintance', 'friend', 'close_friend', 'ally'
     */
    public function getRelationshipLevel(string $npcName): string
    {
        $progress = $this->getRelationshipProgress($npcName);
        
        if ($progress >= 4) return 'ally';
        if ($progress >= 3) return 'close_friend';
        if ($progress >= 2) return 'friend';
        if ($progress >= 1) return 'acquaintance';
        return 'stranger';
    }

    /**
     * Get events that are unlocked based on NPC relationships.
     * @param object $event The event to check
     * @return bool True if event requirements are met
     */
    public function meetsNPCRelationshipRequirements(object $event): bool
    {
        $requiredNPC = $event->related_npc ?? null;
        if (!$requiredNPC) return true; // No NPC requirement
        
        $minLevel = $event->min_relationship_level ?? 1;
        $currentProgress = $this->getRelationshipProgress($requiredNPC);
        
        return $currentProgress >= $minLevel;
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

    /**
     * Get the achievements unlocked by this character (database-backed).
     */
    public function achievements(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'character_achievements')
            ->withPivot('unlocked_at', 'metadata')
            ->withTimestamps();
    }

    /**
     * Check if character has unlocked a specific achievement.
     */
    public function hasAchievement(string $achievementId): bool
    {
        return $this->achievements()->where('achievement_id', $achievementId)->exists();
    }

    /**
     * Unlock an achievement for this character.
     */
    public function unlockAchievement(string $achievementId, array $metadata = []): bool
    {
        $achievement = Achievement::where('achievement_id', $achievementId)->first();
        
        if (!$achievement) {
            return false;
        }

        // Check if already unlocked (unless unlock_once is false)
        if ($achievement->unlock_once && $this->hasAchievement($achievementId)) {
            return false;
        }

        $this->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
            'metadata' => json_encode($metadata),
        ]);

        return true;
    }

    /**
     * Get total achievement points.
     */
    public function getAchievementPoints(): int
    {
        return $this->achievements()->sum('points');
    }

    // =============================================
    // EVENT CHAIN TRACKING METHODS
    // =============================================

    /**
     * Record completion of an event chain.
     * @param string $chainId The unique identifier for the chain
     * @param array $metadata Optional metadata about the completion
     */
    public function recordChainCompletion(string $chainId, array $metadata = []): void
    {
        $completedChains = $this->completed_event_chains ?? [];
        
        // Check if already completed
        $alreadyCompleted = collect($completedChains)->contains('chain_id', $chainId);
        
        if (!$alreadyCompleted) {
            $completedChains[] = [
                'chain_id' => $chainId,
                'day_completed' => $this->current_day,
                'age_group' => $this->age_group,
                'timestamp' => now()->toISOString(),
                'metadata' => $metadata,
            ];
            $this->completed_event_chains = $completedChains;
            $this->save();
        }
    }

    /**
     * Check if a specific event chain has been completed.
     * @param string $chainId The unique identifier for the chain
     * @return bool True if the chain has been completed
     */
    public function hasCompletedChain(string $chainId): bool
    {
        $completedChains = $this->completed_event_chains ?? [];
        return collect($completedChains)->contains('chain_id', $chainId);
    }

    /**
     * Get all completed chain IDs.
     * @return array List of completed chain IDs
     */
    public function getCompletedChainIds(): array
    {
        $completedChains = $this->completed_event_chains ?? [];
        return collect($completedChains)->pluck('chain_id')->toArray();
    }

    /**
     * Get chain completion details.
     * @param string $chainId The unique identifier for the chain
     * @return array|null The chain completion data or null if not found
     */
    public function getChainCompletion(string $chainId): ?array
    {
        $completedChains = $this->completed_event_chains ?? [];
        $chain = collect($completedChains)->firstWhere('chain_id', $chainId);
        return $chain ?? null;
    }

    /**
     * Check if character has completed ALL chains in a category.
     * @param string $category The category to check (e.g., 'career', 'relationship')
     * @param array $chainIds List of chain IDs that belong to this category
     * @return bool True if all chains in the category are completed
     */
    public function hasCompletedAllChainsInCategory(string $category, array $chainIds): bool
    {
        $completedIds = $this->getCompletedChainIds();
        $completedInCategory = array_intersect($completedIds, $chainIds);
        return count($completedInCategory) === count($chainIds);
    }

    /**
     * Get the current position in an event chain.
     * @param string $chainId The unique identifier for the chain
     * @return int The current order position (0 if not started)
     */
    public function getChainProgress(string $chainId): int
    {
        $completedChains = $this->completed_event_chains ?? [];
        $chain = collect($completedChains)->firstWhere('chain_id', $chainId);
        return $chain ? ($chain['progress'] ?? 0) : 0;
    }

    /**
     * Update progress in an event chain.
     * @param string $chainId The unique identifier for the chain
     * @param int $progress The current progress position
     * @param array $metadata Optional metadata
     */
    public function updateChainProgress(string $chainId, int $progress, array $metadata = []): void
    {
        $completedChains = $this->completed_event_chains ?? [];
        $found = false;
        
        foreach ($completedChains as &$chain) {
            if ($chain['chain_id'] === $chainId) {
                $chain['progress'] = $progress;
                $chain['metadata'] = array_merge($chain['metadata'] ?? [], $metadata);
                $chain['last_updated'] = now()->toISOString();
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Start new chain progress
            $completedChains[] = [
                'chain_id' => $chainId,
                'progress' => $progress,
                'started_at' => $this->current_day,
                'age_group' => $this->age_group,
                'timestamp' => now()->toISOString(),
                'metadata' => $metadata,
            ];
        }
        
        $this->completed_event_chains = $completedChains;
        $this->save();
    }

    /**
     * Mark a chain as started (in progress).
     * @param string $chainId The unique identifier for the chain
     * @param int $progress Current progress position (0 = just started)
     * @param array $metadata Optional metadata about the chain
     */
    public function startChainProgress(string $chainId, int $progress = 0, array $metadata = []): void
    {
        $pendingChains = $this->pending_chains ?? [];
        
        // Check if already tracking this chain
        $alreadyTracking = collect($pendingChains)->contains('chain_id', $chainId);
        
        if (!$alreadyTracking) {
            $pendingChains[] = [
                'chain_id' => $chainId,
                'progress' => $progress,
                'started_at' => $this->current_day,
                'started_age_group' => $this->age_group,
                'last_updated' => now()->toISOString(),
                'metadata' => $metadata,
            ];
            $this->pending_chains = $pendingChains;
            $this->save();
        }
    }

    /**
     * Update progress in a pending chain.
     * @param string $chainId The unique identifier for the chain
     * @param int $progress The new progress position
     * @param array $metadata Optional metadata
     */
    public function updatePendingChainProgress(string $chainId, int $progress, array $metadata = []): void
    {
        $pendingChains = $this->pending_chains ?? [];
        $found = false;
        
        foreach ($pendingChains as &$chain) {
            if ($chain['chain_id'] === $chainId) {
                $chain['progress'] = $progress;
                $chain['last_updated'] = now()->toISOString();
                $chain['metadata'] = array_merge($chain['metadata'] ?? [], $metadata);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $this->startChainProgress($chainId, $progress, $metadata);
        } else {
            $this->pending_chains = $pendingChains;
            $this->save();
        }
    }

    /**
     * Get all pending chains.
     * @return array List of pending chains with their progress
     */
    public function getPendingChains(): array
    {
        return $this->pending_chains ?? [];
    }

    /**
     * Get a specific pending chain.
     * @param string $chainId The unique identifier for the chain
     * @return array|null The pending chain data or null if not found
     */
    public function getPendingChain(string $chainId): ?array
    {
        $pendingChains = $this->pending_chains ?? [];
        return collect($pendingChains)->firstWhere('chain_id', $chainId) ?? null;
    }

    /**
     * Remove a chain from pending list (either completed or abandoned).
     * @param string $chainId The unique identifier for the chain
     * @param bool $completed Whether the chain was completed (true) or abandoned (false)
     */
    public function removePendingChain(string $chainId, bool $completed = false): void
    {
        $pendingChains = $this->pending_chains ?? [];
        $pendingChains = array_values(array_filter($pendingChains, function ($chain) use ($chainId) {
            return $chain['chain_id'] !== $chainId;
        }));
        
        $this->pending_chains = $pendingChains;
        $this->save();
    }

    /**
     * Get chains that can continue in the new age group.
     * These are chains that were started in a previous age group and can continue.
     * @param string $newAgeGroup The age group to check compatibility for
     * @return array List of chain IDs that can continue
     */
    public function getContinuableChains(string $newAgeGroup): array
    {
        $pendingChains = $this->pending_chains ?? [];
        $continuable = [];
        
        foreach ($pendingChains as $chain) {
            $startedAge = $chain['started_age_group'] ?? null;
            // Chains can continue to next age group (e.g., child->teen, teen->adult)
            $compatibleAges = $this->getCompatibleAgeGroups($startedAge);
            
            if (in_array($newAgeGroup, $compatibleAges)) {
                $continuable[] = $chain;
            }
        }
        
        return $continuable;
    }

    /**
     * Get compatible age groups for continuing a chain.
     * @param string $startAge The age group where chain started
     * @return array List of age groups that can continue the chain
     */
    private function getCompatibleAgeGroups(string $startAge): array
    {
        $ageFlow = [
            'child' => ['child', 'teen'],
            'teen' => ['teen', 'adult'],
            'adult' => ['adult', 'middle'],
            'middle' => ['middle', 'senior'],
            'senior' => ['senior'],
        ];
        
        return $ageFlow[$startAge] ?? [$startAge];
    }

    /**
     * Handle age group transition - record the transition and prepare for continuity.
     * @param string $newAgeGroup The new age group being transitioned to
     * @param string $transitionEventId Optional ID of the event that triggered the transition
     */
    public function transitionToAgeGroup(string $newAgeGroup, ?string $transitionEventId = null): void
    {
        $previousAgeGroup = $this->age_group;
        
        // Store previous age group for continuity
        $this->previous_age_group = $previousAgeGroup;
        
        // Update current age group
        $this->age_group = $newAgeGroup;
        
        // Update character state
        $state = is_array($this->character_state) ? $this->character_state : [];
        $state['life_stage'] = $newAgeGroup;
        $state['previous_life_stage'] = $previousAgeGroup;
        $state['age_transition_day'] = $this->current_day;
        $this->character_state = $state;
        
        // Record the transition in choice history for continuity tracking
        $this->recordChoice('age_transition', $transitionEventId ?? 'age_up', $newAgeGroup, [
            'previous_age_group' => $previousAgeGroup,
            'transition_day' => $this->current_day,
        ]);
        
        $this->save();
    }

    /**
     * Check if character recently transitioned age groups.
     * @param int $days Number of days to check
     * @return bool True if transition happened within the specified days
     */
    public function hasRecentAgeTransition(int $days = 7): bool
    {
        $state = is_array($this->character_state) ? $this->character_state : [];
        $transitionDay = $state['age_transition_day'] ?? null;
        
        if ($transitionDay === null) {
            return false;
        }
        
        return ($this->current_day - $transitionDay) <= $days;
    }

    /**
     * Get the previous age group.
     * @return string|null The previous age group or null if not set
     */
    public function getPreviousAgeGroup(): ?string
    {
        return $this->previous_age_group ?? null;
    }

    /**
     * Get age continuity events - events that should appear after age transition.
     * These are events with next_age_group matching current age group
     * that continue from previous age group choices.
     */
    public function getAgeContinuityEvents()
    {
        $previousAgeGroup = $this->getPreviousAgeGroup();
        if (!$previousAgeGroup) {
            return collect([]);
        }

        return DailyEvent::where('next_age_group', $this->age_group)
            ->where(function ($query) use ($previousAgeGroup) {
                $query->where('age_group', $previousAgeGroup)
                    ->orWhere('parent_category', '!=', null);
            })
            ->orderBy('chain_order', 'asc')
            ->get();
    }
}
