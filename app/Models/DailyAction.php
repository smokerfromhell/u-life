<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'type',
        'deck_label',
        'repeatable',
        'weight',
        'auto_resolve',
        'days_to_advance',
        'choices',
        'conditions',
        'display_order',
    ];

    protected $casts = [
        'repeatable' => 'boolean',
        'auto_resolve' => 'boolean',
        'weight' => 'integer',
        'days_to_advance' => 'integer',
        'display_order' => 'integer',
        'choices' => 'array',
        'conditions' => 'array',
    ];

    /**
     * Check if this action should be available based on character conditions.
     */
    public function isAvailable(Character $character): bool
    {
        $conditions = $this->conditions;

        if (empty($conditions)) {
            return true;
        }

        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $state = is_array($character->character_state) ? $character->character_state : [];
        $ageGroup = $character->age_group ?? 'adult';

        $health = (int) ($effectiveStats['Health'] ?? ($character->health ?? 100));
        $wealth = (int) ($effectiveStats['Wealth'] ?? 50);
        $burnout = (int) ($effectiveStats['Burnout'] ?? 0);
        $debt = (int) ($effectiveStats['Debt'] ?? 0);

        $isBankrupt = ($state['is_bankrupt'] ?? false) === true;
        $hasCancer = ($state['has_cancer'] ?? false) === true;
        $hasDisability = ($state['has_disability'] ?? false) === true;
        $hasSevereAddiction = ($state['has_severe_addiction'] ?? false) === true;
        $healthStatus = $this->getHealthStatus($health);

        // Check age_group condition
        if (isset($conditions['age_group'])) {
            $ageGroups = is_array($conditions['age_group']) ? $conditions['age_group'] : [$conditions['age_group']];
            if (!in_array($ageGroup, $ageGroups, true)) {
                return false;
            }
        }

        // Check min_health condition
        if (isset($conditions['min_health']) && $health < $conditions['min_health']) {
            return false;
        }

        // Check health_status condition
        if (isset($conditions['health_status']) && $healthStatus !== $conditions['health_status'] && !isset($conditions['has_cancer'])) {
            return false;
        }

        // Check has_cancer condition
        if (isset($conditions['has_cancer']) && $conditions['has_cancer'] && !$hasCancer) {
            return false;
        }

        // Check has_disability condition
        if (isset($conditions['has_disability']) && $conditions['has_disability'] && !$hasDisability) {
            return false;
        }

        // Check has_severe_addiction condition
        if (isset($conditions['has_severe_addiction']) && $conditions['has_severe_addiction'] && !$hasSevereAddiction) {
            return false;
        }

        // Check min_wealth condition
        if (isset($conditions['min_wealth']) && $wealth < $conditions['min_wealth']) {
            return false;
        }

        // Check has_debt condition
        if (isset($conditions['has_debt']) && $conditions['has_debt'] && $debt <= 0) {
            return false;
        }

        // Check is_bankrupt condition
        if (isset($conditions['is_bankrupt']) && $conditions['is_bankrupt'] && !$isBankrupt) {
            return false;
        }

        // Check min_burnout condition
        if (isset($conditions['min_burnout']) && $burnout < $conditions['min_burnout']) {
            return false;
        }

        return true;
    }

    /**
     * Get health status based on health value.
     */
    private function getHealthStatus(int $health): string
    {
        if ($health >= 80) {
            return 'healthy';
        } elseif ($health >= 50) {
            return 'fair';
        } elseif ($health >= 20) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Convert to array format for API response.
     */
    public function toActionArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'deck_label' => $this->deck_label,
            'repeatable' => $this->repeatable,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'outcome' => $this->description,
            'statEffects' => null,
            'choices' => $this->choices,
            'weight' => $this->weight,
            'auto_resolve' => $this->auto_resolve,
            'days_to_advance' => $this->days_to_advance,
        ];
    }
}
