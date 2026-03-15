<?php

namespace App\Console\Commands;

use App\Models\CulturalEvent;
use Illuminate\Console\Command;

class UpdateCulturalEventImages extends Command
{
    protected $signature = 'cultural-events:update-images';
    protected $description = 'Update cultural events with image URLs';

    public function handle()
    {
        $events = CulturalEvent::all();
        $updated = 0;

        // Map festival names to image files
        $imageMap = [
            'sinulog' => 'sinulog-festival.png',
            'ati-atihan' => 'ati-atihan.png',
            'dinagyang' => 'dinagyang-festival.png',
            'panagbenga' => 'panagbenga-festival.png',
            'kadayawan' => 'kadayawan-festival.png',
            'pahiyas' => 'pahiyas-festival.png',
            'moriones' => 'moriones-festival.png',
            'masskara' => 'masskara-festival.png',
            'flores de mayo' => 'flores-de-mayo.png',
            'santacruzan' => 'santacruzan-festival.png',
            'higantes' => 'higantes-festival.png',
            'pintados' => 'pintados-festival.png',
            'sandugo' => 'sandugo-festival.png',
            'kaamulan' => 'kaamulan-festival.png',
            'giant lantern' => 'giant-lantern-festival.png',
            'hermosa' => 'hermoza-fesitval.png',
            'malasimbo' => 'malasimbo-festival.png',
            'tuna' => 'tuna-festival.png',
            'lanzones' => 'lanzones-festival.png',
            'mango' => 'mango-festival.png',
            'coconut' => 'coconut-festival.png',
            'bangus' => 'bangus-festival.png',
            'tabak' => 'tabak-festival.png',
            'rodeo' => 'rodeo-masbate.png',
            'surf' => 'surf-festival.png',
            'pagoda' => 'pagoda-festival.png',
            'buyogan' => 'buyogan-festival.png',
            'sangyaw' => 'sangyaw-festival.png',
            'halad' => 'halad-festival.png',
            't\'nalak' => 'tnalak-festival.png',
            'balangay' => 'balangay-festival.png',
            'hinulugang taktak' => 'hinulugang-taktak-festival.png',
        ];

        foreach ($events as $event) {
            $eventChoice = strtolower($event->event_choice);
            
            // Find matching festival image
            $imageFile = '/css/images/event-festival.jpg'; // default
            
            foreach ($imageMap as $key => $image) {
                if (strpos($eventChoice, $key) !== false) {
                    $imageFile = '/css/images/culturalevents/' . $image;
                    break;
                }
            }

            // Update even if image exists to ensure we have specific images
            $event->image = $imageFile;
            $event->save();
            $updated++;
        }

        $this->info("Updated {$updated} cultural events with images.");
        return 0;
    }
}
