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
            ['age_group' => 'child', 'event_choice' => 'School Start', 'description' => 'Start your school journey.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 1, 'event_category' => 'education', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true, 'choices' => [
                    ['text' => 'Go to school', 'stat_effects' => '+5 Intelligence, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Cut class', 'stat_effects' => '+3 Happiness, -3 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'First School Exam', 'description' => 'Your first major school exam.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 2, 'event_category' => 'education', 'chain_order' => 2, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Study hard', 'stat_effects' => '+10 Intelligence, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Guess answers', 'stat_effects' => '-5 Intelligence, +3 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Cheat', 'stat_effects' => '+5 Reputation, -10 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'education_1']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Learning', 'description' => 'Learning to read and write.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 3, 'event_category' => 'education', 'chain_order' => 3, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Practice reading daily', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Ask teacher for help', 'stat_effects' => '+10 Intelligence, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Avoid reading practice', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'education_2']],
            ['age_group' => 'child', 'event_choice' => 'Childhood School Trip', 'description' => 'A school field trip.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 4, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Pay attention to guide', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Play with friends', 'stat_effects' => '+5 Happiness, +3 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'education_3']],
            // === FAMILY CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Family Bonding', 'description' => 'Bond with your family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.8, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 5, 'event_category' => 'family', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true, 'choices' => [
                    ['text' => 'Spend time with parents', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Play alone', 'stat_effects' => '-10 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Sibling Bond', 'description' => 'Bond with your siblings.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 6, 'event_category' => 'family', 'chain_order' => 2, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Play together', 'stat_effects' => '+15 Happiness, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Compete for attention', 'stat_effects' => '-10 Happiness, +5 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'family_1']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Bonding', 'description' => 'Bond with extended family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 7, 'event_category' => 'family', 'chain_order' => 3, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Visit grandparents', 'stat_effects' => '+15 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip family gathering', 'stat_effects' => '-10 Happiness, +3 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'family_2']],
            // === SOCIAL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Childhood Friend', 'description' => 'Make friends at school.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 8, 'event_category' => 'social', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true, 'choices' => [
                    ['text' => 'Talk to classmates', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay alone', 'stat_effects' => '+10 Isolation, -10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Playtime', 'description' => 'Time to play with friends.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.8, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 9, 'event_category' => 'social', 'chain_order' => 2, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Play outdoor games', 'stat_effects' => '+10 Strength, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay indoors and read', 'stat_effects' => '+5 Intelligence, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'social_1']],
            ['age_group' => 'child', 'event_choice' => 'Bullying', 'description' => 'Face bullying at school.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 10, 'event_category' => 'social', 'chain_order' => 3, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Stand up to bully', 'stat_effects' => '+10 Reputation, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Tell a teacher', 'stat_effects' => '+5 Reputation, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Stay silent', 'stat_effects' => '-10 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'social_2']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Playdate', 'description' => 'Playdate with friends.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 11, 'event_category' => 'social', 'chain_order' => 4, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Invite friend over', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Decline invitation', 'stat_effects' => '-5 Happiness, +3 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'social_3']],
            // === HEALTH CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'Birth', 'description' => 'The beginning of life.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.9, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 12, 'event_category' => 'health', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true, 'choices' => [
                    ['text' => 'Born healthy', 'stat_effects' => '+20 Health, +20 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Born with complications', 'stat_effects' => '-10 Health, +10 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'First Steps', 'description' => 'First steps as a baby.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 13, 'event_category' => 'health', 'chain_order' => 2, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Practice walking', 'stat_effects' => '+10 Strength, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Stay in crib', 'stat_effects' => '-5 Strength, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'health_1']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness', 'description' => 'Get sick during childhood.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.8, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 14, 'event_category' => 'health', 'chain_order' => 3, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Rest and recover', 'stat_effects' => '+10 Health, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Ignore symptoms', 'stat_effects' => '-15 Health, +10 Burnout', 'days_to_advance' => 0],
                    ['text' => 'See a doctor', 'stat_effects' => '-5 Wealth, +8 Health', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'health_2']],
            ['age_group' => 'child', 'event_choice' => 'Nutrition', 'description' => 'Nutrition during childhood.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 15, 'event_category' => 'health', 'chain_order' => 5, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Eat healthy foods', 'stat_effects' => '+15 Health, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Eat junk food', 'stat_effects' => '-10 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'health_3']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident', 'description' => 'Accidents happen.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 16, 'event_category' => 'health', 'chain_order' => 6, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Be careful', 'stat_effects' => '-3 Health, +3 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Play dangerously', 'stat_effects' => '-15 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'health_5']],
            // === SKILL CHAIN (Child) ===
            ['age_group' => 'child', 'event_choice' => 'First Words', 'description' => 'First words spoken.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 17, 'event_category' => 'skill', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Practice speaking', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay quiet', 'stat_effects' => '-5 Intelligence, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Hobby', 'description' => 'Develop a hobby.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 18, 'event_category' => 'skill', 'chain_order' => 3, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Practice instrument daily', 'stat_effects' => '+15 Creativity, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip practice', 'stat_effects' => '-5 Creativity, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'skill_1']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Dream', 'description' => 'Dream about the future.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 19, 'event_category' => 'skill', 'chain_order' => 4, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Dream big', 'stat_effects' => '+15 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay realistic', 'stat_effects' => '+5 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'skill_3']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Chores', 'description' => 'Help with chores.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.7, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 20, 'event_category' => 'skill', 'chain_order' => 5, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Help with chores', 'stat_effects' => '+10 Discipline, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Refuse to help', 'stat_effects' => '-10 Discipline, -5 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'skill_4']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Sports', 'description' => 'Play sports.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 21, 'event_category' => 'skill', 'chain_order' => 6, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Join sports team', 'stat_effects' => '+15 Strength, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip practice', 'stat_effects' => '-5 Strength, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'skill_5']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Fear', 'description' => 'Face childhood fears.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 22, 'event_category' => 'skill', 'chain_order' => 7, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Face your fears', 'stat_effects' => '+10 Discipline, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Avoid the fear', 'stat_effects' => '-5 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child', 'has_completed_chain' => 'skill_6']],
            // === RANDOM CHILD EVENTS ===
            ['age_group' => 'child', 'event_choice' => 'First Pet', 'description' => 'Get your first pet.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 23, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Ask for a pet', 'stat_effects' => '+10 Happiness, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Decline pet', 'stat_effects' => '-5 Happiness, +3 Wealth', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Birthday Party', 'description' => 'Celebrate your birthday.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 24, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Invite friends', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip celebration', 'stat_effects' => '-10 Happiness, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Travel', 'description' => 'Travel with family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 25, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Go on trip', 'stat_effects' => '+10 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '-5 Happiness, +3 Intelligence', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Trip', 'description' => 'Go on a fun trip.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 26, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Visit zoo', 'stat_effects' => '+10 Happiness, +5 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Go to amusement park', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Celebration', 'description' => 'Celebrate special occasions.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 27, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join celebration', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip celebration', 'stat_effects' => '-5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Religion', 'description' => 'Learn about faith.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 28, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Learn about faith', 'stat_effects' => '+15 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip religious class', 'stat_effects' => '-5 Morality, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Curiosity', 'description' => 'Explore the world.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 29, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Explore surroundings', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'days_to_advance' => 0],
                    ['text' => 'Stay cautious', 'stat_effects' => '+5 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            // Fatal child events (rare random events)
            ['age_group' => 'child', 'event_choice' => 'Infant Mortality', 'description' => 'Tragic loss of life.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 30, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Fatal', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Illness', 'description' => 'A serious illness.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 31, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Fatal illness', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            ['age_group' => 'child', 'event_choice' => 'Childhood Accident', 'description' => 'A tragic accident.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 32, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Fatal accident', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'child']],
            // ============================================
            // TEEN EVENTS
            // ============================================
            // === EDUCATION CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'High School Start', 'description' => 'Start high school.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.6, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 40, 'event_category' => 'education', 'chain_order' => 4, 'parent_category' => 'education', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Study diligently', 'stat_effects' => '+15 Intelligence, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip classes', 'stat_effects' => '-10 Intelligence, +10 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage School Exam', 'description' => 'Take your exams.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 41, 'event_category' => 'education', 'chain_order' => 5, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Prepare thoroughly', 'stat_effects' => '+15 Intelligence, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Guess on exam', 'stat_effects' => '-10 Intelligence, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'education_4']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Learning', 'description' => 'Learn new subjects.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 42, 'event_category' => 'education', 'chain_order' => 6, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Focus on science', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip math class', 'stat_effects' => '-10 Intelligence, +5 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'education_5']],
            ['age_group' => 'teen', 'event_choice' => 'Graduation', 'description' => 'Graduate from school.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 43, 'event_category' => 'education', 'chain_order' => 7, 'parent_category' => 'education', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Graduate with honors', 'stat_effects' => '+20 Intelligence, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Just graduate', 'stat_effects' => '+5 Intelligence, -5 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'education_6']],
            // === SOCIAL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Crush', 'description' => 'Experience your first crush.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 44, 'event_category' => 'social', 'chain_order' => 5, 'parent_category' => 'social', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Confess feelings', 'stat_effects' => '+15 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Keep it secret', 'stat_effects' => '-5 Happiness, +3 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'First Love', 'description' => 'Fall in love.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 45, 'event_category' => 'social', 'chain_order' => 6, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Start relationship', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Focus on studies', 'stat_effects' => '+10 Intelligence, -5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'social_5']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Friendship', 'description' => 'Friendships during teen years.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 46, 'event_category' => 'social', 'chain_order' => 7, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Be loyal to friends', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Distance from friends', 'stat_effects' => '-10 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'social_6']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Relationship', 'description' => 'Romantic relationships.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 47, 'event_category' => 'social', 'chain_order' => 8, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Invest in relationship', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'End relationship', 'stat_effects' => '-15 Happiness, +8 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'social_7']],
            // === FAMILY CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding', 'description' => 'Bond with family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 48, 'event_category' => 'family', 'chain_order' => 4, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Talk with family', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Argue with family', 'stat_effects' => '-20 Happiness, +10 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'family_4']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Bonding', 'description' => 'Bond with extended family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 49, 'event_category' => 'family', 'chain_order' => 5, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Visit grandparents', 'stat_effects' => '+15 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip family visit', 'stat_effects' => '-10 Happiness, +3 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'family_4']],
            // === CAREER CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'First Job (Part-time)', 'description' => 'Get your first job.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 50, 'event_category' => 'career', 'chain_order' => 1, 'parent_category' => null, 'is_milestone' => true, 'choices' => [
                    ['text' => 'Apply for job', 'stat_effects' => '+10 Wealth, +5 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip job search', 'stat_effects' => '-5 Wealth, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Peer Pressure', 'description' => 'Face peer pressure.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 51, 'event_category' => 'career', 'chain_order' => 2, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Say no to pressure', 'stat_effects' => '+10 Discipline, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Give in to pressure', 'stat_effects' => '-10 Discipline, -10 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'career_1']],
            ['age_group' => 'teen', 'event_choice' => 'Rebellion', 'description' => 'Rebel against rules.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 52, 'event_category' => 'career', 'chain_order' => 3, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Sneak out', 'stat_effects' => '+10 Happiness, -10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Obey parents', 'stat_effects' => '+5 Discipline, +5 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'career_2']],
            // === SKILL CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Sports Team', 'description' => 'Join a sports team.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 53, 'event_category' => 'skill', 'chain_order' => 9, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Join sports team', 'stat_effects' => '+15 Strength, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Quit team', 'stat_effects' => '-5 Strength, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Hobby', 'description' => 'Develop a hobby.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 54, 'event_category' => 'skill', 'chain_order' => 10, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Learn guitar', 'stat_effects' => '+15 Creativity, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Skip practice', 'stat_effects' => '-5 Creativity, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_9']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Talent', 'description' => 'Show your talent.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 55, 'event_category' => 'skill', 'chain_order' => 11, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Show talent publicly', 'stat_effects' => '+15 Creativity, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Keep talent private', 'stat_effects' => '+3 Creativity, +2 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_10']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Contest', 'description' => 'Enter a contest.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 56, 'event_category' => 'skill', 'chain_order' => 12, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Practice for contest', 'stat_effects' => '+15 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip contest', 'stat_effects' => '-3 Reputation, +3 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_11']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Sports', 'description' => 'Play sports.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 57, 'event_category' => 'skill', 'chain_order' => 13, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Join championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Watch from sidelines', 'stat_effects' => '+3 Strength, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_12']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Fear', 'description' => 'Face your fears.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 58, 'event_category' => 'skill', 'chain_order' => 14, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Face your fears', 'stat_effects' => '+10 Discipline, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Avoid fears', 'stat_effects' => '-5 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_13']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Exploration', 'description' => 'Explore the world.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 59, 'event_category' => 'skill', 'chain_order' => 15, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Explore safely', 'stat_effects' => '+10 Creativity, +5 Intelligence', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '+3 Discipline, +2 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_14']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Chores', 'description' => 'Help with chores.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.5, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 60, 'event_category' => 'skill', 'chain_order' => 16, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Help with chores', 'stat_effects' => '+10 Discipline, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Refuse chores', 'stat_effects' => '-5 Discipline, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'skill_15']],
            // === HEALTH CHAIN (Teen) ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness', 'description' => 'Get sick during teen years.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 61, 'event_category' => 'health', 'chain_order' => 7, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Rest and recover', 'stat_effects' => '-10 Health, -5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Push through sickness', 'stat_effects' => '-20 Health, +10 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident', 'description' => 'Have an accident.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 62, 'event_category' => 'health', 'chain_order' => 8, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Be careful', 'stat_effects' => '-5 Health, +3 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Take risks', 'stat_effects' => '-20 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen', 'has_completed_chain' => 'health_7']],
            // === RANDOM TEEN EVENTS ===
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party', 'description' => 'Attend a party.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 63, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Go to party', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip party', 'stat_effects' => '+5 Discipline, -3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Festival', 'description' => 'Attend a festival.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 64, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join parade', 'stat_effects' => '+10 Happiness, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Watch from crowd', 'stat_effects' => '+5 Happiness, +3 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Celebration', 'description' => 'Celebrate special occasions.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 65, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join celebration', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip celebration', 'stat_effects' => '-5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            // Fatal teen events (rare random events - no conditions needed)
            ['age_group' => 'teen', 'event_choice' => 'Teenage Illness', 'description' => 'A serious illness.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 66, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Dengue fatal', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Accident', 'description' => 'A tragic accident.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 67, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Fatal accident', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            ['age_group' => 'teen', 'event_choice' => 'Teenage Party', 'description' => 'A dangerous party.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.01, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 68, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Fatal overdose', 'stat_effects' => '-100 Health, End of game', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'teen']],
            // ============================================
            // ADULT EVENTS
            // ============================================
            // === EDUCATION CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'College Graduation', 'description' => 'Graduate from college.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 80, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Graduate with honors', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Just graduate', 'stat_effects' => '+10 Intelligence, +5 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'College Dropout', 'description' => 'Drop out of college.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 81, 'event_category' => 'education', 'chain_order' => 8, 'parent_category' => 'education', 'choices' => [
                    ['text' => 'Drop out and work', 'stat_effects' => '+10 Wealth, -10 Intelligence', 'days_to_advance' => 0],
                    ['text' => 'Take a break', 'stat_effects' => '-5 Intelligence, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'education_7']],
            // === CAREER CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Career Start', 'description' => 'Start your career.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 82, 'event_category' => 'career', 'chain_order' => 4, 'parent_category' => 'career', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Accept job offer', 'stat_effects' => '+20 Wealth, +10 Discipline', 'days_to_advance' => 0],
                    ['text' => 'Keep looking', 'stat_effects' => '-5 Wealth, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Career Promotion', 'description' => 'Get promoted.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 83, 'event_category' => 'career', 'chain_order' => 5, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Work for promotion', 'stat_effects' => '+20 Wealth, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay in current role', 'stat_effects' => '+5 Happiness, +3 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'career_4']],
            ['age_group' => 'adult', 'event_choice' => 'Career Change', 'description' => 'Change your career.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 84, 'event_category' => 'career', 'chain_order' => 6, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Switch careers', 'stat_effects' => '+20 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay in field', 'stat_effects' => '+5 Wealth, +5 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'career_5']],
            ['age_group' => 'adult', 'event_choice' => 'Business Venture', 'description' => 'Start a business.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 85, 'event_category' => 'career', 'chain_order' => 7, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Start business', 'stat_effects' => '+30 Wealth, +20 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Play it safe', 'stat_effects' => '+5 Wealth, +3 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'career_6']],
            ['age_group' => 'adult', 'event_choice' => 'Migration', 'description' => 'Move abroad.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 86, 'event_category' => 'career', 'chain_order' => 8, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Apply for visa', 'stat_effects' => '+20 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '+5 Happiness, +3 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'career_7']],
            ['age_group' => 'adult', 'event_choice' => 'Midlife Crisis', 'description' => 'Face a midlife crisis.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 87, 'event_category' => 'career', 'chain_order' => 9, 'parent_category' => 'career', 'choices' => [
                    ['text' => 'Reinvent yourself', 'stat_effects' => '+15 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Push through', 'stat_effects' => '+10 Wealth, -10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'career_8']],
            // === FAMILY CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Marriage', 'description' => 'Get married.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 90, 'event_category' => 'family', 'chain_order' => 6, 'parent_category' => 'family', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Get married', 'stat_effects' => '+30 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Call off wedding', 'stat_effects' => '-20 Happiness, -10 Reputation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Parenthood', 'description' => 'Have a child.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 91, 'event_category' => 'family', 'chain_order' => 7, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Start a family', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Focus on career', 'stat_effects' => '+10 Wealth, -5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'family_6']],
            ['age_group' => 'adult', 'event_choice' => 'Divorce', 'description' => 'Get divorced.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.1, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 92, 'event_category' => 'family', 'chain_order' => 8, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'File for divorce', 'stat_effects' => '-30 Happiness, -10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Try counseling', 'stat_effects' => '+10 Happiness, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'family_7']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Bonding', 'description' => 'Bond with family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 93, 'event_category' => 'family', 'chain_order' => 9, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Spend time with family', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Focus on work', 'stat_effects' => '+10 Wealth, -10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'family_8']],
            // === SOCIAL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Friendship', 'description' => 'Make new friends.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 94, 'event_category' => 'social', 'chain_order' => 10, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Build friendships', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay isolated', 'stat_effects' => '+5 Isolation, +5 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Relationship', 'description' => 'Find romance.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 95, 'event_category' => 'social', 'chain_order' => 11, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Pursue relationship', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Focus on self', 'stat_effects' => '+5 Happiness, +5 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'social_10']],
            // === HEALTH CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis', 'description' => 'Face a health crisis.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.1, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 96, 'event_category' => 'health', 'chain_order' => 10, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Seek treatment', 'stat_effects' => '+20 Health, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Ignore symptoms', 'stat_effects' => '-30 Health, +20 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Illness', 'description' => 'Get sick.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 97, 'event_category' => 'health', 'chain_order' => 11, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'See doctor', 'stat_effects' => '-5 Wealth, +10 Health', 'days_to_advance' => 0],
                    ['text' => 'Rest at home', 'stat_effects' => '-10 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'health_10']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident', 'description' => 'Have an accident.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 98, 'event_category' => 'health', 'chain_order' => 12, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Go to hospital', 'stat_effects' => '-10 Wealth, +15 Health', 'days_to_advance' => 0],
                    ['text' => 'Self-treat', 'stat_effects' => '-15 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'health_11']],
            // === SKILL CHAIN (Adult) ===
            ['age_group' => 'adult', 'event_choice' => 'Adult Hobby', 'description' => 'Pick up a hobby.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 100, 'event_category' => 'skill', 'chain_order' => 17, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Learn painting', 'stat_effects' => '+15 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Give up hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Talent', 'description' => 'Show your talent.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 101, 'event_category' => 'skill', 'chain_order' => 18, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Showcase talent', 'stat_effects' => '+15 Creativity, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Keep it private', 'stat_effects' => '+5 Happiness, +5 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'skill_17']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Learning', 'description' => 'Learn new skills.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 102, 'event_category' => 'skill', 'chain_order' => 19, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Excel in training', 'stat_effects' => '+20 Intelligence, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Struggle through', 'stat_effects' => '-10 Intelligence, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'skill_18']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Sports', 'description' => 'Play sports.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 103, 'event_category' => 'skill', 'chain_order' => 20, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Train for championship', 'stat_effects' => '+20 Strength, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Just for fun', 'stat_effects' => '+10 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'skill_19']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Contest', 'description' => 'Enter a contest.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 104, 'event_category' => 'skill', 'chain_order' => 21, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Enter competition', 'stat_effects' => '+15 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay out', 'stat_effects' => '+5 Happiness, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult', 'has_completed_chain' => 'skill_20']],
            // === RANDOM ADULT EVENTS ===
            ['age_group' => 'adult', 'event_choice' => 'Inheritance', 'description' => 'Receive inheritance.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.1, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 105, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Accept inheritance', 'stat_effects' => '+50 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Dispute with family', 'stat_effects' => '-20 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Fame', 'description' => 'Become famous.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.1, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 106, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Embrace fame', 'stat_effects' => '+30 Reputation, +20 Ego', 'days_to_advance' => 0],
                    ['text' => 'Stay humble', 'stat_effects' => '+5 Reputation, +10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Political Change', 'description' => 'Join a political movement.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 107, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join movement', 'stat_effects' => '+20 Morality, +20 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay out', 'stat_effects' => '+5 Happiness, +5 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster', 'description' => 'Face a disaster.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.1, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 108, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Rebuild after disaster', 'stat_effects' => '-10 Health, -10 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Move away', 'stat_effects' => '-30 Wealth, +10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Celebration', 'description' => 'Celebrate a holiday.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 109, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Celebrate with family', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Celebrate alone', 'stat_effects' => '+5 Happiness, +10 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Festival', 'description' => 'Join a festival.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 110, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Watch from afar', 'stat_effects' => '+5 Happiness, +5 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'adult']],
            // Fatal adult events (rare random events - no conditions needed)
            ['age_group' => 'adult', 'event_choice' => 'Health Crisis - Fatal illness', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Career Start - Fatal workplace accident', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Natural Disaster - Fatal disaster', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'adult']],
            ['age_group' => 'adult', 'event_choice' => 'Adult Accident - Car crash fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'adult']],
            // ============================================
            // OLD EVENTS
            // ============================================
            // === RETIREMENT CHAIN ===
            ['age_group' => 'old', 'event_choice' => 'Retirement', 'description' => 'Retire from work.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 115, 'event_category' => 'career', 'chain_order' => 10, 'parent_category' => 'career', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Enjoy retirement', 'stat_effects' => '+20 Happiness, -10 Isolation', 'days_to_advance' => 0],
                    ['text' => 'Keep working', 'stat_effects' => '+10 Wealth, -15 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            // === FAMILY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Grandparenthood', 'description' => 'Become a grandparent.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 116, 'event_category' => 'family', 'chain_order' => 10, 'parent_category' => 'family', 'is_milestone' => true, 'choices' => [
                    ['text' => 'Bond with grandchild', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Keep distance', 'stat_effects' => '-20 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Bonding', 'description' => 'Connect with family.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 117, 'event_category' => 'family', 'chain_order' => 11, 'parent_category' => 'family', 'choices' => [
                    ['text' => 'Attend reunion', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip event', 'stat_effects' => '-20 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'family_10']],
            // === HEALTH CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Health Decline', 'description' => 'Face health issues.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 118, 'event_category' => 'health', 'chain_order' => 13, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Seek treatment', 'stat_effects' => '-5 Wealth, +10 Health', 'days_to_advance' => 0],
                    ['text' => 'Accept fate', 'stat_effects' => '-10 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness', 'description' => 'Get sick.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 119, 'event_category' => 'health', 'chain_order' => 14, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'See doctor', 'stat_effects' => '-5 Wealth, +15 Health', 'days_to_advance' => 0],
                    ['text' => 'Rest at home', 'stat_effects' => '-10 Health, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'health_13']],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident', 'description' => 'Have an accident.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 120, 'event_category' => 'health', 'chain_order' => 15, 'parent_category' => 'health', 'choices' => [
                    ['text' => 'Call for help', 'stat_effects' => '-5 Wealth, +15 Health', 'days_to_advance' => 0],
                    ['text' => 'Try to get up', 'stat_effects' => '-10 Health, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'health_14']],
            // === SKILL/LEGACY CHAIN (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Legacy Project', 'description' => 'Create a legacy.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 121, 'event_category' => 'skill', 'chain_order' => 22, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Write memoir', 'stat_effects' => '+20 Creativity, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Start project', 'stat_effects' => '+10 Creativity, +5 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Wisdom Sharing', 'description' => 'Share your wisdom.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 122, 'event_category' => 'skill', 'chain_order' => 23, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Mentor youth', 'stat_effects' => '+20 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Keep to yourself', 'stat_effects' => '-10 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'skill_22']],
            ['age_group' => 'old', 'event_choice' => 'Community Role', 'description' => 'Join community.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 123, 'event_category' => 'social', 'chain_order' => 12, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Join senior group', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+10 Isolation, -10 Happiness', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            // === SOCIAL (Old) ===
            ['age_group' => 'old', 'event_choice' => 'Elder Friendship', 'description' => 'Make friends.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 124, 'event_category' => 'social', 'chain_order' => 13, 'parent_category' => 'social', 'choices' => [
                    ['text' => 'Build companionship', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Stay isolated', 'stat_effects' => '-20 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'social_12']],
            // === RANDOM OLD EVENTS ===
            ['age_group' => 'old', 'event_choice' => 'Elder Celebration', 'description' => 'Celebrate a holiday.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.4, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 125, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Celebrate with family', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip holiday', 'stat_effects' => '-15 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Travel', 'description' => 'Travel somewhere.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.2, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 126, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Go on pilgrimage', 'stat_effects' => '+20 Morality, +15 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay home', 'stat_effects' => '+5 Happiness, +5 Morality', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Hobby', 'description' => 'Pick up a hobby.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 127, 'event_category' => 'skill', 'chain_order' => 24, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Start gardening', 'stat_effects' => '+10 Creativity, +10 Health', 'days_to_advance' => 0],
                    ['text' => 'Abandon hobby', 'stat_effects' => '-10 Happiness, +5 Burnout', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'skill_23']],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection', 'description' => 'Reflect on life.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 128, 'event_category' => 'skill', 'chain_order' => 25, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Find peace', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Feel regret', 'stat_effects' => '-20 Happiness, +10 Isolation', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'skill_24']],
            ['age_group' => 'old', 'event_choice' => 'Elder Festival', 'description' => 'Join a festival.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 129, 'event_category' => 'random', 'chain_order' => 1, 'parent_category' => null, 'choices' => [
                    ['text' => 'Join parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Watch from home', 'stat_effects' => '+5 Happiness, +5 Creativity', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Contest', 'description' => 'Enter a contest.', 'image' => '/css/images/event-placeholder.jpg', 'type' => 'ageSpecific', 'deck_label' => 'Age Event', 'repeatable' => false, 'weight' => 0.3, 'auto_resolve' => false, 'days_to_advance' => 0, 'display_order' => 130, 'event_category' => 'skill', 'chain_order' => 26, 'parent_category' => 'skill', 'choices' => [
                    ['text' => 'Enter competition', 'stat_effects' => '+15 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay out', 'stat_effects' => '+5 Happiness, +5 Discipline', 'days_to_advance' => 0],
                ], 'conditions' => ['age_group' => 'old', 'has_completed_chain' => 'skill_25']],
            // === END OF LIFE (Old) ===
            ['age_group' => 'old', 'event_choice' => 'End of Life - Peaceful passing', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.5, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'is_milestone' => true, 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Sudden death', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.2, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'End of Life - Violent death', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
            // Fatal old events
            ['age_group' => 'old', 'event_choice' => 'Health Decline - Fatal illness', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Illness - Pneumonia fatal', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Accident - Fatal fall', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.01, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
            ['age_group' => 'old', 'event_choice' => 'Elder Reflection - Fatal heart attack', 'stat_effects' => '-100 Health, End of game', 'weight' => 0.05, 'event_category' => 'health', 'chain_order' => 99, 'parent_category' => 'health', 'conditions' => ['age_group' => 'old']],
        ];
        foreach ($events as $event) {
            // Use title as event_choice if not specified
            if (!isset($event['event_choice']) && isset($event['title'])) {
                $event['event_choice'] = $event['title'];
            }
            // Provide default outcome if not set
            if (!isset($event['outcome'])) {
                $event['outcome'] = $event['description'] ?? 'Event experienced.';
            }
            // Provide default stat_effects if not set
            if (!isset($event['stat_effects'])) {
                $event['stat_effects'] = '+5 Happiness';
            }
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
