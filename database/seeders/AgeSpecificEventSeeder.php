<?php
namespace Database\Seeders;
use App\Models\AgeSpecificEvent;
use Database\Seeders\Concerns\GeneratesOutcomeChoices;
use Illuminate\Database\Seeder;
class AgeSpecificEventSeeder extends Seeder
{
    use GeneratesOutcomeChoices;
    /**
     * Run the database seeds.
     * 
     * Events with branching fields for continuous story flow:
     * - event_category: groups related events (education, career, family, health, social, skill, random)
     * - chain_order: sequence in narrative (1=start, 2=follow-up, 3=conclusion)
     * - parent_category: links to previous event in chain
     * - required_choice_outcome: outcome needed from parent (positive, negative, neutral)
     */
    public function run(): void
    {
        // Clear existing events
        AgeSpecificEvent::truncate();
        $events = [
            // ============================================
            // CHILD EVENTS
            // ============================================
            // === EDUCATION CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'School Start - Excited to learn','stat_effects' => '+10 Intelligence, +10 Discipline', 'weight' => 0.7, 'event_category' => 'education', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'School Start - Struggles with lessons', 'stat_effects' => '-5 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - High score', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education', 'required_choice_outcome' => 'positive'],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - Low score', 'stat_effects' => '-10 Intelligence, -5 Happiness', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - Cheating caught', 'stat_effects' => '-15 Reputation, -10 Morality', 'weight' => 0.2, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Learning - Reads early', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 3, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Learning - Struggles to read', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 3, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood School Trip - Educational success', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education'],
            // === FAMILY CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Family Bonding - Supportive parents', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.8, 'event_category' => 'family', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Family Bonding - Neglectful parents', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Sibling Bond - Close sibling', 'stat_effects' => '+15 Happiness, +5 Morality', 'weight' => 0.7, 'event_category' => 'family', 'chain_order' => 2, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Sibling Bond - Rivalry', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 2, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Bonding - Grandparent love', 'stat_effects' => '+15 Happiness, +10 Morality', 'weight' => 0.6, 'event_category' => 'family', 'chain_order' => 3, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Bonding - Grandparent loss', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 3, 'parent_category' => 'family'],
            // === SOCIAL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Childhood Friend - Best friend found', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.7, 'event_category' => 'social', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Childhood Friend - Lonely childhood', 'stat_effects' => '+10 Isolation, -10 Happiness', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Playtime - Outdoor games', 'stat_effects' => '+10 Strength, +10 Happiness', 'weight' => 0.8, 'event_category' => 'social', 'chain_order' => 2, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Playtime - Stays indoors', 'stat_effects' => '-5 Health, +5 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 2, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Stands up', 'stat_effects' => '+10 Reputation, +10 Morality', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Silent', 'stat_effects' => '-10 Happiness, +10 Isolation', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Severe trauma', 'stat_effects' => '-30 Happiness, +20 Burnout', 'weight' => 0.1, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Playdate - Fun with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.6, 'event_category' => 'social', 'chain_order' => 4, 'parent_category' => 'social'],
            // === HEALTH CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Birth - Healthy baby', 'stat_effects' => '+20 Health, +20 Happiness', 'weight' => 0.9, 'event_category' => 'health', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Birth - Complicated birth', 'stat_effects' => '-10 Health, +10 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Steps - Walk early', 'stat_effects' => '+10 Strength, +5 Discipline', 'weight' => 0.7, 'event_category' => 'health', 'chain_order' => 2, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'First Steps - Walk late', 'stat_effects' => '-5 Strength, -5 Happiness', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 2, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Quick recovery', 'stat_effects' => '+10 Health, +5 Happiness', 'weight' => 0.8, 'event_category' => 'health', 'chain_order' => 3, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Long sickness', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 3, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Chickenpox', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.4, 'event_category' => 'health', 'chain_order' => 4, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Nutrition - Balanced diet', 'stat_effects' => '+15 Health, +5 Discipline', 'weight' => 0.7, 'event_category' => 'health', 'chain_order' => 5, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Nutrition - Malnutrition', 'stat_effects' => '-20 Health, +10 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 5, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Minor fall', 'stat_effects' => '-5 Health, +5 Burnout', 'weight' => 0.4, 'event_category' => 'health', 'chain_order' => 6, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Major injury', 'stat_effects' => '-20 Health, -10 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 6, 'parent_category' => 'health'],
            // === SKILL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'First Words - Speaks clearly', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'weight' => 0.7, 'event_category' => 'skill', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Words - Speech delay', 'stat_effects' => '-5 Intelligence, -5 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 1, 'parent_category' => null],
            [
                'age_group' => 'child',
                'event_choice' => 'Childhood Talent',
                'description' => 'Every child has unique talents waiting to be discovered.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'ageSpecific',
                'deck_label' => 'Age Event',
                'repeatable' => false,
                'weight' => 0.4,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 17,
                'event_category' => 'skill',
                'chain_order' => 2,
                'parent_category' => 'skill',
                'choices' => [
                    ['text' => 'Shows skill', 'stat_effects' => '+15 Creativity, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Talent ignored', 'stat_effects' => '-10 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            ['age_group' => 'child', 'event_choice' => 'Childhood Hobby - Learns instrument', 'stat_effects' => '+15 Creativity, +10 Discipline', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 3, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Hobby - Quits hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 3, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Dream - Inspired', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 4, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Chores - Helps family', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.7, 'event_category' => 'skill', 'chain_order' => 5, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Chores - Refuses chores', 'stat_effects' => '-10 Discipline, -5 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 5, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Sports - Wins race', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 6, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Sports - Loses race', 'stat_effects' => '-5 Happiness, +5 Burnout', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 6, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Fear - Overcomes fear', 'stat_effects' => '+10 Discipline, +5 Morality', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 7, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Fear - Fear worsens', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 7, 'parent_category' => 'skill'],
            // === RANDOM CHILD EVENTS ===
            ['age_group' => 'child', 'event_choice' => 'First Pet - Pet adopted', 'stat_effects' => '+10 Happiness, +5 Morality', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Pet - Pet lost', 'stat_effects' => '-15 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Birthday Party - Celebrated', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Birthday Party - Forgotten', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Travel - Family trip', 'stat_effects' => '+10 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Trip - Zoo visit', 'stat_effects' => '+10 Happiness, +5 Creativity', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Trip - Amusement park', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Celebration - Christmas joy', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Celebration - New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Religion - Joins devotion', 'stat_effects' => '+15 Morality, +10 Reputation', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Curiosity - Explores safely', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            // Fatal child events
            ['age_group' => 'child', 'event_choice' => 'Infant Mortality', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Fatal illness', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Fatal accident', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            // ============================================
            // TEEN EVENTS
            // ============================================
            // === EDUCATION CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'High School Start - Good grades', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'weight' => 0.6, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'High School Start - Poor grades', 'stat_effects' => '-10 Intelligence, +10 Burnout', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage School Exam - High score', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 5, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage School Exam - Low score', 'stat_effects' => '-10 Intelligence, -5 Happiness', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 5, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Learning - Excels in science', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 6, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Learning - Struggles in math', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 6, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Graduation - With honors', 'stat_effects' => '+20 Intelligence, +15 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 7, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'Graduation - Barely passes', 'stat_effects' => '+5 Intelligence, -5 Reputation', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 7, 'parent_category' => 'education'],
            // === SOCIAL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Crush - Mutual feelings', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'social', 'chain_order' => 5, 'parent_category' => 'social', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'First Crush - Rejected', 'stat_effects' => '-15 Happiness, +5 Isolation', 'weight' => 0.5, 'event_category' => 'social', 'chain_order' => 5, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'First Love - Relationship begins', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 6, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'First Love - Heartbreak', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 6, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Friendship - Loyal friend', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 7, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Friendship - Betrayal', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 7, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Relationship - Healthy romance', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 8, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Relationship - Breakup', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 8, 'parent_category' => 'social'],
            // === FAMILY CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Family support', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 4, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Family conflict', 'stat_effects' => '-20 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 4, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Grandparent love', 'stat_effects' => '+15 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 5, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Grandparent loss', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 5, 'parent_category' => 'family'],
            // === CAREER CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Job (Part-time) - Hired', 'stat_effects' => '+10 Wealth, +5 Discipline', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'First Job (Part-time) - Fired', 'stat_effects' => '-5 Wealth, -5 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Peer Pressure - Resists', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 2, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Peer Pressure - Gives in', 'stat_effects' => '-10 Discipline, -10 Morality', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 2, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Rebellion - Sneaks out', 'stat_effects' => '+10 Happiness, -10 Discipline', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 3, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Rebellion - Caught', 'stat_effects' => '-10 Reputation, +5 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 3, 'parent_category' => 'career'],
            // === SKILL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Sports Team - Makes varsity', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 9, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Sports Team - Cut from team', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 9, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Hobby - Learns guitar', 'stat_effects' => '+15 Creativity, +10 Discipline', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 10, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Hobby - Quits hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 10, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Talent - Shows skill', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 11, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Talent - Talent mocked', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'skill', 'chain_order' => 11, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Contest - Wins', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 12, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Contest - Loses', 'stat_effects' => '-5 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 12, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Sports - Wins championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 13, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Fear - Overcomes fear', 'stat_effects' => '+10 Discipline, +5 Morality', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 14, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Fear - Fear worsens', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 14, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Exploration - Safe adventure', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 15, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Chores - Helps family', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 16, 'parent_category' => 'skill'],
            // === HEALTH CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Flu recovery', 'stat_effects' => '-10 Health, -5 Happiness', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 7, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Dengue recovery', 'stat_effects' => '-20 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 7, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Minor injury', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 8, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Major injury', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 8, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Bike crash', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 9, 'parent_category' => 'health'],
            // === RANDOM TEEN EVENTS ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Fun night', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Embarrassment', 'stat_effects' => '-10 Reputation, -10 Happiness', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Festival - Joins parade', 'stat_effects' => '+10 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Festival - Joins cultural parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Celebration - Christmas joy', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Celebration - New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            // Fatal teen events
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Dengue fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Fatal accident', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Fatal overdose', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            // ============================================
            // ADULT EVENTS
            // ============================================
            // === EDUCATION CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'College - Graduates', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'College - Drops out', 'stat_effects' => '-10 Intelligence, -10 Reputation', 'weight' => 0.2, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education'],
            // === CAREER CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Hired', 'stat_effects' => '+20 Wealth, +10 Discipline', 'weight' => 0.4, 'event_category' => 'career', 'chain_order' => 4, 'parent_category' => 'career', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Rejected', 'stat_effects' => '-10 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 4, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Promotion - Rise in rank', 'stat_effects' => '+20 Wealth, +15 Reputation', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 5, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Promotion - Passed over', 'stat_effects' => '-10 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 5, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Change - Successful transition', 'stat_effects' => '+20 Wealth, +10 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 6, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Change - Failed transition', 'stat_effects' => '-10 Wealth, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 6, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Business Venture - Startup success', 'stat_effects' => '+30 Wealth, +20 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 7, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Business Venture - Startup failure', 'stat_effects' => '-20 Wealth, +20 Debt', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 7, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Migration - Move abroad', 'stat_effects' => '+20 Wealth, +10 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 8, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Migration - Denied visa', 'stat_effects' => '-10 Happiness, -5 Reputation', 'weight' => 0.1, 'event_category' => 'career', 'chain_order' => 8, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Midlife Crisis - Reinvent self', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 9, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Midlife Crisis - Burnout deepens', 'stat_effects' => '+20 Burnout, -20 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 9, 'parent_category' => 'career'],
            // === FAMILY CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Marriage - Wedding', 'stat_effects' => '+30 Happiness, +10 Reputation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 6, 'parent_category' => 'family', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'Marriage - Cancelled engagement', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 6, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child born', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child illness', 'stat_effects' => '-15 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child fatality', 'stat_effects' => '-50 Happiness, +20 Isolation', 'weight' => 0.05, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Divorce - Separation', 'stat_effects' => '-30 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 8, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Divorce - Amicable split', 'stat_effects' => '-10 Happiness, +5 Morality', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 8, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Bonding - Family support', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 9, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Bonding - Family conflict', 'stat_effects' => '-20 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 9, 'parent_category' => 'family'],
            // === SOCIAL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Friendship - Loyal friend', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 10, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Friendship - Betrayal', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 10, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Relationship - Healthy romance', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 11, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Relationship - Breakup', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 11, 'parent_category' => 'social'],
            // === HEALTH CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Major illness', 'stat_effects' => '-30 Health, +20 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 10, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Recovery', 'stat_effects' => '+20 Health, +10 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 10, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Illness - Flu recovery', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 11, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Illness - Severe dengue recovery', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 11, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Bike crash', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 12, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Car crash survival', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 12, 'parent_category' => 'health'],
            // === SKILL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Hobby - Learns painting', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 17, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Hobby - Gives up hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 17, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Talent - Shows skill', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 18, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Talent - Talent mocked', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'skill', 'chain_order' => 18, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Learning - Excels in training', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 19, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Learning - Struggles in training', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 19, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Sports - Wins championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 20, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Contest - Wins', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 21, 'parent_category' => 'skill'],
            // === RANDOM ADULT EVENTS ===
            ['age_group' => 'adult', 'event_choice' => 'Inheritance - Receive wealth', 'stat_effects' => '+50 Wealth, +10 Reputation', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Inheritance - Family dispute', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Fame - Become celebrity', 'stat_effects' => '+30 Reputation, +20 Ego', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Fame - Scandal', 'stat_effects' => '-30 Reputation, -20 Happiness', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Political Change - Join movement', 'stat_effects' => '+20 Morality, +20 Reputation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Survive typhoon', 'stat_effects' => '-10 Health, -10 Wealth', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Lose property', 'stat_effects' => '-30 Wealth, -20 Happiness', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Celebration - Christmas joy', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Celebration - New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Festival - Joins cultural parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            // Fatal adult events
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Fatal illness', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Fatal workplace accident', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Fatal disaster', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Car crash fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            // ============================================
            // OLD EVENTS
            // ============================================
            // === RETIREMENT CHAIN ===
            ['age_group' => 'old', 'event_choice' => 'Retirement - Peaceful retirement', 'stat_effects' => '+20 Happiness, -10 Isolation', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 10, 'parent_category' => 'career', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'Retirement - Forced retirement', 'stat_effects' => '-15 Happiness, -10 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 10, 'parent_category' => 'career'],
            // === FAMILY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Bond with grandchild', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Estranged family', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Grandchild fatality', 'stat_effects' => '-50 Happiness, +20 Isolation', 'weight' => 0.05, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Elder Bonding - Family reunion', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 11, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Elder Bonding - Family conflict', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 11, 'parent_category' => 'family'],
            // === HEALTH CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Arthritis', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 13, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Dementia onset', 'stat_effects' => '-20 Intelligence, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 13, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Flu recovery', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 14, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Pneumonia recovery', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 14, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Minor fall', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 15, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Hip fracture', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 15, 'parent_category' => 'health'],
            // === SKILL/LEGACY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Writes memoir', 'stat_effects' => '+20 Creativity, +15 Reputation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Memoir ignored', 'stat_effects' => '-10 Happiness, +5 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Memoir celebrated', 'stat_effects' => '+30 Reputation, +20 Happiness', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Wisdom Sharing - Mentor youth', 'stat_effects' => '+20 Morality, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 23, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Wisdom Sharing - Advice ignored', 'stat_effects' => '-10 Happiness, +5 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 23, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Community Role - Joins senior group', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 12, 'parent_category' => 'social'],
            ['age_group' => 'old', 'event_choice' => 'Community Role - Declines participation', 'stat_effects' => '+10 Isolation, -10 Happiness', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 12, 'parent_category' => 'social'],
            // === SOCIAL (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Elder Friendship - Loyal companion', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 13, 'parent_category' => 'social'],
            ['age_group' => 'old', 'event_choice' => 'Elder Friendship - Betrayal', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 13, 'parent_category' => 'social'],
            // === RANDOM OLD EVENTS ===
            ['age_group' => 'old', 'event_choice' => 'Elder Celebration - Christmas joy', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Celebration - Missed Christmas', 'stat_effects' => '-15 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Travel - Pilgrimage', 'stat_effects' => '+20 Morality, +15 Happiness', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Hobby - Gardening', 'stat_effects' => '+10 Creativity, +10 Health', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 24, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Hobby - Abandons hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 24, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Peaceful reflection', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 25, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Regret', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 25, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Festival - Joins parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Contest - Wins', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 26, 'parent_category' => 'skill'],
            // === END OF LIFE (Old) ===
            ['age_group' => 'old', 'event_choice' => 'End of Life - Peaceful passing', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.5, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Sudden death', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Violent death', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            // Fatal old events
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Fatal illness', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Pneumonia fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Fatal fall', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Fatal heart attack', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
        ];
        foreach ($events as $event) {
            AgeSpecificEvent::create($event);
        }
        // Ensure all age-specific events have life-action style choices with outcome variants
        $this->ensureAgeSpecificChoices();
    }
    private function ensureAgeSpecificChoices(): void
    {
        $events = AgeSpecificEvent::all();
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }
            $eventName = (string) ($event->event_choice ?? $event->title ?? 'Story Event');
            $choices = $this->generateChoices($eventName, $event->stat_effects ?? null, (string) ($event->age_group ?? 'adult'), 'ageSpecific');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
}
