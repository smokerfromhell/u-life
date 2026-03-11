<?php

namespace Database\Seeders;

use App\Models\ProfessionTrigger;
use Illuminate\Database\Seeder;

class ProfessionTriggerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add your profession triggers from Excel here.
     * Format: ['profession' => 'Doctor', 'unlock_condition' => 'Intelligence >= 60 AND Creativity >= 40', 'notes' => 'Optional notes']
     */
    public function run(): void
    {
        $professions = [
          [
    'profession' => 'Doctor',
    'requirements' => [
        'Intelligence >= 30',
        'Discipline >= 15',
        'Morality >= 10'
    ],
    'note' => 'Medical path'
],
[
    'profession' => 'Teacher',
    'requirements' => [
        'Intelligence >= 20',
        'Charisma >= 10',
        'Discipline >= 15'
    ],
    'note' => 'Education path'
],
[
    'profession' => 'Scientist',
    'requirements' => [
        'Intelligence >= 35',
        'Creativity >= 15',
        'Discipline >= 20'
    ],
    'note' => 'Research/innovation'
],
[
    'profession' => 'Engineer',
    'requirements' => [
        'Intelligence >= 25',
        'Discipline >= 20',
        'Creativity >= 10'
    ],
    'note' => 'Technical path'
],
[
    'profession' => 'Lawyer',
    'requirements' => [
        'Intelligence >= 30',
        'Charisma >= 15',
        'Morality >= 10'
    ],
    'note' => 'Legal advocacy'
],
[
    'profession' => 'Nurse',
    'requirements' => [
        'Intelligence >= 20',
        'Discipline >= 15',
        'Morality >= 15'
    ],
    'note' => 'Caregiving'
],
[
    'profession' => 'Soldier',
    'requirements' => [
        'Strength >= 25',
        'Discipline >= 20',
        'Morality >= 5'
    ],
    'note' => 'Military path'
],
[
    'profession' => 'Athlete',
    'requirements' => [
        'Strength >= 30',
        'Ego >= 10',
        'Reputation >= 10'
    ],
    'note' => 'Sports competition'
],
[
    'profession' => 'Farmer',
    'requirements' => [
        'Strength >= 15',
        'Discipline >= 10',
        'Wealth >= 5'
    ],
    'note' => 'Agricultural livelihood'
],
[
    'profession' => 'Fisher',
    'requirements' => [
        'Luck >= 20',
        'Strength >= 10',
        'Discipline >= 5'
    ],
    'note' => 'Risk + endurance livelihood'
],
[
    'profession' => 'Artist',
    'requirements' => [
        'Creativity >= 25',
        'Isolation >= 10',
        'Ego >= 5'
    ],
    'note' => 'Creative + solitary path'
],
[
    'profession' => 'Writer',
    'requirements' => [
        'Creativity >= 20',
        'Isolation >= 5',
        'Discipline >= 10'
    ],
    'note' => 'Literary path'
],
[
    'profession' => 'Designer',
    'requirements' => [
        'Creativity >= 25',
        'Charisma >= 10',
        'Discipline >= 10'
    ],
    'note' => 'Applied creativity'
],
[
    'profession' => 'Musician',
    'requirements' => [
        'Creativity >= 20',
        'Charisma >= 15',
        'Ego >= 5'
    ],
    'note' => 'Arts + performance'
],
[
    'profession' => 'Chef',
    'requirements' => [
        'Creativity >= 20',
        'Discipline >= 15',
        'Wealth >= 5'
    ],
    'note' => 'Culinary path'
],
[
    'profession' => 'Actor',
    'requirements' => [
        'Charisma >= 25',
        'Ego >= 15',
        'Reputation >= 10'
    ],
    'note' => 'Performance + fame'
],
[
    'profession' => 'Journalist',
    'requirements' => [
        'Intelligence >= 20',
        'Creativity >= 15',
        'Morality >= 10'
    ],
    'note' => 'Writing + truth-seeking'
],
[
    'profession' => 'Business Owner',
    'requirements' => [
        'Wealth >= 40',
        'Luck >= 15',
        'Discipline >= 10'
    ],
    'note' => 'Entrepreneurial path'
],
[
    'profession' => 'Investor',
    'requirements' => [
        'Wealth >= 50',
        'Luck >= 20',
        'Reputation >= 10'
    ],
    'note' => 'Finance path'
],
[
    'profession' => 'Entrepreneur',
    'requirements' => [
        'Wealth >= 30',
        'Luck >= 15',
        'Discipline >= 10'
    ],
    'note' => 'Startup/business path'
],
[
    'profession' => 'Politician',
    'requirements' => [
        'Charisma >= 25',
        'Reputation >= 20',
        'Ego >= 10'
    ],
    'note' => 'Leadership/social influence'
],
[
    'profession' => 'Performer',
    'requirements' => [
        'Charisma >= 20',
        'Creativity >= 15',
        'Ego >= 10'
    ],
    'note' => 'Entertainment path'
],
[
    'profession' => 'Salesperson',
    'requirements' => [
        'Charisma >= 15',
        'Discipline >= 10',
        'Reputation >= 5'
    ],
    'note' => 'Commerce path'
],
[
    'profession' => 'Community Leader',
    'requirements' => [
        'Reputation >= 25',
        'Morality >= 15',
        'Discipline >= 15'
    ],
    'note' => 'Civic leadership'
],
[
    'profession' => 'Priest/Religious Leader',
    'requirements' => [
        'Morality >= 25',
        'Reputation >= 20',
        'Discipline >= 10'
    ],
    'note' => 'Spiritual path'
],
[
    'profession' => 'Philosopher/Poet',
    'requirements' => [
        'Creativity >= 25',
        'Isolation >= 15',
        'Morality >= 10'
    ],
    'note' => 'Reflective path'
],
[
    'profession' => 'Gambler',
    'requirements' => [
        'Luck >= 25',
        'Wealth >= 10',
        'Discipline <= 5'
    ],
    'note' => 'Risk-taking livelihood'
],
[
    'profession' => 'Casino Owner',
    'requirements' => [
        'Luck >= 30',
        'Wealth >= 50',
        'Reputation >= 10'
    ],
    'note' => 'High-stakes business'
]

        ];

        foreach ($professions as $profession) {
            ProfessionTrigger::firstOrCreate(
                ['profession' => $profession['profession']],
                [
                    'profession' => $profession['profession'],
                    'unlock_condition' => json_encode($profession['requirements']),
                    'notes' => $profession['note']
                ]
            );
        }
    }
}
