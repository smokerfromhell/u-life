<?php

namespace Database\Seeders;

use App\Models\DailyAction;
use Illuminate\Database\Seeder;

class DailyActionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = [
            // Basic Actions - Always available
            [
                'title' => 'Eat',
                'description' => 'Take care of your body with a proper meal.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Eat a healthy meal', 'stat_effects' => '+4 Health, +2 Happiness, -1 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Eat fast food', 'stat_effects' => '-1 Health, +2 Happiness, -1 Wealth, +1 Debt', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'title' => 'Exercise',
                'description' => 'Train your body and clear your mind.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Go to the gym', 'stat_effects' => '+3 Health, +3 Strength, +2 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Take a walk', 'stat_effects' => '+2 Health, +2 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_disability' => false,
                ],
            ],

            // Health-related - shown when health is not healthy or has cancer
            [
                'title' => 'Doctor Visit',
                'description' => 'Get medical help to stabilize your condition.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Schedule a check-up', 'stat_effects' => '+6 Health, -2 Wealth, -1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-4 Health, +2 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'health_status' => 'fair',
                ],
            ],
            [
                'title' => 'Doctor Visit',
                'description' => 'Get medical help to stabilize your condition.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Schedule a check-up', 'stat_effects' => '+6 Health, -2 Wealth, -1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-4 Health, +2 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'health_status' => 'poor',
                ],
            ],
            [
                'title' => 'Doctor Visit',
                'description' => 'Get medical help to stabilize your condition.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Schedule a check-up', 'stat_effects' => '+6 Health, -2 Wealth, -1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-4 Health, +2 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'health_status' => 'critical',
                ],
            ],
            [
                'title' => 'Doctor Visit',
                'description' => 'Get medical help to stabilize your condition.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Schedule a check-up', 'stat_effects' => '+6 Health, -2 Wealth, -1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-4 Health, +2 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_cancer' => true,
                ],
            ],

            // Financial-related - shown when in debt or bankrupt or low wealth
            [
                'title' => 'Pay Bills',
                'description' => 'Manage obligations so debt does not spiral.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Pay what you can', 'stat_effects' => '-6 Debt, -3 Wealth, +1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Delay payment', 'stat_effects' => '+6 Debt, +1 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_debt' => true,
                ],
            ],
            [
                'title' => 'Pay Bills',
                'description' => 'Manage obligations so debt does not spiral.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Pay what you can', 'stat_effects' => '-6 Debt, -3 Wealth, +1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Delay payment', 'stat_effects' => '+6 Debt, +1 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'is_bankrupt' => true,
                ],
            ],
            [
                'title' => 'Pay Bills',
                'description' => 'Manage obligations so debt does not spiral.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Pay what you can', 'stat_effects' => '-6 Debt, -3 Wealth, +1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Delay payment', 'stat_effects' => '+6 Debt, +1 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'min_wealth' => 25,
                ],
            ],

            // Burnout recovery - shown when burnout >= 70
            [
                'title' => 'Rest',
                'description' => 'Recover before you burn out completely.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 5,
                'choices' => [
                    ['text' => 'Take a rest day', 'stat_effects' => '-6 Burnout, +2 Health, +2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Push through anyway', 'stat_effects' => '+4 Burnout, -2 Health', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'min_burnout' => 70,
                ],
            ],

            // Age-stage: Child - Study
            [
                'title' => 'Study',
                'description' => 'Learn something new while you are young.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 10,
                'choices' => [
                    ['text' => 'Go to school', 'stat_effects' => '+4 Intelligence, +2 Discipline, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Skip class', 'stat_effects' => '-2 Discipline, +2 Happiness, +2 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => 'child',
                ],
            ],

            // Age-stage: Teenager - Part-time Job
            [
                'title' => 'Part-time Job',
                'description' => 'Earn money and build discipline.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 11,
                'choices' => [
                    ['text' => 'Work a shift', 'stat_effects' => '+4 Wealth, +2 Discipline, +2 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Hang out with friends', 'stat_effects' => '+3 Happiness, +1 Charisma, +1 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => ['teenager', 'teen'],
                ],
            ],

            // Age-stage: Adult - Work
            [
                'title' => 'Work',
                'description' => 'Push your career and income forward.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'choices' => [
                    ['text' => 'Work normally', 'stat_effects' => '+4 Wealth, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Work overtime', 'stat_effects' => '+6 Wealth, +3 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => 'adult',
                ],
            ],
            [
                'title' => 'Work',
                'description' => 'Push your career and income forward.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'choices' => [
                    ['text' => 'Work normally', 'stat_effects' => '+4 Wealth, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Work overtime', 'stat_effects' => '+6 Wealth, +3 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => 'old',
                ],
            ],
            [
                'title' => 'Work',
                'description' => 'Push your career and income forward.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'choices' => [
                    ['text' => 'Work normally', 'stat_effects' => '+4 Wealth, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Work overtime', 'stat_effects' => '+6 Wealth, +3 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => 'senior',
                ],
            ],
            [
                'title' => 'Work',
                'description' => 'Push your career and income forward.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'choices' => [
                    ['text' => 'Work normally', 'stat_effects' => '+4 Wealth, +1 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Work overtime', 'stat_effects' => '+6 Wealth, +3 Burnout, -1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'age_group' => 'adult',
                    'is_bankrupt' => false,
                ],
            ],

            // Addiction recovery
            [
                'title' => 'Rehab',
                'description' => 'Attempt to regain control of your life.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 20,
                'choices' => [
                    ['text' => 'Enter rehab', 'stat_effects' => '-10 Addiction, +4 Health, -4 Wealth, -2 Burnout', 'days_to_advance' => 1],
                    ['text' => 'Relapse', 'stat_effects' => '+6 Addiction, -2 Health, +2 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_severe_addiction' => true,
                ],
            ],

            // ========== SKILL-BASED ACTIONS ==========
            // Actions that require specific skills

            // Requires: Cooking skill
            [
                'title' => 'Cook a Gourmet Meal',
                'description' => 'Put your cooking skills to the test with an elaborate dish.',
                'image' => '/css/images/skill/cooking.png',
                'type' => 'skill',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 25,
                'choices' => [
                    ['text' => 'Cook something fancy', 'stat_effects' => '+5 Happiness, +3 Health, -3 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Cook for friends', 'stat_effects' => '+4 Happiness, +2 Charisma, -2 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_skill' => 'Cooking',
                ],
            ],

            // Requires: Meditation skill
            [
                'title' => 'Deep Meditation',
                'description' => 'Use your meditation practice to find inner peace.',
                'image' => '/css/images/skill/meditation.png',
                'type' => 'skill',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 26,
                'choices' => [
                    ['text' => 'Morning meditation', 'stat_effects' => '+4 Discipline, +3 Morality, -2 Stress', 'days_to_advance' => 0],
                    ['text' => 'Extended retreat', 'stat_effects' => '+6 Discipline, +4 Morality, +2 Health, -4 Happiness', 'days_to_advance' => 1],
                ],
                'conditions' => [
                    'has_skill' => 'Meditation',
                ],
            ],

            // Requires: Public Speaking skill
            [
                'title' => 'Give a Speech',
                'description' => 'Share your knowledge and inspire others with your oratory skills.',
                'image' => '/css/images/skill/public-speaking.png',
                'type' => 'skill',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 27,
                'choices' => [
                    ['text' => 'Give a motivational talk', 'stat_effects' => '+6 Charisma, +4 Reputation, +2 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Host a workshop', 'stat_effects' => '+4 Charisma, +3 Intelligence, +3 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_skill' => 'Public Speaking',
                ],
            ],

            // Requires: Basic First Aid skill
            [
                'title' => 'Help the Injured',
                'description' => 'Use your first aid knowledge to help someone in need.',
                'image' => '/css/images/skill/basic-first-aid.png',
                'type' => 'skill',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 28,
                'choices' => [
                    ['text' => 'Help a stranger', 'stat_effects' => '+5 Morality, +3 Reputation, +2 Health', 'days_to_advance' => 0],
                    ['text' => 'Teach first aid', 'stat_effects' => '+4 Morality, +3 Charisma, +2 Intelligence', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_skill' => 'Basic First Aid',
                ],
            ],

            // Requires: Budgeting skill
            [
                'title' => 'Financial Planning',
                'description' => 'Use your budgeting expertise to optimize your finances.',
                'image' => '/css/images/skill/budgeting.png',
                'type' => 'skill',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 29,
                'choices' => [
                    ['text' => 'Create a budget plan', 'stat_effects' => '+5 Wealth, +3 Discipline, -1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Invest wisely', 'stat_effects' => '+6 Wealth, +2 Intelligence, -2 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_skill' => 'Budgeting',
                ],
            ],

            // ========== TALENT-BASED ACTIONS ==========
            // Actions that unlock with specific talents

            // Requires: Resilience talent
            [
                'title' => 'Push Through Adversity',
                'description' => 'Your natural resilience helps you overcome challenges.',
                'image' => '/css/images/talent/resilience.png',
                'type' => 'talent',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 30,
                'choices' => [
                    ['text' => 'Face your fears', 'stat_effects' => '+6 Strength, +4 Morality, +2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Mentor others', 'stat_effects' => '+4 Strength, +4 Charisma, +3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Resilience',
                ],
            ],

            // Requires: Empathy talent
            [
                'title' => 'Counsel Someone',
                'description' => 'Your empathic nature allows you to help others emotionally.',
                'image' => '/css/images/talent/empathy.png',
                'type' => 'talent',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 31,
                'choices' => [
                    ['text' => 'Listen and support', 'stat_effects' => '+6 Morality, +3 Happiness, +2 Charisma', 'days_to_advance' => 0],
                    ['text' => 'Offer advice', 'stat_effects' => '+4 Morality, +4 Intelligence, +2 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Empathy',
                ],
            ],

            // Requires: Ambition talent
            [
                'title' => 'Network Aggressively',
                'description' => 'Your ambition drives you to make powerful connections.',
                'image' => '/css/images/talent/ambition.png',
                'type' => 'talent',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 32,
                'choices' => [
                    ['text' => 'Attend a conference', 'stat_effects' => '+5 Charisma, +4 Wealth, +3 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Seek mentorship', 'stat_effects' => '+4 Charisma, +4 Intelligence, +2 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Ambition',
                ],
            ],

            // Requires: Creativity Spark talent
            [
                'title' => 'Creative Burst',
                'description' => 'Your creative spark ignites into inspiration.',
                'image' => '/css/images/talent/creativity-spark.png',
                'type' => 'talent',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 33,
                'choices' => [
                    ['text' => 'Create art', 'stat_effects' => '+6 Creativity, +4 Happiness, +2 Intelligence', 'days_to_advance' => 0],
                    ['text' => 'Invent something', 'stat_effects' => '+5 Creativity, +4 Intelligence, +2 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Creativity Spark',
                ],
            ],

            // ========== COMBINED CONDITIONS ==========
            // Actions requiring BOTH skill AND talent

            // Requires: Focus talent + Observation skill
            [
                'title' => 'Investigate Mystery',
                'description' => 'Your focus and observation skills combine to solve a puzzle.',
                'image' => '/css/images/skill/observation.png',
                'type' => 'combined',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 35,
                'choices' => [
                    ['text' => 'Investigate thoroughly', 'stat_effects' => '+6 Intelligence, +4 Luck, +3 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Quick scan', 'stat_effects' => '+4 Intelligence, +3 Luck, +2 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Focus',
                    'has_skill' => 'Observation',
                ],
            ],

            // Requires: Courage talent + Swimming skill
            [
                'title' => 'Water Rescue',
                'description' => 'Your courage and swimming ability allow you to save someone.',
                'image' => '/css/images/skill/swimming.png',
                'type' => 'combined',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 36,
                'choices' => [
                    ['text' => 'Dive in immediately', 'stat_effects' => '+6 Strength, +5 Morality, +4 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Call for help first', 'stat_effects' => '+4 Strength, +4 Morality, +2 Intelligence', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'has_talent' => 'Courage',
                    'has_skill' => 'Swimming',
                ],
            ],

            // ========== NEGATIVE CONDITIONS ==========
            // Actions that appear when LACKING specific skills/talents

            // Only available if you DON'T have Meditation skill
            [
                'title' => 'Learn Meditation',
                'description' => 'You feel stressed and could benefit from learning meditation.',
                'image' => '/css/images/skill/meditation.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 40,
                'choices' => [
                    ['text' => 'Start learning', 'stat_effects' => '+2 Intelligence, +2 Discipline, +1 Stress', 'days_to_advance' => 0],
                    ['text' => 'Hire a tutor', 'stat_effects' => '+3 Intelligence, +3 Discipline, -3 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_skill' => 'Meditation',
                    'min_burnout' => 30,
                ],
            ],

            // Only available if you DON'T have Budgeting skill
            [
                'title' => 'Financial Struggles',
                'description' => 'Without proper budgeting skills, money management is hard.',
                'image' => '/css/images/skill/budgeting.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 41,
                'choices' => [
                    ['text' => 'Try to budget', 'stat_effects' => '+2 Discipline, +1 Wealth, -1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Seek help', 'stat_effects' => '+3 Discipline, +2 Wealth, -2 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_skill' => 'Budgeting',
                    'has_debt' => true,
                ],
            ],

            // Only available if you DON'T have Resilience talent
            [
                'title' => 'Build Resilience',
                'description' => 'Life is challenging - you need to build resilience.',
                'image' => '/css/images/talent/resilience.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 42,
                'choices' => [
                    ['text' => 'Face challenges', 'stat_effects' => '+3 Strength, +2 Discipline, -1 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Get coaching', 'stat_effects' => '+4 Strength, +3 Discipline, -3 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Resilience',
                    'min_burnout' => 40,
                ],
            ],

            // ========== SKILL LEARNING ACTIONS ==========
            // Actions that allow learning new skills

            // Learn Reading
            [
                'title' => 'Learn to Read',
                'description' => 'Start your journey into the world of books.',
                'image' => '/css/images/skill/reading.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 10,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 50,
                'choices' => [
                    ['text' => 'Start reading basics', 'stat_effects' => '+2 Intelligence, +1 Isolation', 'days_to_advance' => 0, 'learn_skill' => 'Reading'],
                    ['text' => 'Get a tutor', 'stat_effects' => '+3 Intelligence, -2 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Reading'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Reading',
                ],
            ],

            // Learn Cooking Basics
            [
                'title' => 'Learn Cooking',
                'description' => 'Discover the art of preparing food.',
                'image' => '/css/images/skill/cooking-basics.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 10,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 51,
                'choices' => [
                    ['text' => 'Follow simple recipes', 'stat_effects' => '+2 Creativity, +1 Health', 'days_to_advance' => 0, 'learn_skill' => 'Cooking Basics'],
                    ['text' => 'Take a cooking class', 'stat_effects' => '+3 Creativity, -3 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Cooking'],
                ],
                'conditions' => [
                    'lacks_skill' => ['Cooking', 'Cooking Basics'],
                ],
            ],

            // Learn Meditation
            [
                'title' => 'Learn Meditation',
                'description' => 'Master the art of mindfulness and inner peace.',
                'image' => '/css/images/skill/meditation.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 52,
                'choices' => [
                    ['text' => 'Practice breathing exercises', 'stat_effects' => '+2 Discipline, +1 Morality', 'days_to_advance' => 0, 'learn_skill' => 'Meditation'],
                    ['text' => 'Join a meditation group', 'stat_effects' => '+3 Discipline, +2 Morality, -1 Happiness', 'days_to_advance' => 0, 'learn_skill' => 'Meditation'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Meditation',
                    'min_burnout' => 20,
                ],
            ],

            // Learn Fitness
            [
                'title' => 'Start Fitness Training',
                'description' => 'Begin your journey to physical strength.',
                'image' => '/css/images/skill/fitness.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 10,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 53,
                'choices' => [
                    ['text' => 'Home workout', 'stat_effects' => '+2 Strength, +1 Health', 'days_to_advance' => 0, 'learn_skill' => 'Fitness'],
                    ['text' => 'Join a gym', 'stat_effects' => '+3 Strength, -3 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Fitness'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Fitness',
                    'has_disability' => false,
                ],
            ],

            // Learn Conversation
            [
                'title' => 'Improve Communication',
                'description' => 'Learn to express yourself better.',
                'image' => '/css/images/skill/conversation.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 54,
                'choices' => [
                    ['text' => 'Practice with friends', 'stat_effects' => '+2 Charisma, +1 Reputation', 'days_to_advance' => 0, 'learn_skill' => 'Conversation'],
                    ['text' => 'Take a public speaking course', 'stat_effects' => '+3 Charisma, +2 Reputation, -2 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Public Speaking'],
                ],
                'conditions' => [
                    'lacks_skill' => ['Conversation', 'Public Speaking'],
                ],
            ],

            // Learn Budgeting
            [
                'title' => 'Learn Financial Management',
                'description' => 'Take control of your finances.',
                'image' => '/css/images/skill/budgeting.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 55,
                'choices' => [
                    ['text' => 'Self-study budgeting', 'stat_effects' => '+2 Wealth, +1 Discipline', 'days_to_advance' => 0, 'learn_skill' => 'Budgeting'],
                    ['text' => 'Hire a financial advisor', 'stat_effects' => '+3 Wealth, -4 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Planning'],
                ],
                'conditions' => [
                    'lacks_skill' => ['Budgeting', 'Planning'],
                    'min_wealth' => 10,
                ],
            ],

            // Learn Basic First Aid
            [
                'title' => 'Learn First Aid',
                'description' => 'Be prepared for medical emergencies.',
                'image' => '/css/images/skill/basic-first-aid.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 56,
                'choices' => [
                    ['text' => 'Online course', 'stat_effects' => '+2 Intelligence, +1 Health', 'days_to_advance' => 0, 'learn_skill' => 'Basic First Aid'],
                    ['text' => 'In-person training', 'stat_effects' => '+3 Intelligence, +2 Health, -2 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Basic First Aid'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Basic First Aid',
                ],
            ],

            // Learn Driving
            [
                'title' => 'Learn to Drive',
                'description' => 'Gain independence with mobility.',
                'image' => '/css/images/skill/driving.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 57,
                'choices' => [
                    ['text' => 'Learn from family', 'stat_effects' => '+2 Luck, +1 Intelligence', 'days_to_advance' => 0, 'learn_skill' => 'Driving'],
                    ['text' => 'Driving school', 'stat_effects' => '+3 Luck, -3 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Driving'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Driving',
                    'has_disability' => false,
                    'min_wealth' => 15,
                ],
            ],

            // Learn Swimming
            [
                'title' => 'Learn Swimming',
                'description' => 'Master the water for safety and recreation.',
                'image' => '/css/images/skill/swimming.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 58,
                'choices' => [
                    ['text' => 'Community pool lessons', 'stat_effects' => '+2 Strength, +1 Health', 'days_to_advance' => 0, 'learn_skill' => 'Swimming'],
                    ['text' => 'Private instructor', 'stat_effects' => '+3 Strength, -3 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Swimming'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Swimming',
                    'has_disability' => false,
                ],
            ],

            // Learn Music
            [
                'title' => 'Learn Music',
                'description' => 'Discover the joy of making music.',
                'image' => '/css/images/skill/music.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 59,
                'choices' => [
                    ['text' => 'Self-taught', 'stat_effects' => '+2 Creativity, +1 Isolation', 'days_to_advance' => 0, 'learn_skill' => 'Music'],
                    ['text' => 'Music lessons', 'stat_effects' => '+3 Creativity, -3 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Music'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Music',
                    'min_wealth' => 15,
                ],
            ],

            // Learn Drawing
            [
                'title' => 'Learn to Draw',
                'description' => 'Express yourself through visual art.',
                'image' => '/css/images/skill/drawing.png',
                'type' => 'learning',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 60,
                'choices' => [
                    ['text' => 'Practice daily', 'stat_effects' => '+2 Creativity, +1 Isolation', 'days_to_advance' => 0, 'learn_skill' => 'Drawing'],
                    ['text' => 'Art classes', 'stat_effects' => '+3 Creativity, -2 Wealth', 'days_to_advance' => 0, 'learn_skill' => 'Drawing'],
                ],
                'conditions' => [
                    'lacks_skill' => 'Drawing',
                ],
            ],

            // ========== TALENT DISCOVERY ACTIONS ==========
            // Actions that may lead to discovering talents

            // Chance to discover Resilience
            [
                'title' => 'Face Hardships',
                'description' => 'Going through difficult times might reveal your inner strength.',
                'image' => '/css/images/talent/resilience.png',
                'type' => 'discovery',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 70,
                'choices' => [
                    ['text' => 'Push through', 'stat_effects' => '+4 Strength, +2 Discipline, -2 Happiness', 'days_to_advance' => 0, 'discover_talent' => 'Resilience'],
                    ['text' => 'Seek support', 'stat_effects' => '+2 Strength, +3 Morality, +1 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Resilience',
                    'min_burnout' => 40,
                ],
            ],

            // Chance to discover Empathy
            [
                'title' => 'Emotional Connection',
                'description' => 'Deep connections with others can reveal your empathetic nature.',
                'image' => '/css/images/talent/empathy.png',
                'type' => 'discovery',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 71,
                'choices' => [
                    ['text' => 'Open up emotionally', 'stat_effects' => '+4 Morality, +2 Happiness, -1 Isolation', 'days_to_advance' => 0, 'discover_talent' => 'Empathy'],
                    ['text' => 'Listen deeply', 'stat_effects' => '+3 Morality, +2 Charisma, +1 Intelligence', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Empathy',
                ],
            ],

            // Chance to discover Ambition
            [
                'title' => 'Career Drive',
                'description' => 'Your career aspirations might reveal your ambitious nature.',
                'image' => '/css/images/talent/ambition.png',
                'type' => 'discovery',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 72,
                'choices' => [
                    ['text' => 'Aim for promotion', 'stat_effects' => '+4 Charisma, +3 Wealth, -1 Health', 'days_to_advance' => 0, 'discover_talent' => 'Ambition'],
                    ['text' => 'Network aggressively', 'stat_effects' => '+3 Charisma, +2 Reputation, +1 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Ambition',
                ],
            ],

            // Chance to discover Curiosity
            [
                'title' => 'Explore Interests',
                'description' => 'Your natural curiosity leads you to discover new things.',
                'image' => '/css/images/talent/curiosity.png',
                'type' => 'discovery',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 73,
                'choices' => [
                    ['text' => 'Research topics', 'stat_effects' => '+4 Intelligence, +2 Creativity', 'days_to_advance' => 0, 'discover_talent' => 'Curiosity'],
                    ['text' => 'Ask questions', 'stat_effects' => '+3 Intelligence, +2 Charisma', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Curiosity',
                ],
            ],

            // Chance to discover Optimism
            [
                'title' => 'Positive Outlook',
                'description' => 'Maintaining a positive attitude can reveal your optimistic nature.',
                'image' => '/css/images/talent/optimism.png',
                'type' => 'discovery',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 74,
                'choices' => [
                    ['text' => 'Stay hopeful', 'stat_effects' => '+3 Luck, +3 Charisma, +1 Happiness', 'days_to_advance' => 0, 'discover_talent' => 'Optimism'],
                    ['text' => 'Help others stay positive', 'stat_effects' => '+4 Luck, +2 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'lacks_talent' => 'Optimism',
                ],
            ],

            // ========================================
            // TRAUMA HEALING ACTIONS (Step 1)
            // ========================================

            // Therapy for anxiety
            [
                'title' => 'Therapy Session',
                'description' => 'Professional help to manage anxiety.',
                'image' => '/css/images/health/therapy.png',
                'type' => 'healing',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 80,
                'choices' => [
                    ['text' => 'Weekly therapy', 'stat_effects' => '+3 Happiness, -2 Wealth, -1 Health', 'days_to_advance' => 0, 'heal_trauma' => 'anxiety'],
                    ['text' => 'Intensive treatment', 'stat_effects' => '+5 Happiness, -5 Wealth', 'days_to_advance' => 0, 'heal_trauma' => ['anxiety', 'depression']],
                ],
                'conditions' => [
                    'has_trauma' => 'anxiety',
                ],
            ],

            // Treatment for depression
            [
                'title' => 'Depression Treatment',
                'description' => 'Seek professional help for depression.',
                'image' => '/css/images/health/mental.png',
                'type' => 'healing',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 81,
                'choices' => [
                    ['text' => 'Start medication', 'stat_effects' => '+4 Happiness, -3 Wealth, -1 Health', 'days_to_advance' => 0, 'heal_trauma' => 'depression'],
                    ['text' => 'Combined therapy', 'stat_effects' => '+6 Happiness, -6 Wealth', 'days_to_advance' => 0, 'heal_trauma' => ['depression', 'anxiety']],
                ],
                'conditions' => [
                    'has_trauma' => 'depression',
                ],
            ],

            // PTSD treatment
            [
                'title' => 'PTSD Treatment',
                'description' => 'Specialized trauma therapy for PTSD.',
                'image' => '/css/images/health/ptsd.png',
                'type' => 'healing',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 82,
                'choices' => [
                    ['text' => 'EMDR therapy', 'stat_effects' => '+5 Happiness, -4 Wealth', 'days_to_advance' => 0, 'heal_trauma' => 'ptsd'],
                    ['text' => 'Full rehabilitation', 'stat_effects' => '+7 Happiness, -8 Wealth, +2 Health', 'days_to_advance' => 0, 'heal_trauma' => ['ptsd', 'anxiety', 'depression']],
                ],
                'conditions' => [
                    'has_trauma' => 'ptsd',
                ],
            ],

            // Mindfulness and meditation healing
            [
                'title' => 'Mindfulness Practice',
                'description' => 'Holistic approach to heal mental health.',
                'image' => '/css/images/health/mindfulness.png',
                'type' => 'healing',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 83,
                'choices' => [
                    ['text' => 'Daily meditation', 'stat_effects' => '+2 Happiness, +1 Discipline', 'days_to_advance' => 0, 'heal_trauma' => 'anxiety'],
                    ['text' => 'Retreat program', 'stat_effects' => '+4 Happiness, +2 Discipline, -3 Wealth', 'days_to_advance' => 0, 'heal_trauma' => ['anxiety', 'depression']],
                ],
                'conditions' => [
                    'has_trauma' => ['anxiety', 'depression'],
                    'age_group' => ['teen', 'adult', 'old'],
                ],
            ],

            // ========================================
            // RELATIONSHIP ACTIONS (Step 2)
            // ========================================

            // Dating - for single adults
            [
                'title' => 'Ask Someone Out',
                'description' => 'Take a chance on love.',
                'image' => '/css/images/relationship/dating.png',
                'type' => 'romance',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 85,
                'choices' => [
                    ['text' => 'Ask crush on date', 'stat_effects' => '+5 Happiness, -2 Wealth', 'days_to_advance' => 0, 'relationship_status_change' => 'dating'],
                    ['text' => 'Try online dating', 'stat_effects' => '+3 Happiness, -1 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'relationship_status' => 'single',
                    'age_group' => ['teen', 'adult'],
                ],
            ],

            // Getting engaged
            [
                'title' => 'Propose Marriage',
                'description' => 'Take your relationship to the next level.',
                'image' => '/css/images/relationship/engaged.png',
                'type' => 'romance',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 1,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 86,
                'choices' => [
                    ['text' => 'Plan romantic proposal', 'stat_effects' => '+10 Happiness, -10 Wealth', 'days_to_advance' => 0, 'relationship_status_change' => 'engaged'],
                    ['text' => 'Simple proposal', 'stat_effects' => '+8 Happiness', 'days_to_advance' => 0, 'relationship_status_change' => 'engaged'],
                ],
                'conditions' => [
                    'relationship_status' => 'dating',
                    'age_group' => ['adult', 'old'],
                ],
            ],

            // Getting married
            [
                'title' => 'Get Married',
                'description' => 'The big day has arrived!',
                'image' => '/css/images/relationship/married.png',
                'type' => 'romance',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 1,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 87,
                'choices' => [
                    ['text' => 'Dream wedding', 'stat_effects' => '+15 Happiness, -20 Wealth', 'days_to_advance' => 0, 'relationship_status_change' => 'married'],
                    ['text' => 'Small ceremony', 'stat_effects' => '+10 Happiness, -5 Wealth', 'days_to_advance' => 0, 'relationship_status_change' => 'married'],
                ],
                'conditions' => [
                    'relationship_status' => 'engaged',
                ],
            ],

            // Family bonding
            [
                'title' => 'Family Reunion',
                'description' => 'Spend quality time with family.',
                'image' => '/css/images/relationship/family.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 88,
                'choices' => [
                    ['text' => 'Organize family dinner', 'stat_effects' => '+4 Happiness, +2 Morality', 'days_to_advance' => 0, 'relationship_state_change' => ['family' => 'warm']], 
                    ['text' => 'Plan family trip', 'stat_effects' => '+6 Happiness, -5 Wealth', 'days_to_advance' => 0, 'relationship_state_change' => ['family' => 'close']],
                ],
                'conditions' => [
                    'min_relationship_state' => ['family' => 'neutral'],
                    'age_group' => ['teen', 'adult', 'old'],
                ],
            ],

            // Making friends
            [
                'title' => 'Join Social Group',
                'description' => 'Meet new people and make friends.',
                'image' => '/css/images/relationship/friends.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 89,
                'choices' => [
                    ['text' => 'Join hobby club', 'stat_effects' => '+3 Happiness, +1 Charisma', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'friend', 'name' => 'Club Friend', 'details' => ['meeting' => 'weekly']]],
                    ['text' => 'Volunteer work', 'stat_effects' => '+4 Happiness, +2 Morality', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'friend', 'name' => 'Volunteer Buddy', 'details' => ['cause' => 'community']]],
                ],
                'conditions' => [
                    'lacks_social_connection' => 'friend',
                    'age_group' => ['teen', 'adult', 'old'],
                ],
            ],

            // Finding a mentor
            [
                'title' => 'Find Mentor',
                'description' => 'Seek guidance from someone experienced in your field.',
                'image' => '/css/images/relationship/mentor.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 90,
                'choices' => [
                    ['text' => 'Approach expert', 'stat_effects' => '+3 Intelligence, +2 Discipline', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'mentor', 'name' => 'Wise Mentor', 'details' => ['expertise' => 'career']]],
                    ['text' => 'Join workshop', 'stat_effects' => '+2 Creativity, +2 Charisma', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'mentor', 'name' => 'Workshop Leader', 'details' => ['field' => 'skills']]],
                ],
                'conditions' => [
                    'lacks_social_connection' => 'mentor',
                    'age_group' => ['teen', 'adult'],
                ],
            ],

            // Dating/romantic relationship
            [
                'title' => 'Start Dating',
                'description' => 'Put yourself out there and look for romance.',
                'image' => '/css/images/relationship/dating.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 91,
                'choices' => [
                    ['text' => 'Try online dating', 'stat_effects' => '+3 Happiness, +1 Charisma', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'romantic', 'name' => 'Dating Match', 'details' => ['status' => 'dating']], 'relationship_status_change' => 'dating'],
                    ['text' => 'Ask someone out', 'stat_effects' => '+4 Happiness, -2 Ego', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'romantic', 'name' => 'Crush', 'details' => ['status' => 'dating']], 'relationship_status_change' => 'dating'],
                ],
                'conditions' => [
                    'relationship_status' => ['single', 'divorced'],
                    'age_group' => ['teen', 'adult'],
                ],
            ],

            // Family connections
            [
                'title' => 'Reconnect with Family',
                'description' => 'Reach out to family members you have lost touch with.',
                'image' => '/css/images/relationship/family.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 92,
                'choices' => [
                    ['text' => 'Visit parents', 'stat_effects' => '+5 Happiness, +3 Morality', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'family', 'name' => 'Parent', 'details' => ['relation' => 'parent', 'bond' => 'reconnected']]],
                    ['text' => 'Call sibling', 'stat_effects' => '+3 Happiness, +2 Empathy', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'family', 'name' => 'Sibling', 'details' => ['relation' => 'sibling', 'bond' => 'reconnected']]],
                ],
                'conditions' => [
                    'age_group' => ['adult', 'old'],
                ],
            ],

            // Work colleagues
            [
                'title' => 'Network at Work',
                'description' => 'Build professional relationships with coworkers.',
                'image' => '/css/images/relationship/work.png',
                'type' => 'social',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 93,
                'choices' => [
                    ['text' => 'Mentor junior', 'stat_effects' => '+3 Reputation, +2 Discipline', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'colleague', 'name' => 'Junior Coworker', 'details' => ['relation' => 'mentee']]],
                    ['text' => 'Team lunch', 'stat_effects' => '+3 Charisma, +2 Happiness', 'days_to_advance' => 0, 'add_social_connection' => ['type' => 'colleague', 'name' => 'Work Buddy', 'details' => ['relation' => 'friend']]],
                ],
                'conditions' => [
                    'age_group' => ['teen', 'adult', 'old'],
                ],
            ],

            // ========================================
            // LOCATION/ENVIRONMENT ACTIONS (Step 3)
            // ========================================

            // Travel vacation
            [
                'title' => 'Plan Vacation',
                'description' => 'Take a break and travel somewhere new.',
                'image' => '/css/images/environment/vacation.png',
                'type' => 'travel',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 91,
                'choices' => [
                    ['text' => 'Beach resort', 'stat_effects' => '+8 Happiness, -15 Wealth', 'days_to_advance' => 0, 'change_location' => 'resort', 'change_weather' => 'clear'],
                    ['text' => 'Mountain retreat', 'stat_effects' => '+6 Happiness, -10 Wealth', 'days_to_advance' => 0, 'change_location' => 'mountains'],
                    ['text' => 'City exploration', 'stat_effects' => '+5 Happiness, -8 Wealth', 'days_to_advance' => 0, 'change_location' => 'city'],
                ],
                'conditions' => [
                    'min_wealth' => 20,
                    'age_group' => ['teen', 'adult', 'old'],
                ],
            ],

            // Move to new city
            [
                'title' => 'Relocate',
                'description' => 'Move to a new location for opportunities.',
                'image' => '/css/images/environment/moving.png',
                'type' => 'travel',
                'deck_label' => 'Action',
                'repeatable' => false,
                'weight' => 1,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 92,
                'choices' => [
                    ['text' => 'Move to big city', 'stat_effects' => '+5 Happiness, -30 Wealth, +3 Intelligence', 'days_to_advance' => 0, 'change_location' => 'big_city'],
                    ['text' => 'Move to suburbs', 'stat_effects' => '+4 Happiness, -20 Wealth', 'days_to_advance' => 0, 'change_location' => 'suburbs'],
                    ['text' => 'Move abroad', 'stat_effects' => '+6 Happiness, -40 Wealth, +2 Charisma', 'days_to_advance' => 0, 'change_location' => 'abroad'],
                ],
                'conditions' => [
                    'min_wealth' => 50,
                    'age_group' => ['adult', 'old'],
                ],
            ],

            // Seasonal activities - spring
            [
                'title' => 'Spring Festival',
                'description' => 'Enjoy the arrival of spring.',
                'image' => '/css/images/environment/spring.png',
                'type' => 'seasonal',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 93,
                'choices' => [
                    ['text' => 'Plant a garden', 'stat_effects' => '+3 Happiness, +1 Health', 'days_to_advance' => 0],
                    ['text' => 'Spring cleaning', 'stat_effects' => '+2 Happiness, +1 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'season' => 'spring',
                ],
            ],

            // Seasonal activities - winter
            [
                'title' => 'Winter Activities',
                'description' => 'Enjoy the winter season.',
                'image' => '/css/images/environment/winter.png',
                'type' => 'seasonal',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 94,
                'choices' => [
                    ['text' => 'Build snowman', 'stat_effects' => '+3 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skiing trip', 'stat_effects' => '+5 Happiness, -10 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'season' => 'winter',
                    'weather' => 'snowy',
                ],
            ],

            // Weather-dependent activities
            [
                'title' => 'Outdoor Adventures',
                'description' => 'Perfect weather for outdoor activities.',
                'image' => '/css/images/environment/outdoor.png',
                'type' => 'activity',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 95,
                'choices' => [
                    ['text' => 'Go hiking', 'stat_effects' => '+4 Health, +2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Picnic in the park', 'stat_effects' => '+3 Happiness, +1 Health', 'days_to_advance' => 0],
                ],
                'conditions' => [
                    'weather' => 'clear',
                ],
            ],

            // Time control - always available
            [
                'title' => 'Advance Age',
                'description' => 'Let time pass. You grow older and new events appear.',
                'image' => '/css/images/milestone.jpg',
                'type' => 'system',
                'deck_label' => 'Action',
                'repeatable' => true,
                'weight' => 0,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 90,
                'choices' => [
                    ['text' => 'Advance 1 year', 'stat_effects' => null, 'days_to_advance' => 1],
                    ['text' => 'Fast-forward 5 years', 'stat_effects' => '+5 Burnout, -3 Health, -2 Happiness', 'days_to_advance' => 5],
                ],
                'conditions' => null,
            ],
        ];

        foreach ($actions as $action) {
            DailyAction::create($action);
        }
    }
}
