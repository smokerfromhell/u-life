<?php

namespace App\Console\Commands;

use App\Models\AgeSpecificEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateAgeSpecificEventImages extends Command
{
    protected $signature = 'events:update-age-specific-images';
    protected $description = 'Update image paths for age-specific events based on event_choice';

    public function handle(): int
    {
        $events = AgeSpecificEvent::all();
        $updated = 0;

        foreach ($events as $event) {
            $imagePath = $this->getImagePathForEvent($event->event_choice);
            
            if ($imagePath) {
                $event->image = $imagePath;
                $event->save();
                $updated++;
                $this->line("Updated: {$event->event_choice} -> {$imagePath}");
            } else {
                $this->warn("No image found for: {$event->event_choice}");
            }
        }

        $this->info("Updated {$updated} events with image paths.");
        return Command::SUCCESS;
    }

    private function getImagePathForEvent(string $eventChoice): ?string
    {
        // Map event_choice to image filename
        // Convert to lowercase, replace spaces with hyphens, remove special chars
        
        $mapping = [
            // Child events
            'School Start - Excited to learn' => 'school-start.png',
            'School Start - Struggles with lessons' => 'school-start.png',
            'First School Exam - High score' => 'first-school-exam.png',
            'First School Exam - Low score' => 'first-school-exam.png',
            'First School Exam - Cheating caught' => 'first-school-exam.png',
            'Childhood Learning - Reads early' => 'childhood-learning.png',
            'Childhood Learning - Struggles to read' => 'childhood-learning.png',
            'Childhood School Trip - Educational success' => 'childhood-school-trip.png',
            
            'Family Bonding - Supportive parents' => 'family-bonding.png',
            'Family Bonding - Neglectful parents' => 'family-bonding.png',
            'Sibling Bond - Close sibling' => 'sibling-bond.png',
            'Sibling Bond - Rivalry' => 'sibling-bond.png',
            'Childhood Bonding - Grandparent love' => 'childhood-bonding.png',
            'Childhood Bonding - Grandparent loss' => 'childhood-bonding.png',
            
            'Childhood Friend - Best friend found' => 'childhood-friend.png',
            'Childhood Friend - Lonely childhood' => 'childhood-friend.png',
            'Playtime - Outdoor games' => 'playtime.png',
            'Playtime - Stays indoors' => 'playtime.png',
            'Bullying - Stands up' => 'bullying.png',
            'Bullying - Silent' => 'bullying.png',
            'Bullying - Severe trauma' => 'bullying.png',
            'Childhood Playdate - Fun with friends' => 'childhood-playdate.png',
            
            'Birth - Healthy baby' => 'birth.png',
            'Birth - Complicated birth' => 'birth.png',
            'First Steps - Walk early' => 'first-steps.png',
            'First Steps - Walk late' => 'first-steps.png',
            'Childhood Illness - Quick recovery' => 'childhood-illness.png',
            'Childhood Illness - Long sickness' => 'childhood-illness.png',
            'Childhood Illness - Chickenpox' => 'childhood-illness.png',
            'Nutrition - Balanced diet' => 'nutrition.png',
            'Nutrition - Malnutrition' => 'nutrition.png',
            'Childhood Accident - Minor fall' => 'childhood-accident.png',
            'Childhood Accident - Major injury' => 'childhood-accident.png',
            'Childhood Accident - Fatal accident' => 'childhood-accident.png',
            
            'First Words - Speaks clearly' => 'first-word.png',
            'First Words - Speech delay' => 'first-word.png',
            'Childhood Talent - Shows skill' => 'childhood-talent.png',
            'Childhood Talent - Talent ignored' => 'childhood-talent.png',
            'Childhood Hobby - Learns instrument' => 'childhood-hobby.png',
            'Childhood Hobby - Quits hobby' => 'childhood-hobby.png',
            'Childhood Dream - Inspired' => 'childhood-dream.png',
            'Childhood Chores - Helps family' => 'childhood-chores.png',
            'Childhood Chores - Refuses chores' => 'childhood-chores.png',
            'Childhood Sports - Wins race' => 'childhood-sports.png',
            'Childhood Sports - Loses race' => 'childhood-sports.png',
            'Childhood Fear - Overcomes fear' => 'childhood-fear.png',
            'Childhood Fear - Fear worsens' => 'childhood-fear.png',
            
            'First Pet - Pet adopted' => 'first-pet.png',
            'First Pet - Pet lost' => 'first-pet.png',
            'Birthday Party - Celebrated' => 'birthday-party.png',
            'Birthday Party - Forgotten' => 'birthday-party.png',
            'Childhood Travel - Family trip' => 'childhood-travel.png',
            'Childhood Trip - Zoo visit' => 'childhood-trip.png',
            'Childhood Trip - Amusement park' => 'childhood-trip.png',
            'Childhood Celebration - Christmas joy' => 'childhood-celebration.png',
            'Childhood Celebration - New Year fireworks' => 'childhood-celebration.png',
            'Childhood Religion - Joins devotion' => 'childhood-religion.png',
            'Childhood Curiosity - Explores safely' => 'childhood-curiosity.png',
            'Infant Mortality' => 'infant-mortality.png',
            'Childhood Illness - Fatal illness' => 'childhood-illness.png',
            
            // Teen events
            'High School Start - Good grades' => 'high-school-start.png',
            'High School Start - Poor grades' => 'high-school-start.png',
            'Teenage School Exam - High score' => 'teenage-school-exam.png',
            'Teenage School Exam - Low score' => 'teenage-school-exam.png',
            'Teenage Learning - Excels in science' => 'teenage-learning.png',
            'Teenage Learning - Struggles in math' => 'teenage-learning.png',
            'Graduation - With honors' => 'graduation.png',
            'Graduation - Barely passes' => 'graduation.png',
            
            'First Crush - Mutual feelings' => 'first-crush.png',
            'First Crush - Rejected' => 'first-crush.png',
            'First Love - Relationship begins' => 'first-love.png',
            'First Love - Heartbreak' => 'first-love.png',
            'Teenage Friendship - Loyal friend' => 'teenage-friendship.png',
            'Teenage Friendship - Betrayal' => 'teenage-friendship.png',
            'Teenage Relationship - Healthy romance' => 'teenage-relationship.png',
            'Teenage Relationship - Breakup' => 'teenage-relationship.png',
            
            'Teenage Bonding - Family support' => 'teenage-bonding.png',
            'Teenage Bonding - Family conflict' => 'teenage-bonding.png',
            'Teenage Bonding - Grandparent love' => 'teenage-bonding.png',
            'Teenage Bonding - Grandparent loss' => 'teenage-bonding.png',
            
            'First Job (Part-time) - Hired' => 'first-job-part-time.png',
            'First Job (Part-time) - Fired' => 'first-job-part-time.png',
            'Peer Pressure - Resists' => 'peer-pressure.png',
            'Peer Pressure - Gives in' => 'peer-pressure.png',
            'Rebellion - Sneaks out' => 'rebellion.png',
            'Rebellion - Caught' => 'rebellion.png',
            
            'Sports Team - Makes varsity' => 'sports-team.png',
            'Sports Team - Cut from team' => 'sports-team.png',
            'Teenage Hobby - Learns guitar' => 'teenage-hobby.png',
            'Teenage Hobby - Quits hobby' => 'teenage-hobby.png',
            'Teenage Talent - Shows skill' => 'teenage-talent.png',
            'Teenage Talent - Talent mocked' => 'teenage-talent.png',
            'Teenage Contest - Wins' => 'teenage-contest.png',
            'Teenage Contest - Loses' => 'teenage-contest.png',
            'Teenage Sports - Wins championship' => 'teenage-sports.png',
            'Teenage Fear - Overcomes fear' => 'teenage-fear.png',
            'Teenage Fear - Fear worsens' => 'teenage-fear.png',
            'Teenage Exploration - Safe adventure' => 'teenage-exploration.png',
            'Teenage Chores - Helps family' => 'teenage-chores.png',
            
            'Teenage Illness - Flu recovery' => 'teenage-illness.png',
            'Teenage Illness - Dengue recovery' => 'teenage-illness.png',
            'Teenage Illness - Dengue fatal' => 'teenage-illness.png',
            'Teenage Accident - Minor injury' => 'teenage-accident.png',
            'Teenage Accident - Major injury' => 'teenage-accident.png',
            'Teenage Accident - Bike crash' => 'teenage-accident.png',
            'Teenage Accident - Fatal accident' => 'teenage-accident.png',
            
            'Teenage Party - Fun night' => 'teenage-party.png',
            'Teenage Party - Embarrassment' => 'teenage-party.png',
            'Teenage Party - Fatal overdose' => 'teenage-party.png',
            'Teenage Festival - Joins parade' => 'teenage-festival.png',
            'Teenage Festival - Joins cultural parade' => 'teenage-festival.png',
            'Teenage Celebration - Christmas joy' => 'teenage-celebration.png',
            'Teenage Celebration - New Year fireworks' => 'teenage-celebration.png',
            
            // Adult events
            'College - Graduates' => 'college.png',
            'College - Drops out' => 'college.png',
            
            'Career Start - Hired' => 'career-start.png',
            'Career Start - Rejected' => 'career-start.png',
            'Career Start - Fatal workplace accident' => 'career-start.png',
            'Career Promotion - Rise in rank' => 'career-promotion.png',
            'Career Promotion - Passed over' => 'career-promotion.png',
            'Career Change - Successful transition' => 'career-change.png',
            'Career Change - Failed transition' => 'career-change.png',
            'Business Venture - Startup success' => 'business-venture.png',
            'Business Venture - Startup failure' => 'business-venture.png',
            'Migration - Move abroad' => 'migration.png',
            'Migration - Denied visa' => 'migration.png',
            'Midlife Crisis - Reinvent self' => 'midlife-crisis.png',
            'Midlife Crisis - Burnout deepens' => 'midlife-crisis.png',
            
            'Marriage - Wedding' => 'marriage.png',
            'Marriage - Cancelled engagement' => 'marriage.png',
            'Parenthood - Child born' => 'parenthood.png',
            'Parenthood - Child illness' => 'parenthood.png',
            'Parenthood - Child fatality' => 'parenthood.png',
            'Divorce - Separation' => 'divorce.png',
            'Divorce - Amicable split' => 'divorce.png',
            'Adult Bonding - Family support' => 'adult-bonding.png',
            'Adult Bonding - Family conflict' => 'adult-bonding.png',
            
            'Adult Friendship - Loyal friend' => 'adult-friendship.png',
            'Adult Friendship - Betrayal' => 'adult-friendship.png',
            'Adult Relationship - Healthy romance' => 'adult-relationship.png',
            'Adult Relationship - Breakup' => 'adult-relationship.png',
            
            'Health Crisis - Major illness' => 'health-crisis.png',
            'Health Crisis - Recovery' => 'health-crisis.png',
            'Health Crisis - Fatal illness' => 'health-crisis.png',
            'Adult Illness - Flu recovery' => 'adult-illness.png',
            'Adult Illness - Severe dengue recovery' => 'adult-illness.png',
            'Adult Accident - Bike crash' => 'adult-accident.png',
            'Adult Accident - Car crash survival' => 'adult-accident.png',
            'Adult Accident - Car crash fatal' => 'adult-accident.png',
            
            'Adult Hobby - Learns painting' => 'adult-hobby.png',
            'Adult Hobby - Gives up hobby' => 'adult-hobby.png',
            'Adult Talent - Shows skill' => 'adult-talent.png',
            'Adult Talent - Talent mocked' => 'adult-talent.png',
            'Adult Learning - Excels in training' => 'adult-learning.png',
            'Adult Learning - Struggles in training' => 'adult-learning.png',
            'Adult Sports - Wins championship' => 'adult-sports.png',
            'Adult Contest - Wins' => 'adult-contest.png',
            
            'Inheritance - Receive wealth' => 'inheritance.png',
            'Inheritance - Family dispute' => 'inheritance.png',
            'Fame - Become celebrity' => 'fame.png',
            'Fame - Scandal' => 'fame.png',
            'Political Change - Join movement' => 'political-change.png',
            'Natural Disaster - Survive typhoon' => 'natural-disaster.png',
            'Natural Disaster - Lose property' => 'natural-disaster.png',
            'Natural Disaster - Fatal disaster' => 'natural-disaster.png',
            'Adult Celebration - Christmas joy' => 'adult-celebration.png',
            'Adult Celebration - New Year fireworks' => 'adult-celebration.png',
            'Adult Festival - Joins cultural parade' => 'adult-festival.png',
            
            // Old events
            'Retirement - Peaceful retirement' => 'retirement.png',
            'Retirement - Forced retirement' => 'retirement.png',
            
            'Grandparenthood - Bond with grandchild' => 'grandparenthood.png',
            'Grandparenthood - Estranged family' => 'grandparenthood.png',
            'Grandparenthood - Grandchild fatality' => 'grandparenthood.png',
            'Elder Bonding - Family reunion' => 'elder-bonding.png',
            'Elder Bonding - Family conflict' => 'elder-bonding.png',
            
            'Health Decline - Arthritis' => 'health-crisis.png',
            'Health Decline - Dementia onset' => 'health-crisis.png',
            'Health Decline - Fatal illness' => 'health-crisis.png',
            'Elder Illness - Flu recovery' => 'adult-illness.png',
            'Elder Illness - Pneumonia recovery' => 'adult-illness.png',
            'Elder Illness - Pneumonia fatal' => 'adult-illness.png',
            'Elder Accident - Minor fall' => 'adult-accident.png',
            'Elder Accident - Hip fracture' => 'adult-accident.png',
            'Elder Accident - Fatal fall' => 'adult-accident.png',
            
            'Legacy Project - Writes memoir' => 'adult-hobby.png',
            'Legacy Project - Memoir ignored' => 'adult-hobby.png',
            'Legacy Project - Memoir celebrated' => 'adult-hobby.png',
            'Wisdom Sharing - Mentor youth' => 'elder-bonding.png',
            'Wisdom Sharing - Advice ignored' => 'elder-bonding.png',
            'Community Role - Joins senior group' => 'elder-bonding.png',
            'Community Role - Declines participation' => 'elder-bonding.png',
            
            'Elder Friendship - Loyal companion' => 'elder-bonding.png',
            'Elder Friendship - Betrayal' => 'elder-bonding.png',
            
            'Elder Celebration - Christmas joy' => 'adult-celebration.png',
            'Elder Celebration - Missed Christmas' => 'adult-celebration.png',
            'Elder Travel - Pilgrimage' => 'childhood-travel.png',
            'Elder Hobby - Gardening' => 'adult-hobby.png',
            'Elder Hobby - Abandons hobby' => 'adult-hobby.png',
            'Elder Reflection - Peaceful reflection' => 'adult-learning.png',
            'Elder Reflection - Regret' => 'adult-learning.png',
            'Elder Reflection - Fatal heart attack' => 'adult-accident.png',
            'Elder Festival - Joins parade' => 'adult-festival.png',
            'Elder Contest - Wins' => 'adult-contest.png',
            
            'End of Life - Peaceful passing' => 'retirement.png',
            'End of Life - Sudden death' => 'retirement.png',
            'End of Life - Violent death' => 'retirement.png',
        ];

        // Check if we have a direct mapping
        if (isset($mapping[$eventChoice])) {
            return '/css/images/age-group/' . $mapping[$eventChoice];
        }

        // Try to generate from event_choice
        // Extract the main part before " - "
        $mainPart = explode(' - ', $eventChoice)[0];
        $filename = Str::slug($mainPart, '-') . '.png';
        
        return '/css/images/age-group/' . $filename;
    }
}
