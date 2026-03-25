<?php

namespace Database\Seeders;

use App\Models\DailyEvent;
use Illuminate\Database\Seeder;

class DailyEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Daily events with choices structure (similar to DailyActionSeeder):
     * - type: 'daily'
     * - deck_label: 'Daily Event'
     * - Each event has: title, description, image, type, deck_label, repeatable, weight, 
     *   auto_resolve, days_to_advance, display_order, choices, conditions
     * - event_category: Added for narrative branching (education, career, health, wealth, social, family)
     */
    public function run(): void
    {
        $events = [
            // Child events
            [
                'event_choice' => 'TV',
                'title' => 'TV',
                'description' => 'Watch your favorite cartoons all day.',
                'image' => '/css/images/dailyevents/tv.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'age_group' => 'child',
                'event_category' => 'health', // Entertainment affects health/discipline
                'choices' => [
                    ['text' => 'Cartoon marathon', 'stat_effects' => '+5 Happiness, -5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Play outside instead', 'stat_effects' => '+5 Health, +3 Happiness, -2 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Playtime',
                'description' => 'Enjoy playing outside with friends.',
                'image' => '/css/images/dailyevents/playtime.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'age_group' => 'child',
                'event_category' => 'health', // Physical activity
                'choices' => [
                    ['text' => 'Outdoor fun', 'stat_effects' => '+10 Health, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay home and rest', 'stat_effects' => '+2 Health, -3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Homework',
                'description' => 'Finish your homework assignments.',
                'image' => '/css/images/dailyevents/study.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'age_group' => 'child',
                'event_category' => 'education',
                'choices' => [
                    ['text' => 'Complete all homework', 'stat_effects' => '+10 Intelligence, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip homework', 'stat_effects' => '-5 Intelligence, -3 Discipline, +5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Chores',
                'description' => 'Help around the house with chores.',
                'image' => '/css/images/dailyevents/chores.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'age_group' => 'child',
                'event_category' => 'family',
                'choices' => [
                    ['text' => 'Help parents', 'stat_effects' => '+10 Discipline, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Refuse to help', 'stat_effects' => '-5 Discipline, -3 Morality, +5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Meal',
                'description' => 'Eat a nutritious meal.',
                'image' => '/css/images/dailyevents/meal.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 5,
                'age_group' => 'child',
                'event_category' => 'health',
                'choices' => [
                    ['text' => 'Healthy dinner', 'stat_effects' => '+10 Health, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Junk food', 'stat_effects' => '+3 Happiness, -5 Health', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Bedtime',
                'description' => 'Get some restful sleep.',
                'image' => '/css/images/dailyevents/bedtime.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 6,
                'age_group' => 'child',
                'event_category' => 'health',
                'choices' => [
                    ['text' => 'Sleep early', 'stat_effects' => '+10 Health, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Stay up late', 'stat_effects' => '-5 Health, +3 Happiness, -3 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'School',
                'description' => 'Have a good day at school.',
                'image' => '/css/images/dailyevents/school-start.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 7,
                'age_group' => 'child',
                'event_category' => 'education',
                'choices' => [
                    ['text' => 'Focus on studies', 'stat_effects' => '+10 Intelligence, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Daydream in class', 'stat_effects' => '+2 Happiness, -5 Intelligence', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            [
                'title' => 'Reading',
                'description' => 'Read a good book.',
                'image' => '/css/images/dailyevents/reading.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 8,
                'age_group' => 'child',
                'event_category' => 'education',
                'choices' => [
                    ['text' => 'Read books', 'stat_effects' => '+10 Intelligence, +5 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Play games instead', 'stat_effects' => '+3 Happiness, -3 Intelligence', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child', 'has_skill' => 'Reading'],
            ],
            [
                'title' => 'Friends',
                'description' => 'Spend time with friends.',
                'image' => '/css/images/dailyevents/friends.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 9,
                'age_group' => 'child',
                'event_category' => 'social',
                'choices' => [
                    ['text' => 'Play with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Play alone', 'stat_effects' => '+2 Happiness, -5 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'child'],
            ],
            // Teen events
            [
                'title' => 'TV',
                'description' => 'Watch TV shows all evening.',
                'image' => '/css/images/dailyevents/tv.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 10,
                'age_group' => 'teen',
                'event_category' => 'health',
                'choices' => [
                    ['text' => 'TV marathon', 'stat_effects' => '+5 Happiness, -5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Do something productive', 'stat_effects' => '+5 Discipline, -3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager']],
            ],
            [
                'title' => 'Study',
                'description' => 'Prepare for your exams.',
                'image' => '/css/images/dailyevents/study.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 11,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Study hard', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip studying', 'stat_effects' => '-5 Intelligence, +5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager']],
            ],
            [
                'title' => 'Part-time job',
                'description' => 'Work at a part-time job.',
                'image' => '/css/images/dailyevents/part-time-job.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Work part-time job', 'stat_effects' => '+10 Wealth, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip work', 'stat_effects' => '-5 Wealth, +5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager'], 'min_wealth' => 5],
            ],
            [
                'title' => 'Friends',
                'description' => 'Spend time with friends.',
                'image' => '/css/images/dailyevents/friends.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 13,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Hang out with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '-5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager']],
            ],
            [
                'title' => 'Sports',
                'description' => 'Play sports with your team.',
                'image' => '/css/images/dailyevents/sports.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 6,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 14,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Play sports', 'stat_effects' => '+15 Strength, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip practice', 'stat_effects' => '-5 Strength, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager'], 'has_skill' => 'Fitness'],
            ],
            [
                'title' => 'Date',
                'description' => 'Go on a date.',
                'image' => '/css/images/dailyevents/date-night.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 6,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 15,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Go on date', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Cancel date', 'stat_effects' => '-5 Happiness, -3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager'], 'relationship_status' => 'dating'],
            ],
            [
                'title' => 'Curfew',
                'description' => 'Break curfew rules.',
                'image' => '/css/images/dailyevents/curfew.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 16,
                'age_group' => 'teen',
                'choices' => [
                    ['text' => 'Break curfew', 'stat_effects' => '-10 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Follow curfew', 'stat_effects' => '+5 Reputation, -3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'teenager'], 'min_burnout' => 30],
            ],
            // Adult events
            [
                'title' => 'TV',
                'description' => 'Watch TV shows after work.',
                'image' => '/css/images/dailyevents/tv.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 5,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 17,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Watch TV', 'stat_effects' => '+5 Happiness, -5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Do hobby instead', 'stat_effects' => '+3 Creativity, -2 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult'],
            ],
            [
                'title' => 'Commute',
                'description' => 'Deal with commute.',
                'image' => '/css/images/dailyevents/commute.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 4,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 18,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Stay patient', 'stat_effects' => '-2 Happiness, +3 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Get frustrated', 'stat_effects' => '+5 Burnout, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'profession_state' => ['entry_level', 'mid_level', 'senior', 'executive']],
            ],
            [
                'title' => 'Work',
                'description' => 'Have a successful work meeting.',
                'image' => '/css/images/dailyevents/work.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 19,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Excel in meeting', 'stat_effects' => '+10 Discipline, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Just attend', 'stat_effects' => '+2 Discipline, -3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'profession_state' => ['entry_level', 'mid_level', 'senior', 'executive']],
            ],
            [
                'title' => 'Family Time',
                'description' => 'Spend quality time with family.',
                'image' => '/css/images/dailyevents/family-time.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 20,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Spend time with family', 'stat_effects' => '+10 Happiness, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Work late instead', 'stat_effects' => '+5 Wealth, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'relationship_status' => ['married', 'dating', 'engaged']],
            ],
            [
                'title' => 'Gym',
                'description' => 'Go to the gym.',
                'image' => '/css/images/dailyevents/gym.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 21,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Work out', 'stat_effects' => '+10 Strength, +5 Health', 'days_to_advance' => 0],
                    ['text' => 'Skip workout', 'stat_effects' => '-3 Health, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'min_health' => 30],
            ],
            [
                'title' => 'Cooking',
                'description' => 'Cook a nice meal.',
                'image' => '/css/images/dailyevents/cooking.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 22,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Cook a meal', 'stat_effects' => '+10 Happiness, +5 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Order takeout', 'stat_effects' => '+3 Happiness, -3 Wealth, +2 Health', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'has_skill' => 'Cooking'],
            ],
            [
                'title' => 'Date night',
                'description' => 'Go on a romantic date.',
                'image' => '/css/images/dailyevents/date-night.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 6,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 23,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Go on date night', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '-5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'relationship_status' => ['married', 'dating', 'engaged']],
            ],
            [
                'title' => 'Bills',
                'description' => 'Pay your bills.',
                'image' => '/css/images/dailyevents/bills.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 24,
                'age_group' => 'adult',
                'choices' => [
                    ['text' => 'Pay bills on time', 'stat_effects' => '+5 Discipline, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Delay payment', 'stat_effects' => '-5 Reputation, -3 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'adult', 'min_wealth' => 10],
            ],
            // Old events
            [
                'title' => 'Television',
                'description' => 'Watch classic movies.',
                'image' => '/css/images/dailyevents/tv.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 25,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Watch movies', 'stat_effects' => '+10 Happiness, +5 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Do puzzles instead', 'stat_effects' => '+5 Intelligence, -3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old'],
            ],
            [
                'title' => 'Morning walk',
                'description' => 'Take a morning walk.',
                'image' => '/css/images/dailyevents/morning-walk.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 26,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Go for walk', 'stat_effects' => '+10 Health, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay in bed', 'stat_effects' => '-5 Health, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old', 'health_status' => ['healthy', 'fair']],
            ],
            [
                'title' => 'Grandchildren',
                'description' => 'Visit with grandchildren.',
                'image' => '/css/images/dailyevents/grandchildren.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 27,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Visit grandchildren', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Decline visit', 'stat_effects' => '-10 Happiness, +3 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old', 'relationship_status' => 'married'],
            ],
            [
                'title' => 'Garden',
                'description' => 'Tend to your garden.',
                'image' => '/css/images/dailyevents/garden.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 28,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Tend garden', 'stat_effects' => '+10 Happiness, +5 Health', 'days_to_advance' => 0],
                    ['text' => 'Ignore garden', 'stat_effects' => '-3 Happiness, +3 Rest', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old'],
            ],
            [
                'title' => 'Doctor checkup',
                'description' => 'Visit the doctor.',
                'image' => '/css/images/dailyevents/doctor-checkup.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 29,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Get checkup', 'stat_effects' => '+10 Health, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip checkup', 'stat_effects' => '-5 Health, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old', 'health_status' => ['fair', 'poor', 'critical']],
            ],
            [
                'title' => 'Nap',
                'description' => 'Take a nap.',
                'image' => '/css/images/dailyevents/nap.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 8,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 30,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Take nap', 'stat_effects' => '+10 Health, +5 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Stay awake', 'stat_effects' => '-3 Health, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old', 'min_burnout' => 30],
            ],
            [
                'title' => 'Memories',
                'description' => 'Reminisce about good times.',
                'image' => '/css/images/dailyevents/memories.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 7,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 31,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Remember good times', 'stat_effects' => '+15 Happiness, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Focus on regrets', 'stat_effects' => '-10 Happiness, +3 Wisdom', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old'],
            ],
            [
                'title' => 'Medications',
                'description' => 'Take your medications.',
                'image' => '/css/images/dailyevents/medication.png',
                'type' => 'daily',
                'deck_label' => 'Daily Event',
                'repeatable' => true,
                'weight' => 9,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 32,
                'age_group' => 'old',
                'choices' => [
                    ['text' => 'Take medications', 'stat_effects' => '+5 Health, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip medications', 'stat_effects' => '-10 Health, +3 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => 'old', 'health_status' => ['fair', 'poor', 'critical']],
            ],
        ];

        foreach ($events as $event) {
            // Use title as event_choice if not specified
            if (!isset($event['event_choice']) && isset($event['title'])) {
                $event['event_choice'] = $event['title'];
            }
            // Auto-assign event_category if not set
            if (!isset($event['event_category'])) {
                $event['event_category'] = $this->determineEventCategory($event);
            }
            // Provide default outcome if not set
            if (!isset($event['outcome'])) {
                $event['outcome'] = $event['description'] ?? 'Event completed.';
            }
            // Provide default stat_effects if not set
            if (!isset($event['stat_effects'])) {
                $event['stat_effects'] = '+1 Happiness';
            }
            DailyEvent::create($event);
        }
    }

    /**
     * Determine event_category based on title and description keywords.
     * Maps to NarrativeService STORY_PATHS keywords.
     */
    private function determineEventCategory(array $event): string
    {
        $title = strtolower($event['title'] ?? '');
        $description = strtolower($event['description'] ?? '');
        $text = $title . ' ' . $description;

        // Education keywords
        if (preg_match('/(homework|study|school|exam|learning|college|university|reading|book|course|train|trainings?|lesson)/', $text)) {
            return 'education';
        }
        // Career/Work keywords
        if (preg_match('/(work|job|career|office|boss|meeting|project|client|customer|shift|career)/', $text)) {
            return 'career';
        }
        // Health keywords
        if (preg_match('/(exercise|gym|workout|health|doctor|medicine|sleep|rest|bedtime|meal|food|nutrition|sick|ill|fitness)/', $text)) {
            return 'health';
        }
        // Wealth keywords  
        if (preg_match('/(money|wealth|finance|investment|shopping|buy|sell|budget|bill|payment|saving)/', $text)) {
            return 'wealth';
        }
        // Family keywords
        if (preg_match('/(family|parent|mother|father|chore|home|house|family)/', $text)) {
            return 'family';
        }
        // Social keywords
        if (preg_match('/(friend|social|party|hangout|friend|community|network|date|dating|relationship)/', $text)) {
            return 'social';
        }
        // Default to random for unmatched events
        return 'random';
    }
}
