<?php

namespace Database\Seeders;

use App\Models\DailyEvent;
use App\Models\CulturalEvent;
use App\Models\AgeSpecificEvent;
use App\Models\ProfessionPathEvent;
use Illuminate\Database\Seeder;

class SampleEventChoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daily Events with Choices
        DailyEvent::create([
            'event_choice' => 'Found Money',
            'title' => 'Found Money',
            'description' => 'You stumble upon some cash on the street.',
            'outcome' => 'A lucky day! You find some cash.',
            'stat_effects' => '+10 Wealth, +5 Happiness',
            'image' => '/css/images/event-coins.jpg',
            'weight' => 3,
            'choices' => [
                ['text' => 'Keep it all', 'stat_effects' => '+15 Wealth, -5 Morality'],
                ['text' => 'Donate half to charity', 'stat_effects' => '+5 Wealth, +10 Morality, +8 Happiness'],
                ['text' => 'Turn it in to police', 'stat_effects' => '+0 Wealth, +15 Morality, +3 Reputation']
            ]
        ]);

        DailyEvent::create([
            'event_choice' => 'Bad Weather',
            'title' => 'Unexpected Storm',
            'description' => 'You get caught in a sudden rainstorm.',
            'outcome' => 'The weather takes a turn for the worse.',
            'stat_effects' => '-8 Health, +2 Discipline',
            'image' => '/css/images/event-storm.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Run for shelter', 'stat_effects' => '-5 Health, +3 Luck'],
                ['text' => 'Enjoy the rain', 'stat_effects' => '+5 Happiness, -3 Health'],
                ['text' => 'Help someone in need', 'stat_effects' => '-10 Health, +12 Morality, +5 Reputation']
            ]
        ]);

        DailyEvent::create([
            'event_choice' => 'Delicious Meal',
            'title' => 'Found a Great Restaurant',
            'description' => 'You discover an amazing new restaurant.',
            'outcome' => 'A wonderful culinary experience.',
            'stat_effects' => '+8 Happiness, +5 Wealth (cost)',
            'image' => '/css/images/event-food.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Treat yourself to a fancy meal', 'stat_effects' => '+15 Happiness, -10 Wealth'],
                ['text' => 'Grab a quick snack', 'stat_effects' => '+5 Happiness, -2 Wealth'],
                ['text' => 'Skip it (save money)', 'stat_effects' => '-3 Happiness, +3 Wealth']
            ]
        ]);

        // Cultural Events with Choices
        CulturalEvent::create([
            'event_choice' => 'Festival',
            'title' => 'Local Festival',
            'description' => 'The town is celebrating with music, food, and games.',
            'outcome' => 'A vibrant cultural celebration.',
            'stat_effects' => '+10 Happiness, +5 Reputation',
            'image' => '/css/images/event-festival.jpg',
            'weight' => 3,
            'choices' => [
                ['text' => 'Fully participate and dance', 'stat_effects' => '+15 Happiness, +8 Charisma, +5 Reputation'],
                ['text' => 'Watch and enjoy from afar', 'stat_effects' => '+8 Happiness, +3 Reputation'],
                ['text' => 'Volunteer to help organize', 'stat_effects' => '+10 Happiness, +12 Morality, +10 Reputation, +5 Discipline']
            ]
        ]);

        CulturalEvent::create([
            'event_choice' => 'Religious Service',
            'title' => 'Religious Ceremony',
            'description' => 'You attend a local religious gathering.',
            'outcome' => 'A spiritually uplifting experience.',
            'stat_effects' => '+10 Morality, +5 Peace',
            'image' => '/css/images/event-ceremony.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Actively participate', 'stat_effects' => '+15 Morality, +8 Discipline, +8 Happiness'],
                ['text' => 'Observe quietly', 'stat_effects' => '+8 Morality, +5 Happiness'],
                ['text' => 'Donate generously', 'stat_effects' => '+10 Morality, -8 Wealth, +12 Reputation']
            ]
        ]);

        // Age-Specific Events - Child
        AgeSpecificEvent::create([
            'age_group' => 'child',
            'event_choice' => 'Playing with Friends',
            'title' => 'Playing with Friends',
            'description' => 'Your friends invite you to play outside.',
            'outcome' => 'Have fun and learn through play.',
            'stat_effects' => '+8 Creativity, +5 Happiness, +3 Luck',
            'image' => '/css/images/event-play.jpg',
            'weight' => 4,
            'choices' => [
                ['text' => 'Play adventure games', 'stat_effects' => '+10 Creativity, +8 Happiness, +3 Luck'],
                ['text' => 'Play sports', 'stat_effects' => '+5 Strength, +8 Happiness, +4 Health'],
                ['text' => 'Stay home and read', 'stat_effects' => '+10 Intelligence, +2 Happiness, +5 Discipline']
            ]
        ]);

        AgeSpecificEvent::create([
            'age_group' => 'child',
            'event_choice' => 'School Lessons',
            'title' => 'School Lessons',
            'description' => 'It\'s time for schooling.',
            'outcome' => 'Learn basic knowledge.',
            'stat_effects' => '+10 Intelligence, +3 Discipline',
            'image' => '/css/images/event-school.jpg',
            'weight' => 3,
            'choices' => [
                ['text' => 'Pay close attention', 'stat_effects' => '+15 Intelligence, +8 Discipline'],
                ['text' => 'Do the minimum', 'stat_effects' => '+5 Intelligence, +2 Discipline'],
                ['text' => 'Daydream about adventures', 'stat_effects' => '+8 Creativity, -10 Discipline, +5 Happiness']
            ]
        ]);

        // Age-Specific Events - Adult
        AgeSpecificEvent::create([
            'age_group' => 'adult',
            'event_choice' => 'Job Opportunity',
            'title' => 'Job Promotion Opportunity',
            'description' => 'Your boss offers you a significant promotion.',
            'outcome' => 'A major career advancement.',
            'stat_effects' => '+20 Wealth, +15 Reputation',
            'image' => '/css/images/event-promotion.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Accept eagerly', 'stat_effects' => '+25 Wealth, +20 Reputation, -5 Health, +5 Burnout'],
                ['text' => 'Negotiate for better terms', 'stat_effects' => '+30 Wealth, +10 Reputation, -3 Health, +8 Charisma'],
                ['text' => 'Politely decline', 'stat_effects' => '+0 Wealth, +8 Reputation, +5 Health, +3 Happiness']
            ]
        ]);

        AgeSpecificEvent::create([
            'age_group' => 'adult',
            'event_choice' => 'Community Leadership',
            'title' => 'Community Leadership Role',
            'description' => 'You\'re asked to lead a community initiative.',
            'outcome' => 'A chance to make a real difference.',
            'stat_effects' => '+12 Morality, +10 Reputation',
            'image' => '/css/images/event-community.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Take on the responsibility', 'stat_effects' => '+15 Morality, +15 Reputation, +10 Discipline, -5 Health'],
                ['text' => 'Support someone else', 'stat_effects' => '+8 Morality, +8 Reputation, +5 Happiness'],
                ['text' => 'Decline gracefully', 'stat_effects' => '+3 Morality, -5 Reputation, +5 Happiness']
            ]
        ]);

        // Profession Events - Doctor
        ProfessionPathEvent::create([
            'profession' => 'Doctor',
            'event_choice' => 'Emergency Surgery',
            'title' => 'Emergency Surgery',
            'description' => 'You must perform emergency surgery to save a patient\'s life.',
            'outcome' => 'A critical medical procedure.',
            'stat_effects' => '+25 Morality, +20 Reputation, -8 Health',
            'image' => '/css/images/event-surgery.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Perform the surgery yourself', 'stat_effects' => '+30 Morality, +25 Reputation, +15 Intelligence, -10 Health, +5 Burnout'],
                ['text' => 'Call for surgical team', 'stat_effects' => '+20 Morality, +15 Reputation, -5 Health'],
                ['text' => 'Refer to specialist', 'stat_effects' => '+15 Morality, +8 Reputation, +2 Health']
            ]
        ]);

        ProfessionPathEvent::create([
            'profession' => 'Doctor',
            'event_choice' => 'Medical Conference',
            'title' => 'Medical Conference',
            'description' => 'A prestigious medical conference offers you to present your research.',
            'outcome' => 'An opportunity to share knowledge.',
            'stat_effects' => '+12 Intelligence, +8 Reputation',
            'image' => '/css/images/event-conference.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Present groundbreaking research', 'stat_effects' => '+15 Intelligence, +15 Reputation, +8 Charisma'],
                ['text' => 'Present recent findings', 'stat_effects' => '+10 Intelligence, +10 Reputation'],
                ['text' => 'Skip and focus on patients', 'stat_effects' => '+0 Intelligence, +3 Reputation, +5 Morality']
            ]
        ]);

        // Profession Events - Engineer
        ProfessionPathEvent::create([
            'profession' => 'Engineer',
            'event_choice' => 'Major Project Win',
            'title' => 'Major Project Bid',
            'description' => 'You have a chance to bid on a major industrial project.',
            'outcome' => 'A career-defining opportunity.',
            'stat_effects' => '+30 Wealth, +20 Reputation',
            'image' => '/css/images/event-project.jpg',
            'weight' => 2,
            'choices' => [
                ['text' => 'Bid aggressively for maximum profit', 'stat_effects' => '+40 Wealth, +15 Reputation, -5 Ethics, +5 Burnout'],
                ['text' => 'Bid competitively and fairly', 'stat_effects' => '+35 Wealth, +20 Reputation, +10 Discipline'],
                ['text' => 'Bid conservatively', 'stat_effects' => '+20 Wealth, +10 Reputation, +5 Health']
            ]
        ]);

        ProfessionPathEvent::create([
            'profession' => 'Engineer',
            'event_choice' => 'Technical Innovation',
            'title' => 'Patent Your Innovation',
            'description' => 'You\'ve developed a revolutionary engineering solution.',
            'outcome' => 'An opportunity to patent your invention.',
            'stat_effects' => '+25 Wealth, +25 Reputation, +20 Intelligence',
            'image' => '/css/images/event-patent.jpg',
            'weight' => 1,
            'choices' => [
                ['text' => 'File for patent and commercialize', 'stat_effects' => '+35 Wealth, +30 Reputation, +20 Intelligence, +8 Charisma'],
                ['text' => 'Publish in academic journal', 'stat_effects' => '+15 Wealth, +20 Reputation, +25 Intelligence'],
                ['text' => 'Open-source the technology', 'stat_effects' => '+5 Wealth, +25 Reputation, +30 Morality']
            ]
        ]);
    }
}
