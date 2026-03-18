<?php

namespace Database\Seeders;

use App\Models\ProfessionTrigger;
use Database\Seeders\Concerns\GeneratesOutcomeChoices;
use Illuminate\Database\Seeder;

class ProfessionTriggerSeeder extends Seeder
{
    use GeneratesOutcomeChoices;

    /**
     * Run the database seeds.
     * 
     * Profession triggers with:
     * - unlock_condition: Stats required to unlock this profession
     * - stat_effects: Stat bonuses applied when choosing this profession
     * - description: Long description of the profession
     * - notes: Short notes about the profession
     */
    public function run(): void
    {
        $professions = [
            [
                'profession' => 'Doctor',
                'unlock_condition' => 'Intelligence >= 30 AND Discipline >= 15 AND Morality >= 10',
                'stat_effects' => '+10 Intelligence, +5 Empathy, -3 Happiness',
                'description' => 'A medical professional dedicated to healing others. Your expertise in medicine brings both respect and emotional burden.',
                'notes' => 'Medical path'
            ],
            [
                'profession' => 'Teacher',
                'unlock_condition' => 'Intelligence >= 20 AND Charisma >= 10 AND Discipline >= 15',
                'stat_effects' => '+8 Intelligence, +5 Charisma, +3 Empathy',
                'description' => 'Shape young minds as an educator. You have the power to inspire the next generation.',
                'notes' => 'Education path'
            ],
            [
                'profession' => 'Scientist',
                'unlock_condition' => 'Intelligence >= 35 AND Creativity >= 15 AND Discipline >= 20',
                'stat_effects' => '+12 Intelligence, +5 Creativity, -2 Social',
                'description' => 'Push the boundaries of human knowledge through research and innovation.',
                'notes' => 'Research/innovation'
            ],
            [
                'profession' => 'Engineer',
                'unlock_condition' => 'Intelligence >= 25 AND Discipline >= 20 AND Creativity >= 10',
                'stat_effects' => '+8 Intelligence, +5 Discipline, +3 Creativity',
                'description' => 'Build solutions to real-world problems through technical expertise.',
                'notes' => 'Technical path'
            ],
            [
                'profession' => 'Lawyer',
                'unlock_condition' => 'Intelligence >= 30 AND Charisma >= 15 AND Morality >= 10',
                'stat_effects' => '+8 Intelligence, +6 Charisma, -3 Morality',
                'description' => 'Advocate for clients in legal matters. Justice is in your hands.',
                'notes' => 'Legal advocacy'
            ],
            [
                'profession' => 'Nurse',
                'unlock_condition' => 'Intelligence >= 20 AND Discipline >= 15 AND Morality >= 15',
                'stat_effects' => '+5 Intelligence, +8 Empathy, +3 Discipline',
                'description' => 'Provide compassionate care to patients. Your dedication saves lives.',
                'notes' => 'Caregiving'
            ],
            [
                'profession' => 'Soldier',
                'unlock_condition' => 'Strength >= 25 AND Discipline >= 20 AND Morality >= 5',
                'stat_effects' => '+10 Strength, +5 Discipline, -4 Empathy',
                'description' => 'Serve your nation through military service. Honor and duty define your path.',
                'notes' => 'Military path'
            ],
            [
                'profession' => 'Athlete',
                'unlock_condition' => 'Strength >= 30 AND Ego >= 10 AND Reputation >= 10',
                'stat_effects' => '+12 Strength, +5 Ego, -2 Discipline',
                'description' => 'Compete at the highest levels of sports. Your body is your instrument.',
                'notes' => 'Sports competition'
            ],
            [
                'profession' => 'Farmer',
                'unlock_condition' => 'Strength >= 15 AND Discipline >= 10 AND Wealth >= 5',
                'stat_effects' => '+6 Strength, +4 Discipline, +3 Empathy',
                'description' => 'Work the land and provide for your community. A simple, honest life.',
                'notes' => 'Agricultural livelihood'
            ],
            [
                'profession' => 'Fisher',
                'unlock_condition' => 'Luck >= 20 AND Strength >= 10 AND Discipline >= 5',
                'stat_effects' => '+5 Luck, +6 Strength, +2 Discipline',
                'description' => 'Braving the seas for your catch. A risky but rewarding livelihood.',
                'notes' => 'Risk + endurance livelihood'
            ],
            [
                'profession' => 'Artist',
                'unlock_condition' => 'Creativity >= 25 AND Isolation >= 10 AND Ego >= 5',
                'stat_effects' => '+10 Creativity, +5 Isolation, +3 Ego',
                'description' => 'Express yourself through art. Your vision creates beauty for the world.',
                'notes' => 'Creative + solitary path'
            ],
            [
                'profession' => 'Writer',
                'unlock_condition' => 'Creativity >= 20 AND Isolation >= 5 AND Discipline >= 10',
                'stat_effects' => '+8 Creativity, +4 Isolation, +3 Intelligence',
                'description' => 'Tell stories that move hearts and minds. Words are your superpower.',
                'notes' => 'Literary path'
            ],
            [
                'profession' => 'Designer',
                'unlock_condition' => 'Creativity >= 25 AND Charisma >= 10 AND Discipline >= 10',
                'stat_effects' => '+8 Creativity, +4 Charisma, +3 Intelligence',
                'description' => 'Create visual solutions that inspire. Form meets function in your work.',
                'notes' => 'Applied creativity'
            ],
            [
                'profession' => 'Musician',
                'unlock_condition' => 'Creativity >= 20 AND Charisma >= 15 AND Ego >= 5',
                'stat_effects' => '+7 Creativity, +6 Charisma, +4 Ego',
                'description' => 'Move souls with your music. Performance is your calling.',
                'notes' => 'Arts + performance'
            ],
            [
                'profession' => 'Chef',
                'unlock_condition' => 'Creativity >= 20 AND Discipline >= 15 AND Wealth >= 5',
                'stat_effects' => '+6 Creativity, +5 Discipline, +4 Charisma',
                'description' => 'Delight palates with your culinary creations. Food is your art.',
                'notes' => 'Culinary path'
            ],
            [
                'profession' => 'Actor',
                'unlock_condition' => 'Charisma >= 25 AND Ego >= 15 AND Reputation >= 10',
                'stat_effects' => '+10 Charisma, +5 Ego, +3 Reputation',
                'description' => 'Bring characters to life on stage and screen. You are the star.',
                'notes' => 'Performance + fame'
            ],
            [
                'profession' => 'Journalist',
                'unlock_condition' => 'Intelligence >= 20 AND Creativity >= 15 AND Morality >= 10',
                'stat_effects' => '+6 Intelligence, +5 Creativity, +4 Morality',
                'description' => 'Seek truth and report it. The pen is mightier than the sword.',
                'notes' => 'Writing + truth-seeking'
            ],
            [
                'profession' => 'Business Owner',
                'unlock_condition' => 'Wealth >= 40 AND Luck >= 15 AND Discipline >= 10',
                'stat_effects' => '+8 Wealth, +4 Discipline, +3 Luck',
                'description' => 'Build and run your own enterprise. Success is your destiny.',
                'notes' => 'Entrepreneurial path'
            ],
            [
                'profession' => 'Investor',
                'unlock_condition' => 'Wealth >= 50 AND Luck >= 20 AND Reputation >= 10',
                'stat_effects' => '+10 Wealth, +5 Luck, +3 Intelligence',
                'description' => 'Make your money work for you. Financial智慧 is your game.',
                'notes' => 'Finance path'
            ],
            [
                'profession' => 'Entrepreneur',
                'unlock_condition' => 'Wealth >= 30 AND Luck >= 15 AND Discipline >= 10',
                'stat_effects' => '+7 Wealth, +5 Luck, +4 Creativity',
                'description' => 'Turn ideas into businesses. Innovation drives your success.',
                'notes' => 'Startup/business path'
            ],
            [
                'profession' => 'Politician',
                'unlock_condition' => 'Charisma >= 25 AND Reputation >= 20 AND Ego >= 10',
                'stat_effects' => '+8 Charisma, +6 Reputation, +3 Ego',
                'description' => 'Shape society through governance. Power and responsibility unite.',
                'notes' => 'Leadership/social influence'
            ],
            [
                'profession' => 'Performer',
                'unlock_condition' => 'Charisma >= 20 AND Creativity >= 15 AND Ego >= 10',
                'stat_effects' => '+7 Charisma, +5 Creativity, +4 Ego',
                'description' => 'Entertain audiences with your talents. Joy is your gift.',
                'notes' => 'Entertainment path'
            ],
            [
                'profession' => 'Salesperson',
                'unlock_condition' => 'Charisma >= 15 AND Discipline >= 10 AND Reputation >= 5',
                'stat_effects' => '+6 Charisma, +4 Discipline, +3 Reputation',
                'description' => 'Sell dreams and solutions. persuasion is your craft.',
                'notes' => 'Commerce path'
            ],
            [
                'profession' => 'Community Leader',
                'unlock_condition' => 'Reputation >= 25 AND Morality >= 15 AND Discipline >= 15',
                'stat_effects' => '+8 Reputation, +6 Morality, +4 Charisma',
                'description' => 'Lead your community toward better days. Service is your purpose.',
                'notes' => 'Civic leadership'
            ],
            [
                'profession' => 'Priest/Religious Leader',
                'unlock_condition' => 'Morality >= 25 AND Reputation >= 20 AND Discipline >= 10',
                'stat_effects' => '+10 Morality, +6 Reputation, +4 Empathy',
                'description' => 'Guide souls on their spiritual journey. Faith is your foundation.',
                'notes' => 'Spiritual path'
            ],
            [
                'profession' => 'Philosopher/Poet',
                'unlock_condition' => 'Creativity >= 25 AND Isolation >= 15 AND Morality >= 10',
                'stat_effects' => '+8 Creativity, +6 Isolation, +5 Intelligence',
                'description' => 'Contemplate the deep questions of life. Wisdom is your pursuit.',
                'notes' => 'Reflective path'
            ],
            [
                'profession' => 'Gambler',
                'unlock_condition' => 'Luck >= 25 AND Wealth >= 10 AND Discipline <= 5',
                'stat_effects' => '+8 Luck, +4 Wealth, -3 Discipline',
                'description' => 'Risk it all for big rewards. Fortune favors the bold.',
                'notes' => 'Risk-taking livelihood'
            ],
            [
                'profession' => 'Casino Owner',
                'unlock_condition' => 'Luck >= 30 AND Wealth >= 50 AND Reputation >= 10',
                'stat_effects' => '+8 Wealth, +5 Luck, +4 Reputation',
                'description' => 'Run a gambling empire. The house always wins... or does it?',
                'notes' => 'High-stakes business'
            ]
        ];

        foreach ($professions as $profession) {
            ProfessionTrigger::updateOrCreate(
                ['profession' => $profession['profession']],
                [
                    'profession' => $profession['profession'],
                    'unlock_condition' => $profession['unlock_condition'],
                    'notes' => $profession['notes'],
                    'stat_effects' => $profession['stat_effects'],
                    'description' => $profession['description'],
                    // Career choice uses the same structure as other decks (choices + outcome variants)
                    'choices' => $this->makeProfessionChoices($profession['profession'], $profession['stat_effects']),
                ]
            );
        }
    }

    private function makeProfessionChoices(string $professionName, ?string $professionEffects): array
    {
        $professionName = trim((string) $professionName);
        $professionEffects = $professionEffects ? trim((string) $professionEffects) : null;

        return [
            [
                'text' => "Commit to {$professionName}",
                'set_profession' => true,
                'stat_effects' => $professionEffects,
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => "You find your footing in the {$professionName} path.",
                        'stat_effects' => '+2 Reputation, +1 Discipline',
                    ],
                    [
                        'key' => 'failure',
                        'text' => "The {$professionName} path overwhelms you at first.",
                        'stat_effects' => '+4 Burnout, -1 Happiness',
                    ],
                ],
            ],
            [
                'text' => "Start {$professionName} carefully",
                'set_profession' => true,
                'stat_effects' => $professionEffects ? $this->neutralizeEffects($professionEffects) : null,
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => 'Small wins build momentum.',
                        'stat_effects' => '+1 Reputation, +1 Happiness',
                    ],
                    [
                        'key' => 'failure',
                        'text' => 'Progress is slow and frustrating.',
                        'stat_effects' => '+2 Burnout',
                    ],
                ],
            ],
            [
                'text' => 'Not right now',
                'set_profession' => false,
                'stat_effects' => null,
                'outcomes' => [
                    [
                        'key' => 'success',
                        'text' => 'You keep your options open.',
                        'stat_effects' => '+1 Discipline',
                    ],
                    [
                        'key' => 'failure',
                        'text' => 'Opportunity passes and doubt creeps in.',
                        'stat_effects' => '+2 Burnout, +2 Isolation',
                    ],
                ],
            ],
        ];
    }
}
