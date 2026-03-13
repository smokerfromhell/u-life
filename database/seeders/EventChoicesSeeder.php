<?php

namespace Database\Seeders;

use App\Models\AgeSpecificEvent;
use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\ProfessionPathEvent;
use Illuminate\Database\Seeder;

class EventChoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add multiple choices to each event with different stat effects
     */
    public function run(): void
    {
        $this->command->info('Adding choices to Age Specific Events...');
        $this->addChoicesToAgeSpecificEvents();
        
        $this->command->info('Adding choices to Daily Events...');
        $this->addChoicesToDailyEvents();
        
        $this->command->info('Adding choices to Cultural Events...');
        $this->addChoicesToCulturalEvents();
        
        $this->command->info('Adding choices to Profession Path Events...');
        $this->addChoicesToProfessionPathEvents();
        
        $this->command->info('All event choices have been added!');
    }
    
    private function addChoicesToAgeSpecificEvents()
    {
        $events = AgeSpecificEvent::all();
        
        foreach ($events as $event) {
            $choices = $this->generateChoices($event->event_choice, $event->stat_effects, $event->age_group);
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToDailyEvents()
    {
        $events = DailyEvent::all();
        
        foreach ($events as $event) {
            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToCulturalEvents()
    {
        $events = CulturalEvent::all();
        
        foreach ($events as $event) {
            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    private function addChoicesToProfessionPathEvents()
    {
        $events = ProfessionPathEvent::all();
        
        foreach ($events as $event) {
            $choices = $this->generateChoices($event->event_choice ?? $event->title, $event->stat_effects, 'adult');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
    
    /**
     * Generate choices based on event type and original stat effects
     */
    private function generateChoices(string $eventName, ?string $originalEffects, string $ageGroup): array
    {
        $choices = [
            [
                'text' => 'Good Choice (Positive Stats)',
                'stat_effects' => $this->adjustEffects($originalEffects, 1.5) // Good: amplified positive
            ],
            [
                'text' => 'Neutral Choice',
                'stat_effects' => $this->neutralizeEffects($originalEffects) // Neutral: minimal change
            ],
            [
                'text' => 'Bad Choice (Negative Stats)',
                'stat_effects' => $this->negateEffects($this->worsenEffects($originalEffects)) // Bad: amplified negative
            ],
            [
                'text' => 'Skip / Consequences',
                'stat_effects' => $this->negateEffects($originalEffects) . ', +5 Burnout' // Skip with consequence
            ]
        ];
        
        return $choices;
    }
    
    /**
     * Adjust stat effects by a multiplier
     */
    private function adjustEffects(?string $effects, float $multiplier): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $adjusted = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Apply multiplier
                $newValue = (int)($value * $multiplier);
                
                // Ensure we keep the sign
                if ($value < 0) {
                    $newValue = -abs($newValue);
                } else {
                    $newValue = abs($newValue);
                }
                
                // Handle special cases
                if ($stat === 'Health' && $newValue < -90) {
                    $newValue = -100;
                    $adjusted[] = $newValue . ' ' . $stat . ', End of game';
                } else {
                    $adjusted[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
                }
            } else {
                // Keep non-stat effects as-is (like "End of game")
                $adjusted[] = $part;
            }
        }
        
        return implode(', ', $adjusted);
    }
    
    /**
     * Negate the stat effects (make positive negative and vice versa)
     */
    private function negateEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $negated = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Negate the value
                $newValue = -$value;
                
                $negated[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $negated[] = $part;
            }
        }
        
        return implode(', ', $negated);
    }
    
    /**
     * Neutralize stat effects (reduce to near zero)
     */
    private function neutralizeEffects(?string $effects): string
    {
        if (empty($effects)) {
            return '';
        }
        
        $neutral = [];
        $parts = explode(',', $effects);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                $value = (int)$matches[1];
                $stat = $matches[2];
                
                // Reduce significantly
                $newValue = (int)($value * 0.1);
                
                $neutral[] = ($newValue >= 0 ? '+' : '') . $newValue . ' ' . $stat;
            } else {
                $neutral[] = $part;
            }
        }
        
        return implode(', ', $neutral);
    }
    
    /**
     * Worsen the stat effects
     */
    private function worsenEffects(?string $effects): string
    {
        return $this->adjustEffects($effects, 1.5);
    }
    
    /**
     * Get cheating-related effects
     */
    private function getCheatEffects(?string $effects): string
    {
        // Cheating gives positive short-term but negative long-term effects
        return '+15 Intelligence, -10 Morality, -5 Reputation';
    }
}

