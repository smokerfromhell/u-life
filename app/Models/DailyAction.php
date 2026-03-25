<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'mini_game_type',
        'mini_game_difficulty',
        'mini_game_category',
    ];

    protected $casts = [
        'repeatable' => 'boolean',
        'auto_resolve' => 'boolean',
        'weight' => 'integer',
        'days_to_advance' => 'integer',
        'display_order' => 'integer',
        'choices' => 'array',
        'conditions' => 'array',
        'mini_game_difficulty' => 'integer',
    ];

    /**
     * Check if this action should be available based on character conditions.
     */
    public function isAvailable(Character $character): bool
    {
        \Illuminate\Support\Facades\Log::debug('DailyAction::isAvailable checking', [
            'action_id' => $this->id,
            'action_title' => $this->title,
            'character_id' => $character->id,
        ]);
        
        $conditions = $this->conditions;

        if (empty($conditions)) {
            return true;
        }

        $effectiveStats = is_array($character->effective_stats) ? $character->effective_stats : [];
        $state = is_array($character->character_state) ? $character->character_state : [];
        $ageGroup = $character->age_group ?? 'adult';

        $health = (int) ($effectiveStats['Health'] ?? ($character->health ?? 78));
        $wealth = (int) ($effectiveStats['Wealth'] ?? 20);
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

        // Check has_skill condition - character must have ALL specified skills
        if (isset($conditions['has_skill'])) {
            $requiredSkills = is_array($conditions['has_skill']) ? $conditions['has_skill'] : [$conditions['has_skill']];
            $characterSkills = $character->skills()->pluck('skills.name')->toArray();
            
            foreach ($requiredSkills as $requiredSkill) {
                if (!in_array($requiredSkill, $characterSkills, true)) {
                    return false;
                }
            }
        }

        // Check lacks_skill condition - character must NOT have ANY of the specified skills
        if (isset($conditions['lacks_skill'])) {
            $forbiddenSkills = is_array($conditions['lacks_skill']) ? $conditions['lacks_skill'] : [$conditions['lacks_skill']];
            $characterSkills = $character->skills()->pluck('skills.name')->toArray();
            
            foreach ($forbiddenSkills as $forbiddenSkill) {
                if (in_array($forbiddenSkill, $characterSkills, true)) {
                    return false;
                }
            }
        }

        // Check has_talent condition - character must have ALL specified talents
        if (isset($conditions['has_talent'])) {
            $requiredTalents = is_array($conditions['has_talent']) ? $conditions['has_talent'] : [$conditions['has_talent']];
            $characterTalents = $character->talents()->pluck('talents.name')->toArray();
            
            foreach ($requiredTalents as $requiredTalent) {
                if (!in_array($requiredTalent, $characterTalents, true)) {
                    return false;
                }
            }
        }

        // Check lacks_talent condition - character must NOT have ANY of the specified talents
        if (isset($conditions['lacks_talent'])) {
            $forbiddenTalents = is_array($conditions['lacks_talent']) ? $conditions['lacks_talent'] : [$conditions['lacks_talent']];
            $characterTalents = $character->talents()->pluck('talents.name')->toArray();
            
            foreach ($forbiddenTalents as $forbiddenTalent) {
                if (in_array($forbiddenTalent, $characterTalents, true)) {
                    return false;
                }
            }
        }

        // Check min_reputation condition - character must have minimum reputation in faction
        if (isset($conditions['min_reputation'])) {
            $minReputations = is_array($conditions['min_reputation']) 
                ? $conditions['min_reputation'] 
                : [$conditions['min_reputation']];
            
            foreach ($minReputations as $faction => $minimum) {
                if (!$character->hasMinReputation($faction, (int) $minimum)) {
                    return false;
                }
            }
        }

        // Check max_reputation condition - character must have reputation at or below threshold
        if (isset($conditions['max_reputation'])) {
            $maxReputations = is_array($conditions['max_reputation']) 
                ? $conditions['max_reputation'] 
                : [$conditions['max_reputation']];
            
            foreach ($maxReputations as $faction => $maximum) {
                if (!$character->hasMaxReputation($faction, (int) $maximum)) {
                    return false;
                }
            }
        }

        // Check has_trauma condition - character must have ALL specified trauma types
        if (isset($conditions['has_trauma'])) {
            $requiredTraumas = is_array($conditions['has_trauma']) 
                ? $conditions['has_trauma'] 
                : [$conditions['has_trauma']];
            
            foreach ($requiredTraumas as $traumaType) {
                if (!$character->hasTrauma($traumaType)) {
                    return false;
                }
            }
        }

        // Check lacks_trauma condition - character must NOT have ANY of the specified traumas
        if (isset($conditions['lacks_trauma'])) {
            $forbiddenTraumas = is_array($conditions['lacks_trauma']) 
                ? $conditions['lacks_trauma'] 
                : [$conditions['lacks_trauma']];
            
            foreach ($forbiddenTraumas as $traumaType) {
                if ($character->hasTrauma($traumaType)) {
                    return false;
                }
            }
        }

        // Check relationship_status condition - character must have specific status
        if (isset($conditions['relationship_status'])) {
            $requiredStatuses = is_array($conditions['relationship_status']) 
                ? $conditions['relationship_status'] 
                : [$conditions['relationship_status']];
            
            if (!in_array($character->getRelationshipStatus(), $requiredStatuses, true)) {
                return false;
            }
        }

        // Check not_relationship_status condition - character must NOT have specific status
        if (isset($conditions['not_relationship_status'])) {
            $forbiddenStatuses = is_array($conditions['not_relationship_status']) 
                ? $conditions['not_relationship_status'] 
                : [$conditions['not_relationship_status']];
            
            if (in_array($character->getRelationshipStatus(), $forbiddenStatuses, true)) {
                return false;
            }
        }

        // Check in_relationship condition - must be in a relationship (dating, engaged, married)
        if (isset($conditions['in_relationship'])) {
            if ($conditions['in_relationship'] && !$character->isInRelationship()) {
                return false;
            }
            if (!$conditions['in_relationship'] && $character->isInRelationship()) {
                return false;
            }
        }

        // Check min_relationship_state condition - relationship group must meet minimum state
        if (isset($conditions['min_relationship_state'])) {
            $minStates = is_array($conditions['min_relationship_state']) 
                ? $conditions['min_relationship_state'] 
                : [$conditions['min_relationship_state']];
            
            foreach ($minStates as $group => $minimumState) {
                if (!$character->hasMinRelationshipState($group, $minimumState)) {
                    return false;
                }
            }
        }

        // Check max_relationship_state condition - relationship group must be at or below maximum state
        if (isset($conditions['max_relationship_state'])) {
            $maxStates = is_array($conditions['max_relationship_state']) 
                ? $conditions['max_relationship_state'] 
                : [$conditions['max_relationship_state']];
            
            foreach ($maxStates as $group => $maximumState) {
                if (!$character->hasMaxRelationshipState($group, $maximumState)) {
                    return false;
                }
            }
        }

        // Check has_social_connection condition - character must have connection of type
        if (isset($conditions['has_social_connection'])) {
            $requiredConnections = is_array($conditions['has_social_connection']) 
                ? $conditions['has_social_connection'] 
                : [$conditions['has_social_connection']];
            
            foreach ($requiredConnections as $connectionType) {
                if (!$character->hasSocialConnection($connectionType)) {
                    return false;
                }
            }
        }

        // Check lacks_social_connection condition - character must NOT have connection of type
        if (isset($conditions['lacks_social_connection'])) {
            $forbiddenConnections = is_array($conditions['lacks_social_connection']) 
                ? $conditions['lacks_social_connection'] 
                : [$conditions['lacks_social_connection']];
            
            foreach ($forbiddenConnections as $connectionType) {
                if ($character->hasSocialConnection($connectionType)) {
                    return false;
                }
            }
        }

        // Check location condition - character must be at specific location
        if (isset($conditions['location'])) {
            $requiredLocations = is_array($conditions['location']) 
                ? $conditions['location'] 
                : [$conditions['location']];
            
            if (!in_array($character->getLocation(), $requiredLocations, true)) {
                return false;
            }
        }

        // Check not_location condition - character must NOT be at specific location
        if (isset($conditions['not_location'])) {
            $forbiddenLocations = is_array($conditions['not_location']) 
                ? $conditions['not_location'] 
                : [$conditions['not_location']];
            
            if (in_array($character->getLocation(), $forbiddenLocations, true)) {
                return false;
            }
        }

        // Check season condition - must be specific season
        if (isset($conditions['season'])) {
            $requiredSeasons = is_array($conditions['season']) 
                ? $conditions['season'] 
                : [$conditions['season']];
            
            if (!in_array($character->getSeason(), $requiredSeasons, true)) {
                return false;
            }
        }

        // Check weather condition - must be specific weather
        if (isset($conditions['weather'])) {
            $requiredWeather = is_array($conditions['weather']) 
                ? $conditions['weather'] 
                : [$conditions['weather']];
            
            if (!in_array($character->getWeather(), $requiredWeather, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get health status based on health value.
     * Matches EventService::getHealthStatus() thresholds.
     */
    private function getHealthStatus(int $health): string
    {
        if ($health >= 70) {
            return 'healthy';
        } elseif ($health >= 50) {
            return 'fever';
        } elseif ($health >= 31) {
            return 'unhealthy';
        } elseif ($health >= 11) {
            return 'sick';
        } elseif ($health >= 1) {
            return 'critical';
        } else {
            return 'dead';
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
            // Mini-game fields for skill learning events
            'mini_game' => $this->mini_game_type,
            'mini_game_difficulty' => $this->mini_game_difficulty,
            'mini_game_category' => $this->mini_game_category,
        ];
    }

    /**
     * Process a choice and apply all effects to a character.
     * Returns array of effects applied.
     */
    public function processChoice(Character $character, array $choice): array
    {
        $effects = [];
        
        // Apply stat effects if present
        if (isset($choice['stat_effects'])) {
            // TODO: Parse and apply stat effects
            $effects['stat_effects'] = $choice['stat_effects'];
        }
        
        // Learn skill if present
        if (isset($choice['learn_skill'])) {
            $skillName = $choice['learn_skill'];
            $learned = $character->learnSkill($skillName);
            $effects['learned_skill'] = $learned ? $skillName : null;
        }
        
        // Discover talent if present
        if (isset($choice['discover_talent'])) {
            $talentName = $choice['discover_talent'];
            $discovered = $character->discoverTalent($talentName);
            $effects['discovered_talent'] = $discovered ? $talentName : null;
        }
        
        return $effects;
    }
}
