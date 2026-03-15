<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LifeStatsSnapshot extends Model
{
    protected $table = 'life_stats_snapshots';

    protected $fillable = [
        'anon_user_id',
        'anon_character_id',
        'is_guest',
        'user_name',
        'day',
        'event_type',
        'event_id',
        'choice_index',
        'event_title',
        'choice_text',
        'health',
        'happiness',
        'finance',
        'relationship_status',
        'career_level',
        'mbti',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'health' => 'integer',
        'happiness' => 'integer',
        'finance' => 'integer',
    ];

    /**
     * Get the character that this snapshot belongs to.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'anon_character_id', 'anon_character_id');
    }

    /**
     * Get the corresponding character decision log.
     */
    public function characterDecisionLog(): HasOne
    {
        return $this->hasOne(CharacterDecisionLog::class, 'event_id', 'event_id')
            ->whereColumn('character_decision_logs.day', 'life_stats_snapshots.day')
            ->whereColumn('character_decision_logs.choice_index', 'life_stats_snapshots.choice_index');
    }

    /**
     * Get the latest snapshot for each character.
     */
    public static function getLatestForEachCharacter()
    {
        return static::select('life_stats_snapshots.*')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('life_stats_snapshots')
                    ->groupBy('anon_character_id');
            });
    }

    /**
     * Get aggregate stats from all latest snapshots.
     */
    public static function getAggregateStatsFromSnapshots()
    {
        $latestSnapshots = static::getLatestForEachCharacter();
        
        return [
            'avg_health' => round($latestSnapshots->avg('health') ?? 0),
            'avg_happiness' => round($latestSnapshots->avg('happiness') ?? 0),
            'avg_finance' => round($latestSnapshots->avg('finance') ?? 0),
            'total_snapshots' => $latestSnapshots->count(),
        ];
    }
}
