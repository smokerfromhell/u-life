<?php

namespace App\Console\Commands;

use App\Models\Character;
use Illuminate\Console\Command;

class UpdateCharacterProfilePictures extends Command
{
    protected $signature = 'characters:update-profile-pictures';
    protected $description = 'Update character profile pictures based on age group and gender';

    public function handle()
    {
        $characters = Character::all();
        $updated = 0;

        // Map gender to image filename format
        $genderMap = [
            'male' => 'male',
            'female' => 'female',
            'non-binary' => 'male',
            'transgender' => 'male',
        ];

        // Map age_group to image filename format
        $ageGroupMap = [
            'child' => 'child',
            'teenager' => 'teenage',
            'adult' => 'adult',
            'old' => 'old',
        ];

        foreach ($characters as $character) {
            $genderSuffix = $genderMap[$character->gender] ?? 'male';
            $ageGroupPrefix = $ageGroupMap[$character->age_group] ?? 'child';
            
            $newImage = "/css/images/profilepicnormal/{$ageGroupPrefix}-{$genderSuffix}.png";
            
            // Only update if character doesn't have a custom image or has the default one
            $currentImage = $character->image ?? '';
            if (empty($currentImage) || $currentImage === '/css/images/player.jpg') {
                $character->image = $newImage;
                $character->save();
                $updated++;
            }
        }

        $this->info("Updated {$updated} characters with profile pictures.");
        return 0;
    }
}
