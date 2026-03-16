<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionLog extends Model
{
    protected $table = 'decision_logs';

    protected $fillable = [
        'character_id',
        'user_id',
        'anon_user_id',
        'anon_character_id',
        'is_guest',
        'user_name',
        'day',
        'age_group',
        'profession',
        'event_type',
        'event_id',
        'event_title',
        'choice_index',
        'choice_text',
        'outcome',
        'effects',
        // Before state
        'before_health',
        'before_happiness',
        'before_finance',
        'before_relationship_status',
        'before_career_level',
        'before_profession',
        // After state
        'after_health',
        'after_happiness',
        'after_finance',
        'after_relationship_status',
        'after_career_level',
        'after_profession',
        // Changes
        'health_change',
        'happiness_change',
        'finance_change',
        // Additional
        'mbti',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'before_health' => 'integer',
        'before_happiness' => 'integer',
        'before_finance' => 'integer',
        'after_health' => 'integer',
        'after_happiness' => 'integer',
        'after_finance' => 'integer',
        'health_change' => 'integer',
        'happiness_change' => 'integer',
        'finance_change' => 'integer',
    ];
}
