<?php

namespace Database\Seeders;

use App\Models\AgeSpecificEvent;
use App\Models\CulturalEvent;
use App\Models\DailyAction;
use App\Models\DailyEvent;
use App\Models\ProfessionPathEvent;
use App\Models\ProfessionTrigger;
use Illuminate\Database\Seeder;

class RandomOutcomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populate random outcomes for event choices based on choice and event context
     */
    public function run(): void
    {
        $this->seedAgeSpecificEvents();
        $this->seedDailyEvents();
        $this->seedCulturalEvents();
        $this->seedProfessionPathEvents();
        $this->seedProfessionTriggers();
        $this->seedDailyActions();
    }

    /**
     * Seed random outcomes for Age Specific Events
     */
    protected function seedAgeSpecificEvents(): void
    {
        $events = AgeSpecificEvent::all();
        
        foreach ($events as $event) {
            $choices = $event->choices;
            
            // Handle JSON string or array
            if (is_string($choices)) {
                $choices = json_decode($choices, true);
            }
            
            if (!is_array($choices) || empty($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Always regenerate random outcomes based on choice and event name
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $event->event_category ?? 'random',
                    $choice,
                    $event->event_choice ?? ''
                );
                $updated = true;
            }
            
            if ($updated) {
                $event->choices = $choices;
                $event->save();
            }
        }
    }

    /**
     * Seed random outcomes for Daily Events
     */
    protected function seedDailyEvents(): void
    {
        $events = DailyEvent::all();
        
        foreach ($events as $event) {
            $choices = $event->choices;
            if (!is_array($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Always regenerate random outcomes based on choice and event name
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $event->event_category ?? 'random',
                    $choice,
                    $event->event_choice ?? ''
                );
                $updated = true;
            }
            
            if ($updated) {
                $event->choices = $choices;
                $event->save();
            }
        }
    }

    /**
     * Seed random outcomes for Daily Actions
     * This handles daily actions like eating, sleeping, exercising, etc.
     */
    protected function seedDailyActions(): void
    {
        $actions = DailyAction::all();
        
        foreach ($actions as $action) {
            $choices = $action->choices;
            if (!is_array($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Get random outcomes based on choice text and action title
                // Use the action type as category, or 'random' as fallback
                $category = $this->mapActionTypeToCategory($action->type ?? 'random');
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $category,
                    $choice,
                    $action->title ?? ''
                );
                $updated = true;
            }
            
            if ($updated) {
                $action->choices = $choices;
                $action->save();
            }
        }
    }

    /**
     * Map action type to event category for random outcomes
     */
    private function mapActionTypeToCategory(string $type): string
    {
        return match($type) {
            'health' => 'health',
            'social' => 'social',
            'education' => 'education',
            'career' => 'career',
            'creative' => 'creative',
            'competition' => 'competition',
            default => 'random',
        };
    }

    /**
     * Seed random outcomes for Cultural Events
     */
    protected function seedCulturalEvents(): void
    {
        $events = CulturalEvent::all();
        
        foreach ($events as $event) {
            $choices = $event->choices;
            
            // Handle JSON string or array
            if (is_string($choices)) {
                $choices = json_decode($choices, true);
            }
            
            if (!is_array($choices) || empty($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Always regenerate random outcomes based on choice and event name
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $event->event_category ?? 'culture',
                    $choice,
                    $event->event_choice ?? ''
                );
                $updated = true;
            }
            
            if ($updated) {
                $event->choices = $choices;
                $event->save();
            }
        }
    }

    /**
     * Seed random outcomes for Profession Path Events
     */
    protected function seedProfessionPathEvents(): void
    {
        $events = ProfessionPathEvent::all();
        
        foreach ($events as $event) {
            $choices = $event->choices;
            
            // Handle JSON string or array
            if (is_string($choices)) {
                $choices = json_decode($choices, true);
            }
            
            if (!is_array($choices) || empty($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Use profession as category with fallback to career
                // Convert to lowercase for switch matching
                $category = !empty($event->profession) ? strtolower($event->profession) : 'career';
                // Use title if event_choice is not set (for newer events)
                $eventName = $event->event_choice ?? $event->title ?? '';
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $category,
                    $choice,
                    $eventName
                );
                $updated = true;
            }
            
            if ($updated) {
                $event->choices = $choices;
                $event->save();
            }
        }
    }

    /**
     * Seed random outcomes for Profession Triggers
     */
    protected function seedProfessionTriggers(): void
    {
        $triggers = ProfessionTrigger::all();
        
        foreach ($triggers as $trigger) {
            $choices = $trigger->choices;
            
            // Handle JSON string or array
            if (is_string($choices)) {
                $choices = json_decode($choices, true);
            }
            
            if (!is_array($choices) || empty($choices)) {
                continue;
            }
            
            $updated = false;
            foreach ($choices as &$choice) {
                // Use profession as category with fallback to career
                // Convert to lowercase for switch matching
                $category = !empty($trigger->profession) ? strtolower($trigger->profession) : 'career';
                $choice['random_outcomes'] = $this->getRandomOutcomesForCategory(
                    $category,
                    $choice,
                    $trigger->description ?? ''
                );
                $updated = true;
            }
            
            if ($updated) {
                $trigger->choices = $choices;
                $trigger->save();
            }
        }
    }

    /**
     * Get random outcomes based on event category and choice/event text
     */
    protected function getRandomOutcomesForCategory(string $category, array $choice, string $eventChoice = ''): array
    {
        $choiceText = strtolower($choice['text'] ?? '');
        $eventText = strtolower($eventChoice ?? '');
        $combinedText = $choiceText . ' ' . $eventText;
        
        $outcomes = [];
        
        // Generate contextual outcomes based on keywords from both choice and event
        $keywords = $this->extractKeywords($combinedText);
        
        switch ($category) {
            case 'education':
                if (in_array('exam', $keywords) || in_array('test', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Perfect Score',
                            'description' => 'You ace the exam beyond expectations',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Intelligence' => 10, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Memory Blank',
                            'description' => 'You draw a complete blank on questions you knew',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => -5, 'Burnout' => 5]
                        ],
                        [
                            'name' => '意外好成绩',
                            'description' => 'You guessed many correctly and got a better score than expected',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 3, 'Luck' => 5]
                        ]
                    ];
                } elseif (in_array('study', $keywords) || in_array('learn', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Breakthrough Understanding',
                            'description' => 'Your focused studying leads to a sudden insight',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 8, 'Discipline' => 3]
                        ],
                        [
                            'name' => 'Mental Block',
                            'description' => 'Despite your efforts, you hit a mental wall',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -5, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Teacher\'s Praise',
                            'description' => 'Your teacher notices your hard work',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => 5, 'Happiness' => 3]
                        ]
                    ];
                } elseif (in_array('school', $keywords) || in_array('class', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Scholastic Achievement',
                            'description' => 'Your dedication pays off with unexpected academic recognition',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 6, 'Reputation' => 4]
                        ],
                        [
                            'name' => 'Learning Difficulty',
                            'description' => 'Unexpected difficulty grasping the material',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Classmate Help',
                            'description' => 'A classmate offers to help you understand',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 4, 'Happiness' => 2]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Learning Momentum',
                            'description' => 'Your education efforts gain unexpected momentum',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 5, 'Discipline' => 3]
                        ],
                        [
                            'name' => 'Distraction',
                            'description' => 'Unexpected distractions derail your learning',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'New Study Method',
                            'description' => 'You discover a more effective way to learn',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 6, 'Discipline' => 2]
                        ]
                    ];
                }
                break;
                
            case 'career':
                if (in_array('work', $keywords) || in_array('job', $keywords) || in_array('office', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Promotion',
                            'description' => 'Your hard work is recognized with a promotion',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]
                        ],
                        [
                            'name' => 'Layoff',
                            'description' => 'Company downsizing affects your position',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Wealth' => -10, 'Happiness' => -8]
                        ],
                        [
                            'name' => 'Bonus',
                            'description' => 'You receive an unexpected performance bonus',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => 10, 'Happiness' => 5]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Career Advancement',
                            'description' => 'Your career takes a positive turn',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Work Conflict',
                            'description' => 'Conflict with coworkers creates tension',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -5, 'Reputation' => -3]
                        ],
                        [
                            'name' => 'New Opportunity',
                            'description' => 'A better opportunity presents itself',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Wealth' => 8, 'Happiness' => 4]
                        ]
                    ];
                }
                break;
                
            case 'family':
                if (in_array('parent', $keywords) || in_array('father', $keywords) || in_array('mother', $keywords) || in_array('dad', $keywords) || in_array('mom', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Family Pride',
                            'description' => 'Your parents are incredibly proud of you',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 10, 'Morality' => 5]
                        ],
                        [
                            'name' => 'Family Conflict',
                            'description' => 'A disagreement creates tension in the family',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -8, 'Isolation' => 3]
                        ],
                        [
                            'name' => 'Family Support',
                            'description' => 'Your family offers unwavering support',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => 8, 'Discipline' => 3]
                        ]
                    ];
                } elseif (in_array('sibling', $keywords) || in_array('brother', $keywords) || in_array('sister', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Sibling Bond',
                            'description' => 'You grow closer to your sibling',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 8, 'Morality' => 3]
                        ],
                        [
                            'name' => 'Sibling Rivalry',
                            'description' => 'Competition with your sibling intensifies',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -5, 'Burnout' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Family Reunion',
                            'description' => 'A joyful family gathering brings happiness',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 8, 'Morality' => 3]
                        ],
                        [
                            'name' => 'Family Trouble',
                            'description' => 'Family issues create stress',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -6, 'Isolation' => 3]
                        ],
                        [
                            'name' => 'Family Gift',
                            'description' => 'Your family gives you something special',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Happiness' => 6, 'Wealth' => 3]
                        ]
                    ];
                }
                break;
                
            case 'health':
                if (in_array('exercise', $keywords) || in_array('workout', $keywords) || in_array('gym', $keywords) || in_array('sport', $keywords) || in_array('run', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Peak Performance',
                            'description' => 'Your fitness reaches new heights',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Health' => 8, 'Strength' => 5]
                        ],
                        [
                            'name' => 'Injury',
                            'description' => 'You sustain an exercise-related injury',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -8, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'New Personal Best',
                            'description' => 'You achieve a new personal record',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Health' => 5, 'Reputation' => 3]
                        ]
                    ];
                } elseif (in_array('sick', $keywords) || in_array('ill', $keywords) || in_array('disease', $keywords) || in_array('doctor', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Quick Recovery',
                            'description' => 'You recover faster than expected',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 8, 'Happiness' => 5]
                        ],
                        [
                            'name' => 'Complication',
                            'description' => 'Unexpected complications arise',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Health' => -10, 'Burnout' => 5]
                        ]
                    ];
                } elseif (in_array('eat', $keywords) || in_array('food', $keywords) || in_array('meal', $keywords) || in_array('breakfast', $keywords) || in_array('lunch', $keywords) || in_array('dinner', $keywords) || in_array('cook', $keywords) || in_array('vegetable', $keywords) || in_array('fruit', $keywords) || in_array('meat', $keywords)) {
                    // Eating/Food related choices
                    $outcomes = [
                        [
                            'name' => 'Food Poisoning',
                            'description' => 'You accidentally ate something bad and get sick',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Health' => -10, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Extra Energy',
                            'description' => 'The meal gives you unexpected energy',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Health' => 5, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Delicious Surprise',
                            'description' => 'The food was even better than expected',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => 5, 'Reputation' => 2]
                        ],
                        [
                            'name' => 'Upset Stomach',
                            'description' => 'Something doesn\'t agree with you',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -4, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Nutritious Boost',
                            'description' => 'You feel the health benefits of good nutrition',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 6, 'Discipline' => 2]
                        ]
                    ];
                } elseif (in_array('sleep', $keywords) || in_array('nap', $keywords) || in_array('rest', $keywords) || in_array('bed', $keywords) || in_array('wake', $keywords) || in_array('sleeping', $keywords)) {
                    // Sleeping/Rest related choices
                    $outcomes = [
                        [
                            'name' => 'Overslept',
                            'description' => 'You slept too long and wasted precious time',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -3, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Fully Rested',
                            'description' => 'You wake up feeling completely refreshed',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 6, 'Happiness' => 5, 'Discipline' => 2]
                        ],
                        [
                            'name' => 'Vivid Dream',
                            'description' => 'You had an interesting dream that stays with you',
                            'type' => 'neutral',
                            'chance' => 15,
                            'stat_effects' => ['Creativity' => 3, 'Happiness' => 2]
                        ],
                        [
                            'name' => 'Nightmare',
                            'description' => 'A bad dream disturbs your rest',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Happiness' => -4, 'Burnout' => 2]
                        ],
                        [
                            'name' => 'Productive Rest',
                            'description' => 'You feel rejuvenated and ready for anything',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Health' => 4, 'Intelligence' => 2]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Health Boost',
                            'description' => 'Your health improves beyond expectations',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Health' => 6, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Health Setback',
                            'description' => 'Your health unexpectedly declines',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -6, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Medical Checkup',
                            'description' => 'A checkup reveals good news',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Health' => 4, 'Happiness' => 3]
                        ]
                    ];
                }
                break;
                
            case 'social':
                if (in_array('friend', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Best Friend',
                            'description' => 'You meet someone who becomes your best friend',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 10, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Friend Betrayal',
                            'description' => 'A friend lets you down badly',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Happiness' => -10, 'Isolation' => 5]
                        ],
                        [
                            'name' => 'Friend Network',
                            'description' => 'Your circle of friends expands',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => 6, 'Happiness' => 4]
                        ]
                    ];
                } elseif (in_array('party', $keywords) || in_array('social', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Popular',
                            'description' => 'Everyone at the party loves you',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 8, 'Happiness' => 5]
                        ],
                        [
                            'name' => 'Awkward Moment',
                            'description' => 'An embarrassing moment occurs',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => -5, 'Happiness' => -3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'New Connection',
                            'description' => 'You meet someone who becomes important',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 3]
                        ],
                        [
                            'name' => 'Social Rejection',
                            'description' => 'You face unexpected social difficulty',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -6, 'Isolation' => 4]
                        ],
                        [
                            'name' => 'Popularity Surge',
                            'description' => 'Everyone wants to be your friend',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => 8, 'Happiness' => 4]
                        ]
                    ];
                }
                break;
                
            case 'doctor':
                if (in_array('surgery', $keywords) || in_array('operation', $keywords) || in_array('hospital', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Successful Surgery',
                            'description' => 'The surgery goes perfectly',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 10, 'Wealth' => 5, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Complication',
                            'description' => 'Unexpected complications arise during surgery',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => -8, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Grateful Patient',
                            'description' => 'The patient and family express deep gratitude',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('diagnosis', $keywords) || in_array('checkup', $keywords) || in_array('examination', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Accurate Diagnosis',
                            'description' => 'You correctly identify the condition',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 6, 'Intelligence' => 3]
                        ],
                        [
                            'name' => 'Misdiagnosis',
                            'description' => 'The diagnosis proves to be incorrect',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => -5, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Learning Experience',
                            'description' => 'Even wrong diagnoses teach valuable lessons',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Intelligence' => 4]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Medical Recognition',
                            'description' => 'Your medical skills are recognized',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 8, 'Wealth' => 4]
                        ],
                        [
                            'name' => 'Difficult Patient',
                            'description' => 'A challenging patient case',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 4, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Career Growth',
                            'description' => 'Your medical career advances',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 6, 'Reputation' => 3]
                        ]
                    ];
                }
                break;
                
            case 'teacher':
                if (in_array('class', $keywords) || in_array('lecture', $keywords) || in_array('student', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Inspiring Lesson',
                            'description' => 'Your lesson inspires students',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 6, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Disruptive Class',
                            'description' => 'Students cause disruptions',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 4, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Student Success',
                            'description' => 'A student shows great improvement',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 5, 'Reputation' => 3]
                        ]
                    ];
                } elseif (in_array('exam', $keywords) || in_array('test', $keywords) || in_array('grading', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Perfect Grading',
                            'description' => 'Grading goes smoothly',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Discipline' => 4, 'Wealth' => 2]
                        ],
                        [
                            'name' => 'Plagiarism Incident',
                            'description' => 'Academic dishonesty discovered',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Extra Work',
                            'description' => 'More papers to grade than expected',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Burnout' => 5]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Teaching Excellence',
                            'description' => 'Your teaching methods excel',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 6, 'Intelligence' => 3]
                        ],
                        [
                            'name' => 'Budget Cuts',
                            'description' => 'School budget affects your resources',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -3, 'Wealth' => -2]
                        ],
                        [
                            'name' => 'Parent Praise',
                            'description' => 'Parents appreciate your work',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 5, 'Happiness' => 3]
                        ]
                    ];
                }
                break;
                
            case 'scientist':
                if (in_array('research', $keywords) || in_array('experiment', $keywords) || in_array('lab', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Breakthrough Discovery',
                            'description' => 'A major scientific breakthrough',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 10, 'Reputation' => 8]
                        ],
                        [
                            'name' => 'Failed Experiment',
                            'description' => 'The experiment yields unexpected results',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Burnout' => 4, 'Wealth' => -2]
                        ],
                        [
                            'name' => 'Grant Approval',
                            'description' => 'Research funding is approved',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 6, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('paper', $keywords) || in_array('publication', $keywords) || in_array('journal', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Published Paper',
                            'description' => 'Your paper gets published in a major journal',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 10, 'Intelligence' => 5]
                        ],
                        [
                            'name' => 'Peer Review Issues',
                            'description' => 'The peer review process is challenging',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 4]
                        ],
                        [
                            'name' => 'Citation Increase',
                            'description' => 'Your work gets cited frequently',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 6, 'Intelligence' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Scientific Recognition',
                            'description' => 'Your scientific contributions are recognized',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Intelligence' => 6, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Lab Accident',
                            'description' => 'A lab accident causes issues',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Health' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Collaboration Offer',
                            'description' => 'Other scientists want to collaborate',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 4, 'Intelligence' => 3]
                        ]
                    ];
                }
                break;
                
            case 'lawyer':
                if (in_array('court', $keywords) || in_array('trial', $keywords) || in_array('case', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Case Win',
                            'description' => 'You win the case decisively',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]
                        ],
                        [
                            'name' => 'Case Loss',
                            'description' => 'You lose the case',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => -6, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Settlement',
                            'description' => 'The case settles favorably',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 8, 'Reputation' => 3]
                        ]
                    ];
                } elseif (in_array('client', $keywords) || in_array('meeting', $keywords) || in_array('consultation', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'New Client',
                            'description' => 'A high-paying client hires you',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 4]
                        ],
                        [
                            'name' => 'Difficult Client',
                            'description' => 'The client is demanding and unreasonable',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 4, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Referral',
                            'description' => 'A client refers their friends',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 5, 'Reputation' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Legal Victory',
                            'description' => 'A legal victory boosts your career',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 6]
                        ],
                        [
                            'name' => 'Ethics Complaint',
                            'description' => 'An ethics complaint is filed',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => -8, 'Happiness' => -4]
                        ],
                        [
                            'name' => 'Partner Promotion',
                            'description' => 'You are promoted to partner',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 5]
                        ]
                    ];
                }
                break;
                
            case 'nurse':
                if (in_array('patient', $keywords) || in_array('care', $keywords) || in_array('hospital', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Patient Recovery',
                            'description' => 'Your care helps a patient recover faster',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 8, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Difficult Patient',
                            'description' => 'A challenging patient case leaves you exhausted',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 5, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Thankful Family',
                            'description' => 'The patient\'s family expresses deep gratitude',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('emergency', $keywords) || in_array('shift', $keywords) || in_array('夜班', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Life Saved',
                            'description' => 'Your quick action saves a patient\'s life',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 10, 'Happiness' => 8]
                        ],
                        [
                            'name' => 'Overwhelmed',
                            'description' => 'The emergency overwhelms you',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 6, 'Health' => -3]
                        ],
                        [
                            'name' => 'Team Recognition',
                            'description' => 'Your team work is recognized by management',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 5, 'Wealth' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Nursing Excellence',
                            'description' => 'Your dedication to patient care shines',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 6, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Shift Exhaustion',
                            'description' => 'Long shifts leave you exhausted',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Burnout' => 5, 'Health' => -3]
                        ],
                        [
                            'name' => 'Professional Growth',
                            'description' => 'You learn new medical techniques',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Intelligence' => 4, 'Reputation' => 2]
                        ]
                    ];
                }
                break;
                
            case 'soldier':
                if (in_array('combat', $keywords) || in_array('mission', $keywords) || in_array('battle', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Heroic Action',
                            'description' => 'Your bravery in combat is recognized',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 15, 'Strength' => 5]
                        ],
                        [
                            'name' => 'Combat Injury',
                            'description' => 'You sustain injuries in combat',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Health' => -15, 'Burnout' => 5]
                        ],
                        [
                            'name' => 'Medal Award',
                            'description' => 'You receive a military medal for valor',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => 12, 'Wealth' => 5]
                        ]
                    ];
                } elseif (in_array('training', $keywords) || in_array('drill', $keywords) || in_array('exercise', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Top Performer',
                            'description' => 'You excel in military training exercises',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Strength' => 6, 'Discipline' => 4]
                        ],
                        [
                            'name' => 'Training Injury',
                            'description' => 'A training accident causes injuries',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Health' => -8, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Promotion',
                            'description' => 'Your performance earns a promotion',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 8, 'Wealth' => 4]
                        ]
                    ];
                } elseif (in_array('deployment', $keywords) || in_array('overseas', $keywords) || in_array('abroad', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Safe Return',
                            'description' => 'You return from deployment safely',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 10, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Deployment Hardship',
                            'description' => 'Deployment takes a mental toll',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Burnout' => 8, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Foreign Connection',
                            'description' => 'You form meaningful connections abroad',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Military Honor',
                            'description' => 'Your service is honored by superiors',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 8, 'Discipline' => 4]
                        ],
                        [
                            'name' => 'Discipline Strike',
                            'description' => 'A discipline issue creates problems',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => -5, 'Discipline' => -3]
                        ],
                        [
                            'name' => 'Comradery',
                            'description' => 'You bond strongly with fellow soldiers',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 3]
                        ]
                    ];
                }
                break;
                
            case 'athlete':
                if (in_array('competition', $keywords) || in_array('game', $keywords) || in_array('championship', $keywords) || in_array('match', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Victory',
                            'description' => 'You win the competition decisively',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 12, 'Wealth' => 8, 'Happiness' => 5]
                        ],
                        [
                            'name' => 'Defeat',
                            'description' => 'You lose the competition',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => -5, 'Ego' => -3]
                        ],
                        [
                            'name' => 'Personal Best',
                            'description' => 'You achieve a new personal record',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Strength' => 6, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('training', $keywords) || in_array('practice', $keywords) || in_array('workout', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Breakthrough',
                            'description' => 'Your training yields major improvements',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Strength' => 8, 'Discipline' => 4]
                        ],
                        [
                            'name' => 'Overtraining',
                            'description' => 'Too much training leads to exhaustion',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -5, 'Burnout' => 4]
                        ],
                        [
                            'name' => 'Coach Praise',
                            'description' => 'Your coach recognizes your dedication',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 5, 'Discipline' => 3]
                        ]
                    ];
                } elseif (in_array('injury', $keywords) || in_array('recovery', $keywords) || in_array('rehab', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Full Recovery',
                            'description' => 'You recover fully from your injury',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 10, 'Happiness' => 6]
                        ],
                        [
                            'name' => 'Reinjury',
                            'description' => 'You suffer a setback in recovery',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -8, 'Burnout' => 4]
                        ],
                        [
                            'name' => 'Comeback Stronger',
                            'description' => 'You come back stronger than before',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Strength' => 5, 'Discipline' => 4]
                        ]
                    ];
                } elseif (in_array('endorsement', $keywords) || in_array('sponsor', $keywords) || in_array('contract', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Big Endorsement',
                            'description' => 'A major brand wants to sponsor you',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]
                        ],
                        [
                            'name' => 'Endorsement Lost',
                            'description' => 'A sponsor drops you',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Wealth' => -8, 'Reputation' => -4]
                        ],
                        [
                            'name' => 'Brand Deal',
                            'description' => 'You secure a profitable brand deal',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 5]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Peak Performance',
                            'description' => 'Your athletic performance reaches new heights',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Strength' => 6, 'Reputation' => 4]
                        ],
                        [
                            'name' => 'Slump',
                            'description' => 'You hit a performance slump',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Ego' => -4, 'Discipline' => -3]
                        ],
                        [
                            'name' => 'Fan Support',
                            'description' => 'Your fans show incredible support',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 6, 'Happiness' => 5]
                        ]
                    ];
                }
                break;
                
            case 'farmer':
                if (in_array('harvest', $keywords) || in_array('crop', $keywords) || in_array('farm', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Bountiful Harvest',
                            'description' => 'Your crops yield an exceptional harvest',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 15, 'Happiness' => 5]
                        ],
                        [
                            'name' => 'Failed Harvest',
                            'description' => 'Disease or pests destroy your crops',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => -12, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Market Success',
                            'description' => 'Your produce commands a premium price',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 3]
                        ]
                    ];
                } elseif (in_array('livestock', $keywords) || in_array('animal', $keywords) || in_array('cattle', $keywords) || in_array('cow', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Healthy Livestock',
                            'description' => 'Your animals thrive and multiply',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 10, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Animal Disease',
                            'description' => 'A disease affects your livestock',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -15, 'Happiness' => -6]
                        ],
                        [
                            'name' => 'Premium Stock',
                            'description' => 'You acquire high-quality breeding stock',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 8, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('weather', $keywords) || in_array('drought', $keywords) || in_array('rain', $keywords) || in_array('storm', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Perfect Weather',
                            'description' => 'Ideal weather conditions help your farm flourish',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 8, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Drought',
                            'description' => 'A drought devastates your crops',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -15, 'Health' => -3]
                        ],
                        [
                            'name' => 'Storm Damage',
                            'description' => 'A storm causes damage to your farm',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -8, 'Happiness' => -3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Farm Expansion',
                            'description' => 'You expand your farming operations',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 12, 'Reputation' => 4]
                        ],
                        [
                            'name' => 'Pest Infestation',
                            'description' => 'Pests cause damage to your farm',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -8, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Sustainable Practice',
                            'description' => 'Your farming methods improve sustainability',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 5, 'Morality' => 3]
                        ]
                    ];
                }
                break;
                
            case 'fisher':
                if (in_array('fishing', $keywords) || in_array('catch', $keywords) || in_array('fish', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Big Catch',
                            'description' => 'You catch a massive fish',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 12, 'Reputation' => 5, 'Luck' => 3]
                        ],
                        [
                            'name' => 'Empty Net',
                            'description' => 'You catch nothing today',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => -3, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Rare Species',
                            'description' => 'You catch a rare species worth money',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]
                        ]
                    ];
                } elseif (in_array('boat', $keywords) || in_array('vessel', $keywords) || in_array('ship', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'New Boat',
                            'description' => 'You acquire a better fishing boat',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 10, 'Luck' => 4]
                        ],
                        [
                            'name' => 'Boat Damage',
                            'description' => 'Your boat requires expensive repairs',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -10, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Upgrade Success',
                            'description' => 'Your boat upgrade improves catches',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 8, 'Luck' => 3]
                        ]
                    ];
                } elseif (in_array('storm', $keywords) || in_array('weather', $keywords) || in_array('sea', $keywords) || in_array('ocean', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Safe Return',
                            'description' => 'You navigate the rough seas safely',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 5, 'Luck' => 4]
                        ],
                        [
                            'name' => 'Storm at Sea',
                            'description' => 'A storm at sea puts you in danger',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Health' => -10, 'Wealth' => -5]
                        ],
                        [
                            'name' => 'Calm Waters',
                            'description' => 'Peaceful conditions lead to good catches',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 8, 'Happiness' => 4]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Daily Limit',
                            'description' => 'You reach your fishing limit quickly',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 8, 'Luck' => 3]
                        ],
                        [
                            'name' => 'Tough Day',
                            'description' => 'Fishing is tough today',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => -4, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'Sustainable Catch',
                            'description' => 'You practice sustainable fishing',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 4, 'Morality' => 3]
                        ]
                    ];
                }
                break;
                
            case 'artist':
                if (in_array('painting', $keywords) || in_array('canvas', $keywords) || in_array('art', $keywords) || in_array('gallery', $keywords) || in_array('exhibition', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Art Sold',
                            'description' => 'Your artwork sells for a good price',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 12, 'Reputation' => 6]
                        ],
                        [
                            'name' => 'Art Critics',
                            'description' => 'Art critics pan your work',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => -6, 'Happiness' => -4]
                        ],
                        [
                            'name' => 'Breakthrough Piece',
                            'description' => 'You create a masterpiece',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 10, 'Creativity' => 5]
                        ]
                    ];
                } elseif (in_array('creative', $keywords) || in_array('inspiration', $keywords) || in_array('idea', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Creative Surge',
                            'description' => 'Inspiration flows effortlessly',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Creativity' => 8, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Creative Block',
                            'description' => 'You struggle to create anything',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Creativity' => -5, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'New Style',
                            'description' => 'You develop a new artistic style',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Creativity' => 6, 'Reputation' => 4]
                        ]
                    ];
                } elseif (in_array('commission', $keywords) || in_array('client', $keywords) || in_array('sale', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Commission Accepted',
                            'description' => 'A client commissions your work',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Difficult Client',
                            'description' => 'The client is impossible to please',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 5, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Repeat Customer',
                            'description' => 'A satisfied client returns for more',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 10, 'Reputation' => 4]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Artistic Recognition',
                            'description' => 'Your art gets recognized',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Reputation' => 8, 'Creativity' => 4]
                        ],
                        [
                            'name' => 'Financial Struggle',
                            'description' => 'Making a living as an artist is hard',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => -6, 'Happiness' => -4]
                        ],
                        [
                            'name' => 'Creative Community',
                            'description' => 'You connect with other artists',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 5, 'Creativity' => 3]
                        ]
                    ];
                }
                break;
                
            case 'writer':
                if (in_array('book', $keywords) || in_array('novel', $keywords) || in_array('publish', $keywords)) {
                    $outcomes = [
                        ['name' => 'Book Published', 'description' => 'Your book gets published', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 12, 'Reputation' => 8]],
                        ['name' => 'Rejection', 'description' => 'Publishers reject your manuscript', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Happiness' => -5, 'Burnout' => 3]],
                        ['name' => 'Bestseller', 'description' => 'Your book becomes a bestseller', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Wealth' => 20, 'Reputation' => 12]]
                    ];
                } elseif (in_array('writer block', $keywords) || in_array('block', $keywords) || in_array('inspiration', $keywords)) {
                    $outcomes = [
                        ['name' => 'Inspiration Strikes', 'description' => 'You overcome writer block', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Creativity' => 6, 'Happiness' => 4]],
                        ['name' => 'Writer Block', 'description' => 'You cannot write anything', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Burnout' => 5, 'Happiness' => -3]],
                        ['name' => 'Breakthrough', 'description' => 'You write your best work yet', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Creativity' => 8, 'Reputation' => 5]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Productive Day', 'description' => 'You write many pages today', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Creativity' => 5, 'Discipline' => 3]],
                        ['name' => 'Procrastination', 'description' => 'You waste the day instead of writing', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Discipline' => -4, 'Happiness' => -2]],
                        ['name' => 'Fan Mail', 'description' => 'Readers express love for your work', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Happiness' => 6, 'Reputation' => 3]]
                    ];
                }
                break;
                
            case 'designer':
                if (in_array('project', $keywords) || in_array('client', $keywords)) {
                    $outcomes = [
                        ['name' => 'Project Success', 'description' => 'Your design project is a success', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 10, 'Reputation' => 6]],
                        ['name' => 'Design Revision', 'description' => 'Client requests endless revisions', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Burnout' => 5, 'Happiness' => -3]],
                        ['name' => 'Portfolio Boost', 'description' => 'The project enhances your portfolio', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Creativity' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Creative Solution', 'description' => 'You find an innovative design solution', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Creativity' => 6, 'Reputation' => 4]],
                        ['name' => 'Software Issue', 'description' => 'Design software crashes and loses work', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Burnout' => 4, 'Discipline' => -2]],
                        ['name' => 'Happy Client', 'description' => 'The client loves your work', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 8, 'Happiness' => 4]]
                    ];
                }
                break;
                
            case 'musician':
                if (in_array('concert', $keywords) || in_array('performance', $keywords) || in_array('show', $keywords) || in_array('gig', $keywords)) {
                    $outcomes = [
                        ['name' => 'Crowd Goes Wild', 'description' => 'The audience loves your performance', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Happiness' => 6]],
                        ['name' => 'Bad Performance', 'description' => 'Your performance falls flat', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -6, 'Ego' => -3]],
                        ['name' => 'Standing Ovation', 'description' => 'You receive a standing ovation', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 12, 'Happiness' => 8]]
                    ];
                } elseif (in_array('recording', $keywords) || in_array('album', $keywords) || in_array('song', $keywords) || in_array('music', $keywords)) {
                    $outcomes = [
                        ['name' => 'Hit Single', 'description' => 'Your song becomes a hit', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 10]],
                        ['name' => 'Poor Sales', 'description' => 'Your album does not sell well', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -5, 'Happiness' => -4]],
                        ['name' => 'Viral Success', 'description' => 'Your music goes viral online', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Reputation' => 15, 'Wealth' => 8]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Musical Breakthrough', 'description' => 'You master a new technique', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Creativity' => 6, 'Intelligence' => 3]],
                        ['name' => 'Instrument Trouble', 'description' => 'Your instrument needs expensive repairs', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -6]],
                        ['name' => 'Collaboration', 'description' => 'You collaborate with other musicians', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Creativity' => 5, 'Reputation' => 4]]
                    ];
                }
                break;
                
            case 'chef':
                if (in_array('kitchen', $keywords) || in_array('cooking', $keywords) || in_array('restaurant', $keywords)) {
                    $outcomes = [
                        ['name' => 'Signature Dish', 'description' => 'You create a signature dish that becomes famous', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Wealth' => 8]],
                        ['name' => 'Kitchen Disaster', 'description' => 'A kitchen disaster ruins service', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -8, 'Wealth' => -5]],
                        ['name' => 'Michelin Star', 'description' => 'You receive a Michelin star', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Reputation' => 15, 'Wealth' => 12]]
                    ];
                } elseif (in_array('menu', $keywords) || in_array('dish', $keywords) || in_array('food', $keywords)) {
                    $outcomes = [
                        ['name' => 'Dish Success', 'description' => 'Customers love your new dish', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 8, 'Reputation' => 5]],
                        ['name' => 'Food Poisoning', 'description' => 'A food safety issue occurs', 'type' => 'negative', 'chance' => 15, 'stat_effects' => ['Reputation' => -10, 'Wealth' => -8]],
                        ['name' => 'Recipe Viral', 'description' => 'Your recipe goes viral online', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Wealth' => 6]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Perfect Service', 'description' => 'Restaurant service goes perfectly', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 6, 'Reputation' => 4]],
                        ['name' => 'Staff Conflict', 'description' => 'Kitchen staff conflict affects service', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Happiness' => -4, 'Burnout' => 3]],
                        ['name' => 'Chef Recognition', 'description' => 'Other chefs recognize your talent', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 6, 'Creativity' => 3]]
                    ];
                }
                break;
                
            case 'actor':
                if (in_array('audition', $keywords) || in_array('role', $keywords) || in_array('casting', $keywords)) {
                    $outcomes = [
                        ['name' => 'Role Landed', 'description' => 'You get the role', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Wealth' => 8]],
                        ['name' => 'Audition Failed', 'description' => 'You do not get the role', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Happiness' => -4, 'Ego' => -3]],
                        ['name' => 'Callback', 'description' => 'You get called back for another audition', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 5, 'Happiness' => 3]]
                    ];
                } elseif (in_array('film', $keywords) || in_array('movie', $keywords) || in_array('premiere', $keywords)) {
                    $outcomes = [
                        ['name' => 'Box Office Hit', 'description' => 'Your film is a box office hit', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 12]],
                        ['name' => 'Box Office Flop', 'description' => 'Your film flops at the box office', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -8, 'Ego' => -5]],
                        ['name' => 'Award Nomination', 'description' => 'You receive an award nomination', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Reputation' => 15, 'Happiness' => 6]]
                    ];
                } elseif (in_array('scandal', $keywords) || in_array('controversy', $keywords)) {
                    $outcomes = [
                        ['name' => 'Scandal Survived', 'description' => 'You weather the scandal', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 5]],
                        ['name' => 'Career Damage', 'description' => 'The scandal damages your career', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Reputation' => -12, 'Wealth' => -8]],
                        ['name' => 'Public Support', 'description' => 'The public supports you', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Happiness' => 5]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Acting Breakthrough', 'description' => 'Your acting improves dramatically', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Creativity' => 6, 'Reputation' => 5]],
                        ['name' => 'Typecast', 'description' => 'You get typecast in certain roles', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -3, 'Ego' => -2]],
                        ['name' => 'Fan Following', 'description' => 'You gain a dedicated fan following', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Happiness' => 6]]
                    ];
                }
                break;
                
            case 'journalist':
                if (in_array('article', $keywords) || in_array('story', $keywords) || in_array('news', $keywords) || in_array('report', $keywords)) {
                    $outcomes = [
                        ['name' => 'Breaking News', 'description' => 'You break a major story', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 12, 'Wealth' => 6]],
                        ['name' => 'Story Killed', 'description' => 'Your story gets killed by editors', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Happiness' => -4, 'Burnout' => 2]],
                        ['name' => 'Viral Article', 'description' => 'Your article goes viral', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 10, 'Wealth' => 5]]
                    ];
                } elseif (in_array('interview', $keywords) || in_array('source', $keywords)) {
                    $outcomes = [
                        ['name' => 'Exclusive Interview', 'description' => 'You get an exclusive interview', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 8, 'Wealth' => 5]],
                        ['name' => 'Source Backfires', 'description' => 'Your source provides bad information', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -6, 'Happiness' => -3]],
                        ['name' => 'Insightful Interview', 'description' => 'Your interview provides great insights', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Intelligence' => 5, 'Reputation' => 4]]
                    ];
                } elseif (in_array('investigation', $keywords) || in_array('expose', $keywords)) {
                    $outcomes = [
                        ['name' => 'Expose Success', 'description' => 'Your investigation exposes the truth', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 15, 'Morality' => 8]],
                        ['name' => 'Legal Threat', 'description' => 'You face legal threats', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -5, 'Wealth' => -8]],
                        ['name' => 'Pulitzer Prize', 'description' => 'You win a journalism award', 'type' => 'positive', 'chance' => 15, 'stat_effects' => ['Reputation' => 20, 'Wealth' => 15]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Editorial Praise', 'description' => 'Your work is praised by editors', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 6, 'Intelligence' => 3]],
                        ['name' => 'Missed Deadline', 'description' => 'You miss a deadline', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -4, 'Discipline' => -3]],
                        ['name' => 'Feature Story', 'description' => 'You get assigned a feature story', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 5, 'Creativity' => 3]]
                    ];
                }
                break;
                
            case 'business owner':
                if (in_array('business', $keywords) || in_array('company', $keywords) || in_array('expansion', $keywords)) {
                    $outcomes = [
                        ['name' => 'Business Expansion', 'description' => 'Your business expands successfully', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]],
                        ['name' => 'Competitor Threat', 'description' => 'A competitor takes market share', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -10, 'Happiness' => -4]],
                        ['name' => 'Market Leader', 'description' => 'You become a market leader', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Reputation' => 12, 'Wealth' => 10]]
                    ];
                } elseif (in_array('employee', $keywords) || in_array('staff', $keywords) || in_array('team', $keywords)) {
                    $outcomes = [
                        ['name' => 'Great Hire', 'description' => 'You hire an exceptional employee', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 8, 'Reputation' => 5]],
                        ['name' => 'Employee Lawsuit', 'description' => 'An employee files a lawsuit', 'type' => 'negative', 'chance' => 15, 'stat_effects' => ['Wealth' => -12, 'Reputation' => -8]],
                        ['name' => 'Team Morale', 'description' => 'Your team morale is high', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Happiness' => 5, 'Discipline' => 3]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Profit Increase', 'description' => 'Your profits increase significantly', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 12, 'Happiness' => 5]],
                        ['name' => 'Cash Flow Problem', 'description' => 'You face a cash flow crisis', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -10, 'Burnout' => 4]],
                        ['name' => 'Award Recognition', 'description' => 'Your business wins an award', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 10, 'Wealth' => 5]]
                    ];
                }
                break;
                
            case 'investor':
                if (in_array('investment', $keywords) || in_array('portfolio', $keywords) || in_array('stock', $keywords)) {
                    $outcomes = [
                        ['name' => 'Great Investment', 'description' => 'Your investment pays off big', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 20, 'Luck' => 5]],
                        ['name' => 'Investment Loss', 'description' => 'You lose money on an investment', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -15, 'Happiness' => -5]],
                        ['name' => 'Market Boom', 'description' => 'The market booms and you profit', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 15, 'Luck' => 4]]
                    ];
                } elseif (in_array('market', $keywords) || in_array('crash', $keywords) || in_array('recession', $keywords)) {
                    $outcomes = [
                        ['name' => 'Market Crash Survival', 'description' => 'You survive the market crash', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 8, 'Luck' => 6]],
                        ['name' => 'Market Crash', 'description' => 'The market crashes and you lose', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Wealth' => -20, 'Reputation' => -5]],
                        ['name' => 'Opportunity', 'description' => 'You find opportunity in the crash', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Wealth' => 10, 'Intelligence' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Successful Deal', 'description' => 'You close a successful deal', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 6]],
                        ['name' => 'Failed Deal', 'description' => 'A deal falls through', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -8, 'Happiness' => -3]],
                        ['name' => 'New Connection', 'description' => 'You connect with a valuable investor', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 6, 'Wealth' => 5]]
                    ];
                }
                break;
                
            case 'entrepreneur':
                if (in_array('startup', $keywords) || in_array('venture', $keywords) || in_array('launch', $keywords)) {
                    $outcomes = [
                        ['name' => 'Startup Success', 'description' => 'Your startup takes off', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 20, 'Reputation' => 10]],
                        ['name' => 'Startup Failure', 'description' => 'Your startup fails', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Wealth' => -15, 'Happiness' => -8]],
                        ['name' => 'Funding Secured', 'description' => 'You secure major funding', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]]
                    ];
                } elseif (in_array('pitch', $keywords) || in_array('investor', $keywords) || in_array('funding', $keywords)) {
                    $outcomes = [
                        ['name' => 'Pitch Success', 'description' => 'Investors love your pitch', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]],
                        ['name' => 'Pitch Rejected', 'description' => 'Investors reject your pitch', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Happiness' => -5, 'Burnout' => 3]],
                        ['name' => 'Angel Investor', 'description' => 'An angel investor backs you', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 12, 'Reputation' => 6]]
                    ];
                } elseif (in_array('pivot', $keywords) || in_array('change', $keywords)) {
                    $outcomes = [
                        ['name' => 'Pivot Success', 'description' => 'Your pivot leads to success', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 10, 'Intelligence' => 5]],
                        ['name' => 'Pivot Fails', 'description' => 'The pivot does not work', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -8, 'Happiness' => -4]],
                        ['name' => 'New Direction', 'description' => 'You find a better direction', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Creativity' => 5, 'Reputation' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Innovation', 'description' => 'You create an innovative product', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Creativity' => 5]],
                        ['name' => 'Burnout', 'description' => 'Entrepreneurial stress leads to burnout', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Burnout' => 8, 'Health' => -4]],
                        ['name' => 'Media Attention', 'description' => 'Your startup gets media attention', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Wealth' => 4]]
                    ];
                }
                break;
                
            case 'politician':
                if (in_array('election', $keywords) || in_array('campaign', $keywords) || in_array('vote', $keywords)) {
                    $outcomes = [
                        ['name' => 'Election Win', 'description' => 'You win the election', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 15, 'Wealth' => 10]],
                        ['name' => 'Election Loss', 'description' => 'You lose the election', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Reputation' => -8, 'Happiness' => -6]],
                        ['name' => 'Landslide Victory', 'description' => 'You win by a landslide', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Reputation' => 20, 'Wealth' => 8]]
                    ];
                } elseif (in_array('policy', $keywords) || in_array('law', $keywords) || in_array('bill', $keywords)) {
                    $outcomes = [
                        ['name' => 'Policy Success', 'description' => 'Your policy is implemented', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 12, 'Morality' => 5]],
                        ['name' => 'Policy Blocked', 'description' => 'Your policy is blocked', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -5, 'Happiness' => -3]],
                        ['name' => 'Bipartisan Support', 'description' => 'Both parties support your policy', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 10, 'Morality' => 4]]
                    ];
                } elseif (in_array('scandal', $keywords) || in_array('controversy', $keywords)) {
                    $outcomes = [
                        ['name' => 'Scandal Survived', 'description' => 'You survive the scandal', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 5]],
                        ['name' => 'Resignation', 'description' => 'You must resign due to scandal', 'type' => 'negative', 'chance' => 15, 'stat_effects' => ['Reputation' => -20, 'Wealth' => -10]],
                        ['name' => 'Public Forgiveness', 'description' => 'The public forgives you', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Happiness' => 5]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Public Support', 'description' => 'Public support grows', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Happiness' => 4]],
                        ['name' => 'Criticism', 'description' => 'You face heavy criticism', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -6, 'Happiness' => -3]],
                        ['name' => 'Media Coverage', 'description' => 'Positive media coverage', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Charisma' => 3]]
                    ];
                }
                break;
                
            case 'performer':
                if (in_array('show', $keywords) || in_array('stage', $keywords) || in_array('performance', $keywords)) {
                    $outcomes = [
                        ['name' => 'Standing Ovation', 'description' => 'The audience gives you a standing ovation', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Happiness' => 6]],
                        ['name' => 'Booed', 'description' => 'The audience boos you', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Reputation' => -8, 'Ego' => -5]],
                        ['name' => 'Encore', 'description' => 'The audience demands an encore', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Happiness' => 5]]
                    ];
                } elseif (in_array('fan', $keywords) || in_array('audience', $keywords)) {
                    $outcomes = [
                        ['name' => 'Fan Love', 'description' => 'Fans show you overwhelming love', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Happiness' => 10, 'Reputation' => 6]],
                        ['name' => 'Stalker', 'description' => 'A fan becomes obsessive', 'type' => 'negative', 'chance' => 15, 'stat_effects' => ['Happiness' => -6, 'Isolation' => 4]],
                        ['name' => 'Fan Club Growth', 'description' => 'Your fan club grows', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Wealth' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Career Milestone', 'description' => 'You reach a career milestone', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Wealth' => 6]],
                        ['name' => 'Career Slump', 'description' => 'Your career hits a slump', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -5, 'Happiness' => -4]],
                        ['name' => 'Award Win', 'description' => 'You win a performance award', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 12, 'Happiness' => 6]]
                    ];
                }
                break;
                
            case 'salesperson':
                if (in_array('sale', $keywords) || in_array('deal', $keywords) || in_array('client', $keywords)) {
                    $outcomes = [
                        ['name' => 'Big Sale', 'description' => 'You close a big sale', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 12, 'Reputation' => 6]],
                        ['name' => 'Sale Lost', 'description' => 'You lose a sale', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -5, 'Happiness' => -3]],
                        ['name' => 'Commission', 'description' => 'You earn a big commission', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 10, 'Happiness' => 4]]
                    ];
                } elseif (in_array('quota', $keywords) || in_array('target', $keywords)) {
                    $outcomes = [
                        ['name' => 'Quota Met', 'description' => 'You meet your sales quota', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 8, 'Reputation' => 5]],
                        ['name' => 'Quota Missed', 'description' => 'You miss your sales quota', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -6, 'Burnout' => 4]],
                        ['name' => 'Overachiever', 'description' => 'You exceed your quota significantly', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'New Client', 'description' => 'You acquire a new client', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 8, 'Reputation' => 4]],
                        ['name' => 'Rejection', 'description' => 'You face repeated rejection', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Burnout' => 4, 'Happiness' => -3]],
                        ['name' => 'Referral', 'description' => 'A client refers you to others', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 6, 'Reputation' => 5]]
                    ];
                }
                break;
                
            case 'community leader':
                if (in_array('community', $keywords) || in_array('project', $keywords) || in_array('volunteer', $keywords)) {
                    $outcomes = [
                        ['name' => 'Project Success', 'description' => 'Your community project succeeds', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Morality' => 5]],
                        ['name' => 'Funding Cut', 'description' => 'Project funding gets cut', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -5, 'Happiness' => -3]],
                        ['name' => 'Community Impact', 'description' => 'Your project makes real impact', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 12, 'Morality' => 6]]
                    ];
                } elseif (in_array('conflict', $keywords) || in_array('dispute', $keywords)) {
                    $outcomes = [
                        ['name' => 'Conflict Resolution', 'description' => 'You resolve the conflict', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 8, 'Morality' => 4]],
                        ['name' => 'Community Split', 'description' => 'The community becomes divided', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -8, 'Happiness' => -4]],
                        ['name' => 'Mediation Success', 'description' => 'Your mediation is successful', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 10, 'Charisma' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Community Recognition', 'description' => 'The community recognizes your work', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Happiness' => 5]],
                        ['name' => 'Burnout', 'description' => 'Community work becomes overwhelming', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Burnout' => 6, 'Happiness' => -3]],
                        ['name' => 'Volunteer Growth', 'description' => 'More volunteers join your cause', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Morality' => 4]]
                    ];
                }
                break;
                
            case 'priest':
                if (in_array('service', $keywords) || in_array('sermon', $keywords) || in_array('worship', $keywords)) {
                    $outcomes = [
                        ['name' => 'Inspiring Service', 'description' => 'Your service inspires the congregation', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Morality' => 5]],
                        ['name' => 'Low Attendance', 'description' => 'Few people attend service', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Happiness' => -4, 'Reputation' => -3]],
                        ['name' => 'Spiritual Awakening', 'description' => 'A spiritual awakening occurs', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Morality' => 8, 'Happiness' => 5]]
                    ];
                } elseif (in_array('faith', $keywords) || in_array('crisis', $keywords) || in_array('doubt', $keywords)) {
                    $outcomes = [
                        ['name' => 'Faith Strengthened', 'description' => 'Your faith is strengthened', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Morality' => 8, 'Happiness' => 5]],
                        ['name' => 'Faith Crisis', 'description' => 'You experience a faith crisis', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Morality' => -6, 'Happiness' => -5]],
                        ['name' => 'Divine Sign', 'description' => 'You receive what you believe is a divine sign', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Morality' => 6, 'Luck' => 4]]
                    ];
                } elseif (in_array('outreach', $keywords) || in_array('help', $keywords) || in_array('charity', $keywords)) {
                    $outcomes = [
                        ['name' => 'Charity Success', 'description' => 'Your charity work helps many', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Morality' => 8]],
                        ['name' => 'Scandal', 'description' => 'A scandal affects your organization', 'type' => 'negative', 'chance' => 15, 'stat_effects' => ['Reputation' => -12, 'Happiness' => -6]],
                        ['name' => 'New Believers', 'description' => 'New people join your faith', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 8, 'Morality' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Congregation Growth', 'description' => 'Your congregation grows', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 8, 'Happiness' => 4]],
                        ['name' => 'Budget Issues', 'description' => 'Church budget faces problems', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -5, 'Happiness' => -3]],
                        ['name' => 'Spiritual Guidance', 'description' => 'You provide effective spiritual guidance', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Morality' => 6, 'Reputation' => 5]]
                    ];
                }
                break;
                
            case 'philosopher':
                if (in_array('philosophy', $keywords) || in_array('writing', $keywords) || in_array('book', $keywords)) {
                    $outcomes = [
                        ['name' => 'Published Work', 'description' => 'Your philosophical work gets published', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Intelligence' => 5]],
                        ['name' => 'Criticism', 'description' => 'Your ideas face heavy criticism', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -6, 'Happiness' => -4]],
                        ['name' => 'Academic Recognition', 'description' => 'Academics recognize your work', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 12, 'Intelligence' => 4]]
                    ];
                } elseif (in_array('debate', $keywords) || in_array('argument', $keywords)) {
                    $outcomes = [
                        ['name' => 'Debate Win', 'description' => 'You win the debate', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Reputation' => 10, 'Intelligence' => 4]],
                        ['name' => 'Debate Loss', 'description' => 'You lose the debate', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Reputation' => -5, 'Ego' => -3]],
                        ['name' => 'Mind Changed', 'description' => 'Your view changes through debate', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Intelligence' => 6, 'Morality' => 3]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Insight', 'description' => 'You have a profound insight', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Intelligence' => 8, 'Creativity' => 4]],
                        ['name' => 'Writer Block', 'description' => 'You cannot express your ideas', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Burnout' => 4, 'Happiness' => -3]],
                        ['name' => 'Teaching', 'description' => 'You inspire students with your ideas', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Reputation' => 6, 'Happiness' => 4]]
                    ];
                }
                break;
                
            case 'gambler':
                if (in_array('bet', $keywords) || in_array('gamble', $keywords) || in_array('risk', $keywords)) {
                    $outcomes = [
                        ['name' => 'Big Win', 'description' => 'You win big', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 20, 'Luck' => 8]],
                        ['name' => 'Big Loss', 'description' => 'You lose big', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Wealth' => -15, 'Happiness' => -6]],
                        ['name' => 'Lucky Break', 'description' => 'Fortune smiles on you', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 10, 'Luck' => 5]]
                    ];
                } elseif (in_array('luck', $keywords) || in_array('streak', $keywords)) {
                    $outcomes = [
                        ['name' => 'Winning Streak', 'description' => 'You have a winning streak', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15, 'Luck' => 6]],
                        ['name' => 'Losing Streak', 'description' => 'You have a losing streak', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Wealth' => -12, 'Happiness' => -5]],
                        ['name' => 'Comeback', 'description' => 'You make a comeback', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 8, 'Luck' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Fortune', 'description' => 'Lady luck is on your side', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Luck' => 10, 'Wealth' => 5]],
                        ['name' => 'Misfortune', 'description' => 'Bad luck strikes', 'type' => 'negative', 'chance' => 30, 'stat_effects' => ['Wealth' => -8, 'Luck' => -4]],
                        ['name' => 'High Roller', 'description' => 'You play with high rollers', 'type' => 'positive', 'chance' => 20, 'stat_effects' => ['Wealth' => 12, 'Reputation' => 4]]
                    ];
                }
                break;
                
            case 'casino owner':
                if (in_array('casino', $keywords) || in_array('gambling', $keywords)) {
                    $outcomes = [
                        ['name' => 'Profitable Night', 'description' => 'The casino has a profitable night', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 5]],
                        ['name' => 'Cheater Caught', 'description' => 'A cheater is caught', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 8, 'Reputation' => 6]],
                        ['name' => 'Jackpot Winner', 'description' => 'A player hits the jackpot', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -15, 'Reputation' => 3]]
                    ];
                } elseif (in_array('high roller', $keywords) || in_array('vip', $keywords)) {
                    $outcomes = [
                        ['name' => 'VIP Win', 'description' => 'A VIP player wins big', 'type' => 'negative', 'chance' => 25, 'stat_effects' => ['Wealth' => -12]],
                        ['name' => 'VIP Loss', 'description' => 'A VIP player loses', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 15]],
                        ['name' => 'Loyal Customer', 'description' => 'A high roller becomes loyal', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 10, 'Reputation' => 4]]
                    ];
                } else {
                    $outcomes = [
                        ['name' => 'Business Boom', 'description' => 'Casino business is booming', 'type' => 'positive', 'chance' => 30, 'stat_effects' => ['Wealth' => 12, 'Reputation' => 5]],
                        ['name' => 'Regulatory Fine', 'description' => 'You face regulatory fines', 'type' => 'negative', 'chance' => 20, 'stat_effects' => ['Wealth' => -10, 'Reputation' => -6]],
                        ['name' => 'Expansion', 'description' => 'You expand your casino', 'type' => 'positive', 'chance' => 25, 'stat_effects' => ['Wealth' => 15, 'Reputation' => 6]]
                    ];
                }
                break;
                
            case 'profession':
                // Profession-specific outcomes based on profession name keywords
                if (in_array('medical', $keywords) || in_array('doctor', $keywords) || in_array('nurse', $keywords) || in_array('health', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Medical Breakthrough',
                            'description' => 'Your medical expertise saves a life',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 10, 'Wealth' => 5]
                        ],
                        [
                            'name' => 'Medical Malpractice',
                            'description' => 'A medical mistake causes complications',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Reputation' => -10, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Grateful Patient',
                            'description' => 'A patient expresses deep gratitude',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 3]
                        ]
                    ];
                } elseif (in_array('legal', $keywords) || in_array('lawyer', $keywords) || in_array('court', $keywords) || in_array('judge', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Case Win',
                            'description' => 'You win a major case',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 8]
                        ],
                        [
                            'name' => 'Case Loss',
                            'description' => 'You lose an important case',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => -6, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Client Praise',
                            'description' => 'Your client is extremely satisfied',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 5, 'Happiness' => 4]
                        ]
                    ];
                } elseif (in_array('tech', $keywords) || in_array('engineer', $keywords) || in_array('developer', $keywords) || in_array('code', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Innovation',
                            'description' => 'You create something revolutionary',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 8, 'Wealth' => 10]
                        ],
                        [
                            'name' => 'Bug Crisis',
                            'description' => 'A critical bug causes major issues',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Burnout' => 6, 'Reputation' => -4]
                        ],
                        [
                            'name' => 'Tech Recognition',
                            'description' => 'Your technical skills are recognized',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 6, 'Intelligence' => 4]
                        ]
                    ];
                } elseif (in_array('business', $keywords) || in_array('entrepreneur', $keywords) || in_array('company', $keywords) || in_array('startup', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Business Success',
                            'description' => 'Your business venture pays off',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 15, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Business Failure',
                            'description' => 'Your business faces major setbacks',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -10, 'Happiness' => -5]
                        ],
                        [
                            'name' => 'Investor Interest',
                            'description' => 'Investors show interest in your venture',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 8, 'Reputation' => 4]
                        ]
                    ];
                } else {
                    // Generic profession outcomes
                    $outcomes = [
                        [
                            'name' => 'Career Advancement',
                            'description' => 'Your professional skills lead to advancement',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Wealth' => 8, 'Reputation' => 5]
                        ],
                        [
                            'name' => 'Professional Setback',
                            'description' => 'A professional challenge creates obstacles',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -4, 'Reputation' => -3]
                        ],
                        [
                            'name' => 'Networking Success',
                            'description' => 'You make valuable professional connections',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Reputation' => 6, 'Wealth' => 3]
                        ]
                    ];
                }
                break;
                
            case 'culture':
                if (in_array('festival', $keywords) || in_array('celebration', $keywords) || in_array('holiday', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Cultural Enrichment',
                            'description' => 'You deeply connect with your heritage',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 8, 'Morality' => 3]
                        ],
                        [
                            'name' => 'Tradition Lost',
                            'description' => 'The celebration feels empty this year',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -4, 'Morality' => -2]
                        ],
                        [
                            'name' => 'New Memories',
                            'description' => 'You create lasting memories with loved ones',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 2]
                        ]
                    ];
                } elseif (in_array('art', $keywords) || in_array('museum', $keywords) || in_array('music', $keywords) || in_array('dance', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Artistic Inspiration',
                            'description' => 'You feel deeply inspired by the cultural experience',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Intelligence' => 5, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Cultural Appreciation',
                            'description' => 'You gain a new appreciation for the arts',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 3, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Disconnected',
                            'description' => 'You fail to connect with the cultural experience',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Happiness' => -2]
                        ]
                    ];
                } elseif (in_array('food', $keywords) || in_array('cuisine', $keywords) || in_array('restaurant', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Culinary Delight',
                            'description' => 'The food exceeds all expectations',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 3, 'Happiness' => 5]
                        ],
                        [
                            'name' => 'Food Poisoning',
                            'description' => 'You get sick from the food',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Health' => -6, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'New Favorite',
                            'description' => 'You discover a new favorite dish',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 4, 'Wealth' => 1]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Cultural Experience',
                            'description' => 'You have a meaningful cultural experience',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 5, 'Morality' => 2]
                        ],
                        [
                            'name' => 'Cultural Misunderstanding',
                            'description' => 'An awkward cultural misunderstanding occurs',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Reputation' => -3, 'Happiness' => -2]
                        ],
                        [
                            'name' => 'New Connection',
                            'description' => 'You bond with someone over shared culture',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => 4, 'Reputation' => 2]
                        ]
                    ];
                }
                break;
                
            case 'skill':
                if (in_array('practice', $keywords) || in_array('train', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Skill Breakthrough',
                            'description' => 'You suddenly master a difficult technique',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 6, 'Discipline' => 4]
                        ],
                        [
                            'name' => 'Skill Plateau',
                            'description' => 'You struggle to improve further',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Mastery Insight',
                            'description' => 'You gain insight into mastering the skill',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 8, 'Discipline' => 3]
                        ]
                    ];
                } elseif (in_array('hobby', $keywords) || in_array('talent', $keywords)) {
                    $outcomes = [
                        [
                            'name' => 'Talent Discovery',
                            'description' => 'You discover a hidden talent',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 6, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Skill Difficulty',
                            'description' => 'The skill proves harder to learn',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Natural Ability',
                            'description' => 'Your natural ability shines through',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 5, 'Happiness' => 3]
                        ]
                    ];
                } else {
                    $outcomes = [
                        [
                            'name' => 'Skill Improvement',
                            'description' => 'Your skills improve faster than expected',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 5, 'Discipline' => 3]
                        ],
                        [
                            'name' => 'Plateau',
                            'description' => 'You hit a plateau in skill development',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -3, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Inspiration',
                            'description' => 'You find inspiration to improve',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Intelligence' => 4, 'Happiness' => 3]
                        ]
                    ];
                }
                break;
                
            case 'random':
            default:
                // Handle additional daily action keywords - shopping, cleaning, entertainment, traveling, etc.
                if (in_array('shop', $keywords) || in_array('buy', $keywords) || in_array('market', $keywords) || in_array('store', $keywords) || in_array('purchase', $keywords)) {
                    // Shopping related choices
                    $outcomes = [
                        [
                            'name' => 'Great Find',
                            'description' => 'You find exactly what you were looking for at a great price',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => 5, 'Wealth' => 3]
                        ],
                        [
                            'name' => 'Buyer\'s Remorse',
                            'description' => 'You spend more than you wanted to',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Wealth' => -5, 'Happiness' => -3]
                        ],
                        [
                            'name' => 'Perfect Match',
                            'description' => 'The item is even better than expected',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 2]
                        ],
                        [
                            'name' => 'Scam',
                            'description' => 'You realize you were scammed',
                            'type' => 'negative',
                            'chance' => 10,
                            'stat_effects' => ['Wealth' => -8, 'Happiness' => -5]
                        ]
                    ];
                } elseif (in_array('clean', $keywords) || in_array('wash', $keywords) || in_array('tidy', $keywords) || in_array('shower', $keywords) || in_array('bath', $keywords) || in_array('brush', $keywords)) {
                    // Cleaning/Hygiene related choices
                    $outcomes = [
                        [
                            'name' => 'Sparkling Clean',
                            'description' => 'Everything is perfectly clean and fresh',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Health' => 5, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Accident',
                            'description' => 'Something goes wrong during cleaning',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Health' => -3, 'Wealth' => -2]
                        ],
                        [
                            'name' => 'Surprise Clean',
                            'description' => 'You find something valuable while cleaning',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Wealth' => 5, 'Happiness' => 3]
                        ]
                    ];
                } elseif (in_array('watch', $keywords) || in_array('movie', $keywords) || in_array('game', $keywords) || in_array('play', $keywords) || in_array('tv', $keywords) || in_array('film', $keywords) || in_array('netflix', $keywords) || in_array('youtube', $keywords)) {
                    // Entertainment related choices
                    $outcomes = [
                        [
                            'name' => 'Great Entertainment',
                            'description' => 'You really enjoy the entertainment',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 6, 'Creativity' => 2]
                        ],
                        [
                            'name' => 'Bored',
                            'description' => 'It\'s not as interesting as you hoped',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -3, 'Burnout' => 2]
                        ],
                        [
                            'name' => 'Addicted',
                            'description' => 'You spend too much time on it',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Discipline' => -4, 'Burnout' => 3]
                        ],
                        [
                            'name' => 'Hidden Gem',
                            'description' => 'You discover something amazing',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Creativity' => 5, 'Intelligence' => 3]
                        ]
                    ];
                } elseif (in_array('travel', $keywords) || in_array('trip', $keywords) || in_array('journey', $keywords) || in_array('visit', $keywords) || in_array('go', $keywords) || in_array('vacation', $keywords) || in_array('holiday', $keywords)) {
                    // Traveling related choices
                    $outcomes = [
                        [
                            'name' => 'Smooth Journey',
                            'description' => 'Everything goes perfectly',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 8, 'Reputation' => 3]
                        ],
                        [
                            'name' => 'Travel Delay',
                            'description' => 'Your trip is delayed or interrupted',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => -4, 'Discipline' => -2]
                        ],
                        [
                            'name' => 'Unexpected Adventure',
                            'description' => 'Something exciting happens along the way',
                            'type' => 'positive',
                            'chance' => 20,
                            'stat_effects' => ['Luck' => 5, 'Happiness' => 4]
                        ],
                        [
                            'name' => 'Lost',
                            'description' => 'You get lost and waste time',
                            'type' => 'negative',
                            'chance' => 15,
                            'stat_effects' => ['Happiness' => -5, 'Wealth' => -2]
                        ]
                    ];
                } elseif (in_array('talk', $keywords) || in_array('chat', $keywords) || in_array('call', $keywords) || in_array('hang', $keywords) || in_array('meet', $keywords)) {
                    // Communication related choices
                    $outcomes = [
                        [
                            'name' => 'Great Conversation',
                            'description' => 'You have a wonderful chat',
                            'type' => 'positive',
                            'chance' => 30,
                            'stat_effects' => ['Happiness' => 6, 'Reputation' => 2]
                        ],
                        [
                            'name' => 'Awkward Silence',
                            'description' => 'The conversation falls flat',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -3, 'Isolation' => 2]
                        ],
                        [
                            'name' => 'New Insight',
                            'description' => 'You learn something valuable',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Intelligence' => 4, 'Happiness' => 3]
                        ]
                    ];
                } elseif (in_array('relax', $keywords) || in_array('vacation', $keywords) || in_array('holiday', $keywords) || in_array('leisure', $keywords) || in_array('unwind', $keywords)) {
                    // Relaxation related choices
                    $outcomes = [
                        [
                            'name' => 'Perfect Relaxation',
                            'description' => 'You feel completely refreshed',
                            'type' => 'positive',
                            'chance' => 35,
                            'stat_effects' => ['Health' => 6, 'Happiness' => 5, 'Burnout' => -3]
                        ],
                        [
                            'name' => 'Interrupted',
                            'description' => 'Someone disturbs your peace',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Happiness' => -4, 'Burnout' => 2]
                        ],
                        [
                            'name' => 'Deep Peace',
                            'description' => 'You achieve inner calm',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Health' => 4, 'Morality' => 3]
                        ]
                    ];
                } else {
                    // Default random outcomes (fallback)
                    $outcomes = [
                        [
                            'name' => 'Lucky Break',
                            'description' => 'Fortune smiles upon you',
                            'type' => 'positive',
                            'chance' => 25,
                            'stat_effects' => ['Luck' => 10, 'Happiness' => 3]
                        ],
                        [
                            'name' => 'Misfortune',
                            'description' => 'Something unexpected goes wrong',
                            'type' => 'negative',
                            'chance' => 25,
                            'stat_effects' => ['Happiness' => -5, 'Health' => -3]
                        ],
                        [
                            'name' => 'Serendipity',
                            'description' => 'A fortunate coincidence occurs',
                            'type' => 'positive',
                            'chance' => 15,
                            'stat_effects' => ['Luck' => 5, 'Reputation' => 2]
                        ],
                        [
                            'name' => 'Setback',
                            'description' => 'Unexpected obstacles appear',
                            'type' => 'negative',
                            'chance' => 20,
                            'stat_effects' => ['Discipline' => -3, 'Wealth' => -2]
                        ]
                    ];
                }
                break;
        }
        
        return $outcomes;
    }

    /**
     * Extract keywords from text for contextual outcomes
     */
    protected function extractKeywords(string $text): array
    {
        $keywords = [];
        $words = preg_split('/[^a-zA-Z]/', strtolower($text));
        
        $relevantWords = [
            'study', 'learn', 'read', 'write', 'exam', 'test', 'school', 'class', 'teacher',
            'work', 'job', 'office', 'boss', 'career', 'promotion', 'interview',
            'parent', 'father', 'mother', 'dad', 'mom', 'sibling', 'brother', 'sister', 'family',
            'exercise', 'workout', 'gym', 'sport', 'run', 'health', 'sick', 'ill', 'doctor',
            'friend', 'party', 'social', 'meet', 'talk', 'date',
            'practice', 'train', 'skill', 'talent', 'hobby',
            // Cultural keywords
            'festival', 'celebration', 'holiday', 'art', 'museum', 'music', 'dance',
            'food', 'cuisine', 'restaurant', 'culture', 'tradition', 'heritage',
            'theater', 'concert', 'exhibition', 'cultural',
            // NEW: Daily action keywords
            'eat', 'meal', 'breakfast', 'lunch', 'dinner', 'cook', 'vegetable', 'fruit', 'meat', 'fast food', 'healthy', 'junk',
            'sleep', 'nap', 'rest', 'bed', 'wake', 'sleeping',
            'shop', 'buy', 'market', 'store', 'purchase',
            'clean', 'wash', 'tidy', 'shower', 'bath', 'brush',
            'watch', 'movie', 'game', 'play', 'tv', 'film', 'netflix', 'youtube',
            'travel', 'trip', 'journey', 'visit', 'vacation',
            'relax', 'leisure', 'unwind',
            'chat', 'call', 'hang',
            // Profession keywords
            'medical', 'doctor', 'nurse', 'health', 'legal', 'lawyer', 'court', 'judge',
            'tech', 'engineer', 'developer', 'code', 'business', 'entrepreneur', 'company',
            'startup', 'manager', 'ceo', 'employee', 'client', 'patient',
            // Doctor specific
            'surgery', 'operation', 'hospital', 'diagnosis', 'checkup', 'examination',
            // Teacher specific
            'lecture', 'student', 'grading', 'lesson', 'school',
            // Scientist specific
            'research', 'experiment', 'lab', 'paper', 'publication', 'journal', 'discovery',
            // Lawyer specific
            'trial', 'case', 'consultation', 'settlement',
            // Nurse specific
            'patient', 'care', 'hospital', 'shift', 'emergency', 'nursing', 'medical',
            // Soldier specific
            'combat', 'mission', 'battle', 'training', 'drill', 'deployment', 'overseas', 'abroad', 'military', 'soldier',
            // Athlete specific
            'competition', 'game', 'championship', 'match', 'injury', 'recovery', 'rehab', 'endorsement', 'sponsor', 'contract', 'athlete', 'victory', 'defeat',
            // Farmer specific
            'farm', 'farmer', 'harvest', 'crop', 'crops', 'livestock', 'animal', 'animals', 'cattle', 'cow', 'weather', 'drought', 'rain', 'storm', 'pest', 'pests',
            // Fisher specific
            'fishing', 'fisher', 'catch', 'fish', 'boat', 'vessel', 'ship', 'sea', 'ocean', 'net',
            // Artist specific
            'painting', 'canvas', 'gallery', 'exhibition', 'artwork', 'commission', 'creative', 'art', 'artist', 'sketch', 'draw',
            // Writer specific
            'book', 'novel', 'writing', 'publish', 'publisher', 'manuscript', 'writer', 'story', 'chapter', 'editor',
            // Designer specific
            'designer', 'design', 'project', 'revision', 'portfolio', 'software',
            // Musician specific
            'musician', 'concert', 'performance', 'show', 'gig', 'recording', 'album', 'song', 'music', 'tour', 'venue', 'instrument',
            // Chef specific
            'chef', 'kitchen', 'cooking', 'restaurant', 'menu', 'dish', 'food', 'cuisine',
            // Actor specific
            'actor', 'audition', 'role', 'casting', 'film', 'movie', 'premiere', 'scandal',
            // Journalist specific
            'journalist', 'article', 'story', 'news', 'report', 'interview', 'source', 'investigation', 'expose',
            // Business Owner specific
            'business owner', 'company', 'expansion', 'employee', 'staff', 'team',
            // Investor specific
            'investor', 'investment', 'portfolio', 'stock', 'market', 'crash',
            // Entrepreneur specific
            'entrepreneur', 'startup', 'venture', 'pitch', 'funding', 'pivot',
            // Politician specific
            'politician', 'election', 'campaign', 'vote', 'policy', 'law', 'scandal',
            // Performer specific
            'performer', 'show', 'stage', 'fan', 'audience',
            // Salesperson specific
            'salesperson', 'sale', 'deal', 'quota', 'target', 'commission',
            // Community Leader specific
            'community leader', 'community', 'project', 'volunteer', 'outreach', 'conflict',
            // Priest/Religious Leader specific
            'priest', 'religious', 'service', 'sermon', 'worship', 'faith', 'congregation', 'church',
            // Philosopher/Poet specific
            'philosopher', 'philosophy', 'poet', 'poetry', 'debate', 'idea',
            // Gambler specific
            'gambler', 'gamble', 'bet', 'risk', 'luck', 'streak', 'casino',
            // Casino Owner specific
            'casino owner', 'casino', 'gambling', 'high roller', 'vip',
        ];
        
        foreach ($words as $word) {
            if (in_array($word, $relevantWords)) {
                $keywords[] = $word;
            }
        }
        
        return $keywords;
    }
}
