<?php

namespace Database\Seeders;

use App\Models\CulturalEvent;
use Illuminate\Database\Seeder;

class CulturalEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Filipino cultural events with weights as integers (multiplied by 10)
     */
    public function run(): void
    {
        $events = [
            // Sinulog Festival (Cebu)
            ['event_choice' => 'Sinulog Festival (Cebu) - Join parade and dance', 'outcome' => 'You join the Sinulog parade and dance joyfully', 'stat_effects' => '+20 Happiness, +10 Morality', 'weight' => 4, 'image' => '/css/images/sinulog-festival.png'],
            ['event_choice' => 'Sinulog Festival (Cebu) - Skip festival', 'outcome' => 'You skip the Sinulog festival and feel left out', 'stat_effects' => '+5 Isolation, -5 Happiness', 'weight' => 2, 'image' => '/css/images/sinulog-festival.png'],
            // Ati-Atihan (Kalibo)
            ['event_choice' => 'Ati-Atihan (Kalibo) - Tribal costume street dancing', 'outcome' => 'You join the Ati-Atihan tribal costume street dancing', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 3, 'image' => '/css/images/ati-atihan.png'],
            ['event_choice' => 'Ati-Atihan (Kalibo) - Avoid crowds', 'outcome' => 'You avoid the Ati-Atihan crowds', 'stat_effects' => '+5 Isolation, -5 Happiness', 'weight' => 2, 'image' => '/css/images/ati-atihan.png'],
            // Dinagyang (Iloilo)
            ['event_choice' => 'Dinagyang (Iloilo) - Join street dance competition', 'outcome' => 'You join the Dinagyang street dance competition', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 3, 'image' => '/css/images/dinagyang-festival.png'],
            ['event_choice' => 'Dinagyang (Iloilo) - Skip festivities', 'outcome' => 'You skip the Dinagyang festivities', 'stat_effects' => '+5 Isolation, -5 Happiness', 'weight' => 2, 'image' => '/css/images/dinagyang-festival.png'],
            // Panagbenga (Baguio)
            ['event_choice' => 'Panagbenga (Baguio) - Flower parade participation', 'outcome' => 'You participate in the Panagbenga flower parade', 'stat_effects' => '+10 Creativity, +15 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Panagbenga (Baguio) - Miss parade', 'outcome' => 'You miss the Panagbenga parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Kadayawan (Davao)
            ['event_choice' => 'Kadayawan (Davao) - Harvest celebration, join street party', 'outcome' => 'You join the Kadayawan harvest street party', 'stat_effects' => '+10 Wealth, +15 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Kadayawan (Davao) - Skip harvest', 'outcome' => 'You skip the Kadayawan harvest celebration', 'stat_effects' => '-5 Happiness, -5 Morality', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Pahiyas (Lucban)
            ['event_choice' => 'Pahiyas (Lucban) - Decorate house with harvest produce', 'outcome' => 'You decorate your house with harvest produce for Pahiyas', 'stat_effects' => '+10 Creativity, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Pahiyas (Lucban) - House undecorated', 'outcome' => 'Your house remains undecorated during Pahiyas', 'stat_effects' => '-5 Reputation, -5 Happiness', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Moriones (Marinduque)
            ['event_choice' => 'Moriones (Marinduque) - Join biblical reenactment', 'outcome' => 'You join the Moriones biblical reenactment', 'stat_effects' => '+15 Morality, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Moriones (Marinduque) - Skip Holy Week tradition', 'outcome' => 'You skip the Moriones Holy Week tradition', 'stat_effects' => '-5 Morality, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // MassKara (Bacolod)
            ['event_choice' => 'MassKara (Bacolod) - Wear mask and join parade', 'outcome' => 'You wear a mask and join the MassKara parade', 'stat_effects' => '+20 Happiness, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'MassKara (Bacolod) - Skip festivities', 'outcome' => 'You skip the MassKara festivities', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Flores de Mayo
            ['event_choice' => 'Flores de Mayo - Offer flowers to Mary', 'outcome' => 'You offer flowers to Mary during Flores de Mayo', 'stat_effects' => '+10 Morality, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Flores de Mayo - Skip devotion', 'outcome' => 'You skip the Flores de Mayo devotion', 'stat_effects' => '-5 Morality, -5 Happiness', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // Santacruzan
            ['event_choice' => 'Santacruzan - Join procession', 'outcome' => 'You join the Santacruzan procession', 'stat_effects' => '+15 Morality, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Santacruzan - Skip procession', 'outcome' => 'You skip the Santacruzan procession', 'stat_effects' => '-5 Morality, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // Higantes (Angono)
            ['event_choice' => 'Higantes (Angono) - Parade with giant puppets', 'outcome' => 'You parade with giant puppets during Higantes', 'stat_effects' => '+15 Creativity, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Higantes (Angono) - Avoid crowds', 'outcome' => 'You avoid the Higantes crowds', 'stat_effects' => '+5 Isolation, -5 Happiness', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Pintados (Leyte)
            ['event_choice' => 'Pintados (Leyte) - Body painting parade', 'outcome' => 'You join the Pintados body painting parade', 'stat_effects' => '+10 Creativity, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Pintados (Leyte) - Skip parade', 'outcome' => 'You skip the Pintados parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Sandugo (Bohol)
            ['event_choice' => 'Sandugo (Bohol) - Reenact blood compact', 'outcome' => 'You join the Sandugo blood compact reenactment', 'stat_effects' => '+10 Morality, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Sandugo (Bohol) - Skip reenactment', 'outcome' => 'You skip the Sandugo reenactment', 'stat_effects' => '-5 Morality, -5 Happiness', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // Kaamulan (Bukidnon)
            ['event_choice' => 'Kaamulan (Bukidnon) - Tribal unity celebration', 'outcome' => 'You join the Kaamulan tribal unity celebration', 'stat_effects' => '+15 Morality, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Kaamulan (Bukidnon) - Skip tribal rites', 'outcome' => 'You skip the Kaamulan tribal rites', 'stat_effects' => '+5 Isolation, -5 Morality', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Giant Lantern (Pampanga)
            ['event_choice' => 'Giant Lantern (Pampanga) - Lantern contest', 'outcome' => 'You join the Giant Lantern contest', 'stat_effects' => '+20 Creativity, +15 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Giant Lantern (Pampanga) - No lantern entry', 'outcome' => 'You do not enter a lantern in the contest', 'stat_effects' => '-5 Creativity, -5 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Hermosa (Zamboanga)
            ['event_choice' => 'Hermosa (Zamboanga) - Street parade', 'outcome' => 'You join the Hermosa street parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Hermosa (Zamboanga) - Skip parade', 'outcome' => 'You skip the Hermosa parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Malasimbo (Puerto Galera)
            ['event_choice' => 'Malasimbo (Puerto Galera) - Attend concert', 'outcome' => 'You attend the Malasimbo concert', 'stat_effects' => '+20 Happiness, +10 Creativity', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Malasimbo (Puerto Galera) - Skip concert', 'outcome' => 'You skip the Malasimbo concert', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Tuna Festival (GenSan)
            ['event_choice' => 'Tuna Festival (GenSan) - Join fish parade', 'outcome' => 'You join the Tuna Festival fish parade', 'stat_effects' => '+10 Wealth, +10 Reputation', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Tuna Festival (GenSan) - Skip festival', 'outcome' => 'You skip the Tuna Festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Lanzones (Camiguin)
            ['event_choice' => 'Lanzones (Camiguin) - Join fruit harvest', 'outcome' => 'You join the Lanzones fruit harvest', 'stat_effects' => '+10 Wealth, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Lanzones (Camiguin) - Skip harvest', 'outcome' => 'You skip the Lanzones harvest', 'stat_effects' => '-5 Happiness, -5 Morality', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Mango Festival (Zambales)
            ['event_choice' => 'Mango Festival (Zambales) - Celebrate mango season', 'outcome' => 'You celebrate the Mango Festival season', 'stat_effects' => '+10 Wealth, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Mango Festival (Zambales) - Skip festival', 'outcome' => 'You skip the Mango Festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Coconut Festival (Quezon)
            ['event_choice' => 'Coconut Festival (Quezon) - Join parade', 'outcome' => 'You join the Coconut Festival parade', 'stat_effects' => '+10 Wealth, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Coconut Festival (Quezon) - Skip parade', 'outcome' => 'You skip the Coconut Festival parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Bangus Festival (Dagupan)
            ['event_choice' => 'Bangus Festival (Dagupan) - Celebrate milkfish harvest', 'outcome' => 'You celebrate the Bangus Festival milkfish harvest', 'stat_effects' => '+15 Wealth, +10 Happiness', 'weight' => 3, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Bangus Festival (Dagupan) - Skip festival', 'outcome' => 'You skip the Bangus Festival', 'stat_effects' => '-5 Happiness, -5 Morality', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Tabak Festival (Toledo)
            ['event_choice' => 'Tabak Festival (Toledo) - Join martial parade', 'outcome' => 'You join the Tabak Festival martial parade', 'stat_effects' => '+10 Strength, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Tabak Festival (Toledo) - Skip parade', 'outcome' => 'You skip the Tabak Festival parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Rodeo Masbateño (Masbate)
            ['event_choice' => 'Rodeo Masbateño (Masbate) - Join rodeo', 'outcome' => 'You join the Rodeo Masbateño competition', 'stat_effects' => '+15 Strength, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Rodeo Masbateño (Masbate) - Skip rodeo', 'outcome' => 'You skip the Rodeo Masbateño', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Surf Festival (La Union)
            ['event_choice' => 'Surf Festival (La Union) - Win surf competition', 'outcome' => 'You win the Surf Festival competition', 'stat_effects' => '+10 Strength, +10 Happiness', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Surf Festival (La Union) - Skip surf event', 'outcome' => 'You skip the Surf Festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Pagoda Festival (Bulacan)
            ['event_choice' => 'Pagoda Festival (Bulacan) - Join river procession', 'outcome' => 'You join the Pagoda Festival river procession', 'stat_effects' => '+10 Morality, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Pagoda Festival (Bulacan) - Skip procession', 'outcome' => 'You skip the Pagoda Festival procession', 'stat_effects' => '-5 Morality, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // Buyogan Festival (Leyte)
            ['event_choice' => 'Buyogan Festival (Leyte) - Bee dance competition', 'outcome' => 'You join the Buyogan Festival bee dance competition', 'stat_effects' => '+10 Creativity, +10 Happiness', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Buyogan Festival (Leyte) - Skip festival', 'outcome' => 'You skip the Buyogan Festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Sangyaw Festival (Tacloban)
            ['event_choice' => 'Sangyaw Festival (Tacloban) - Join parade', 'outcome' => 'You join the Sangyaw Festival parade', 'stat_effects' => '+15 Happiness, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Sangyaw Festival (Tacloban) - Skip parade', 'outcome' => 'You skip the Sangyaw Festival parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Halad Festival (Cebu)
            ['event_choice' => 'Halad Festival (Cebu) - Join thanksgiving', 'outcome' => 'You join the Halad Festival thanksgiving', 'stat_effects' => '+10 Morality, +10 Happiness', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            ['event_choice' => 'Halad Festival (Cebu) - Skip thanksgiving', 'outcome' => 'You skip the Halad Festival thanksgiving', 'stat_effects' => '-5 Morality, -5 Happiness', 'weight' => 2, 'image' => '/css/images/event-ceremony.jpg'],
            // T'nalak Festival (South Cotabato)
            ['event_choice' => 'T\'nalak Festival (South Cotabato) - Celebrate woven cloth', 'outcome' => 'You celebrate the T\'nalak Festival woven cloth tradition', 'stat_effects' => '+15 Creativity, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'T\'nalak Festival (South Cotabato) - Skip festival', 'outcome' => 'You skip the T\'nalak Festival', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Balangay Festival (Butuan)
            ['event_choice' => 'Balangay Festival (Butuan) - Join boat parade', 'outcome' => 'You join the Balangay Festival boat parade', 'stat_effects' => '+10 Creativity, +10 Reputation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Balangay Festival (Butuan) - Skip parade', 'outcome' => 'You skip the Balangay Festival parade', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            // Hinulugang Taktak Festival (Antipolo)
            ['event_choice' => 'Hinulugang Taktak Festival (Antipolo) - Join nature event', 'outcome' => 'You join the Hinulugang Taktak nature event', 'stat_effects' => '+10 Health, +10 Happiness', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
            ['event_choice' => 'Hinulugang Taktak Festival (Antipolo) - Skip event', 'outcome' => 'You skip the Hinulugang Taktak event', 'stat_effects' => '-5 Happiness, +5 Isolation', 'weight' => 2, 'image' => '/css/images/event-festival.jpg'],
        ];

        foreach ($events as $event) {
            CulturalEvent::create($event);
        }
    }
}

