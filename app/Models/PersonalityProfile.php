<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityProfile extends Model
{
    use HasFactory;

    protected $table = 'personality_profiles';

    protected $fillable = [
        'character_id',
        // Core MBTI dimensions
        'energy_orientation',
        'information_gathering',
        'decision_forming',
        'lifestyle_approach',
        // Big Five traits
        'openness',
        'conscientiousness',
        'extraversion',
        'agreeableness',
        'neuroticism',
        // Behavioral pattern scores
        'social_boldness_score',
        'emotional_stability_score',
        'agreeableness_score',
        'conscientiousness_score',
        'openness_score',
        // Decision history
        'decision_patterns',
        'recent_social_decisions',
        'recent_career_decisions',
        'recent_relationship_decisions',
        'recent_moral_decisions',
        // Current MBTI
        'current_mbti',
        'mbti_confidence',
    ];

    protected $casts = [
        'energy_orientation' => 'integer',
        'information_gathering' => 'integer',
        'decision_forming' => 'integer',
        'lifestyle_approach' => 'integer',
        'openness' => 'integer',
        'conscientiousness' => 'integer',
        'extraversion' => 'integer',
        'agreeableness' => 'integer',
        'neuroticism' => 'integer',
        'social_boldness_score' => 'integer',
        'emotional_stability_score' => 'integer',
        'agreeableness_score' => 'integer',
        'conscientiousness_score' => 'integer',
        'openness_score' => 'integer',
        'decision_patterns' => 'array',
        'recent_social_decisions' => 'array',
        'recent_career_decisions' => 'array',
        'recent_relationship_decisions' => 'array',
        'recent_moral_decisions' => 'array',
        'mbti_confidence' => 'integer',
    ];

    /**
     * Get the character this profile belongs to
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Calculate and update MBTI type from current dimensions
     */
    public function calculateMBTI(): string
    {
        $type = '';
        $type .= $this->energy_orientation >= 50 ? 'E' : 'I';
        $type .= $this->information_gathering >= 50 ? 'N' : 'S';
        $type .= $this->decision_forming >= 50 ? 'T' : 'F';
        $type .= $this->lifestyle_approach >= 50 ? 'J' : 'P';
        
        $this->current_mbti = $type;
        $this->save();
        
        return $type;
    }

    /**
     * Update a dimension with weighted new value
     * Uses exponential moving average: new = alpha * new_value + (1 - alpha) * old
     */
    public function updateDimension(string $dimension, int $newValue, float $alpha = 0.1): void
    {
        if (!in_array($dimension, ['energy_orientation', 'information_gathering', 'decision_forming', 'lifestyle_approach'])) {
            return;
        }
        
        $currentValue = $this->$dimension ?? 50;
        $weightedValue = (int) round($alpha * $newValue + (1 - $alpha) * $currentValue);
        $weightedValue = max(0, min(100, $weightedValue));
        
        $this->$dimension = $weightedValue;
        $this->save();
    }

    /**
     * Update Big Five trait with weighted new value
     */
    public function updateTrait(string $trait, int $newValue, float $alpha = 0.1): void
    {
        if (!in_array($trait, ['openness', 'conscientiousness', 'extraversion', 'agreeableness', 'neuroticism'])) {
            return;
        }
        
        $currentValue = $this->$trait ?? 50;
        $weightedValue = (int) round($alpha * $newValue + (1 - $alpha) * $currentValue);
        $weightedValue = max(0, min(100, $weightedValue));
        
        $this->$trait = $weightedValue;
        $this->save();
    }

    /**
     * Add a decision to recent history (keeps last 10)
     */
    public function addDecision(string $category, int $choiceScore): void
    {
        $field = match($category) {
            'social', 'cultural' => 'recent_social_decisions',
            'career', 'profession' => 'recent_career_decisions',
            'relationship', 'family', 'romantic' => 'recent_relationship_decisions',
            'moral', 'ethics' => 'recent_moral_decisions',
            default => 'decision_patterns',
        };
        
        $decisions = $this->$field ?? [];
        $decisions[] = [
            'score' => $choiceScore,
            'day' => $this->character?->current_day ?? 0,
            'timestamp' => now()->toISOString(),
        ];
        
        // Keep only last 10
        if (count($decisions) > 10) {
            $decisions = array_slice($decisions, -10);
        }
        
        $this->$field = $decisions;
        $this->save();
    }

    /**
     * Update behavioral score
     */
    public function updateBehavioralScore(string $scoreType, int $delta): void
    {
        $field = $scoreType . '_score';
        if (in_array($field, ['social_boldness_score', 'emotional_stability_score', 'agreeableness_score', 'conscientiousness_score', 'openness_score'])) {
            $this->$field = ($this->$field ?? 0) + $delta;
            $this->save();
        }
    }

    /**
     * Get MBTI with confidence based on data volume
     */
    public function getMBTIWithConfidence(): array
    {
        $totalDecisions = 0;
        
        foreach (['recent_social_decisions', 'recent_career_decisions', 'recent_relationship_decisions', 'recent_moral_decisions'] as $field) {
            $totalDecisions += count($this->$field ?? []);
        }
        
        // Confidence increases with more decisions (max 100 at 40+ decisions)
        $confidence = min(100, $totalDecisions * 2.5);
        
        return [
            'mbti' => $this->current_mbti ?? $this->calculateMBTI(),
            'confidence' => $confidence,
            'dimensions' => [
                'E/I' => $this->energy_orientation,
                'N/S' => $this->information_gathering,
                'T/F' => $this->decision_forming,
                'J/P' => $this->lifestyle_approach,
            ],
            'big_five' => [
                'O' => $this->openness,
                'C' => $this->conscientiousness,
                'E' => $this->extraversion,
                'A' => $this->agreeableness,
                'N' => $this->neuroticism,
            ],
        ];
    }

    /**
     * Create or get profile for a character
     */
    public static function getOrCreateForCharacter(Character $character): self
    {
        $profile = self::where('character_id', $character->id)->first();
        
        if (!$profile) {
            // Initialize from character stats
            $stats = $character->effective_stats ?? [];
            $hiddenStats = $character->hidden_stats ?? [];
            
            $profile = self::create([
                'character_id' => $character->id,
                // Initialize MBTI dimensions from stats
                'energy_orientation' => isset($stats['Social']) ? (int) (30 + $stats['Social'] * 0.4) : 50,
                'information_gathering' => isset($stats['Creativity']) ? (int) (30 + $stats['Creativity'] * 0.4) : 50,
                'decision_forming' => isset($stats['Empathy']) ? (int) (30 + $stats['Empathy'] * 0.4) : 50,
                'lifestyle_approach' => isset($stats['Discipline']) ? (int) (30 + $stats['Discipline'] * 0.4) : 50,
                // Initialize Big Five
                'openness' => isset($stats['Creativity']) ? (int) ($stats['Creativity']) : 50,
                'conscientiousness' => isset($stats['Discipline']) ? (int) ($stats['Discipline']) : 50,
                'extraversion' => isset($stats['Social']) ? (int) ($stats['Social']) : 50,
                'agreeableness' => isset($stats['Empathy']) ? (int) ($stats['Empathy']) : 50,
                'neuroticism' => isset($hiddenStats['Happiness']) ? (int) (100 - $hiddenStats['Happiness'] * 0.5) : 50,
            ]);
            
            $profile->calculateMBTI();
        }
        
        return $profile;
    }
}
