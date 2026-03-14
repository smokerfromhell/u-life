<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
