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
