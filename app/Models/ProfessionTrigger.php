<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession',
        'unlock_condition',
        'notes',
        'stat_effects',
        'description',
        'choices',
    ];

    protected $casts = [
        'choices' => 'array',
    ];

    /**
     * Get all profession path events for this profession
     */
    public function professionPathEvents(): HasMany
    {
        return $this->hasMany(ProfessionPathEvent::class, 'profession', 'profession');
    }

    /**
     * Check if a character meets the unlock condition
     */
    public function checkUnlockCondition(array $characterStats): bool
    {
        // Parse unlock condition and check if stats meet the requirement
        // Example: "Intelligence >= 60 AND Creativity >= 40"
        // This would need custom logic based on your condition format
        return true; // Placeholder
    }

    /**
     * Get the stat effects as an array
     */
    public function getStatEffectsArray(): array
    {
        if (empty($this->stat_effects)) {
            return [];
        }

        if (is_array($this->stat_effects)) {
            return $this->stat_effects;
        }
        
        $effects = [];
        $parts = explode(',', $this->stat_effects);
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]?\d+)\s+(\w+)/', $part, $matches)) {
                $effects[$matches[2]] = (int)$matches[1];
            }
        }
        return $effects;
    }
}
