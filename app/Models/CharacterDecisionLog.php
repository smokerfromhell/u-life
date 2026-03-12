<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterDecisionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character_id',
        'day',
        'event_type',
        'event_id',
        'event_title',
        'event_description',
        'choice_index',
        'choice_text',
        'stat_effects_text',
        'effects',
        'stats_before',
        'stats_after',
        'hidden_stats_before',
        'hidden_stats_after',
        'effective_stats_before',
        'effective_stats_after',
        'effective_stats_delta',
        'narrative_before',
        'narrative_after',
        'active_event_paths_before',
        'active_event_paths_after',
    ];

    protected $casts = [
        'effects' => 'array',
        'stats_before' => 'array',
        'stats_after' => 'array',
        'hidden_stats_before' => 'array',
        'hidden_stats_after' => 'array',
        'effective_stats_before' => 'array',
        'effective_stats_after' => 'array',
        'effective_stats_delta' => 'array',
        'active_event_paths_before' => 'array',
        'active_event_paths_after' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}

