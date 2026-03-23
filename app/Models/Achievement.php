<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'name',
        'description',
        'category',
        'rarity',
        'icon',
        'conditions',
        'unlock_once',
        'points',
    ];

    protected $casts = [
        'conditions' => 'array',
        'unlock_once' => 'boolean',
        'points' => 'integer',
    ];

    /**
     * Get the characters that have unlocked this achievement.
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_achievements')
            ->withPivot('unlocked_at', 'metadata')
            ->withTimestamps();
    }

    /**
     * Check if this achievement has been unlocked by a character.
     */
    public function isUnlockedBy(Character $character): bool
    {
        return $this->characters()->where('characters.id', $character->id)->exists();
    }

    /**
     * Get achievement unlocked at date for a character.
     */
    public function unlockedAt(Character $character): ?\Carbon\Carbon
    {
        $pivot = $this->characters()->where('characters.id', $character->id)->first();
        return $pivot ? $pivot->pivot->unlocked_at : null;
    }

    /**
     * Achievement categories
     */
    const CATEGORY_CAREER = 'career';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_RELATIONSHIP = 'relationship';
    const CATEGORY_SKILLS = 'skills';
    const CATEGORY_LIFESPAN = 'lifespan';
    const CATEGORY_SPECIAL = 'special';
    const CATEGORY_MILESTONE = 'milestone';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_LUCK = 'luck';

    /**
     * Rarity levels
     */
    const RARITY_COMMON = 'common';
    const RARITY_UNCOMMON = 'uncommon';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'epic';
    const RARITY_LEGENDARY = 'legendary';
}
