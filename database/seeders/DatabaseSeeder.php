<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters! Run seeders in the correct sequence.
     */
    public function run(): void
    {
        // 1. First, set up roles and permissions
        $this->call(RoleSeeder::class);
        
        // 2. Seed skills and talents (prerequisites for characters)
        $this->call(SkillSeeder::class);
        $this->call(TalentSeeder::class);
        
        // 3. Seed event data
        $this->call(AgeSpecificEventSeeder::class);
        $this->call(DailyEventSeeder::class);
        $this->call(CulturalEventSeeder::class);
        
        // 4. Seed profession-related data
        $this->call(ProfessionTriggerSeeder::class);
        $this->call(ProfessionPathEventSeeder::class);
        
        // 5. Seed stat trigger conditions
        $this->call(StatTriggerConditionSeeder::class);
        
        // 6. Add choices to events (must run after events are created)
        $this->call(EventChoicesSeeder::class);
    }
}

