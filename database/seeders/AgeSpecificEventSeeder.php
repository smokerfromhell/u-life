<?php

namespace Database\Seeders;

use App\Models\AgeSpecificEvent;
use Illuminate\Database\Seeder;

class AgeSpecificEventSeeder extends Seeder
{
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
            ['age_group' => 'child', 'event_choice' => 'School Start - Excited to learn', 'outcome' => 'You begin school with excitement and curiosity', 'stat_effects' => '+10 Intelligence, +10 Discipline', 'weight' => 0.7, 'event_category' => 'education', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'School Start - Struggles with lessons', 'outcome' => 'You struggle with lessons and feel discouraged', 'stat_effects' => '-5 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - High score', 'outcome' => 'You score high on your first exam', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education', 'required_choice_outcome' => 'positive'],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - Low score', 'outcome' => 'You score low on your first exam', 'stat_effects' => '-10 Intelligence, -5 Happiness', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'First School Exam - Cheating caught', 'outcome' => 'You are caught cheating on your exam', 'stat_effects' => '-15 Reputation, -10 Morality', 'weight' => 0.2, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Learning - Reads early', 'outcome' => 'You learn to read earlier than peers', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 3, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Learning - Struggles to read', 'outcome' => 'You struggle to learn reading skills', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 3, 'parent_category' => 'education'],
            ['age_group' => 'child', 'event_choice' => 'Childhood School Trip - Educational success', 'outcome' => 'You learn a lot during a school trip', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'weight' => 0.5, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education'],

            // === FAMILY CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Family Bonding - Supportive parents', 'outcome' => 'Your parents are supportive and loving', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.8, 'event_category' => 'family', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Family Bonding - Neglectful parents', 'outcome' => 'Your parents neglect you, leaving scars', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Sibling Bond - Close sibling', 'outcome' => 'You form a close bond with your sibling', 'stat_effects' => '+15 Happiness, +5 Morality', 'weight' => 0.7, 'event_category' => 'family', 'chain_order' => 2, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Sibling Bond - Rivalry', 'outcome' => 'You develop rivalry with your sibling', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 2, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Bonding - Grandparent love', 'outcome' => 'You receive love and wisdom from grandparents', 'stat_effects' => '+15 Happiness, +10 Morality', 'weight' => 0.6, 'event_category' => 'family', 'chain_order' => 3, 'parent_category' => 'family'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Bonding - Grandparent loss', 'outcome' => 'You lose a grandparent and feel grief', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 3, 'parent_category' => 'family'],

            // === SOCIAL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Childhood Friend - Best friend found', 'outcome' => 'You find a best friend who brings joy', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.7, 'event_category' => 'social', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Childhood Friend - Lonely childhood', 'outcome' => 'You grow up feeling lonely without close friends', 'stat_effects' => '+10 Isolation, -10 Happiness', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Playtime - Outdoor games', 'outcome' => 'You enjoy outdoor games with friends', 'stat_effects' => '+10 Strength, +10 Happiness', 'weight' => 0.8, 'event_category' => 'social', 'chain_order' => 2, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Playtime - Stays indoors', 'outcome' => 'You stay indoors and miss out on play', 'stat_effects' => '-5 Health, +5 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 2, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Stands up', 'outcome' => 'You stand up to bullying with courage', 'stat_effects' => '+10 Reputation, +10 Morality', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Silent', 'outcome' => 'You remain silent and suffer quietly', 'stat_effects' => '-10 Happiness, +10 Isolation', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Bullying - Severe trauma', 'outcome' => 'Bullying leaves you with severe trauma', 'stat_effects' => '-30 Happiness, +20 Burnout', 'weight' => 0.1, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Playdate - Fun with friends', 'outcome' => 'You enjoy a fun playdate with friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.6, 'event_category' => 'social', 'chain_order' => 4, 'parent_category' => 'social'],

            // === HEALTH CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Birth - Healthy baby', 'outcome' => 'You are born healthy and full of life', 'stat_effects' => '+20 Health, +20 Happiness', 'weight' => 0.9, 'event_category' => 'health', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'child', 'event_choice' => 'Birth - Complicated birth', 'outcome' => 'Your birth is difficult, leaving lasting effects', 'stat_effects' => '-10 Health, +10 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Steps - Walk early', 'outcome' => 'You walk earlier than expected, impressing family', 'stat_effects' => '+10 Strength, +5 Discipline', 'weight' => 0.7, 'event_category' => 'health', 'chain_order' => 2, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'First Steps - Walk late', 'outcome' => 'You walk later than peers, causing concern', 'stat_effects' => '-5 Strength, -5 Happiness', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 2, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Quick recovery', 'outcome' => 'You recover quickly from illness', 'stat_effects' => '+10 Health, +5 Happiness', 'weight' => 0.8, 'event_category' => 'health', 'chain_order' => 3, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Long sickness', 'outcome' => 'You suffer a long sickness that drains your energy', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 3, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Chickenpox', 'outcome' => 'You suffer from chickenpox but recover', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.4, 'event_category' => 'health', 'chain_order' => 4, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Nutrition - Balanced diet', 'outcome' => 'You grow strong with a balanced diet', 'stat_effects' => '+15 Health, +5 Discipline', 'weight' => 0.7, 'event_category' => 'health', 'chain_order' => 5, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Nutrition - Malnutrition', 'outcome' => 'You suffer from malnutrition', 'stat_effects' => '-20 Health, +10 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 5, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Minor fall', 'outcome' => 'You suffer a minor fall while playing', 'stat_effects' => '-5 Health, +5 Burnout', 'weight' => 0.4, 'event_category' => 'health', 'chain_order' => 6, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Major injury', 'outcome' => 'You experience a major injury during childhood', 'stat_effects' => '-20 Health, -10 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 6, 'parent_category' => 'health'],

            // === SKILL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'First Words - Speaks clearly', 'outcome' => 'You speak clearly and delight your family', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'weight' => 0.7, 'event_category' => 'skill', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Words - Speech delay', 'outcome' => 'You struggle with speech, worrying your parents', 'stat_effects' => '-5 Intelligence, -5 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Talent - Shows skill', 'outcome' => 'You show talent and gain recognition', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 2, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Talent - Talent ignored', 'outcome' => 'Your talent is ignored by others', 'stat_effects' => '-10 Happiness, +5 Isolation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 2, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Hobby - Learns instrument', 'outcome' => 'You learn to play a musical instrument', 'stat_effects' => '+15 Creativity, +10 Discipline', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 3, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Hobby - Quits hobby', 'outcome' => 'You quit your hobby and feel disappointed', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 3, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Dream - Inspired', 'outcome' => 'You are inspired by a childhood dream', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 4, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Chores - Helps family', 'outcome' => 'You help your family with chores', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.7, 'event_category' => 'skill', 'chain_order' => 5, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Chores - Refuses chores', 'outcome' => 'You refuse to do chores', 'stat_effects' => '-10 Discipline, -5 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 5, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Sports - Wins race', 'outcome' => 'You win a race and feel proud', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 6, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Sports - Loses race', 'outcome' => 'You lose a race and feel discouraged', 'stat_effects' => '-5 Happiness, +5 Burnout', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 6, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Fear - Overcomes fear', 'outcome' => 'You overcome a childhood fear', 'stat_effects' => '+10 Discipline, +5 Morality', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 7, 'parent_category' => 'skill'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Fear - Fear worsens', 'outcome' => 'Your fear worsens and affects your happiness', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 7, 'parent_category' => 'skill'],

            // === RANDOM CHILD EVENTS ===
            ['age_group' => 'child', 'event_choice' => 'First Pet - Pet adopted', 'outcome' => 'You adopt a pet and feel joy', 'stat_effects' => '+10 Happiness, +5 Morality', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'First Pet - Pet lost', 'outcome' => 'You lose your pet and feel heartbroken', 'stat_effects' => '-15 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Birthday Party - Celebrated', 'outcome' => 'Your birthday is celebrated with joy and friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Birthday Party - Forgotten', 'outcome' => 'Your birthday is forgotten, leaving you sad', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Travel - Family trip', 'outcome' => 'You enjoy a family trip full of memories', 'stat_effects' => '+10 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Trip - Zoo visit', 'outcome' => 'You visit the zoo and marvel at the animals', 'stat_effects' => '+10 Happiness, +5 Creativity', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Trip - Amusement park', 'outcome' => 'You enjoy thrilling rides at the amusement park', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Celebration - Christmas joy', 'outcome' => 'You enjoy the joy of Christmas celebrations', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.6, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Celebration - New Year fireworks', 'outcome' => 'You enjoy the excitement of New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Religion - Joins devotion', 'outcome' => 'You join religious devotion and feel guided', 'stat_effects' => '+15 Morality, +10 Reputation', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'child', 'event_choice' => 'Childhood Curiosity - Explores safely', 'outcome' => 'You explore safely and learn new things', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'weight' => 0.5, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],

            // Fatal child events
            ['age_group' => 'child', 'event_choice' => 'Infant Mortality', 'outcome' => 'You pass away in infancy', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness - Fatal illness', 'outcome' => 'Your illness proves fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident - Fatal accident', 'outcome' => 'A tragic accident ends your childhood', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],

            // ============================================
            // TEEN EVENTS
            // ============================================

            // === EDUCATION CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'High School Start - Good grades', 'outcome' => 'You begin high school with strong grades', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'weight' => 0.6, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'High School Start - Poor grades', 'outcome' => 'You struggle with poor grades in high school', 'stat_effects' => '-10 Intelligence, +10 Burnout', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage School Exam - High score', 'outcome' => 'You score high on your school exam', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 5, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage School Exam - Low score', 'outcome' => 'You score low on your school exam', 'stat_effects' => '-10 Intelligence, -5 Happiness', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 5, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Learning - Excels in science', 'outcome' => 'You excel in science subjects', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 6, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Learning - Struggles in math', 'outcome' => 'You struggle with math subjects', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 6, 'parent_category' => 'education'],
            ['age_group' => 'teen', 'event_choice' => 'Graduation - With honors', 'outcome' => 'You graduate with honors', 'stat_effects' => '+20 Intelligence, +15 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 7, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'Graduation - Barely passes', 'outcome' => 'You barely pass graduation', 'stat_effects' => '+5 Intelligence, -5 Reputation', 'weight' => 0.3, 'event_category' => 'education', 'chain_order' => 7, 'parent_category' => 'education'],

            // === SOCIAL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Crush - Mutual feelings', 'outcome' => 'Your first crush shares mutual feelings', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.5, 'event_category' => 'social', 'chain_order' => 5, 'parent_category' => 'social', 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'First Crush - Rejected', 'outcome' => 'Your first crush rejects you', 'stat_effects' => '-15 Happiness, +5 Isolation', 'weight' => 0.5, 'event_category' => 'social', 'chain_order' => 5, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'First Love - Relationship begins', 'outcome' => 'You begin your first romantic relationship', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 6, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'First Love - Heartbreak', 'outcome' => 'Your first love ends in heartbreak', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 6, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Friendship - Loyal friend', 'outcome' => 'You gain a loyal friend', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.4, 'event_category' => 'social', 'chain_order' => 7, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Friendship - Betrayal', 'outcome' => 'You are betrayed by a friend', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 7, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Relationship - Healthy romance', 'outcome' => 'You enjoy a healthy teenage romance', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 8, 'parent_category' => 'social'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Relationship - Breakup', 'outcome' => 'Your teenage relationship ends in breakup', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 8, 'parent_category' => 'social'],

            // === FAMILY CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Family support', 'outcome' => 'Your family supports you through challenges', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 4, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Family conflict', 'outcome' => 'You face conflict within your family', 'stat_effects' => '-20 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 4, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Grandparent love', 'outcome' => 'You receive love and wisdom from grandparents', 'stat_effects' => '+15 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 5, 'parent_category' => 'family'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding - Grandparent loss', 'outcome' => 'You lose a grandparent and feel grief', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 5, 'parent_category' => 'family'],

            // === CAREER CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Job (Part-time) - Hired', 'outcome' => 'You are hired for your first part-time job', 'stat_effects' => '+10 Wealth, +5 Discipline', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true],
            ['age_group' => 'teen', 'event_choice' => 'First Job (Part-time) - Fired', 'outcome' => 'You are fired from your part-time job', 'stat_effects' => '-5 Wealth, -5 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Peer Pressure - Resists', 'outcome' => 'You resist peer pressure', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 2, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Peer Pressure - Gives in', 'outcome' => 'You give in to peer pressure', 'stat_effects' => '-10 Discipline, -10 Morality', 'weight' => 0.5, 'event_category' => 'career', 'chain_order' => 2, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Rebellion - Sneaks out', 'outcome' => 'You sneak out for fun', 'stat_effects' => '+10 Happiness, -10 Discipline', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 3, 'parent_category' => 'career'],
            ['age_group' => 'teen', 'event_choice' => 'Rebellion - Caught', 'outcome' => 'You are caught sneaking out', 'stat_effects' => '-10 Reputation, +5 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 3, 'parent_category' => 'career'],

            // === SKILL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Sports Team - Makes varsity', 'outcome' => 'You make the varsity team', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 0.4, 'event_category' => 'skill', 'chain_order' => 9, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Sports Team - Cut from team', 'outcome' => 'You are cut from the sports team', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 9, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Hobby - Learns guitar', 'outcome' => 'You learn to play the guitar', 'stat_effects' => '+15 Creativity, +10 Discipline', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 10, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Hobby - Quits hobby', 'outcome' => 'You quit your hobby and feel disappointed', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 10, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Talent - Shows skill', 'outcome' => 'You show talent and gain recognition', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 11, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Talent - Talent mocked', 'outcome' => 'Your talent is mocked, hurting your confidence', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'skill', 'chain_order' => 11, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Contest - Wins', 'outcome' => 'You win a teenage contest', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 12, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Contest - Loses', 'outcome' => 'You lose a teenage contest', 'stat_effects' => '-5 Happiness, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 12, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Sports - Wins championship', 'outcome' => 'You win a sports championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 13, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Fear - Overcomes fear', 'outcome' => 'You overcome a teenage fear', 'stat_effects' => '+10 Discipline, +5 Morality', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 14, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Fear - Fear worsens', 'outcome' => 'Your fear worsens and affects your happiness', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 14, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Exploration - Safe adventure', 'outcome' => 'You go on a safe adventure', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 15, 'parent_category' => 'skill'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Chores - Helps family', 'outcome' => 'You help your family with chores', 'stat_effects' => '+10 Discipline, +10 Morality', 'weight' => 0.5, 'event_category' => 'skill', 'chain_order' => 16, 'parent_category' => 'skill'],

            // === HEALTH CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Flu recovery', 'outcome' => 'You recover from the flu', 'stat_effects' => '-10 Health, -5 Happiness', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 7, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Dengue recovery', 'outcome' => 'You recover from dengue', 'stat_effects' => '-20 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 7, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Minor injury', 'outcome' => 'You suffer a minor injury', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 8, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Major injury', 'outcome' => 'You suffer a major injury', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 8, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Bike crash', 'outcome' => 'You crash your bike and get injured', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 9, 'parent_category' => 'health'],

            // === RANDOM TEEN EVENTS ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Fun night', 'outcome' => 'You enjoy a fun night at a party', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Embarrassment', 'outcome' => 'You face embarrassment at a party', 'stat_effects' => '-10 Reputation, -10 Happiness', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Festival - Joins parade', 'outcome' => 'You join a parade and enjoy the celebration', 'stat_effects' => '+10 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Festival - Joins cultural parade', 'outcome' => 'You join a cultural parade and enjoy the festivities', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Celebration - Christmas joy', 'outcome' => 'You enjoy the joy of Christmas celebrations', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Celebration - New Year fireworks', 'outcome' => 'You enjoy New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],

            // Fatal teen events
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness - Dengue fatal', 'outcome' => 'Dengue proves fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident - Fatal accident', 'outcome' => 'A fatal accident occurs', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party - Fatal overdose', 'outcome' => 'A fatal overdose occurs at the party', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],

            // ============================================
            // ADULT EVENTS
            // ============================================

            // === EDUCATION CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'College - Graduates', 'outcome' => 'You graduate from college with success', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.4, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'College - Drops out', 'outcome' => 'You drop out of college', 'stat_effects' => '-10 Intelligence, -10 Reputation', 'weight' => 0.2, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education'],

            // === CAREER CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Hired', 'outcome' => 'You are hired for your first career job', 'stat_effects' => '+20 Wealth, +10 Discipline', 'weight' => 0.4, 'event_category' => 'career', 'chain_order' => 4, 'parent_category' => 'career', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Rejected', 'outcome' => 'You are rejected from a job application', 'stat_effects' => '-10 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 4, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Promotion - Rise in rank', 'outcome' => 'You rise in rank at your career', 'stat_effects' => '+20 Wealth, +15 Reputation', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 5, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Promotion - Passed over', 'outcome' => 'You are passed over for promotion', 'stat_effects' => '-10 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 5, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Change - Successful transition', 'outcome' => 'You successfully transition to a new career', 'stat_effects' => '+20 Wealth, +10 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 6, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Career Change - Failed transition', 'outcome' => 'Your career change fails', 'stat_effects' => '-10 Wealth, +10 Burnout', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 6, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Business Venture - Startup success', 'outcome' => 'Your startup becomes successful', 'stat_effects' => '+30 Wealth, +20 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 7, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Business Venture - Startup failure', 'outcome' => 'Your startup fails', 'stat_effects' => '-20 Wealth, +20 Debt', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 7, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Migration - Move abroad', 'outcome' => 'You move abroad for new opportunities', 'stat_effects' => '+20 Wealth, +10 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 8, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Migration - Denied visa', 'outcome' => 'Your visa application is denied', 'stat_effects' => '-10 Happiness, -5 Reputation', 'weight' => 0.1, 'event_category' => 'career', 'chain_order' => 8, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Midlife Crisis - Reinvent self', 'outcome' => 'You reinvent yourself during midlife', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 9, 'parent_category' => 'career'],
            ['age_group' => 'adult', 'event_choice' => 'Midlife Crisis - Burnout deepens', 'outcome' => 'Your burnout deepens during midlife', 'stat_effects' => '+20 Burnout, -20 Happiness', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 9, 'parent_category' => 'career'],

            // === FAMILY CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Marriage - Wedding', 'outcome' => 'You get married in a joyful wedding', 'stat_effects' => '+30 Happiness, +10 Reputation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 6, 'parent_category' => 'family', 'is_milestone' => true],
            ['age_group' => 'adult', 'event_choice' => 'Marriage - Cancelled engagement', 'outcome' => 'Your engagement is cancelled', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 6, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child born', 'outcome' => 'You welcome a child into your family', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child illness', 'outcome' => 'Your child suffers from illness', 'stat_effects' => '-15 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood - Child fatality', 'outcome' => 'You lose a child to fatality', 'stat_effects' => '-50 Happiness, +20 Isolation', 'weight' => 0.05, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Divorce - Separation', 'outcome' => 'You go through a painful separation', 'stat_effects' => '-30 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 8, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Divorce - Amicable split', 'outcome' => 'You split amicably with your partner', 'stat_effects' => '-10 Happiness, +5 Morality', 'weight' => 0.1, 'event_category' => 'family', 'chain_order' => 8, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Bonding - Family support', 'outcome' => 'Your family supports you through challenges', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'family', 'chain_order' => 9, 'parent_category' => 'family'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Bonding - Family conflict', 'outcome' => 'You face conflict within your family', 'stat_effects' => '-20 Happiness, +10 Burnout', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 9, 'parent_category' => 'family'],

            // === SOCIAL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Friendship - Loyal friend', 'outcome' => 'You gain a loyal friend in adulthood', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 10, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Friendship - Betrayal', 'outcome' => 'You are betrayed by a friend', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 10, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Relationship - Healthy romance', 'outcome' => 'You enjoy a healthy adult romance', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 11, 'parent_category' => 'social'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Relationship - Breakup', 'outcome' => 'Your adult relationship ends in breakup', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 11, 'parent_category' => 'social'],

            // === HEALTH CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Major illness', 'outcome' => 'You suffer a major illness', 'stat_effects' => '-30 Health, +20 Burnout', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 10, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Recovery', 'outcome' => 'You recover from a health crisis', 'stat_effects' => '+20 Health, +10 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 10, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Illness - Flu recovery', 'outcome' => 'You recover from the flu', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 11, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Illness - Severe dengue recovery', 'outcome' => 'You recover from severe dengue', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 11, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Bike crash', 'outcome' => 'You crash your bike and get injured', 'stat_effects' => '-15 Health, +10 Burnout', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 12, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Car crash survival', 'outcome' => 'You survive a car crash with injuries', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.1, 'event_category' => 'health', 'chain_order' => 12, 'parent_category' => 'health'],

            // === SKILL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Hobby - Learns painting', 'outcome' => 'You learn painting and express creativity', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 17, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Hobby - Gives up hobby', 'outcome' => 'You give up your hobby and feel disappointed', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 17, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Talent - Shows skill', 'outcome' => 'You showcase your talent and gain recognition', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 18, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Talent - Talent mocked', 'outcome' => 'Your talent is mocked, hurting your confidence', 'stat_effects' => '-20 Happiness, -10 Reputation', 'weight' => 0.1, 'event_category' => 'skill', 'chain_order' => 18, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Learning - Excels in training', 'outcome' => 'You excel in professional training', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 19, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Learning - Struggles in training', 'outcome' => 'You struggle in professional training', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 19, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Sports - Wins championship', 'outcome' => 'You win a sports championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 20, 'parent_category' => 'skill'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Contest - Wins', 'outcome' => 'You win an adult contest', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 21, 'parent_category' => 'skill'],

            // === RANDOM ADULT EVENTS ===
            ['age_group' => 'adult', 'event_choice' => 'Inheritance - Receive wealth', 'outcome' => 'You inherit wealth from family', 'stat_effects' => '+50 Wealth, +10 Reputation', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Inheritance - Family dispute', 'outcome' => 'Inheritance leads to family disputes', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Fame - Become celebrity', 'outcome' => 'You become a celebrity', 'stat_effects' => '+30 Reputation, +20 Ego', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Fame - Scandal', 'outcome' => 'A scandal damages your reputation', 'stat_effects' => '-30 Reputation, -20 Happiness', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Political Change - Join movement', 'outcome' => 'You join a political movement and gain influence', 'stat_effects' => '+20 Morality, +20 Reputation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Survive typhoon', 'outcome' => 'You survive a typhoon with minor losses', 'stat_effects' => '-10 Health, -10 Wealth', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Lose property', 'outcome' => 'You lose property in a disaster', 'stat_effects' => '-30 Wealth, -20 Happiness', 'weight' => 0.1, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Celebration - Christmas joy', 'outcome' => 'You enjoy the joy of Christmas celebrations', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Celebration - New Year fireworks', 'outcome' => 'You enjoy New Year fireworks', 'stat_effects' => '+15 Happiness, +5 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'adult', 'event_choice' => 'Adult Festival - Joins cultural parade', 'outcome' => 'You join a cultural parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],

            // Fatal adult events
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Fatal illness', 'outcome' => 'A fatal illness ends your life', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Fatal workplace accident', 'outcome' => 'A fatal accident occurs at your workplace', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Fatal disaster', 'outcome' => 'A natural disaster proves fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Car crash fatal', 'outcome' => 'A car crash proves fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],

            // ============================================
            // OLD EVENTS
            // ============================================

            // === RETIREMENT CHAIN ===
            ['age_group' => 'old', 'event_choice' => 'Retirement - Peaceful retirement', 'outcome' => 'You retire peacefully and enjoy your later years', 'stat_effects' => '+20 Happiness, -10 Isolation', 'weight' => 0.3, 'event_category' => 'career', 'chain_order' => 10, 'parent_category' => 'career', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'Retirement - Forced retirement', 'outcome' => 'You are forced into retirement', 'stat_effects' => '-15 Happiness, -10 Reputation', 'weight' => 0.2, 'event_category' => 'career', 'chain_order' => 10, 'parent_category' => 'career'],

            // === FAMILY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Bond with grandchild', 'outcome' => 'You bond with your grandchild', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Estranged family', 'outcome' => 'You become estranged from your family', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood - Grandchild fatality', 'outcome' => 'You lose a grandchild to fatality', 'stat_effects' => '-50 Happiness, +20 Isolation', 'weight' => 0.05, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Elder Bonding - Family reunion', 'outcome' => 'You enjoy a family reunion', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'family', 'chain_order' => 11, 'parent_category' => 'family'],
            ['age_group' => 'old', 'event_choice' => 'Elder Bonding - Family conflict', 'outcome' => 'You face conflict within your family', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'family', 'chain_order' => 11, 'parent_category' => 'family'],

            // === HEALTH CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Arthritis', 'outcome' => 'You suffer from arthritis', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 13, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Dementia onset', 'outcome' => 'You experience dementia onset', 'stat_effects' => '-20 Intelligence, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 13, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Flu recovery', 'outcome' => 'You recover from the flu', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 14, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Pneumonia recovery', 'outcome' => 'You recover from pneumonia', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 14, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Minor fall', 'outcome' => 'You suffer a minor fall', 'stat_effects' => '-10 Health, +5 Burnout', 'weight' => 0.3, 'event_category' => 'health', 'chain_order' => 15, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Hip fracture', 'outcome' => 'You suffer a hip fracture', 'stat_effects' => '-30 Health, -20 Happiness', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 15, 'parent_category' => 'health'],

            // === SKILL/LEGACY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Writes memoir', 'outcome' => 'You write a memoir', 'stat_effects' => '+20 Creativity, +15 Reputation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Memoir ignored', 'outcome' => 'Your memoir is ignored', 'stat_effects' => '-10 Happiness, +5 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Legacy Project - Memoir celebrated', 'outcome' => 'Your memoir is celebrated', 'stat_effects' => '+30 Reputation, +20 Happiness', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Wisdom Sharing - Mentor youth', 'outcome' => 'You mentor the youth and share wisdom', 'stat_effects' => '+20 Morality, +10 Reputation', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 23, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Wisdom Sharing - Advice ignored', 'outcome' => 'Your advice is ignored', 'stat_effects' => '-10 Happiness, +5 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 23, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Community Role - Joins senior group', 'outcome' => 'You join a senior group', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 12, 'parent_category' => 'social'],
            ['age_group' => 'old', 'event_choice' => 'Community Role - Declines participation', 'outcome' => 'You decline participation in community', 'stat_effects' => '+10 Isolation, -10 Happiness', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 12, 'parent_category' => 'social'],

            // === SOCIAL (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Elder Friendship - Loyal companion', 'outcome' => 'You gain a loyal companion', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'social', 'chain_order' => 13, 'parent_category' => 'social'],
            ['age_group' => 'old', 'event_choice' => 'Elder Friendship - Betrayal', 'outcome' => 'You are betrayed by a companion', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'social', 'chain_order' => 13, 'parent_category' => 'social'],

            // === RANDOM OLD EVENTS ===
            ['age_group' => 'old', 'event_choice' => 'Elder Celebration - Christmas joy', 'outcome' => 'You enjoy Christmas joy', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.4, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Celebration - Missed Christmas', 'outcome' => 'You miss Christmas celebrations', 'stat_effects' => '-15 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Travel - Pilgrimage', 'outcome' => 'You go on a pilgrimage', 'stat_effects' => '+20 Morality, +15 Happiness', 'weight' => 0.2, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Hobby - Gardening', 'outcome' => 'You enjoy gardening', 'stat_effects' => '+10 Creativity, +10 Health', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 24, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Hobby - Abandons hobby', 'outcome' => 'You abandon your hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 24, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Peaceful reflection', 'outcome' => 'You reflect peacefully on your life', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 25, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Regret', 'outcome' => 'You feel regret during reflection', 'stat_effects' => '-20 Happiness, +10 Isolation', 'weight' => 0.2, 'event_category' => 'skill', 'chain_order' => 25, 'parent_category' => 'skill'],
            ['age_group' => 'old', 'event_choice' => 'Elder Festival - Joins parade', 'outcome' => 'You join a parade and enjoy the celebration', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 0.3, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null],
            ['age_group' => 'old', 'event_choice' => 'Elder Contest - Wins', 'outcome' => 'You win a contest in your elder years', 'stat_effects' => '+15 Reputation, +10 Happiness', 'weight' => 0.3, 'event_category' => 'skill', 'chain_order' => 26, 'parent_category' => 'skill'],

            // === END OF LIFE (Old) ===
            ['age_group' => 'old', 'event_choice' => 'End of Life - Peaceful passing', 'outcome' => 'You pass away peacefully', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.5, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'is_milestone' => true],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Sudden death', 'outcome' => 'You die suddenly', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Violent death', 'outcome' => 'You suffer a violent death', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],

            // Fatal old events
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Fatal illness', 'outcome' => 'A fatal illness ends your life', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Pneumonia fatal', 'outcome' => 'Pneumonia proves fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Fatal fall', 'outcome' => 'A fatal fall occurs', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Fatal heart attack', 'outcome' => 'A fatal heart attack occurs', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health'],
        ];

        foreach ($events as $event) {
            AgeSpecificEvent::create($event);
        }
    }
}
