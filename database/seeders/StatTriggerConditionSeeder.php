<?php
namespace Database\Seeders;
use App\Models\StatTriggerCondition;
use Illuminate\Database\Seeder;
class StatTriggerConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Stat trigger conditions with choices structure:
     * - Each trigger has a choices array with response options
     * - Each choice has: text, stat_effects, days_to_advance
     */
    public function run(): void
    {
        $conditions = [
            // Intelligence Triggers
            [
                'trigger_stat_condition' => 'Intelligence >= 10',
                'stat_name' => 'Intelligence',
                'threshold' => 10,
                'title' => 'Quick Learner',
                'description' => 'Your intelligence is growing. You learn new things quickly.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Study harder', 'stat_effects' => '+5 Confidence, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Teach others', 'stat_effects' => '+3 Reputation, +3 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Intelligence >= 20',
                'stat_name' => 'Intelligence',
                'threshold' => 20,
                'title' => 'Study Success',
                'description' => 'Your study efforts are paying off with improved knowledge.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Continue studying', 'stat_effects' => '+5 Reputation, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Take a break', 'stat_effects' => '+3 Happiness, -2 Intelligence', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Intelligence >= 30',
                'stat_name' => 'Intelligence',
                'threshold' => 30,
                'title' => 'Breakthrough',
                'description' => 'You have a major intellectual breakthrough!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Share your discovery', 'stat_effects' => '+10 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Keep it private', 'stat_effects' => '+5 Ego, -3 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Intelligence <= 5',
                'stat_name' => 'Intelligence',
                'threshold' => 5,
                'title' => 'Struggle',
                'description' => 'You are struggling with learning and comprehension.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Get help', 'stat_effects' => '+3 Discipline, -2 Confidence', 'days_to_advance' => 0],
                    ['text' => 'Give up', 'stat_effects' => '-10 Confidence, +5 Fatigue', 'days_to_advance' => 0],
                ],
            ],
            // Strength Triggers
            [
                'trigger_stat_condition' => 'Strength >= 10',
                'stat_name' => 'Strength',
                'threshold' => 10,
                'title' => 'Extra Effort',
                'description' => 'Your physical strength is improving.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Push yourself more', 'stat_effects' => '+5 Discipline, +5 Fatigue', 'days_to_advance' => 0],
                    ['text' => 'Maintain current level', 'stat_effects' => '+3 Health, -2 Strength', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Strength >= 20',
                'stat_name' => 'Strength',
                'threshold' => 20,
                'title' => 'Hard Work',
                'description' => 'Your hard work in physical training is paying off.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Compete in sports', 'stat_effects' => '+5 Reputation, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Focus on other areas', 'stat_effects' => '+3 Discipline, -3 Strength', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Strength >= 30',
                'stat_name' => 'Strength',
                'threshold' => 30,
                'title' => 'Athletic Feat',
                'description' => 'You achieve an impressive athletic feat!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Become an athlete', 'stat_effects' => '+10 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay amateur', 'stat_effects' => '+5 Ego, +3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Strength <= 5',
                'stat_name' => 'Strength',
                'threshold' => 5,
                'title' => 'Weakness',
                'description' => 'Your physical strength is very low.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Start training', 'stat_effects' => '+3 Health, +3 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Accept it', 'stat_effects' => '-10 Health, +5 Fatigue', 'days_to_advance' => 0],
                ],
            ],
            // Charisma Triggers
            [
                'trigger_stat_condition' => 'Charisma >= 10',
                'stat_name' => 'Charisma',
                'threshold' => 10,
                'title' => 'Friendly Gesture',
                'description' => 'Your charisma helps you make friends easily.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Be social', 'stat_effects' => '+5 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay reserved', 'stat_effects' => '+2 Discipline, -3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Charisma >= 20',
                'stat_name' => 'Charisma',
                'threshold' => 20,
                'title' => 'Public Speech',
                'description' => 'You can speak publicly with confidence.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Give a speech', 'stat_effects' => '+10 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Avoid speaking', 'stat_effects' => '+2 Happiness, -5 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Charisma >= 30',
                'stat_name' => 'Charisma',
                'threshold' => 30,
                'title' => 'Political Rally',
                'description' => 'You have the charisma to lead crowds!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Enter politics', 'stat_effects' => '+15 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay private', 'stat_effects' => '+5 Ego, -3 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Charisma <= 5',
                'stat_name' => 'Charisma',
                'threshold' => 5,
                'title' => 'Awkward Encounter',
                'description' => 'Social interactions are difficult for you.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Practice social skills', 'stat_effects' => '+3 Discipline, -2 Confidence', 'days_to_advance' => 0],
                    ['text' => 'Avoid people', 'stat_effects' => '-10 Confidence, +5 Isolation', 'days_to_advance' => 0],
                ],
            ],
            // Creativity Triggers
            [
                'trigger_stat_condition' => 'Creativity >= 10',
                'stat_name' => 'Creativity',
                'threshold' => 10,
                'title' => 'Inspiration',
                'description' => 'Creative ideas flow to you naturally.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Create something', 'stat_effects' => '+5 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-2 Creativity, +2 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Creativity >= 20',
                'stat_name' => 'Creativity',
                'threshold' => 20,
                'title' => 'Artistic Breakthrough',
                'description' => 'Your artistic abilities are blossoming!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Share your art', 'stat_effects' => '+10 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Keep it personal', 'stat_effects' => '+5 Happiness, -3 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Creativity >= 30',
                'stat_name' => 'Creativity',
                'threshold' => 30,
                'title' => 'Innovation',
                'description' => 'You come up with innovative ideas!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Patent your idea', 'stat_effects' => '+10 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Share freely', 'stat_effects' => '+5 Reputation, +5 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Creativity <= 5',
                'stat_name' => 'Creativity',
                'threshold' => 5,
                'title' => 'Block',
                'description' => 'You are experiencing creative block.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Take a break', 'stat_effects' => '+3 Happiness, -3 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Push through', 'stat_effects' => '-10 Happiness, +5 Burnout', 'days_to_advance' => 0],
                ],
            ],
            // Wealth Triggers
            [
                'trigger_stat_condition' => 'Wealth >= 20',
                'stat_name' => 'Wealth',
                'threshold' => 20,
                'title' => 'Shopping Spree',
                'description' => 'You have enough wealth to enjoy shopping.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Buy things', 'stat_effects' => '-10 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Save money', 'stat_effects' => '+5 Wealth, -3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Wealth >= 40',
                'stat_name' => 'Wealth',
                'threshold' => 40,
                'title' => 'Investment',
                'description' => 'You have wealth to invest.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Invest wisely', 'stat_effects' => '+20 Wealth, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Keep savings', 'stat_effects' => '+5 Wealth, +3 Discipline', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Wealth >= 60',
                'stat_name' => 'Wealth',
                'threshold' => 60,
                'title' => 'Philanthropy',
                'description' => 'You can afford to give back to others.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Donate to charity', 'stat_effects' => '-20 Wealth, +15 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Keep wealth', 'stat_effects' => '+10 Wealth, -5 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Wealth <= 0',
                'stat_name' => 'Wealth',
                'threshold' => 0,
                'title' => 'Debt Spiral',
                'description' => 'You are in debt and struggling.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Seek help', 'stat_effects' => '+5 Discipline, -5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '+20 Debt, -10 Happiness', 'days_to_advance' => 0],
                ],
            ],
            // Luck Triggers
            [
                'trigger_stat_condition' => 'Luck >= 10',
                'stat_name' => 'Luck',
                'threshold' => 10,
                'title' => 'Lucky Break',
                'description' => 'Luck is on your side!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Take advantage', 'stat_effects' => '+10 Wealth, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Be cautious', 'stat_effects' => '+3 Discipline, +2 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Luck >= 20',
                'stat_name' => 'Luck',
                'threshold' => 20,
                'title' => 'Jackpot',
                'description' => 'You hit the jackpot!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Celebrate', 'stat_effects' => '+30 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay humble', 'stat_effects' => '+10 Wealth, +5 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Luck >= 30',
                'stat_name' => 'Luck',
                'threshold' => 30,
                'title' => 'Miracle',
                'description' => 'A miracle happens to you!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Share the luck', 'stat_effects' => '+50 Wealth, +20 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Keep it secret', 'stat_effects' => '+25 Wealth, +10 Ego', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Luck <= 5',
                'stat_name' => 'Luck',
                'threshold' => 5,
                'title' => 'Misfortune',
                'description' => 'Bad luck follows you around.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Stay positive', 'stat_effects' => '+3 Morality, -3 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Give up', 'stat_effects' => '-10 Health, -10 Wealth', 'days_to_advance' => 0],
                ],
            ],
            // Debt Triggers
            [
                'trigger_stat_condition' => 'Debt >= 10',
                'stat_name' => 'Debt',
                'threshold' => 10,
                'title' => 'Late Payment',
                'description' => 'You are falling behind on payments.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Pay what you can', 'stat_effects' => '+3 Discipline, -2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-5 Happiness, +5 Burnout', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Debt >= 20',
                'stat_name' => 'Debt',
                'threshold' => 20,
                'title' => 'Loan Shark Visit',
                'description' => 'Debt collectors are after you.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Negotiate', 'stat_effects' => '-3 Reputation, +3 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Hide', 'stat_effects' => '-10 Happiness, -5 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Debt >= 40',
                'stat_name' => 'Debt',
                'threshold' => 40,
                'title' => 'Bankruptcy',
                'description' => 'You are facing bankruptcy.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Declare bankruptcy', 'stat_effects' => '-30 Wealth, -20 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Fight it', 'stat_effects' => '+10 Burnout, -10 Wealth', 'days_to_advance' => 0],
                ],
            ],
            // Health Triggers
            [
                'trigger_stat_condition' => 'Health <= 20',
                'stat_name' => 'Health',
                'threshold' => 20,
                'title' => 'Illness',
                'description' => 'Your health is declining.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'See a doctor', 'stat_effects' => '-3 Wealth, +3 Health', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '+5 Fatigue, -5 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Health <= 10',
                'stat_name' => 'Health',
                'threshold' => 10,
                'title' => 'Hospitalization',
                'description' => 'You need to be hospitalized.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Get treatment', 'stat_effects' => '-10 Wealth, +10 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Refuse treatment', 'stat_effects' => '-15 Health, +5 Ego', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Health <= 0',
                'stat_name' => 'Health',
                'threshold' => 0,
                'title' => 'Critical Condition',
                'description' => 'Your health is in critical condition.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Fight to survive', 'stat_effects' => '+10 Discipline, -10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Accept fate', 'stat_effects' => '-20 Happiness, -10 Reputation', 'days_to_advance' => 0],
                ],
            ],
            // Addiction Triggers
            [
                'trigger_stat_condition' => 'Addiction >= 10',
                'stat_name' => 'Addiction',
                'threshold' => 10,
                'title' => 'Craving',
                'description' => 'You are experiencing cravings.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Resist', 'stat_effects' => '+5 Discipline, -3 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Give in', 'stat_effects' => '-5 Discipline, -5 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Addiction >= 20',
                'stat_name' => 'Addiction',
                'threshold' => 20,
                'title' => 'Relapse',
                'description' => 'You have relapsed into addiction.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Seek help', 'stat_effects' => '+5 Discipline, -5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Continue using', 'stat_effects' => '-10 Discipline, -10 Health', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Addiction >= 30',
                'stat_name' => 'Addiction',
                'threshold' => 30,
                'title' => 'Rehab',
                'description' => 'You need to go to rehabilitation.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Enter rehab', 'stat_effects' => '-20 Wealth, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Refuse', 'stat_effects' => '-15 Health, +5 Burnout', 'days_to_advance' => 0],
                ],
            ],
            // Burnout Triggers
            [
                'trigger_stat_condition' => 'Burnout >= 10',
                'stat_name' => 'Burnout',
                'threshold' => 10,
                'title' => 'Stress Signs',
                'description' => 'You are showing signs of burnout.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Take a break', 'stat_effects' => '+3 Happiness, -2 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Push through', 'stat_effects' => '-5 Happiness, -5 Discipline', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Burnout >= 20',
                'stat_name' => 'Burnout',
                'threshold' => 20,
                'title' => 'Forced Break',
                'description' => 'You are forced to take a break.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Rest properly', 'stat_effects' => '-10 Burnout, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Work anyway', 'stat_effects' => '-5 Health, +3 Wealth', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Burnout >= 40',
                'stat_name' => 'Burnout',
                'threshold' => 40,
                'title' => 'Breakdown',
                'description' => 'You have a mental breakdown.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Get professional help', 'stat_effects' => '-10 Wealth, -5 Burnout', 'days_to_advance' => 0],
                    ['text' => 'Self-medicate', 'stat_effects' => '-20 Burnout, -10 Wealth, -10 Reputation', 'days_to_advance' => 0],
                ],
            ],
            // Morality Triggers
            [
                'trigger_stat_condition' => 'Morality <= 0',
                'stat_name' => 'Morality',
                'threshold' => 0,
                'title' => 'Corruption',
                'description' => 'Your morality has declined significantly.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Redeem yourself', 'stat_effects' => '+5 Morality, -5 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Embrace corruption', 'stat_effects' => '+10 Wealth, -10 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Morality <= -10',
                'stat_name' => 'Morality',
                'threshold' => -10,
                'title' => 'Crime',
                'description' => 'You have committed crimes.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Turn yourself in', 'stat_effects' => '-15 Reputation, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Stay criminal', 'stat_effects' => '-20 Reputation, -10 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Morality >= 15',
                'stat_name' => 'Morality',
                'threshold' => 15,
                'title' => 'Volunteer Work',
                'description' => 'Your high morality inspires you to help others.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Volunteer', 'stat_effects' => '+10 Reputation, +5 Happiness, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Keep to yourself', 'stat_effects' => '+3 Morality, -2 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Morality >= 30',
                'stat_name' => 'Morality',
                'threshold' => 30,
                'title' => 'Charity',
                'description' => 'You are known for your charitable acts.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Give more', 'stat_effects' => '-10 Wealth, +10 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Maintain current level', 'stat_effects' => '+5 Reputation, +3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            // Happiness Triggers
            [
                'trigger_stat_condition' => 'Happiness <= 5',
                'stat_name' => 'Happiness',
                'threshold' => 5,
                'title' => 'Depression',
                'description' => 'You are feeling depressed.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Seek therapy', 'stat_effects' => '-5 Wealth, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Isolate yourself', 'stat_effects' => '+10 Isolation, +5 Creativity', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Happiness >= 20',
                'stat_name' => 'Happiness',
                'threshold' => 20,
                'title' => 'Joyful Celebration',
                'description' => 'You are feeling very happy!',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Celebrate', 'stat_effects' => '+10 Happiness, -5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Stay productive', 'stat_effects' => '+5 Discipline, -3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Happiness >= 30',
                'stat_name' => 'Happiness',
                'threshold' => 30,
                'title' => 'Fulfillment',
                'description' => 'You feel fulfilled in life.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Share joy', 'stat_effects' => '+10 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Enjoy privately', 'stat_effects' => '+5 Happiness, +3 Ego', 'days_to_advance' => 0],
                ],
            ],
            // Reputation Triggers
            [
                'trigger_stat_condition' => 'Reputation >= 20',
                'stat_name' => 'Reputation',
                'threshold' => 20,
                'title' => 'Recognition',
                'description' => 'You are gaining recognition.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Embrace fame', 'stat_effects' => '+5 Ego, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay humble', 'stat_effects' => '+3 Reputation, +3 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Reputation >= 30',
                'stat_name' => 'Reputation',
                'threshold' => 30,
                'title' => 'Fame',
                'description' => 'You are becoming famous.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Enjoy fame', 'stat_effects' => '+10 Ego, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Avoid spotlight', 'stat_effects' => '+5 Ego, -3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Reputation >= 50',
                'stat_name' => 'Reputation',
                'threshold' => 50,
                'title' => 'Legacy',
                'description' => 'You are building a lasting legacy.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Build legacy', 'stat_effects' => '+20 Morality, +20 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay grounded', 'stat_effects' => '+10 Reputation, +5 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Reputation <= 0',
                'stat_name' => 'Reputation',
                'threshold' => 0,
                'title' => 'Scandal',
                'description' => 'Your reputation has been tarnished.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Apologize', 'stat_effects' => '+5 Morality, -5 Ego', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-10 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ],
            ],
            // Discipline Triggers
            [
                'trigger_stat_condition' => 'Discipline >= 10',
                'stat_name' => 'Discipline',
                'threshold' => 10,
                'title' => 'Responsibility',
                'description' => 'You are becoming more disciplined.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Take on responsibility', 'stat_effects' => '+5 Reputation, +5 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay relaxed', 'stat_effects' => '+3 Happiness, -2 Discipline', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Discipline >= 20',
                'stat_name' => 'Discipline',
                'threshold' => 20,
                'title' => 'Leadership Role',
                'description' => 'You are ready for leadership.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Become a leader', 'stat_effects' => '+10 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+3 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Discipline >= 30',
                'stat_name' => 'Discipline',
                'threshold' => 30,
                'title' => 'Authority',
                'description' => 'You have authority over others.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Exercise authority', 'stat_effects' => '+15 Reputation, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Share power', 'stat_effects' => '+10 Reputation, +5 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Discipline <= 5',
                'stat_name' => 'Discipline',
                'threshold' => 5,
                'title' => 'Laziness',
                'description' => 'You are being lazy.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Get motivated', 'stat_effects' => '+5 Discipline, -3 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Embrace laziness', 'stat_effects' => '-10 Reputation, +5 Burnout', 'days_to_advance' => 0],
                ],
            ],
            // Isolation Triggers
            [
                'trigger_stat_condition' => 'Isolation >= 10',
                'stat_name' => 'Isolation',
                'threshold' => 10,
                'title' => 'Withdraw',
                'description' => 'You are withdrawing from others.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Reach out', 'stat_effects' => '+5 Happiness, -3 Isolation', 'days_to_advance' => 0],
                    ['text' => 'Embrace solitude', 'stat_effects' => '-5 Happiness, +5 Creativity', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Isolation >= 20',
                'stat_name' => 'Isolation',
                'threshold' => 20,
                'title' => 'Hermit Life',
                'description' => 'You are living like a hermit.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Return to society', 'stat_effects' => '+5 Happiness, -5 Isolation', 'days_to_advance' => 0],
                    ['text' => 'Continue isolating', 'stat_effects' => '-10 Happiness, +10 Creativity', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Isolation >= 30',
                'stat_name' => 'Isolation',
                'threshold' => 30,
                'title' => 'Loneliness',
                'description' => 'You are extremely lonely.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Make friends', 'stat_effects' => '+10 Happiness, -10 Isolation', 'days_to_advance' => 0],
                    ['text' => 'Accept loneliness', 'stat_effects' => '-20 Happiness, -10 Ego', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Isolation <= 0',
                'stat_name' => 'Isolation',
                'threshold' => 0,
                'title' => 'Social Butterfly',
                'description' => 'You are always surrounded by people.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Enjoy social life', 'stat_effects' => '+10 Happiness, -5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Find balance', 'stat_effects' => '+5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ],
            ],
            // Ego Triggers
            [
                'trigger_stat_condition' => 'Ego >= 10',
                'stat_name' => 'Ego',
                'threshold' => 10,
                'title' => 'Pride',
                'description' => 'You are feeling proud.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Show pride', 'stat_effects' => '-5 Reputation, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay humble', 'stat_effects' => '+3 Reputation, -2 Happiness', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Ego >= 20',
                'stat_name' => 'Ego',
                'threshold' => 20,
                'title' => 'Arrogance',
                'description' => 'You are becoming arrogant.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Check ego', 'stat_effects' => '+5 Morality, -3 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay arrogant', 'stat_effects' => '-10 Reputation, -10 Morality', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Ego >= 30',
                'stat_name' => 'Ego',
                'threshold' => 30,
                'title' => 'Narcissism',
                'description' => 'You have become narcissistic.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Get help', 'stat_effects' => '+5 Morality, -10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Embrace it', 'stat_effects' => '+10 Isolation, -15 Reputation', 'days_to_advance' => 0],
                ],
            ],
            [
                'trigger_stat_condition' => 'Ego <= 0',
                'stat_name' => 'Ego',
                'threshold' => 0,
                'title' => 'Humility',
                'description' => 'You are very humble.',
                'weight' => 0.3,
                'choices' => [
                    ['text' => 'Build confidence', 'stat_effects' => '+5 Ego, +3 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay humble', 'stat_effects' => '+10 Reputation, +10 Morality', 'days_to_advance' => 0],
                ],
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
