<?php

namespace App\Console\Commands;

use App\Models\DailyEvent;
use Illuminate\Console\Command;

class UpdateDailyEventImages extends Command
{
    protected $signature = 'daily-events:update-images';
    protected $description = 'Update daily events with image URLs';

    public function handle()
    {
        // Map event keywords to image files (order matters - more specific first)
        $imageMap = [
            'part-time job' => 'part-time-job.png',
            'morning walk' => 'morning-walk.png',
            'date-night' => 'date-night.png',
            'date night' => 'date-night.png',
            'grandchildren' => 'grandchildren.png',
            'doctor checkup' => 'doctor-checkup.png',
            'bedtime' => 'bedtime.png',
            'homework' => 'study.png',
            'playtime' => 'playtime.png',
            'family time' => 'family-time.png',
            'gym' => 'gym.png',
            'workout' => 'gym.png',
            'cooking' => 'cooking.png',
            'medication' => 'medication.png',
            'memories' => 'memories.png',
            'television' => 'tv.png',
            'cartoon' => 'tv.png',
            'commute' => 'commute.png',
            'traffic' => 'commute.png',
            'work' => 'work.png',
            'meeting' => 'work.png',
            'chores' => 'chores.png',
            'chore' => 'chores.png',
            'garden' => 'garden.png',
            'plant' => 'garden.png',
            'exercise' => 'gym.png',
            'sports' => 'sports.png',
            'sport' => 'sports.png',
            'team' => 'sports.png',
            'study' => 'study.png',
            'exam' => 'study.png',
            'reading' => 'reading.png',
            'read' => 'reading.png',
            'story' => 'reading.png',
            'friends' => 'friends.png',
            'friend' => 'friends.png',
            'hang out' => 'friends.png',
            'job' => 'work.png',
            'date' => 'date-night.png',
            'curfew' => 'curfew.png',
            'family' => 'family-time.png',
            'cook' => 'cooking.png',
            'bills' => 'bills.png',
            'bill' => 'bills.png',
            'walk' => 'morning-walk.png',
            'grandkids' => 'grandchildren.png',
            'doctor' => 'doctor-checkup.png',
            'checkup' => 'doctor-checkup.png',
            'health' => 'doctor-checkup.png',
            'nap' => 'nap.png',
            'rest' => 'nap.png',
            'memory' => 'memories.png',
            'reminisce' => 'memories.png',
            'medicine' => 'medication.png',
            'drugs' => 'medication.png',
            'tv' => 'tv.png',
            'play' => 'playtime.png',
            'meal' => 'meal.png',
            'dinner' => 'meal.png',
            'eat' => 'meal.png',
            'breakfast' => 'meal.png',
            'lunch' => 'meal.png',
            'sleep' => 'bedtime.png',
            'bed' => 'bedtime.png',
            'school' => 'school-start.png',
        ];

        $events = DailyEvent::all();
        $updated = 0;

        foreach ($events as $event) {
            $eventChoice = strtolower($event->event_choice);
            
            // Find matching image
            $imageFile = '/css/images/dailyevents/tv.png'; // default
            
            foreach ($imageMap as $key => $image) {
                if (strpos($eventChoice, $key) !== false) {
                    $imageFile = '/css/images/dailyevents/' . $image;
                    break;
                }
            }

            $event->image = $imageFile;
            $event->save();
            $updated++;
        }

        $this->info("Updated {$updated} daily events with images.");
        return 0;
    }
}
