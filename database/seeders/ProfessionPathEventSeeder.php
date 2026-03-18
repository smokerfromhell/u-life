<?php
namespace Database\Seeders;
use App\Models\ProfessionPathEvent;
use Database\Seeders\Concerns\GeneratesOutcomeChoices;
use Illuminate\Database\Seeder;
class ProfessionPathEventSeeder extends Seeder
{
    use GeneratesOutcomeChoices;
    /**
     * Run the database seeds.
     * 
     * Profession path events with choices structure:
     * - type: 'profession'
     * - deck_label: 'Profession Event'
     * - Each event has: title, description, image, type, deck_label, repeatable, weight, 
     *   auto_resolve, days_to_advance, display_order, choices, conditions, profession
     */
    public function run(): void
    {
        $events = [
            // Doctor Profession Events
            [
                'profession' => 'Doctor',
                'title' => 'Emergency Surgery',
                'description' => 'A critical patient needs emergency surgery. Your skills are needed.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Perform surgery', 'stat_effects' => '+15 Morality, +10 Reputation, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '-5 Morality, -3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Doctor',
                'title' => 'Medical Error',
                'description' => 'A medical error occurred during treatment. The patient\'s family is upset.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Apologize and compensate', 'stat_effects' => '-10 Wealth, -5 Reputation, +5 Morality', 'days_to_advance' => 0],
                    ['text' => 'Deny responsibility', 'stat_effects' => '-15 Reputation, +5 Ego, -10 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Doctor',
                'title' => 'Award Ceremony',
                'description' => 'You are recognized for your contributions to medicine.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Accept award', 'stat_effects' => '+20 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+5 Morality, -5 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Doctor',
                'title' => 'Research Grant',
                'description' => 'You receive a research grant for medical studies.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Accept grant', 'stat_effects' => '+15 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+5 Discipline, -5 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Doctor',
                'title' => 'Breakthrough Discovery',
                'description' => 'You make a major medical breakthrough!',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 1,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 5,
                'choices' => [
                    ['text' => 'Publish discovery', 'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Keep secret', 'stat_effects' => '+10 Ego, -10 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            // Teacher Profession Events
            [
                'profession' => 'Teacher',
                'title' => 'Inspiring Lecture',
                'description' => 'You deliver an inspiring lecture that moves students.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Continue inspiring', 'stat_effects' => '+10 Reputation, +5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Return to normal', 'stat_effects' => '+3 Discipline, -2 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Teacher',
                'title' => 'Classroom Chaos',
                'description' => 'The classroom becomes unmanageable.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Take control', 'stat_effects' => '+5 Discipline, -2 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Ignore it', 'stat_effects' => '-5 Discipline, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Teacher',
                'title' => 'Student Success',
                'description' => 'Your student achieves great success thanks to your guidance.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Celebrate success', 'stat_effects' => '+15 Reputation, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Stay modest', 'stat_effects' => '+5 Morality, +3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Teacher',
                'title' => 'School Award',
                'description' => 'You receive an award for excellence in teaching.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Accept award', 'stat_effects' => '+15 Reputation, +10 Ego', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+5 Morality, -3 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            // Scientist Profession Events
            [
                'profession' => 'Scientist',
                'title' => 'Breakthrough Discovery',
                'description' => 'You make a major scientific breakthrough!',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Publish findings', 'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Keep secret', 'stat_effects' => '+10 Ego, -10 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Scientist',
                'title' => 'Failed Experiment',
                'description' => 'Your experiment fails spectacularly.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Try again', 'stat_effects' => '+5 Discipline, -2 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Give up', 'stat_effects' => '-5 Reputation, +5 Burnout', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Scientist',
                'title' => 'Research Grant',
                'description' => 'You receive funding for your research.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Accept grant', 'stat_effects' => '+15 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+5 Discipline, -5 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            // Lawyer Profession Events
            [
                'profession' => 'Lawyer',
                'title' => 'Court Victory',
                'description' => 'You win a major court case!',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Celebrate victory', 'stat_effects' => '+15 Reputation, +10 Wealth', 'days_to_advance' => 0],
                    ['text' => 'Stay professional', 'stat_effects' => '+5 Reputation, +3 Wealth', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Lawyer',
                'title' => 'Court Loss',
                'description' => 'You lose an important court case.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Accept defeat', 'stat_effects' => '-10 Reputation, -5 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Appeal', 'stat_effects' => '-5 Wealth, +3 Discipline', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
            [
                'profession' => 'Lawyer',
                'title' => 'Pro Bono Case',
                'description' => 'You take on a pro bono case for a good cause.',
                'image' => '/css/images/event-placeholder.jpg',
                'type' => 'profession',
                'deck_label' => 'Profession Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Take the case', 'stat_effects' => '+10 Morality, +5 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Decline', 'stat_effects' => '+3 Wealth, -3 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => null,
            ],
        ];

        foreach ($events as $event) {
            ProfessionPathEvent::firstOrCreate(
                ['profession' => $event['profession'], 'title' => $event['title']],
                $event
            );
        }

        // Ensure all profession path events have choices
        $this->ensureProfessionPathChoices();
    }

    private function ensureProfessionPathChoices(): void
    {
        $events = ProfessionPathEvent::all();
        foreach ($events as $event) {
            if ($this->eventHasChoices($event->choices ?? null)) {
                continue;
            }
            // Add default structure for events without choices
            if (!isset($event->type)) {
                $event->type = 'profession';
                $event->deck_label = 'Profession Event';
                $event->repeatable = true;
                $event->auto_resolve = false;
                $event->days_to_advance = 0;
                $event->image = '/css/images/event-placeholder.jpg';
                $event->conditions = null;
            }
            $eventName = (string) ($event->event_choice ?? $event->title ?? 'Profession Event');
            $choices = $this->generateChoices($eventName, $event->stat_effects ?? null, 'adult', 'profession');
            $event->choices = json_encode($choices);
            $event->save();
        }
    }
}
