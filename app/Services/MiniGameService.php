<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling mini-game logic and outcomes
 */
class MiniGameService
{
    /**
     * Process a mini-game result and calculate stat effects
     */
    public function processGameResult(Character $character, string $gameType, array $gameData, int $score): array
    {
        $effects = [];
        $outcome = 'neutral';
        
        switch ($gameType) {
            case 'qte':
                $effects = $this->processQTEResult($score, $gameData);
                break;
            case 'memory_match':
                $effects = $this->processMemoryResult($score, $gameData);
                break;
            case 'choice_chain':
                $effects = $this->processChoiceChainResult($score, $gameData);
                break;
            case 'timing':
                $effects = $this->processTimingResult($score, $gameData);
                break;
            default:
                Log::warning('Unknown mini-game type', ['type' => $gameType]);
                break;
        }
        
        return [
            'effects' => $effects,
            'outcome' => $outcome,
            'score' => $score,
            'message' => $this->getOutcomeMessage($gameType, $score),
        ];
    }
    
    /**
     * Quick Time Event - press buttons in sequence
     * Score: 0-100
     */
    private function processQTEResult(int $score, array $gameData): array
    {
        $baseEffects = $gameData['stat_effects'] ?? [];
        
        if ($score >= 90) {
            // Perfect performance
            return $this->applyMultiplier($baseEffects, 1.5);
        } elseif ($score >= 70) {
            // Good performance
            return $this->applyMultiplier($baseEffects, 1.2);
        } elseif ($score >= 50) {
            // Average - no change
            return $baseEffects;
        } elseif ($score >= 30) {
            // Below average
            return $this->applyMultiplier($baseEffects, 0.7);
        } else {
            // Failed
            return $this->applyMultiplier($baseEffects, 0.3);
        }
    }
    
    /**
     * Memory Match - match pairs of cards
     * Score: 0-100 (based on time and moves)
     */
    private function processMemoryResult(int $score, array $gameData): array
    {
        $baseEffects = $gameData['stat_effects'] ?? [];
        
        if ($score >= 90) {
            return $this->applyMultiplier($baseEffects, 1.5);
        } elseif ($score >= 70) {
            return $this->applyMultiplier($baseEffects, 1.2);
        } elseif ($score >= 50) {
            return $baseEffects;
        } elseif ($score >= 30) {
            return $this->applyMultiplier($baseEffects, 0.7);
        } else {
            return $this->applyMultiplier($baseEffects, 0.3);
        }
    }
    
    /**
     * Choice Chain - make quick decisions
     * Score: 0-100 (correct choices)
     */
    private function processChoiceChainResult(int $score, array $gameData): array
    {
        $baseEffects = $gameData['stat_effects'] ?? [];
        
        if ($score >= 90) {
            return $this->applyMultiplier($baseEffects, 1.5);
        } elseif ($score >= 70) {
            return $this->applyMultiplier($baseEffects, 1.2);
        } elseif ($score >= 50) {
            return $baseEffects;
        } elseif ($score >= 30) {
            return $this->applyMultiplier($baseEffects, 0.7);
        } else {
            return $this->applyMultiplier($baseEffects, 0.3);
        }
    }
    
    /**
     * Timing Game - hit the target at the right moment
     * Score: 0-100 (accuracy)
     */
    private function processTimingResult(int $score, array $gameData): array
    {
        $baseEffects = $gameData['stat_effects'] ?? [];
        
        if ($score >= 90) {
            return $this->applyMultiplier($baseEffects, 1.5);
        } elseif ($score >= 70) {
            return $this->applyMultiplier($baseEffects, 1.2);
        } elseif ($score >= 50) {
            return $baseEffects;
        } elseif ($score >= 30) {
            return $this->applyMultiplier($baseEffects, 0.7);
        } else {
            return $this->applyMultiplier($baseEffects, 0.3);
        }
    }
    
    /**
     * Apply multiplier to stat effects
     */
    private function applyMultiplier(array $effects, float $multiplier): array
    {
        if (empty($effects)) {
            return $effects;
        }
        
        $result = [];
        foreach ($effects as $stat => $value) {
            $result[$stat] = round($value * $multiplier);
        }
        
        return $result;
    }
    
    /**
     * Get outcome message based on score
     */
    private function getOutcomeMessage(string $gameType, int $score): string
    {
        $messages = [
            'perfect' => 'Perfect! You nailed it!',
            'great' => 'Great job! Well done!',
            'good' => 'Good performance!',
            'average' => 'Not bad, but room for improvement.',
            'poor' => 'That could have gone better.',
            'failed' => 'Better luck next time!',
        ];
        
        if ($score >= 90) {
            return $messages['perfect'];
        } elseif ($score >= 70) {
            return $messages['great'];
        } elseif ($score >= 50) {
            return $messages['good'];
        } elseif ($score >= 30) {
            return $messages['average'];
        } else {
            return $messages['failed'];
        }
    }
    
    /**
     * Generate QTE sequence for frontend
     */
    public function generateQTESequence(int $difficulty = 1): array
    {
        $sequenceLength = 3 + $difficulty; // 4-7 buttons
        $buttons = ['↑', '↓', '←', '→', 'A', 'B', 'X', 'Y'];
        $sequence = [];
        $timeLimit = 2000 + ($difficulty * 500); // 2.5-4.5 seconds
        
        for ($i = 0; $i < $sequenceLength; $i++) {
            $sequence[] = $buttons[array_rand($buttons)];
        }
        
        return [
            'sequence' => $sequence,
            'time_limit' => $timeLimit,
            'difficulty' => $difficulty,
        ];
    }
    
    /**
     * Generate Memory Match game for frontend
     */
    public function generateMemoryMatch(int $difficulty = 1): array
    {
        $gridSize = $difficulty <= 1 ? 4 : ($difficulty <= 2 ? 6 : 8);
        $pairsCount = ($gridSize * $gridSize) / 2;
        
        // Generate pairs of symbols/emojis
        $symbols = ['📚', '💼', '🎯', '⭐', '🔥', '💡', '🎮', '🏆', '💰', '❤️', '🌟', '✨', '🎨', '🎵', '📱', '💻'];
        $selectedSymbols = array_slice($symbols, 0, $pairsCount);
        
        // Create pairs and shuffle
        $cards = array_merge($selectedSymbols, $selectedSymbols);
        shuffle($cards);
        
        return [
            'cards' => $cards,
            'grid_size' => $gridSize,
            'pairs' => $pairsCount,
            'time_limit' => 60000 + ($difficulty * 30000), // 60-120 seconds
            'difficulty' => $difficulty,
        ];
    }
    
    /**
     * Generate Choice Chain game for frontend
     */
    public function generateChoiceChain(string $category, int $difficulty = 1): array
    {
        $questions = $this->getChoiceChainQuestions($category);
        $count = min(3 + $difficulty, count($questions)); // 4-7 questions
        
        // Shuffle and slice
        shuffle($questions);
        $selectedQuestions = array_slice($questions, 0, $count);
        
        return [
            'questions' => $selectedQuestions,
            'count' => $count,
            'time_per_question' => 8000 - ($difficulty * 1000), // 5-7 seconds
            'difficulty' => $difficulty,
        ];
    }
    
    /**
     * Generate Timing game for frontend
     */
    public function generateTimingGame(int $difficulty = 1): array
    {
        $rounds = 3 + $difficulty;
        $targetSize = max(30, 80 - ($difficulty * 10)); // Decreasing target size
        $speed = 1 + ($difficulty * 0.3);
        
        return [
            'rounds' => $rounds,
            'target_size' => $targetSize,
            'speed' => $speed,
            'difficulty' => $difficulty,
        ];
    }
    
    /**
     * Get choice chain questions based on category
     */
    private function getChoiceChainQuestions(string $category): array
    {
        $questionsByCategory = [
            'career' => [
                ['question' => 'Your boss asks for your opinion on a new project. What do you do?', 'options' => ['Enthusastically share ideas', 'Wait and listen first', 'Keep quiet', 'Decline to comment'], 'correct' => 0],
                ['question' => 'A coworker takes credit for your work. How do you respond?', 'options' => ['Confront them directly', 'Talk to manager privately', 'Let it go', 'Publicly correct them'], 'correct' => 1],
                ['question' => 'You\'re offered a promotion with less work-life balance. What do you do?', 'options' => ['Accept immediately', 'Negotiate terms', 'Decline politely', 'Ask for time to decide'], 'correct' => 3],
                ['question' => 'An unexpected project lands on your desk. What\'s your approach?', 'options' => ['Delegate it', 'Work overtime to complete', 'Discuss priorities with boss', 'Refuse extra work'], 'correct' => 2],
                ['question' => 'Your team is struggling with morale. What do you do?', 'options' => ['Organize team building', 'Ignore it', 'Report to HR', 'Talk to team members'], 'correct' => 3],
            ],
            'education' => [
                ['question' => 'You\'re struggling with a difficult subject. What do you do?', 'options' => ['Drop the class', 'Seek tutoring', 'Study harder alone', 'Ask professor for help'], 'correct' => 3],
                ['question' => 'An important exam is tomorrow. What\'s your strategy?', 'options' => ['All-night cramming', 'Review key topics and sleep', 'Party tonight, study tomorrow', 'Skip to rest'], 'correct' => 1],
                ['question' => 'A group project teammate isn\'t pulling their weight. How do you handle it?', 'options' => ['Do their share', 'Confront them', 'Grade them poorly later', 'Talk to professor'], 'correct' => 1],
                ['question' => 'You have the chance to study abroad. What\'s your reaction?', 'options' => ['Decline - too scary', 'Apply immediately', 'Research program first', 'Ask parents to decide'], 'correct' => 2],
                ['question' => 'Your professor made an error in grading. What do you do?', 'options' => ['Accept the grade', 'Politely discuss after class', 'Challenge publicly', 'Forget about it'], 'correct' => 1],
            ],
            'social' => [
                ['question' => 'You meet someone interesting at a party. How do you start a conversation?', 'options' => ['Talk about yourself', 'Ask about them', 'Make a joke', 'Wait for them to approach'], 'correct' => 1],
                ['question' => 'A friend cancels plans at the last minute. What do you do?', 'options' => ['Get upset', 'Reschedule politely', 'Cancel on them too', 'Spend time alone'], 'correct' => 1],
                ['question' => 'You\'re introduced to someone important. What\'s your approach?', 'options' => ['Be overly formal', 'Act natural and friendly', 'Stay quiet', 'Show off your achievements'], 'correct' => 1],
                ['question' => 'A friend asks for advice about a personal problem. What do you do?', 'options' => ['Give unsolicited opinions', 'Listen and support', 'Change the subject', 'Solve it for them'], 'correct' => 1],
                ['question' => 'You\'re at a networking event. How do you approach strangers?', 'options' => ['Hand out business cards', 'Join existing conversations', 'Wait to be approached', 'Stick to people you know'], 'correct' => 1],
            ],
            'health' => [
                ['question' => 'You feel exhausted but have social plans. What do you do?', 'options' => ['Push through', 'Cancel and rest', 'Shorten the plans', 'Power through with caffeine'], 'correct' => 2],
                ['question' => 'A doctor suggests a lifestyle change. How do you respond?', 'options' => ['Ignore it', 'Follow completely', 'Research and discuss', 'Get a second opinion'], 'correct' => 2],
                ['question' => 'You\'re stressed from work. What\'s your coping mechanism?', 'options' => ['Work more', 'Exercise and relax', 'Stress eat', 'Ignore it'], 'correct' => 1],
                ['question' => 'You\'re tempted to skip exercise today. What motivates you?', 'options' => ['Skip it - no big deal', 'Do a quick workout', 'Reward yourself first', 'Find a workout buddy'], 'correct' => 1],
                ['question' => 'Your sleep schedule is chaotic. How do you fix it?', 'options' => ['Cold turkey change', 'Gradual adjustment', 'Use phone in bed', 'Accept it'], 'correct' => 1],
            ],
            'wealth' => [
                ['question' => 'You receive unexpected money. What do you do?', 'options' => ['Spend it immediately', 'Save half, spend half', 'Invest it', 'Give it away'], 'correct' => 1],
                ['question' => 'A friend asks to borrow money. How do you respond?', 'options' => ['Lend freely', 'Politely decline', 'Lend with conditions', 'Give as a gift'], 'correct' => 2],
                ['question' => 'You find a great deal on something you don\'t need. What do you do?', 'options' => ['Buy it - great deal!', 'Think it over first', 'Leave it', 'Buy multiples'], 'correct' => 1],
                ['question' => 'Your friend shows off expensive purchases. How do you feel?', 'options' => ['Jealous', 'Indifferent', 'Motivated', 'Guilty'], 'correct' => 1],
                ['question' => 'You want to start investing. What\'s your first step?', 'options' => ['Jump in quickly', 'Research first', 'Wait for the perfect time', 'Ask a stranger'], 'correct' => 1],
            ],
            'family' => [
                ['question' => 'You disagree with a family decision. How do you handle it?', 'options' => ['Argue until you win', 'Express your view once', 'Accept quietly', 'Avoid the family'], 'correct' => 1],
                ['question' => 'A family member needs help. What\'s your response?', 'options' => ['Drop everything', 'Help if convenient', 'Set boundaries first', 'Say no'], 'correct' => 2],
                ['question' => 'You want to discuss a sensitive topic with family. Approach?', 'options' => ['Direct approach', 'Write a letter first', 'Avoid entirely', 'Bring it up casually'], 'correct' => 3],
                ['question' => 'Family is visiting unexpectedly. How do you react?', 'options' => ['Cancel plans', 'Welcome them', 'Hide the mess', 'Leave the house'], 'correct' => 1],
                ['question' => 'You and family disagree on holiday plans. What do you do?', 'options' => ['Insist on your plan', 'Compromise', 'Skip holidays', 'Let others decide'], 'correct' => 1],
            ],
        ];
        
        return $questionsByCategory[$category] ?? $questionsByCategory['career'];
    }
}
