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
}
