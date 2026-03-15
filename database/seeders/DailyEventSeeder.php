<?php

namespace Database\Seeders;

use App\Models\DailyEvent;
use Illuminate\Database\Seeder;

class DailyEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            // Child events
            ['event_choice' => 'TV - Cartoon marathon', 'outcome' => 'You watch cartoons', 'stat_effects' => '+5 Happiness, -5 Discipline', 'weight' => 5, 'age_group' => 'child', 'image' => '/css/images/dailyevents/tv.png'],
            ['event_choice' => 'Playtime - Outdoor fun', 'outcome' => 'You play outside', 'stat_effects' => '+10 Health, +10 Happiness', 'weight' => 8, 'age_group' => 'child', 'image' => '/css/images/dailyevents/playtime.png'],
            ['event_choice' => 'Homework - Complete', 'outcome' => 'You complete homework', 'stat_effects' => '+10 Intelligence, +5 Discipline', 'weight' => 8, 'age_group' => 'child', 'image' => '/css/images/dailyevents/study.png'],
            ['event_choice' => 'Chores - Help parents', 'outcome' => 'You help with chores', 'stat_effects' => '+10 Discipline, +5 Morality', 'weight' => 7, 'age_group' => 'child', 'image' => '/css/images/dailyevents/chores.png'],
            ['event_choice' => 'Meal - Healthy dinner', 'outcome' => 'You eat healthy', 'stat_effects' => '+10 Health, +5 Happiness', 'weight' => 8, 'age_group' => 'child', 'image' => '/css/images/dailyevents/meal.png'],
            ['event_choice' => 'Bedtime - Sleep early', 'outcome' => 'You sleep early', 'stat_effects' => '+10 Health, +5 Discipline', 'weight' => 8, 'age_group' => 'child', 'image' => '/css/images/dailyevents/bedtime.png'],
            ['event_choice' => 'School - Good day', 'outcome' => 'Good day at school', 'stat_effects' => '+10 Intelligence, +10 Happiness', 'weight' => 8, 'age_group' => 'child', 'image' => '/css/images/dailyevents/school-start.png'],
            ['event_choice' => 'Reading - Story time', 'outcome' => 'You read a story', 'stat_effects' => '+10 Intelligence, +5 Creativity', 'weight' => 7, 'age_group' => 'child', 'image' => '/css/images/dailyevents/reading.png'],
            ['event_choice' => 'Friends - Play date', 'outcome' => 'Play date with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 7, 'age_group' => 'child', 'image' => '/css/images/dailyevents/friends.png'],
            // Teen events
            ['event_choice' => 'TV - TV Marathon', 'outcome' => 'You watch TV all day', 'stat_effects' => '+5 Happiness, -5 Discipline', 'weight' => 5, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/tv.png'],
            ['event_choice' => 'Study - Ace exam', 'outcome' => 'You ace your exam', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'weight' => 7, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/study.png'],
            ['event_choice' => 'Part-time job - Earn money', 'outcome' => 'You earn money', 'stat_effects' => '+10 Wealth, +5 Discipline', 'weight' => 7, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/part-time-job.png'],
            ['event_choice' => 'Friends - Hang out', 'outcome' => 'You hang out with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 8, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/friends.png'],
            ['event_choice' => 'Sports - Team win', 'outcome' => 'Your team wins', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 6, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/sports.png'],
            ['event_choice' => 'Date - Successful date', 'outcome' => 'Successful date', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 6, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/date-night.png'],
            ['event_choice' => 'Curfew - Break', 'outcome' => 'You break curfew', 'stat_effects' => '-10 Reputation, +10 Happiness', 'weight' => 3, 'age_group' => 'teen', 'image' => '/css/images/dailyevents/curfew.png'],
            // Adult events
            ['event_choice' => 'TV - TV Marathon', 'outcome' => 'Watch TV all evening', 'stat_effects' => '+5 Happiness, -5 Discipline', 'weight' => 5, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/tv.png'],
            ['event_choice' => 'Commute - Traffic jam', 'outcome' => 'Stuck in traffic', 'stat_effects' => '+5 Burnout, -5 Happiness', 'weight' => 4, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/commute.png'],
            ['event_choice' => 'Work - Meeting success', 'outcome' => 'Meeting goes well', 'stat_effects' => '+10 Discipline, +10 Reputation', 'weight' => 7, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/work.png'],
            ['event_choice' => 'Family Time - Quality time', 'outcome' => 'Quality time with family', 'stat_effects' => '+10 Happiness, +5 Morality', 'weight' => 8, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/family-time.png'],
            ['event_choice' => 'Gym - Workout', 'outcome' => 'Complete a workout', 'stat_effects' => '+10 Strength, +5 Health', 'weight' => 7, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/gym.png'],
            ['event_choice' => 'Cooking - Delicious meal', 'outcome' => 'You cook a meal', 'stat_effects' => '+10 Happiness, +5 Creativity', 'weight' => 7, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/cooking.png'],
            ['event_choice' => 'Date night - Romantic', 'outcome' => 'Romantic date night', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 6, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/date-night.png'],
            ['event_choice' => 'Bills - Paid on time', 'outcome' => 'Pay bills on time', 'stat_effects' => '+5 Discipline, +5 Reputation', 'weight' => 8, 'age_group' => 'adult', 'image' => '/css/images/dailyevents/bills.png'],
            // Old events
            ['event_choice' => 'Television - Classic movies', 'outcome' => 'Watch classic movies', 'stat_effects' => '+10 Happiness, +5 Creativity', 'weight' => 7, 'age_group' => 'old', 'image' => '/css/images/dailyevents/tv.png'],
            ['event_choice' => 'Morning walk - Fresh air', 'outcome' => 'Enjoy a morning walk', 'stat_effects' => '+10 Health, +10 Happiness', 'weight' => 8, 'age_group' => 'old', 'image' => '/css/images/dailyevents/morning-walk.png'],
            ['event_choice' => 'Grandchildren - Visit', 'outcome' => 'Grandchildren visit', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 8, 'age_group' => 'old', 'image' => '/css/images/dailyevents/grandchildren.png'],
            ['event_choice' => 'Garden - Tending plants', 'outcome' => 'Tend to your garden', 'stat_effects' => '+10 Happiness, +5 Health', 'weight' => 8, 'age_group' => 'old', 'image' => '/css/images/dailyevents/garden.png'],
            ['event_choice' => 'Doctor checkup - Healthy', 'outcome' => 'Good health checkup', 'stat_effects' => '+10 Health, +5 Happiness', 'weight' => 8, 'age_group' => 'old', 'image' => '/css/images/dailyevents/doctor-checkup.png'],
            ['event_choice' => 'Nap - Restful', 'outcome' => 'Take a restful nap', 'stat_effects' => '+10 Health, +5 Burnout', 'weight' => 8, 'age_group' => 'old', 'image' => '/css/images/dailyevents/nap.png'],
            ['event_choice' => 'Memories - Good memories', 'outcome' => 'Reminisce about good memories', 'stat_effects' => '+15 Happiness, +5 Morality', 'weight' => 7, 'age_group' => 'old', 'image' => '/css/images/dailyevents/memories.png'],
            ['event_choice' => 'Medications - Taken', 'outcome' => 'Take medications', 'stat_effects' => '+5 Health, +5 Discipline', 'weight' => 9, 'age_group' => 'old', 'image' => '/css/images/dailyevents/medication.png'],
        ];

        foreach ($events as $event) {
            DailyEvent::create($event);
        }
    }
}
