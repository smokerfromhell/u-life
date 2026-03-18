<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleDecisionLog extends Model
{
    use HasFactory;

    protected $table = 'sample_decision_logs';

    protected $fillable = [
        'anon_character_id',
        'character_name',
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
        'mbti',
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
        // Tags and display
        'tags',
        'is_featured',
        'view_count',
    ];

    protected $casts = [
        'effects' => 'array',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'before_health' => 'integer',
        'before_happiness' => 'integer',
        'before_finance' => 'integer',
        'after_health' => 'integer',
        'after_happiness' => 'integer',
        'after_finance' => 'integer',
        'health_change' => 'integer',
        'happiness_change' => 'integer',
        'finance_change' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Scope for featured sample decisions
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for filtering by profession
     */
    public function scopeByProfession($query, $profession)
    {
        return $query->where('profession', $profession);
    }

    /**
     * Scope for filtering by age group
     */
    public function scopeByAgeGroup($query, $ageGroup)
    {
        return $query->where('age_group', $ageGroup);
    }

    /**
     * Scope for filtering by MBTI type
     */
    public function scopeByMbti($query, $mbti)
    {
        return $query->where('mbti', $mbti);
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}
