<?php

namespace App\Services;

use App\Models\Character;
use App\Models\DecisionLog;
use App\Models\LifeStatsSnapshot;
use Illuminate\Support\Facades\DB;

class LifeSummaryService
{
    /**
     * Generate comprehensive life summary
     */
    public function generateSummary(Character $character): array
    {
        try {
            $decisions = DecisionLog::where('character_id', $character->id)
                ->orderBy('day')
                ->get();
        } catch (\Exception $e) {
            $decisions = collect([]);
        }

        try {
            $anonId = $character->anon_character_id ?? 'char-' . $character->id;
            $snapshots = LifeStatsSnapshot::where('anon_character_id', $anonId)
                ->orderBy('day')
                ->get();
        } catch (\Exception $e) {
            $snapshots = collect([]);
        }

        try {
            return [
                'character' => $this->getCharacterSummary($character),
                'lifespan' => $this->getLifespanSummary($character, $decisions),
                'statsAnalysis' => $this->analyzeStats($character, $snapshots),
                'decisionsAnalysis' => $this->analyzeDecisions($decisions),
                'milestones' => $this->extractMilestones($decisions),
                'personality' => $this->analyzePersonality($character, $decisions),
                'achievements' => $this->getUnlockedAchievements($character),
                'lifeRating' => $this->calculateLifeRating($character, $decisions, $snapshots),
                'keyMoments' => $this->findKeyMoments($decisions),
                'advice' => $this->generateLifeAdvice($character, $decisions, $snapshots),
            ];
        } catch (\Exception $e) {
            // Return minimal response if any error occurs
            return [
                'character' => $this->getCharacterSummary($character),
                'lifespan' => ['total_days' => $character->current_day ?? 1, 'total_years' => $character->current_day ?? 1],
                'statsAnalysis' => [],
                'decisionsAnalysis' => ['total' => $decisions->count()],
                'milestones' => [],
                'personality' => [],
                'achievements' => [],
                'lifeRating' => ['score' => 50, 'label' => 'Average'],
                'keyMoments' => [],
                'advice' => ['summary' => 'Your life journey has come to an end.'],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get basic character information
     */
    private function getCharacterSummary(Character $character): array
    {
        // In this game, 1 day = 1 year
        $age = $character->current_day ?? 1;
        
        return [
            'name' => $character->name,
            'gender' => $character->gender,
            'profession' => $character->profession ?? 'Unemployed',
            'birth_date' => $character->created_at?->format('Y-m-d'),
            'death_date' => now()->format('Y-m-d'),
            'lifespan_days' => $age,
            'lifespan_years' => $age,
            'age_at_death' => $age,
        ];
    }

    /**
     * Get lifespan summary
     */
    private function getLifespanSummary(Character $character, $decisions): array
    {
        // In this game, 1 day = 1 year
        $days = $character->current_day ?? 1;
        $years = $days;

        // Age group breakdown
        $ageBreakdown = [];
        $ageGroups = [
            ['name' => 'Childhood', 'start' => 0, 'end' => 9],
            ['name' => 'Teenage Years', 'start' => 10, 'end' => 19],
            ['name' => 'Young Adult', 'start' => 20, 'end' => 29],
            ['name' => 'Adulthood', 'start' => 30, 'end' => 39],
            ['name' => 'Middle Age', 'start' => 40, 'end' => 54],
            ['name' => 'Senior Years', 'start' => 55, 'end' => 69],
            ['name' => 'Elderly', 'start' => 70, 'end' => PHP_INT_MAX],
        ];

        foreach ($ageGroups as $ageGroup) {
            $daysInPeriod = min($ageGroup['end'], $days) - $ageGroup['start'];
            if ($daysInPeriod > 0 && $ageGroup['start'] < $days) {
                $ageBreakdown[] = [
                    'period' => $ageGroup['name'],
                    'years' => $daysInPeriod,
                    'percentage' => round(($daysInPeriod / $days) * 100, 1),
                ];
            }
        }

        return [
            'total_days' => $days,
            'total_years' => $years,
            'age_breakdown' => $ageBreakdown,
            'total_decisions' => $decisions->count(),
            'decisions_per_year' => $years > 0 ? round($decisions->count() / $years, 1) : $decisions->count(),
        ];
    }

    /**
     * Analyze stats over the character's life
     */
    private function analyzeStats(Character $character, $snapshots): array
    {
        if ($snapshots->isEmpty()) {
            return [
                'health' => $this->singleStatAnalysis('Health', $character->health ?? 100),
                'happiness' => $this->singleStatAnalysis('Happiness', $character->happiness ?? 100),
                'finance' => $this->singleStatAnalysis('Finance', $character->finance ?? 0),
                'relationship' => $character->relationship_status ?? 'single',
                'career_level' => $character->career_level ?? 1,
            ];
        }

        $healthValues = $snapshots->pluck('health')->filter()->toArray();
        $happinessValues = $snapshots->pluck('happiness')->filter()->toArray();
        $financeValues = $snapshots->pluck('finance')->filter()->toArray();

        return [
            'health' => $this->statTrendAnalysis('Health', $healthValues, $character->health ?? 100),
            'happiness' => $this->statTrendAnalysis('Happiness', $happinessValues, $character->happiness ?? 100),
            'finance' => $this->statTrendAnalysis('Finance', $financeValues, $character->finance ?? 0),
            'relationship' => $character->relationship_status ?? 'single',
            'career_level' => $character->career_level ?? 1,
            'luck' => $character->luck ?? 50,
            'karma' => $character->karma ?? 0,
        ];
    }

    /**
     * Single stat analysis (when no snapshots)
     */
    private function singleStatAnalysis(string $name, $value): array
    {
        return [
            'name' => $name,
            'final' => $value,
            'average' => $value,
            'peak' => $value,
            'lowest' => $value,
            'trend' => 'stable',
            'description' => "You finished with {$value} {$name}.",
        ];
    }

    /**
     * Stat trend analysis with history
     */
    private function statTrendAnalysis(string $name, array $values, $final): array
    {
        if (empty($values)) {
            return $this->singleStatAnalysis($name, $final);
        }

        $average = array_sum($values) / count($values);
        $peak = max($values);
        $lowest = min($values);

        // Determine trend
        $trend = 'stable';
        if (count($values) >= 5) {
            $firstHalf = array_slice($values, 0, floor(count($values) / 2));
            $secondHalf = array_slice($values, floor(count($values) / 2));
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            
            if ($secondAvg > $firstAvg * 1.1) {
                $trend = 'improving';
            } elseif ($secondAvg < $firstAvg * 0.9) {
                $trend = 'declining';
            }
        }

        // Generate description
        $description = $this->generateStatDescription($name, $final, $average, $peak, $trend);

        return [
            'name' => $name,
            'final' => $final,
            'average' => round($average, 1),
            'peak' => $peak,
            'lowest' => $lowest,
            'trend' => $trend,
            'description' => $description,
        ];
    }

    /**
     * Generate descriptive text for stats
     */
    private function generateStatDescription(string $name, $final, $average, $peak, string $trend): string
    {
        $trendText = match($trend) {
            'improving' => 'Your life showed an upward trajectory.',
            'declining' => 'Things became more challenging over time.',
            default => 'You maintained steady levels throughout.',
        };

        $finalText = match(true) {
            $final >= 80 => "You finished strong with high {$name}!",
            $final >= 50 => "You ended with moderate {$name}.",
            $final >= 20 => "Your {$name} was struggling near the end.",
            default => "Your {$name} was critically low.",
        };

        return "{$trendText} {$finalText}";
    }

    /**
     * Analyze decisions made throughout life
     */
    private function analyzeDecisions($decisions): array
    {
        if ($decisions->isEmpty()) {
            return [
                'total' => 0,
                'by_type' => [],
                'story_decisions' => 0,
                'daily_actions' => 0,
                'risk_level' => 'moderate',
                'most_common_choice_type' => 'balanced',
            ];
        }

        // Count by event type
        $byType = [];
        $storyDecisions = 0;
        $dailyActions = 0;
        
        // Event types that are story events
        $storyEventTypes = ['cultural', 'ageSpecific', 'profession', 'age_specific'];
        
        foreach ($decisions as $decision) {
            $type = $decision->event_type ?? 'unknown';
            $byType[$type] = ($byType[$type] ?? 0) + 1;
            
            // Categorize as story or daily
            if (in_array($type, $storyEventTypes)) {
                $storyDecisions++;
            } elseif ($type === 'daily') {
                $dailyActions++;
            } else {
                // Unknown types count as story (conservative approach)
                $storyDecisions++;
            }
        }

        // Analyze risk level based on choices
        $riskyChoices = 0;
        $safeChoices = 0;
        $balancedChoices = 0;

        foreach ($decisions as $decision) {
            $choiceText = strtolower($decision->choice_text ?? '');
            if (preg_match('/(risk|dangerous|challenge|adventure)/', $choiceText)) {
                $riskyChoices++;
            } elseif (preg_match('/(safe|steady|cautious|conservative)/', $choiceText)) {
                $safeChoices++;
            } else {
                $balancedChoices++;
            }
        }

        $total = $decisions->count();
        $riskLevel = 'moderate';
        if ($riskyChoices / $total > 0.4) {
            $riskLevel = 'adventurous';
        } elseif ($safeChoices / $total > 0.5) {
            $riskLevel = 'cautious';
        }

        return [
            'total' => $total,
            'by_type' => $byType,
            'story_decisions' => $storyDecisions,
            'daily_actions' => $dailyActions,
            'risk_level' => $riskLevel,
            'risky_decisions' => $riskyChoices,
            'safe_decisions' => $safeChoices,
            'balanced_decisions' => $balancedChoices,
        ];
    }

    /**
     * Extract significant milestones
     */
    private function extractMilestones($decisions): array
    {
        $milestones = [];
        
        foreach ($decisions as $decision) {
            $eventTitle = strtolower($decision->event_title ?? '');
            $choiceText = strtolower($decision->choice_text ?? '');
            $day = (int) ($decision->day ?? 0);
            $age = $day;

            // Career milestones
            if (preg_match('/(promotion|promoted|new job|career)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'career',
                    'title' => 'Career Milestone',
                    'description' => $decision->choice_text ?? 'Advanced in career',
                    'icon' => '💼',
                ];
            }
            
            // Relationship milestones
            if (preg_match('/(marriage|married|proposal|wedding|engaged)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'relationship',
                    'title' => 'Relationship Milestone',
                    'description' => $decision->choice_text ?? 'Found love',
                    'icon' => '💕',
                ];
            }

            // Family milestones
            if (preg_match('/(child|birth|baby|family|parent)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'family',
                    'title' => 'Family Milestone',
                    'description' => $decision->choice_text ?? 'Family grew',
                    'icon' => '👶',
                ];
            }
            
            // Education milestones
            if (preg_match('/(graduation|graduated|degree|education|college|university)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'education',
                    'title' => 'Education Achievement',
                    'description' => $decision->choice_text ?? 'Completed education',
                    'icon' => '🎓',
                ];
            }

            // Health milestones
            if (preg_match('/(fitness|health|exercise|marathon)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'health',
                    'title' => 'Health Achievement',
                    'description' => $decision->choice_text ?? 'Improved health',
                    'icon' => '💪',
                ];
            }

            // Wealth milestones
            if (preg_match('/(millionaire|wealth|investment|rich)/', $eventTitle . $choiceText)) {
                $milestones[] = [
                    'day' => $day,
                    'age' => $age,
                    'type' => 'wealth',
                    'title' => 'Wealth Achievement',
                    'description' => $decision->choice_text ?? 'Financial success',
                    'icon' => '💰',
                ];
            }
        }

        // Sort by day and limit to most important
        usort($milestones, fn($a, $b) => $b['day'] - $a['day']);
        return array_slice($milestones, 0, 15);
    }

    /**
     * Analyze personality based on decisions
     */
    private function analyzePersonality(Character $character, $decisions): array
    {
        // Count decision types
        $emotional = 0;
        $logical = 0;
        $adventurous = 0;
        $cautious = 0;
        $social = 0;
        $solitary = 0;

        foreach ($decisions as $decision) {
            $text = strtolower($decision->choice_text ?? '');
            
            // Emotional vs Logical
            if (preg_match('/(feel|heart|love|emotion|passion)/', $text)) {
                $emotional++;
            }
            if (preg_match('/(logic|reason|calculate|plan|think)/', $text)) {
                $logical++;
            }

            // Adventurous vs Cautious
            if (preg_match('/(adventure|risk|try|new|different)/', $text)) {
                $adventurous++;
            }
            if (preg_match('/(safe|careful|wait|cautious)/', $text)) {
                $cautious++;
            }

            // Social vs Solitary
            if (preg_match('/(together|friend|group|family|social)/', $text)) {
                $social++;
            }
            if (preg_match('/(alone|independent|self|solo)/', $text)) {
                $solitary++;
            }
        }

        $total = max($decisions->count(), 1);

        // Determine personality traits
        $traits = [];
        if ($emotional / $total > 0.3) $traits[] = 'Emotional';
        if ($logical / $total > 0.3) $traits[] = 'Analytical';
        if ($adventurous / $total > 0.3) $traits[] = 'Adventurous';
        if ($cautious / $total > 0.4) $traits[] = 'Cautious';
        if ($social / $total > 0.3) $traits[] = 'Social';
        if ($solitary / $total > 0.3) $traits[] = 'Independent';

        if (empty($traits)) {
            $traits = ['Balanced'];
        }

        // Generate archetype
        $archetype = $this->determineArchetype($traits, $character);

        return [
            'traits' => $traits,
            'archetype' => $archetype,
            'description' => $this->generatePersonalityDescription($traits, $archetype),
            'emotional_score' => round($emotional / $total * 100),
            'logical_score' => round($logical / $total * 100),
            'adventurous_score' => round($adventurous / $total * 100),
            'social_score' => round($social / $total * 100),
        ];
    }

    /**
     * Determine life archetype
     */
    private function determineArchetype(array $traits, Character $character): string
    {
        $profession = $character->profession ?? '';
        $relationship = $character->relationship_status ?? 'single';
        $finance = $character->finance ?? 0;
        $happiness = $character->happiness ?? 50;

        if ($finance > 1000000) {
            return match(true) {
                $happiness > 70 => 'Wealthy Sage',
                $happiness > 40 => 'Ambitious Capitalist',
                default => 'Wealthy but Unfulfilled',
            };
        }

        if ($relationship === 'married' && $happiness > 60) {
            return 'Family Oriented';
        }

        if (in_array('Adventurous', $traits)) {
            return 'Life Explorer';
        }

        if (in_array('Social', $traits)) {
            return 'Community Builder';
        }

        return match(true) {
            $happiness > 70 => 'Life Enjoyer',
            $happiness > 40 => 'Steady Achiever',
            default => 'Struggling Soul',
        };
    }

    /**
     * Generate personality description
     */
    private function generatePersonalityDescription(array $traits, string $archetype): string
    {
        $traitText = implode(', ', $traits);
        
        return match($archetype) {
            'Wealthy Sage' => "You lived a life of abundance and wisdom, using your resources to find meaning and happiness.",
            'Ambitious Capitalist' => "You were driven by success and material achievements throughout your journey.",
            'Family Oriented' => "Relationships and family were the center of your life, bringing you the most joy.",
            'Life Explorer' => "You embraced every opportunity for adventure and new experiences.",
            'Community Builder' => "Your connections with others defined your journey and legacy.",
            'Life Enjoyer' => "You found joy in the simple pleasures and lived with positivity.",
            'Steady Achiever' => "You took a balanced approach, achieving success while maintaining stability.",
            'Struggling Soul' => "Life presented many challenges, but you persevered through difficulties.",
            default => "You lived a unique life with {$traitText} tendencies.",
        };
    }

    /**
     * Get unlocked achievements
     */
    private function getUnlockedAchievements(Character $character): array
    {
        $flags = is_array($character->achievement_flags) 
            ? $character->achievement_flags 
            : [];
        
        if (empty($flags)) {
            return [];
        }

        // Map flags to achievement details
        $achievementService = app(AchievementService::class);
        $allAchievements = $achievementService->getAllAchievements();
        
        // Rekey to associative array by achievement ID for faster lookup
        $achievementsById = [];
        foreach ($allAchievements as $achievement) {
            if (isset($achievement['id'])) {
                $achievementsById[$achievement['id']] = $achievement;
            }
        }

        $unlocked = [];
        foreach ($flags as $flag) {
            // Handle both string flags and array flags (stored as ['id' => '...', ...])
            $flagId = is_array($flag) ? ($flag['id'] ?? null) : $flag;
            
            if ($flagId && isset($achievementsById[$flagId])) {
                $achievement = $achievementsById[$flagId];
                // Add unlock day/timestamp if available from the flag
                if (is_array($flag)) {
                    $achievement['unlocked_day'] = $flag['day'] ?? null;
                    $achievement['unlocked_timestamp'] = $flag['timestamp'] ?? null;
                }
                $unlocked[] = $achievement;
            } elseif ($flagId) {
                // Generic achievement for unknown flags
                $unlocked[] = [
                    'id' => $flagId,
                    'name' => ucwords(str_replace('_', ' ', $flagId)),
                    'description' => is_array($flag) ? ($flag['description'] ?? 'Achievement unlocked!') : 'Achievement unlocked!',
                    'icon' => '🏆',
                    'category' => 'special',
                    'rarity' => 'common',
                    'unlocked_day' => is_array($flag) ? ($flag['day'] ?? null) : null,
                    'unlocked_timestamp' => is_array($flag) ? ($flag['timestamp'] ?? null) : null,
                ];
            }
        }

        return $unlocked;
    }

    /**
     * Calculate overall life rating
     */
    private function calculateLifeRating(Character $character, $decisions, $snapshots): array
    {
        $score = 0;
        $maxScore = 100;

        // Health factor (25 points)
        $health = $character->health ?? 50;
        $score += ($health / 100) * 25;

        // Happiness factor (25 points)
        $happiness = $character->happiness ?? 50;
        $score += ($happiness / 100) * 25;

        // Finance factor (20 points)
        $finance = $character->finance ?? 0;
        $score += min(($finance / 100000), 1) * 20;

        // Relationship factor (15 points)
        $relationship = $character->relationship_status ?? 'single';
        $relScore = match($relationship) {
            'married' => 15,
            'dating' => 10,
            'engaged' => 12,
            'single' => 5,
            default => 5,
        };
        $score += $relScore;

        // Career factor (15 points)
        $careerLevel = $character->career_level ?? 'unemployed';
        $careerScore = match($careerLevel) {
            'student' => 1,
            'unemployed' => 1,
            'entry' => 2,
            'junior' => 3,
            'mid-level' => 4,
            'senior' => 5,
            'manager' => 6,
            'senior_manager' => 7,
            'executive' => 8,
            'entrepreneur' => 9,
            'retired' => 5,
            default => 1,
        };
        $score += min(($careerScore / 10), 1) * 15;

        $finalScore = round($score);
        $grade = match(true) {
            $finalScore >= 90 => 'A+',
            $finalScore >= 80 => 'A',
            $finalScore >= 70 => 'B',
            $finalScore >= 60 => 'C',
            $finalScore >= 50 => 'D',
            default => 'F',
        };

        $title = match(true) {
            $finalScore >= 90 => 'Legendary Life',
            $finalScore >= 80 => 'Inspiring Journey',
            $finalScore >= 70 => 'Well-Lived Life',
            $finalScore >= 60 => 'Meaningful Existence',
            $finalScore >= 50 => 'Average Journey',
            default => 'Challenging Life',
        };

        return [
            'score' => $finalScore,
            'grade' => $grade,
            'title' => $title,
            'summary' => $this->generateRatingSummary($finalScore, $character),
        ];
    }

    /**
     * Generate rating summary text
     */
    private function generateRatingSummary(int $score, Character $character): string
    {
        $name = $character->name;
        $years = (int) ($character->current_day ?? 1);
        
        return match(true) {
            $score >= 90 => "What an incredible life, {$name}! At {$years} years, you achieved everything and more.",
            $score >= 80 => "A truly remarkable journey, {$name}. You made the most of your {$years} years.",
            $score >= 70 => "You lived a fulfilling life, {$name}. Your {$years} years were well-spent.",
            $score >= 60 => "A solid life journey, {$name}. You found a good balance in your {$years} years.",
            $score >= 50 => "Your life had its ups and downs, {$name}, but you persevered through {$years} years.",
            default => "Life was difficult, {$name}, but you survived {$years} years. Every life has value.",
        };
    }

    /**
     * Find key defining moments
     */
    private function findKeyMoments($decisions): array
    {
        if ($decisions->isEmpty()) {
            return [];
        }

        $moments = [];
        
        // Find biggest stat changes
        foreach ($decisions as $decision) {
            $effects = is_array($decision->effects) ? $decision->effects : [];
            
            // Look for big positive or negative changes
            foreach ($effects as $effect) {
                if (preg_match('/([+-]\d+)/', $effect, $matches)) {
                    $change = (int) $matches[1];
                    if (abs($change) >= 20) {
                        $moments[] = [
                            'day' => (int) ($decision->day ?? 0),
                            'age' => (int) ($decision->day ?? 0),
                            'type' => 'major_change',
                            'title' => $decision->event_title ?? 'Major Event',
                            'description' => $decision->choice_text ?? '',
                            'impact' => $change > 0 ? 'positive' : 'negative',
                            'change' => $change,
                        ];
                    }
                }
            }
        }

        // Sort by absolute impact
        usort($moments, fn($a, $b) => abs($b['change']) - abs($a['change']));
        
        return array_slice($moments, 0, 10);
    }

    /**
     * Generate life advice based on the character's journey
     */
    private function generateLifeAdvice(Character $character, $decisions, $snapshots): array
    {
        $advice = [];
        
        $health = $character->health ?? 50;
        $happiness = $character->happiness ?? 50;
        $finance = $character->finance ?? 0;
        $years = (int) ($character->current_day ?? 1);

        // Health advice
        if ($health < 30) {
            $advice[] = "Your health suffered greatly. In your next life, prioritize physical well-being earlier.";
        } elseif ($health > 70) {
            $advice[] = "You maintained excellent health! Keep nurturing your body.";
        }

        // Happiness advice
        if ($happiness < 30) {
            $advice[] = "Finding inner peace seems to be your next life's goal. Focus on mental health.";
        } elseif ($happiness > 70) {
            $advice[] = "You found true happiness! Your positive outlook is inspiring.";
        }

        // Finance advice
        if ($finance < 0) {
            $advice[] = "Financial struggles defined this life. Consider focusing on stability next time.";
        } elseif ($finance > 500000) {
            $advice[] = "Wealth was never an issue for you. Perhaps explore simpler pleasures?";
        }

        // Lifespan advice
        if ($years < 40) {
            $advice[] = "Life was cut short. Take more risks with your health in your next journey.";
        } elseif ($years > 70) {
            $advice[] = "You lived a long life! Wisdom and patience were clearly your strengths.";
        }

        // Default advice
        if (empty($advice)) {
            $advice[] = "Every life is unique. Your journey was exactly what it needed to be.";
            $advice[] = "Thank you for playing ULife. Would you like to start a new life?";
        }

        return $advice;
    }

    /**
     * Get comparison data (optional - can be expanded with aggregate data)
     */
    public function getComparisonData(Character $character): array
    {
        // This could be expanded to compare against other players
        return [
            'available' => false,
            'message' => 'Compare your life with others coming soon!',
        ];
    }
}
