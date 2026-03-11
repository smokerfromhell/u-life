<?php

namespace Database\Seeders;

use App\Models\Talent;
use Illuminate\Database\Seeder;

class TalentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $talents = [
            ['name' => 'Resilience', 'effect' => '+5 Strength, -2 Creativity', 'hidden' => '-1 Stress', 'description' => 'Able to endure hardships and bounce back stronger.', 'image' => '/css/images/talent/resilience.png'],
            ['name' => 'Empathy', 'effect' => '+5 Morality, -2 Luck', 'hidden' => '-1 Stress', 'description' => 'Deeply attuned to others\' emotions, fostering compassion.', 'image' => '/css/images/talent/empathy.png'],
            ['name' => 'Ambition', 'effect' => '+5 Charisma, -3 Health', 'hidden' => '+2 Stress, +1 Debt', 'description' => 'Driven to succeed, but ambition often comes at personal cost.', 'image' => '/css/images/talent/ambition.png'],
            ['name' => 'Honesty', 'effect' => '+5 Morality, -2 Wealth', 'hidden' => '-1 Corruption', 'description' => 'Guided by truth, even when it costs opportunities.', 'image' => '/css/images/talent/honesty.png'],
            ['name' => 'Charm', 'effect' => '+5 Charisma, -2 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Naturally persuasive and likable, but sometimes superficial.', 'image' => '/css/images/talent/charm.png'],
            ['name' => 'Patience', 'effect' => '+4 Morality, +2 Intelligence', 'hidden' => '-1 Stress', 'description' => 'Able to wait calmly, reducing conflict and mistakes.', 'image' => '/css/images/talent/patience.png'],
            ['name' => 'Courage', 'effect' => '+5 Strength, -2 Luck', 'hidden' => '+2 Stress', 'description' => 'Bravery in the face of danger, but risk of harm is higher.', 'image' => '/css/images/talent/courage.png'],
            ['name' => 'Integrity', 'effect' => '+5 Morality, -2 Wealth', 'hidden' => '-1 Corruption', 'description' => 'Strong moral compass, but may miss financial gain.', 'image' => '/css/images/talent/integrity.png'],
            ['name' => 'Optimism', 'effect' => '+4 Luck, +2 Charisma', 'hidden' => '+1 Stress', 'description' => 'Positive outlook boosts morale, but can ignore risks.', 'image' => '/css/images/talent/optimism.png'],
            ['name' => 'Focus', 'effect' => '+5 Intelligence, -2 Luck', 'hidden' => '+1 Stress', 'description' => 'Laser-sharp concentration improves performance, but reduces spontaneity.', 'image' => '/css/images/talent/focus.png'],
            ['name' => 'Generosity', 'effect' => '+4 Morality, +2 Charisma', 'hidden' => '+1 Debt', 'description' => 'Willingness to give builds goodwill, but risks financial strain.', 'image' => '/css/images/talent/generosity.png'],
            ['name' => 'Creativity Spark', 'effect' => '+5 Creativity, -2 Discipline', 'hidden' => '+1 Fatigue', 'description' => 'Natural imagination fuels innovation, but can lack structure.', 'image' => '/css/images/talent/creativity-spark.png'],
            ['name' => 'Confidence', 'effect' => '+5 Charisma, -2 Morality', 'hidden' => '+1 Stress', 'description' => 'Self-assurance inspires others, but can lead to arrogance.', 'image' => '/css/images/talent/confidence.png'],
            ['name' => 'Wisdom', 'effect' => '+5 Intelligence, +2 Morality', 'hidden' => '-1 Stress', 'description' => 'Life experience guides decisions, but may slow adaptability.', 'image' => '/css/images/talent/wisdom.png'],
            ['name' => 'Humor', 'effect' => '+4 Charisma, +2 Luck', 'hidden' => '+1 Stress', 'description' => 'Lightheartedness builds bonds, but can mask deeper issues.', 'image' => '/css/images/talent/humor.png'],
            ['name' => 'Determination', 'effect' => '+5 Strength, +2 Intelligence', 'hidden' => '+2 Stress', 'description' => 'Unyielding drive achieves goals, but risks burnout.', 'image' => '/css/images/talent/determination.png'],
            ['name' => 'Compassion', 'effect' => '+4 Morality, +2 Health', 'hidden' => '+1 Stress', 'description' => 'Caring nature heals others, but emotional burden is heavy.', 'image' => '/css/images/talent/compassion.png'],
            ['name' => 'Curiosity', 'effect' => '+5 Intelligence, +2 Creativity', 'hidden' => '+1 Stress', 'description' => 'Natural desire to learn expands horizons, but can be distracting.', 'image' => '/css/images/talent/curiosity.png'],
            ['name' => 'Discipline of Mind', 'effect' => '+5 Intelligence, -2 Luck', 'hidden' => '+1 Stress', 'description' => 'Mental rigor improves focus, but reduces spontaneity.', 'image' => '/css/images/talent/discipline-mind.png'],
            ['name' => 'Charisma Aura', 'effect' => '+6 Charisma, -2 Intelligence', 'hidden' => '+2 Stress', 'description' => 'Magnetic personality draws people in, but can be exhausting.', 'image' => '/css/images/talent/charisma-aura.png'],
            ['name' => 'Stoicism', 'effect' => '+5 Morality, +2 Strength', 'hidden' => '-2 Stress', 'description' => 'Calm endurance of hardship builds resilience, but reduces emotional expression.', 'image' => '/css/images/talent/stoicism.png'],
            ['name' => 'Visionary', 'effect' => '+5 Creativity, +2 Intelligence', 'hidden' => '+2 Stress', 'description' => 'Sees possibilities others miss, but risks impracticality.', 'image' => '/css/images/talent/visionary.png'],
            ['name' => 'Loyalty', 'effect' => '+4 Morality, +2 Charisma', 'hidden' => '-1 Stress', 'description' => 'Faithful to allies, but can be exploited.', 'image' => '/css/images/talent/loyalty.png'],
            ['name' => 'Pragmatism', 'effect' => '+5 Intelligence, -2 Morality', 'hidden' => '+1 Stress', 'description' => 'Focus on practical solutions, but risks ethical compromise.', 'image' => '/css/images/talent/pragmatism.png'],
            ['name' => 'Tenacity', 'effect' => '+5 Strength, +2 Luck', 'hidden' => '+2 Stress', 'description' => 'Never gives up, but risks stubbornness.', 'image' => '/css/images/talent/tenacity.png'],
            ['name' => 'Diplomacy', 'effect' => '+5 Charisma, +2 Morality', 'hidden' => '+1 Stress', 'description' => 'Skilled at peacekeeping, but emotionally draining.', 'image' => '/css/images/talent/diplomacy.png'],
            ['name' => 'Inventiveness', 'effect' => '+5 Creativity, -2 Wealth', 'hidden' => '+1 Fatigue', 'description' => 'Natural knack for innovation, but often financially risky.', 'image' => '/css/images/talent/inventiveness.png'],
            ['name' => 'Self-Reliance', 'effect' => '+5 Strength, +2 Intelligence', 'hidden' => '+1 Stress', 'description' => 'Independent and resourceful, but risks isolation.', 'image' => '/css/images/talent/self-reliance.png'],
            ['name' => 'Adaptability Trait', 'effect' => '+4 Luck, +2 Creativity', 'hidden' => '+1 Stress', 'description' => 'Naturally flexible in changing environments, but can feel unstable.', 'image' => '/css/images/talent/adaptability-trait.png'],
            ['name' => 'Altruism', 'effect' => '+5 Morality, -2 Wealth', 'hidden' => '+1 Fatigue', 'description' => 'Selfless concern for others, but drains personal resources.', 'image' => '/css/images/talent/altruism.png'],
        ];

        foreach ($talents as $talent) {
            Talent::firstOrCreate(['name' => $talent['name']], $talent);
        }
    }
}
