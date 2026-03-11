<?php

namespace Database\Seeders;

use App\Models\StatTriggerCondition;
use Illuminate\Database\Seeder;

class StatTriggerConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add your stat trigger conditions from Excel here.
     * Format: ['trigger_stat_condition' => 'Intelligence >= 50', 'stat_name' => 'Intelligence', 'threshold' => 50, ...]
     */
    public function run(): void
    {
        $conditions = [
           [
    'trigger_stat_condition' => 'Intelligence >= 10',
    'stat_name' => 'Intelligence',
    'threshold' => 10,
    'event_choice' => 'Quick Learner',
    'outcome' => 'You understand the lesson fast',
    'stat_effects' => '+5 Confidence, +5 Discipline',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Intelligence >= 20',
    'stat_name' => 'Intelligence',
    'threshold' => 20,
    'event_choice' => 'Study Success',
    'outcome' => 'You pass exams',
    'stat_effects' => '+5 Reputation, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Intelligence >= 30',
    'stat_name' => 'Intelligence',
    'threshold' => 30,
    'event_choice' => 'Breakthrough',
    'outcome' => 'You achieve academic success',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Intelligence <= 5',
    'stat_name' => 'Intelligence',
    'threshold' => 5,
    'event_choice' => 'Struggle',
    'outcome' => 'You fail your class',
    'stat_effects' => '-10 Confidence, +5 Fatigue',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Strength >= 10',
    'stat_name' => 'Strength',
    'threshold' => 10,
    'event_choice' => 'Extra Effort',
    'outcome' => 'You carry a heavy load',
    'stat_effects' => '+5 Discipline, +5 Fatigue',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Strength >= 20',
    'stat_name' => 'Strength',
    'threshold' => 20,
    'event_choice' => 'Hard Work',
    'outcome' => 'You excel physically',
    'stat_effects' => '+5 Reputation, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Strength >= 30',
    'stat_name' => 'Strength',
    'threshold' => 30,
    'event_choice' => 'Athletic Feat',
    'outcome' => 'You win a competition',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Strength <= 5',
    'stat_name' => 'Strength',
    'threshold' => 5,
    'event_choice' => 'Weakness',
    'outcome' => 'You are at risk of injury',
    'stat_effects' => '-10 Health, +5 Fatigue',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Charisma >= 10',
    'stat_name' => 'Charisma',
    'threshold' => 10,
    'event_choice' => 'Friendly Gesture',
    'outcome' => 'You make new friends',
    'stat_effects' => '+5 Happiness, +5 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Charisma >= 20',
    'stat_name' => 'Charisma',
    'threshold' => 20,
    'event_choice' => 'Public Speech',
    'outcome' => 'You inspire a crowd',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Charisma >= 30',
    'stat_name' => 'Charisma',
    'threshold' => 30,
    'event_choice' => 'Political Rally',
    'outcome' => 'You win supporters at a rally',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Charisma <= 5',
    'stat_name' => 'Charisma',
    'threshold' => 5,
    'event_choice' => 'Awkward Encounter',
    'outcome' => 'You suffer a social failure',
    'stat_effects' => '-10 Confidence, +5 Isolation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Creativity >= 10',
    'stat_name' => 'Creativity',
    'threshold' => 10,
    'event_choice' => 'Inspiration',
    'outcome' => 'You come up with a new idea',
    'stat_effects' => '+5 Happiness, +5 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Creativity >= 20',
    'stat_name' => 'Creativity',
    'threshold' => 20,
    'event_choice' => 'Artistic Breakthrough',
    'outcome' => 'You create a masterpiece',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Creativity >= 30',
    'stat_name' => 'Creativity',
    'threshold' => 30,
    'event_choice' => 'Innovation',
    'outcome' => 'You invent something new',
    'stat_effects' => '+10 Wealth, +10 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Creativity <= 5',
    'stat_name' => 'Creativity',
    'threshold' => 5,
    'event_choice' => 'Block',
    'outcome' => 'You have no inspiration',
    'stat_effects' => '-10 Happiness, +5 Burnout',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Wealth >= 20',
    'stat_name' => 'Wealth',
    'threshold' => 20,
    'event_choice' => 'Shopping Spree',
    'outcome' => 'You spend money on luxuries',
    'stat_effects' => '-10 Wealth, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Wealth >= 40',
    'stat_name' => 'Wealth',
    'threshold' => 40,
    'event_choice' => 'Investment',
    'outcome' => 'You expand your business',
    'stat_effects' => '+20 Wealth, +10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Wealth >= 60',
    'stat_name' => 'Wealth',
    'threshold' => 60,
    'event_choice' => 'Philanthropy',
    'outcome' => 'You donate to the community',
    'stat_effects' => '-20 Wealth, +15 Reputation, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Wealth <= 0',
    'stat_name' => 'Wealth',
    'threshold' => 0,
    'event_choice' => 'Debt Spiral',
    'outcome' => 'You encounter a loan shark',
    'stat_effects' => '+20 Debt, -10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Luck >= 10',
    'stat_name' => 'Luck',
    'threshold' => 10,
    'event_choice' => 'Lucky Break',
    'outcome' => 'You experience unexpected success',
    'stat_effects' => '+10 Wealth, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Luck >= 20',
    'stat_name' => 'Luck',
    'threshold' => 20,
    'event_choice' => 'Jackpot',
    'outcome' => 'You win the lottery',
    'stat_effects' => '+30 Wealth, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Luck >= 30',
    'stat_name' => 'Luck',
    'threshold' => 30,
    'event_choice' => 'Miracle',
    'outcome' => 'A life-changing event occurs',
    'stat_effects' => '+50 Wealth, +20 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Luck <= 5',
    'stat_name' => 'Luck',
    'threshold' => 5,
    'event_choice' => 'Misfortune',
    'outcome' => 'You suffer an accident',
    'stat_effects' => '-10 Health, -10 Wealth',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Debt >= 10',
    'stat_name' => 'Debt',
    'threshold' => 10,
    'event_choice' => 'Late Payment',
    'outcome' => 'Stress builds from late payments',
    'stat_effects' => '-5 Happiness, +5 Burnout',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Debt >= 20',
    'stat_name' => 'Debt',
    'threshold' => 20,
    'event_choice' => 'Loan Shark Visit',
    'outcome' => 'You are threatened by a loan shark',
    'stat_effects' => '-10 Happiness, -5 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Debt >= 40',
    'stat_name' => 'Debt',
    'threshold' => 40,
    'event_choice' => 'Bankruptcy',
    'outcome' => 'You lose your assets',
    'stat_effects' => '-30 Wealth, -20 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Health <= 20',
    'stat_name' => 'Health',
    'threshold' => 20,
    'event_choice' => 'Illness',
    'outcome' => 'You take a sick day',
    'stat_effects' => '+5 Fatigue, -5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Health <= 10',
    'stat_name' => 'Health',
    'threshold' => 10,
    'event_choice' => 'Hospitalization',
    'outcome' => 'You are forced to rest in hospital',
    'stat_effects' => '-10 Wealth, +10 Burnout',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Health <= 0',
    'stat_name' => 'Health',
    'threshold' => 0,
    'event_choice' => 'Critical Condition',
    'outcome' => 'You are near death',
    'stat_effects' => '-20 Happiness, -10 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Addiction >= 10',
    'stat_name' => 'Addiction',
    'threshold' => 10,
    'event_choice' => 'Craving',
    'outcome' => 'You feel strong temptation',
    'stat_effects' => '-5 Discipline, -5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Addiction >= 20',
    'stat_name' => 'Addiction',
    'threshold' => 20,
    'event_choice' => 'Relapse',
    'outcome' => 'You lose control to addiction',
    'stat_effects' => '-10 Discipline, -10 Health',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Addiction >= 30',
    'stat_name' => 'Addiction',
    'threshold' => 30,
    'event_choice' => 'Rehab',
    'outcome' => 'You attempt recovery in rehab',
    'stat_effects' => '-20 Wealth, +10 Discipline',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Burnout >= 10',
    'stat_name' => 'Burnout',
    'threshold' => 10,
    'event_choice' => 'Stress Signs',
    'outcome' => 'Your fatigue starts to show',
    'stat_effects' => '-5 Happiness, -5 Discipline',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Burnout >= 20',
    'stat_name' => 'Burnout',
    'threshold' => 20,
    'event_choice' => 'Forced Break',
    'outcome' => 'You are forced to take rest',
    'stat_effects' => '-10 Burnout, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Burnout >= 40',
    'stat_name' => 'Burnout',
    'threshold' => 40,
    'event_choice' => 'Breakdown',
    'outcome' => 'You suffer a breakdown and hospitalization',
    'stat_effects' => '-20 Burnout, -10 Wealth, -10 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Morality <= 0',
    'stat_name' => 'Morality',
    'threshold' => 0,
    'event_choice' => 'Corruption',
    'outcome' => 'You gain wealth through corruption',
    'stat_effects' => '+10 Wealth, -10 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Morality <= -10',
    'stat_name' => 'Morality',
    'threshold' => -10,
    'event_choice' => 'Crime',
    'outcome' => 'You are arrested for crime',
    'stat_effects' => '-20 Reputation, -10 Happiness, +10 Isolation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Morality >= 15',
    'stat_name' => 'Morality',
    'threshold' => 15,
    'event_choice' => 'Volunteer Work',
    'outcome' => 'You help the community',
    'stat_effects' => '+10 Reputation, +5 Happiness, +5 Morality',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Morality >= 30',
    'stat_name' => 'Morality',
    'threshold' => 30,
    'event_choice' => 'Charity',
    'outcome' => 'You donate to the community',
    'stat_effects' => '-10 Wealth, +10 Reputation, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Happiness <= 5',
    'stat_name' => 'Happiness',
    'threshold' => 5,
    'event_choice' => 'Depression',
    'outcome' => 'You withdraw socially',
    'stat_effects' => '+10 Isolation, +5 Creativity',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Happiness >= 20',
    'stat_name' => 'Happiness',
    'threshold' => 20,
    'event_choice' => 'Joyful Celebration',
    'outcome' => 'You party with friends',
    'stat_effects' => '+10 Happiness, -5 Discipline',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Happiness >= 30',
    'stat_name' => 'Happiness',
    'threshold' => 30,
    'event_choice' => 'Fulfillment',
    'outcome' => 'You achieve inner peace',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Reputation >= 20',
    'stat_name' => 'Reputation',
    'threshold' => 20,
    'event_choice' => 'Recognition',
    'outcome' => 'You receive a local award',
    'stat_effects' => '+5 Ego, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Reputation >= 30',
    'stat_name' => 'Reputation',
    'threshold' => 30,
    'event_choice' => 'Fame',
    'outcome' => 'You receive a national award',
    'stat_effects' => '+10 Ego, +10 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Reputation >= 50',
    'stat_name' => 'Reputation',
    'threshold' => 50,
    'event_choice' => 'Legacy',
    'outcome' => 'You become a historical figure',
    'stat_effects' => '+20 Morality, +20 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Reputation <= 0',
    'stat_name' => 'Reputation',
    'threshold' => 0,
    'event_choice' => 'Scandal',
    'outcome' => 'You suffer public shame',
    'stat_effects' => '-10 Happiness, +10 Isolation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Discipline >= 10',
    'stat_name' => 'Discipline',
    'threshold' => 10,
    'event_choice' => 'Responsibility',
    'outcome' => 'You are trusted with responsibility',
    'stat_effects' => '+5 Reputation, +5 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Discipline >= 20',
    'stat_name' => 'Discipline',
    'threshold' => 20,
    'event_choice' => 'Leadership Role',
    'outcome' => 'You are promoted to a leadership role',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Discipline >= 30',
    'stat_name' => 'Discipline',
    'threshold' => 30,
    'event_choice' => 'Authority',
    'outcome' => 'You command respect',
    'stat_effects' => '+15 Reputation, +10 Morality',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Discipline <= 5',
    'stat_name' => 'Discipline',
    'threshold' => 5,
    'event_choice' => 'Laziness',
    'outcome' => 'You fail your responsibilities',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Isolation >= 10',
    'stat_name' => 'Isolation',
    'threshold' => 10,
    'event_choice' => 'Withdraw',
    'outcome' => 'You retreat socially',
    'stat_effects' => '-5 Happiness, +5 Creativity',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Isolation >= 20',
    'stat_name' => 'Isolation',
    'threshold' => 20,
    'event_choice' => 'Hermit Life',
    'outcome' => 'You live in total withdrawal',
    'stat_effects' => '-10 Happiness, +10 Creativity',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Isolation >= 30',
    'stat_name' => 'Isolation',
    'threshold' => 30,
    'event_choice' => 'Loneliness',
    'outcome' => 'You suffer severe isolation',
    'stat_effects' => '-20 Happiness, -10 Ego',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Isolation <= 0',
    'stat_name' => 'Isolation',
    'threshold' => 0,
    'event_choice' => 'Social Butterfly',
    'outcome' => 'You live a party life',
    'stat_effects' => '+10 Happiness, -5 Discipline',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Ego >= 10',
    'stat_name' => 'Ego',
    'threshold' => 10,
    'event_choice' => 'Pride',
    'outcome' => 'You show off',
    'stat_effects' => '-5 Reputation, +5 Happiness',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Ego >= 20',
    'stat_name' => 'Ego',
    'threshold' => 20,
    'event_choice' => 'Arrogance',
    'outcome' => 'You have conflict with peers',
    'stat_effects' => '-10 Reputation, -10 Morality',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Ego >= 30',
    'stat_name' => 'Ego',
    'threshold' => 30,
    'event_choice' => 'Narcissism',
    'outcome' => 'You become self-obsessed',
    'stat_effects' => '+10 Isolation, -15 Reputation',
    'weight' => 0.3
],
[
    'trigger_stat_condition' => 'Ego <= 0',
    'stat_name' => 'Ego',
    'threshold' => 0,
    'event_choice' => 'Humility',
    'outcome' => 'You gain respect through humility',
    'stat_effects' => '+10 Reputation, +10 Morality',
    'weight' => 0.3
],



        ];

        foreach ($conditions as $condition) {
            StatTriggerCondition::firstOrCreate(
                ['trigger_stat_condition' => $condition['trigger_stat_condition']],
                $condition
            );
        }
    }
}
