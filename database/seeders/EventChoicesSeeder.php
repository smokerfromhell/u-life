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
        $eventNameLower = strtolower($eventName);
        
        // Parse original effects to understand the event type
        $isFatal = false;
        
        if ($originalEffects) {
            if (strpos($originalEffects, '-100') !== false || strpos($originalEffects, 'End of game') !== false) {
                $isFatal = true;
            }
        }
        
        // Generate contextually appropriate choices based on event name
        $choices = [];
        
        // Check event category for contextual choices
        if (str_contains($eventNameLower, 'chores') || str_contains($eventNameLower, 'help')) {
            $choices = [
                ['text' => 'Help eagerly', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Help slightly', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Do not help', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Whatever', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'school') || str_contains($eventNameLower, 'exam') || str_contains($eventNameLower, 'study') || str_contains($eventNameLower, 'grade')) {
            $choices = [
                ['text' => 'Study hard', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Study casually', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Skip studying', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Cheat on exam', 'stat_effects' => $this->getCheatEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'friend') || str_contains($eventNameLower, 'relationship') || str_contains($eventNameLower, 'crush') || str_contains($eventNameLower, 'love') || str_contains($eventNameLower, 'party')) {
            $choices = [
                ['text' => 'Fully commit', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Participate casually', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Stay away', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Whatever happens', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'sport') || str_contains($eventNameLower, 'game') || str_contains($eventNameLower, 'contest') || str_contains($eventNameLower, 'race')) {
            $choices = [
                ['text' => 'Give it your all', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Try your best', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Hold back', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Just participate', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'illness') || str_contains($eventNameLower, 'sick') || str_contains($eventNameLower, 'disease') || str_contains($eventNameLower, 'health')) {
            $choices = [
                ['text' => 'Seek treatment immediately', 'stat_effects' => $this->adjustEffects($originalEffects, 1.3)],
                ['text' => 'Rest at home', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
                ['text' => 'Ignore it', 'stat_effects' => $this->worsenEffects($originalEffects)],
                ['text' => 'Try home remedies', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
            ];
        }
        elseif (str_contains($eventNameLower, 'accident') || str_contains($eventNameLower, 'injury') || str_contains($eventNameLower, 'fall')) {
            $choices = [
                ['text' => 'Stay calm and act', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Call for help', 'stat_effects' => $this->adjustEffects($originalEffects, 0.8)],
                ['text' => 'Panic', 'stat_effects' => $this->worsenEffects($originalEffects)],
                ['text' => 'Try to ignore', 'stat_effects' => $this->worsenEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'bullying') || str_contains($eventNameLower, 'bully')) {
            $choices = [
                ['text' => 'Stand up to them', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Tell an adult', 'stat_effects' => $this->adjustEffects($originalEffects, 0.8)],
                ['text' => 'Stay silent', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Avoid the bullies', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
            ];
        }
        elseif (str_contains($eventNameLower, 'family') || str_contains($eventNameLower, 'parent') || str_contains($eventNameLower, 'sibling') || str_contains($eventNameLower, 'bond')) {
            $choices = [
                ['text' => 'Spend quality time', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Join briefly', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Stay in room', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Whatever', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'career') || str_contains($eventNameLower, 'job') || str_contains($eventNameLower, 'work') || str_contains($eventNameLower, 'promotion')) {
            $choices = [
                ['text' => 'Go above and beyond', 'stat_effects' => $this->adjustEffects($originalEffects, 1.3)],
                ['text' => 'Meet expectations', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
                ['text' => 'Do minimum work', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Call in sick', 'stat_effects' => $this->worsenEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'marriage') || str_contains($eventNameLower, 'wedding') || str_contains($eventNameLower, 'divorce')) {
            $choices = [
                ['text' => 'Fully invest', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Take it slow', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Reject/Leave', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Think about it', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'money') || str_contains($eventNameLower, 'wealth') || str_contains($eventNameLower, 'business') || str_contains($eventNameLower, 'inheritance')) {
            $choices = [
                ['text' => 'Invest wisely', 'stat_effects' => $this->adjustEffects($originalEffects, 1.3)],
                ['text' => 'Save it', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
                ['text' => 'Spend it freely', 'stat_effects' => $this->worsenEffects($originalEffects)],
                ['text' => 'Share with others', 'stat_effects' => '+5 Morality, -10 Wealth'],
            ];
        }
        elseif (str_contains($eventNameLower, 'travel') || str_contains($eventNameLower, 'trip') || str_contains($eventNameLower, 'vacation')) {
            $choices = [
                ['text' => 'Embrace the experience', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Enjoy quietly', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Stay home instead', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Plan meticulously', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
            ];
        }
        elseif (str_contains($eventNameLower, 'talent') || str_contains($eventNameLower, 'hobby') || str_contains($eventNameLower, 'skill')) {
            $choices = [
                ['text' => 'Practice diligently', 'stat_effects' => $this->adjustEffects($originalEffects, 1.3)],
                ['text' => 'Practice occasionally', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Give it up', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Share with others', 'stat_effects' => $this->adjustEffects($originalEffects, 0.8)],
            ];
        }
        elseif (str_contains($eventNameLower, 'retirement') || str_contains($eventNameLower, 'elder') || str_contains($eventNameLower, 'old')) {
            $choices = [
                ['text' => 'Enjoy fully', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Stay active', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
                ['text' => 'Feel lonely', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Spend time with family', 'stat_effects' => $this->adjustEffects($originalEffects, 0.9)],
            ];
        }
        elseif (str_contains($eventNameLower, 'birth')) {
            $choices = [
                ['text' => 'Embrace the moment', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Stay positive', 'stat_effects' => $this->adjustEffects($originalEffects, 0.7)],
                ['text' => 'Worry about future', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Take it as it comes', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        elseif (str_contains($eventNameLower, 'death') || $isFatal) {
            $choices = [
                ['text' => 'Accept fate', 'stat_effects' => '+10 Morality, -50 Health'],
                ['text' => 'Fight to survive', 'stat_effects' => '+20 Health, -20 Happiness'],
                ['text' => 'Say goodbye peacefully', 'stat_effects' => '+20 Morality, -30 Health'],
                ['text' => 'Hold onto hope', 'stat_effects' => '+10 Happiness, -30 Health'],
            ];
        }
        else {
            // Default choices for events not matching any category
            $choices = [
                ['text' => 'Embrace fully', 'stat_effects' => $this->adjustEffects($originalEffects, 1.2)],
                ['text' => 'Partially engage', 'stat_effects' => $this->adjustEffects($originalEffects, 0.5)],
                ['text' => 'Reject', 'stat_effects' => $this->negateEffects($originalEffects)],
                ['text' => 'Stay neutral', 'stat_effects' => $this->neutralizeEffects($originalEffects)],
            ];
        }
        
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

