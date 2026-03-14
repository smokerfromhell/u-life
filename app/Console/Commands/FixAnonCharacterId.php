<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Support\Privacy;
use Illuminate\Console\Command;

class FixAnonCharacterId extends Command
{
    protected $signature = 'character:fix-anon-id';
    protected $description = 'Populate anon_character_id for existing characters';

    public function handle()
    {
        $characters = Character::whereNull('anon_character_id')->get();
        
        foreach ($characters as $character) {
            $character->update([
                'anon_character_id' => Privacy::anonymize('character', $character->id)
            ]);
        }

        $this->info("Updated {$characters->count()} characters with anon_character_id.");
        
        return 0;
    }
}