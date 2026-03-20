<?php

namespace Database\Seeders;

use App\Models\CulturalEvent;
use Illuminate\Database\Seeder;

class CulturalEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Filipino cultural events with choices structure:
     * - type: 'cultural'
     * - deck_label: 'Cultural Event'
     * - Each event has: title, description, image, type, deck_label, repeatable, weight, 
     *   auto_resolve, days_to_advance, display_order, choices, conditions
     */
    public function run(): void
    {
        $events = [
            // Sinulog Festival (Cebu)
            [
                'title' => 'Sinulog Festival (Cebu)',
                'description' => 'The Sinulog Festival is a vibrant celebration honoring the Santo Niño. Join the colorful parade and dance in the streets!',
                'image' => '/css/images/sinulog-festival.png',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 4,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 1,
                'choices' => [
                    ['text' => 'Join parade and dance', 'stat_effects' => '+20 Happiness, +10 Morality', 'days_to_advance' => 0],
                    ['text' => 'Skip festival', 'stat_effects' => '+5 Isolation, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult', 'old']],
            ],
            // Ati-Atihan (Kalibo)
            [
                'title' => 'Ati-Atihan (Kalibo)',
                'description' => 'The Ati-Atihan Festival celebrates the indigenous Ati people. Join the tribal costume street dancing!',
                'image' => '/css/images/ati-atihan.png',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 2,
                'choices' => [
                    ['text' => 'Tribal costume street dancing', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Avoid crowds', 'stat_effects' => '+5 Isolation, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult', 'old']],
            ],
            // Dinagyang (Iloilo)
            [
                'title' => 'Dinagyang (Iloilo)',
                'description' => 'The Dinagyang Festival is a religious and cultural festival. Join the street dance competition!',
                'image' => '/css/images/dinagyang-festival.png',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 3,
                'choices' => [
                    ['text' => 'Join street dance competition', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip festivities', 'stat_effects' => '+5 Isolation, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult']], // Requires energy for dancing
            ],
            // Panagbenga (Baguio)
            [
                'title' => 'Panagbenga (Baguio)',
                'description' => 'The Panagbenga Flower Festival celebrates the blooming of flowers. Join the flower parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 4,
                'choices' => [
                    ['text' => 'Flower parade participation', 'stat_effects' => '+10 Creativity, +15 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Miss parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult'], 'has_skill' => 'Creativity'],
            ],
            // Kadayawan (Davao)
            [
                'title' => 'Kadayawan (Davao)',
                'description' => 'The Kadayawan Festival celebrates the harvest and gives thanks for blessings. Join the street party!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 5,
                'choices' => [
                    ['text' => 'Harvest celebration, join street party', 'stat_effects' => '+10 Wealth, +15 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip harvest', 'stat_effects' => '-5 Happiness, -5 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 15], // Harvest celebration
            ],
            // Pahiyas (Lucban)
            [
                'title' => 'Pahiyas (Lucban)',
                'description' => 'The Pahiyas Festival celebrates the harvest. Decorate your house with colorful harvest produce!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 6,
                'choices' => [
                    ['text' => 'Decorate house with harvest produce', 'stat_effects' => '+10 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'House undecorated', 'stat_effects' => '-5 Reputation, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'has_skill' => 'Creativity'],
            ],
            // Moriones (Marinduque)
            [
                'title' => 'Moriones (Marinduque)',
                'description' => 'The Moriones Festival is a Lenten tradition featuring biblical reenactments. Join the Holy Week tradition!',
                'image' => '/css/images/event-ceremony.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 7,
                'choices' => [
                    ['text' => 'Join biblical reenactment', 'stat_effects' => '+15 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip Holy Week tradition', 'stat_effects' => '-5 Morality, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'min_morality' => 30],
            ],
            // MassKara (Bacolod)
            [
                'title' => 'MassKara (Bacolod)',
                'description' => 'The MassKara Festival features colorful masks and parades. Wear a mask and join the fun!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 8,
                'choices' => [
                    ['text' => 'Wear mask and join parade', 'stat_effects' => '+20 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip festivities', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult', 'old']],
            ],
            // Flores de Mayo
            [
                'title' => 'Flores de Mayo',
                'description' => 'Flores de Mayo is a religious festival honoring the Virgin Mary. Offer flowers in devotion!',
                'image' => '/css/images/event-ceremony.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 9,
                'choices' => [
                    ['text' => 'Offer flowers to Mary', 'stat_effects' => '+10 Morality, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip devotion', 'stat_effects' => '-5 Morality, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult', 'old'], 'min_morality' => 25],
            ],
            // Santacruzan
            [
                'title' => 'Santacruzan',
                'description' => 'Santacruzan is a liturgical procession honoring the Finding of the True Cross. Join the procession!',
                'image' => '/css/images/event-ceremony.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 10,
                'choices' => [
                    ['text' => 'Join procession', 'stat_effects' => '+15 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip procession', 'stat_effects' => '-5 Morality, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'min_morality' => 30],
            ],
            // Higantes (Angono)
            [
                'title' => 'Higantes (Angono)',
                'description' => 'The Higantes Festival features giant paper-mache puppets. Parade with the giant puppets!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 11,
                'choices' => [
                    ['text' => 'Parade with giant puppets', 'stat_effects' => '+15 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Avoid crowds', 'stat_effects' => '+5 Isolation, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult']], // Fun for families
            ],
            // Pintados (Leyte)
            [
                'title' => 'Pintados (Leyte)',
                'description' => 'The Pintados Festival features body painting and parades. Join the body painting parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 12,
                'choices' => [
                    ['text' => 'Body painting parade', 'stat_effects' => '+10 Creativity, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult'], 'has_skill' => 'Creativity'],
            ],
            // Sandugo (Bohol)
            [
                'title' => 'Sandugo (Bohol)',
                'description' => 'The Sandugo Festival commemorates the blood compact between Miguel López de Legazpi and Datu Sikatuna. Reenact the historic event!',
                'image' => '/css/images/event-ceremony.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 13,
                'choices' => [
                    ['text' => 'Reenact blood compact', 'stat_effects' => '+10 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip reenactment', 'stat_effects' => '-5 Morality, -5 Happiness', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'min_morality' => 35],
            ],
            // Kaamulan (Bukidnon)
            [
                'title' => 'Kaamulan (Bukidnon)',
                'description' => 'The Kaamulan Festival celebrates the culture of Bukidnon tribes. Join the tribal unity celebration!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 14,
                'choices' => [
                    ['text' => 'Tribal unity celebration', 'stat_effects' => '+15 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip tribal rites', 'stat_effects' => '+5 Isolation, -5 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'min_morality' => 30],
            ],
            // Giant Lantern (Pampanga)
            [
                'title' => 'Giant Lantern (Pampanga)',
                'description' => 'The Giant Lantern Festival features massive illuminated lanterns. Enter the lantern contest!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 15,
                'choices' => [
                    ['text' => 'Lantern contest', 'stat_effects' => '+20 Creativity, +15 Reputation', 'days_to_advance' => 0],
                    ['text' => 'No lantern entry', 'stat_effects' => '-5 Creativity, -5 Reputation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'has_skill' => 'Creativity'],
            ],
            // Hermosa (Zamboanga)
            [
                'title' => 'Hermosa (Zamboanga)',
                'description' => 'The Hermosa Festival features street parades with beautiful attendees. Join the street parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 16,
                'choices' => [
                    ['text' => 'Street parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult']], // Parade event
            ],
            // Malasimbo (Puerto Galera)
            [
                'title' => 'Malasimbo (Puerto Galera)',
                'description' => 'The Malasimbo Festival features cultural concerts and performances. Attend the concert!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 17,
                'choices' => [
                    ['text' => 'Attend concert', 'stat_effects' => '+20 Happiness, +10 Creativity', 'days_to_advance' => 0],
                    ['text' => 'Skip concert', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'has_skill' => 'Music'],
            ],
            // Tuna Festival (GenSan)
            [
                'title' => 'Tuna Festival (GenSan)',
                'description' => 'The Tuna Festival celebrates the tuna industry in General Santos. Join the fish parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 18,
                'choices' => [
                    ['text' => 'Join fish parade', 'stat_effects' => '+10 Wealth, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 15],
            ],
            // Lanzones (Camiguin)
            [
                'title' => 'Lanzones (Camiguin)',
                'description' => 'The Lanzones Festival celebrates the harvest of the lanzones fruit. Join the fruit harvest!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 19,
                'choices' => [
                    ['text' => 'Join fruit harvest', 'stat_effects' => '+10 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip harvest', 'stat_effects' => '-5 Happiness, -5 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 10],
            ],
            // Mango Festival (Zambales)
            [
                'title' => 'Mango Festival (Zambales)',
                'description' => 'The Mango Festival celebrates the sweet mangoes of Zambales. Celebrate mango season!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 20,
                'choices' => [
                    ['text' => 'Celebrate mango season', 'stat_effects' => '+10 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 10],
            ],
            // Coconut Festival (Quezon)
            [
                'title' => 'Coconut Festival (Quezon)',
                'description' => 'The Coconut Festival celebrates the coconut industry. Join the parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 21,
                'choices' => [
                    ['text' => 'Join parade', 'stat_effects' => '+10 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 10],
            ],
            // Bangus Festival (Dagupan)
            [
                'title' => 'Bangus Festival (Dagupan)',
                'description' => 'The Bangus Festival celebrates the milkfish (bangus) harvest. Celebrate the milkfish harvest!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 3,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 22,
                'choices' => [
                    ['text' => 'Celebrate milkfish harvest', 'stat_effects' => '+15 Wealth, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip festival', 'stat_effects' => '-5 Happiness, -5 Morality', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['adult', 'old'], 'min_wealth' => 15],
            ],
            // Tabak Festival (Toledo)
            [
                'title' => 'Tabak Festival (Toledo)',
                'description' => 'The Tabak Festival celebrates martial arts and knives. Join the martial parade!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 23,
                'choices' => [
                    ['text' => 'Join martial parade', 'stat_effects' => '+10 Strength, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult'], 'min_strength' => 20],
            ],
            // Rodeo Masbateño (Masbate)
            [
                'title' => 'Rodeo Masbateño (Masbate)',
                'description' => 'The Rodeo Masbateño is a cowboy-style rodeo. Join the rodeo!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 24,
                'choices' => [
                    ['text' => 'Join rodeo', 'stat_effects' => '+15 Strength, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip rodeo', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult'], 'min_strength' => 25],
            ],
            // Surf Festival (La Union)
            [
                'title' => 'Surf Festival (La Union)',
                'description' => 'The Surf Festival celebrates surfing in La Union. Win the surf competition!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 25,
                'choices' => [
                    ['text' => 'Win surf competition', 'stat_effects' => '+10 Strength, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip surf event', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult'], 'min_strength' => 20, 'has_skill' => 'Fitness'],
            ],
            // Pagoda Festival (Bulacan)
            [
                'title' => 'Pagoda Festival (Bulacan)',
                'description' => 'The Pagoda Festival features a colorful river procession. Join the river procession!',
                'image' => '/css/images/event-ceremony.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 26,
                'choices' => [
                    ['text' => 'Join river procession', 'stat_effects' => '+10 Morality, +10 Reputation', 'days_to_advance' => 0],
                    ['text' => 'Skip procession', 'stat_effects' => '-5 Morality, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['teen', 'adult', 'old'], 'min_morality' => 30],
            ],
            // Buyogan Festival (Leyte)
            [
                'title' => 'Buyogan Festival (Leyte)',
                'description' => 'The Buyogan Festival features bee dance competitions. Join the bee dance!',
                'image' => '/css/images/event-festival.jpg',
                'type' => 'cultural',
                'deck_label' => 'Cultural Event',
                'repeatable' => true,
                'weight' => 2,
                'auto_resolve' => false,
                'days_to_advance' => 0,
                'display_order' => 27,
                'choices' => [
                    ['text' => 'Bee dance competition', 'stat_effects' => '+10 Creativity, +10 Happiness', 'days_to_advance' => 0],
                    ['text' => 'Skip festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'days_to_advance' => 0],
                ],
                'conditions' => ['age_group' => ['child', 'teen', 'adult'], 'has_skill' => 'Creativity'],
            ],
        ];

        foreach ($events as $event) {
            // Use title as event_choice if not specified
            if (!isset($event['event_choice']) && isset($event['title'])) {
                $event['event_choice'] = $event['title'];
            }
            // Provide default outcome if not set
            if (!isset($event['outcome'])) {
                $event['outcome'] = $event['description'] ?? 'Cultural event enjoyed.';
            }
            // Provide default stat_effects if not set
            if (!isset($event['stat_effects'])) {
                $event['stat_effects'] = '+5 Happiness';
            }
            CulturalEvent::create($event);
        }
    }
}
