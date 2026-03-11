<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            ['name' => 'Reading', 'effect' => '+3 Intelligence, +1 Creativity', 'hidden' => '+1 Isolation', 'description' => 'Expand your mind through books. Builds knowledge but can make you retreat inward.', 'image' => '/css/images/skill/reading.png'],
            ['name' => 'Studying', 'effect' => '+4 Intelligence, +1 Discipline', 'hidden' => '+1 Stress', 'description' => 'Sharpen your intellect with focused learning. Boosts academics but can be mentally taxing.', 'image' => '/css/images/skill/studying.png'],
            ['name' => 'Problem-Solving', 'effect' => '+3 Intelligence, +2 Creativity', 'hidden' => '+1 Burnout', 'description' => 'Tackle challenges with logic and innovation. Enhances adaptability but constant puzzles can wear you down.', 'image' => '/css/images/skill/problem-solving.png'],
            ['name' => 'Memory Training', 'effect' => '+3 Intelligence, +1 Luck', 'hidden' => '+1 Stress', 'description' => 'Improve recall and retention. Strengthens intellect but can overload your mind.', 'image' => '/css/images/skill/memory-training.png'],
            ['name' => 'Fitness', 'effect' => '+4 Strength, +1 Health', 'hidden' => '+1 Discipline', 'description' => 'Train your body for endurance and resilience. Improves vitality but demands consistency.', 'image' => '/css/images/skill/fitness.png'],
            ['name' => 'Endurance', 'effect' => '+3 Strength, +2 Discipline', 'hidden' => '+1 Burnout', 'description' => 'Push your body past limits. Builds stamina but risks exhaustion.', 'image' => '/css/images/skill/endurance.png'],
            ['name' => 'Manual Labor', 'effect' => '+3 Strength, +1 Wealth', 'hidden' => '+1 Debt', 'description' => 'Work with your hands to earn and build. Strengthens body and finances but can trap you in exhausting cycles.', 'image' => '/css/images/skill/manual-labor.png'],
            ['name' => 'Cooking', 'effect' => '+3 Creativity, +2 Health', 'hidden' => '+1 Stress', 'description' => 'Prepare meals and nourish yourself. Builds independence but can be tiring.', 'image' => '/css/images/skill/cooking.png'],
            ['name' => 'Conversation', 'effect' => '+4 Charisma, +1 Reputation', 'hidden' => '+1 Stress', 'description' => 'Engage others with words and presence. Builds influence but constant interaction can drain energy.', 'image' => '/css/images/skill/conversation.png'],
            ['name' => 'Confidence', 'effect' => '+3 Charisma, +2 Ego', 'hidden' => '+1 Isolation', 'description' => 'Believe in yourself and project strength. Inspires others but can push you toward arrogance.', 'image' => '/css/images/skill/confidence.png'],
            ['name' => 'Teamwork', 'effect' => '+3 Charisma, +2 Discipline', 'hidden' => '+1 Burnout', 'description' => 'Collaborate effectively with groups. Builds trust but can lead to overreliance on others.', 'image' => '/css/images/skill/teamwork.png'],
            ['name' => 'Persuasion', 'effect' => '+3 Charisma, +2 Intelligence', 'hidden' => '+1 Ego', 'description' => 'Convince others to see your way. Builds influence but risks manipulation.', 'image' => '/css/images/skill/persuasion.png'],
            ['name' => 'Public Speaking', 'effect' => '+4 Charisma, +1 Morality', 'hidden' => '+1 Stress', 'description' => 'Address crowds with confidence. Builds leadership but can be nerve-wracking.', 'image' => '/css/images/skill/public-speaking.png'],
            ['name' => 'Drawing', 'effect' => '+4 Creativity, +1 Happiness', 'hidden' => '+1 Isolation', 'description' => 'Express ideas visually. Sparks joy but can isolate you in your own world.', 'image' => '/css/images/skill/drawing.png'],
            ['name' => 'Writing', 'effect' => '+3 Creativity, +2 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Craft stories and ideas with words. Builds intellect but can be mentally draining.', 'image' => '/css/images/skill/writing.png'],
            ['name' => 'Improvisation', 'effect' => '+3 Creativity, +2 Charisma', 'hidden' => '+1 Stress', 'description' => 'Think on your feet and adapt. Builds flexibility but can be chaotic.', 'image' => '/css/images/skill/improvisation.png'],
            ['name' => 'Music', 'effect' => '+4 Creativity, +1 Charisma', 'hidden' => '+1 Isolation', 'description' => 'Play or compose music. Builds joy and influence but can isolate you in practice.', 'image' => '/css/images/skill/music.png'],
            ['name' => 'Budgeting', 'effect' => '+4 Wealth, +1 Discipline', 'hidden' => '+1 Stress', 'description' => 'Manage money wisely. Builds financial stability but constant tracking can be tiring.', 'image' => '/css/images/skill/budgeting.png'],
            ['name' => 'Negotiation', 'effect' => '+3 Wealth, +2 Charisma', 'hidden' => '+1 Ego', 'description' => 'Strike deals and gain advantage. Improves finances but can inflate self-importance.', 'image' => '/css/images/skill/negotiation.png'],
            ['name' => 'Planning', 'effect' => '+3 Wealth, +2 Intelligence', 'hidden' => '+1 Burnout', 'description' => 'Organize steps toward success. Builds foresight but can lead to overthinking.', 'image' => '/css/images/skill/planning.png'],
            ['name' => 'Risk Awareness', 'effect' => '+3 Luck, +1 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Sense opportunities and dangers. Builds adaptability but can make you overly cautious.', 'image' => '/css/images/skill/risk-awareness.png'],
            ['name' => 'Adaptability', 'effect' => '+4 Luck, +1 Creativity', 'hidden' => '+1 Burnout', 'description' => 'Adjust quickly to change. Enhances resilience but constant shifting can wear you down.', 'image' => '/css/images/skill/adaptability.png'],
            ['name' => 'Opportunism', 'effect' => '+3 Luck, +2 Wealth', 'hidden' => '+1 Morality', 'description' => 'Seize chances when they appear. Builds success but can compromise ethics.', 'image' => '/css/images/skill/opportunism.png'],
            ['name' => 'Intuition', 'effect' => '+3 Luck, +2 Creativity', 'hidden' => '+1 Stress', 'description' => 'Trust your gut instincts. Builds quick decision-making but can be unreliable.', 'image' => '/css/images/skill/intuition.png'],
            ['name' => 'Gardening', 'effect' => '+3 Creativity, +2 Health', 'hidden' => '+1 Isolation', 'description' => 'Cultivate plants and nature. Builds patience and wellness but can be solitary.', 'image' => '/css/images/skill/gardening.png'],
            ['name' => 'Cooking Basics', 'effect' => '+3 Creativity, +2 Health', 'hidden' => '+1 Stress', 'description' => 'Prepare simple meals. Builds independence but can be tiring.', 'image' => '/css/images/skill/cooking-basics.png'],
            ['name' => 'Cleaning', 'effect' => '+3 Discipline, +2 Health', 'hidden' => '+1 Stress', 'description' => 'Maintain order and hygiene. Builds discipline but can feel repetitive.', 'image' => '/css/images/skill/cleaning.png'],
            ['name' => 'Driving', 'effect' => '+3 Luck, +2 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Operate vehicles safely. Builds independence but can be risky.', 'image' => '/css/images/skill/driving.png'],
            ['name' => 'Swimming', 'effect' => '+4 Strength, +1 Health', 'hidden' => '+1 Stress', 'description' => 'Move confidently in water. Builds fitness but requires effort.', 'image' => '/css/images/skill/swimming.png'],
            ['name' => 'Meditation', 'effect' => '+3 Discipline, +2 Morality', 'hidden' => '-1 Stress', 'description' => 'Calm the mind and body. Builds focus but reduces spontaneity.', 'image' => '/css/images/skill/meditation.png'],
            ['name' => 'Basic First Aid', 'effect' => '+3 Intelligence, +2 Health', 'hidden' => '+1 Stress', 'description' => 'Treat minor injuries. Builds resilience but can be emotionally taxing.', 'image' => '/css/images/skill/basic-first-aid.png'],
            ['name' => 'Crafting', 'effect' => '+4 Creativity, +1 Discipline', 'hidden' => '+1 Stress', 'description' => 'Create useful items by hand. Builds innovation but requires patience.', 'image' => '/css/images/skill/crafting.png'],
            ['name' => 'Storytelling', 'effect' => '+3 Charisma, +2 Creativity', 'hidden' => '+1 Stress', 'description' => 'Captivate others with tales. Builds influence but can drain energy.', 'image' => '/css/images/skill/storytelling.png'],
            ['name' => 'Observation', 'effect' => '+3 Intelligence, +2 Luck', 'hidden' => '+1 Stress', 'description' => 'Notice details others miss. Builds awareness but can cause overthinking.', 'image' => '/css/images/skill/observation.png'],
            ['name' => 'Basic Math', 'effect' => '+4 Intelligence, +1 Wealth', 'hidden' => '+1 Stress', 'description' => 'Handle numbers and calculations. Builds problem-solving but can be tedious.', 'image' => '/css/images/skill/basic-math.png'],
            ['name' => 'Organization', 'effect' => '+3 Discipline, +2 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Keep things structured and efficient. Builds stability but reduces flexibility.', 'image' => '/css/images/skill/organization.png'],
            ['name' => 'Negotiation Basics', 'effect' => '+3 Wealth, +2 Charisma', 'hidden' => '+1 Ego', 'description' => 'Find compromises and deals. Builds financial gain but risks manipulation.', 'image' => '/css/images/skill/negotiation-basics.png'],
            ['name' => 'Survival Skills', 'effect' => '+4 Strength, +1 Luck', 'hidden' => '+1 Stress', 'description' => 'Endure harsh conditions. Builds resilience but can be dangerous.', 'image' => '/css/images/skill/survival.png'],
            ['name' => 'Listening', 'effect' => '+3 Charisma, +2 Morality', 'hidden' => '+1 Stress', 'description' => 'Pay attention to others deeply. Builds trust but can be emotionally draining.', 'image' => '/css/images/skill/listening.png'],
            ['name' => 'Time Management', 'effect' => '+3 Discipline, +2 Wealth', 'hidden' => '+1 Stress', 'description' => 'Balance priorities effectively. Builds productivity but can feel rigid.', 'image' => '/css/images/skill/time-management.png'],
            ['name' => 'Basic Technology', 'effect' => '+3 Intelligence, +2 Creativity', 'hidden' => '+1 Stress', 'description' => 'Use everyday devices. Builds adaptability but can be frustrating.', 'image' => '/css/images/skill/basic-technology.png'],
            ['name' => 'Negotiation Advanced', 'effect' => '+4 Wealth, +1 Charisma', 'hidden' => '+1 Ego', 'description' => 'Master complex deals. Builds influence but risks arrogance.', 'image' => '/css/images/skill/negotiation-advanced.png'],
            ['name' => 'Physical Training', 'effect' => '+4 Strength, +1 Discipline', 'hidden' => '+1 Burnout', 'description' => 'Condition your body systematically. Builds fitness but risks fatigue.', 'image' => '/css/images/skill/physical-training.png'],
            ['name' => 'Basic Language', 'effect' => '+3 Intelligence, +2 Charisma', 'hidden' => '+1 Stress', 'description' => 'Learn new words and phrases. Builds communication but can be challenging.', 'image' => '/css/images/skill/basic-language.png'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['name' => $skill['name']], $skill);
        }
    }
}
