<?php

namespace Database\Seeders;

use App\Models\ProfessionPathEvent;
use Illuminate\Database\Seeder;

class ProfessionPathEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Add your profession path events from Excel here.
     * Format: ['profession' => 'Doctor', 'event_choice' => 'name', 'outcome' => 'description', 'stat_effects' => '+15 Health, +10 Morality', 'weight' => 4]
     */
    public function run(): void
    {
        $events = [
      // Doctor Profession Events
[
    'profession' => 'Doctor',
    'event_choice' => 'Emergency Surgery',
    'outcome' => 'You successfully save a patient\'s life',
    'stat_effects' => '+15 Morality, +10 Reputation, +5 Happiness',
    'weight' => 3
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Medical Error',
    'outcome' => 'A mistake causes complications',
    'stat_effects' => '-10 Reputation, -5 Happiness, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Award Ceremony',
    'outcome' => 'You are honored for your skill',
    'stat_effects' => '+20 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Research Grant',
    'outcome' => 'You receive funding for medical research',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Breakthrough Discovery',
    'outcome' => 'You discover a new treatment',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 1
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Lawsuit',
    'outcome' => 'You face legal action for malpractice',
    'stat_effects' => '-20 Wealth, -15 Reputation',
    'weight' => 1
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Stress Leave',
    'outcome' => 'You take time off to recover',
    'stat_effects' => '-10 Reputation, +10 Burnout',
    'weight' => 2
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Return to Practice',
    'outcome' => 'You return to work after recovery',
    'stat_effects' => '+5 Discipline, +5 Morality',
    'weight' => 3
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Hospital Expansion',
    'outcome' => 'You help expand hospital facilities',
    'stat_effects' => '+15 Reputation, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Doctor',
    'event_choice' => 'Medical Conference',
    'outcome' => 'You present at an international conference',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 2
],
// Teacher Profession Events
[
    'profession' => 'Teacher',
    'event_choice' => 'Inspiring Lecture',
    'outcome' => 'Your students are motivated by your lesson',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 3
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Classroom Chaos',
    'outcome' => 'Students misbehave and disrupt class',
    'stat_effects' => '-5 Discipline, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Student Success',
    'outcome' => 'A student wins a competition thanks to your guidance',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Parent Complaint',
    'outcome' => 'A parent criticizes your teaching methods',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Curriculum Innovation',
    'outcome' => 'You introduce a new teaching style',
    'stat_effects' => '+10 Creativity, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'School Award',
    'outcome' => 'You receive recognition from the school board',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Exam Stress',
    'outcome' => 'Students struggle during exams',
    'stat_effects' => '-5 Happiness, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a new teacher',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Community Outreach',
    'outcome' => 'You organize a literacy program',
    'stat_effects' => '+10 Reputation, +10 Morality',
    'weight' => 2
],
[
    'profession' => 'Teacher',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from teaching',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
// Scientist Profession Events
[
    'profession' => 'Scientist',
    'event_choice' => 'Breakthrough Discovery',
    'outcome' => 'You publish groundbreaking research',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Failed Experiment',
    'outcome' => 'Your experiment fails unexpectedly',
    'stat_effects' => '-5 Reputation, +5 Burnout',
    'weight' => 3
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Research Grant',
    'outcome' => 'You secure funding for your project',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Conference Presentation',
    'outcome' => 'You present findings at an international conference',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Peer Recognition',
    'outcome' => 'Colleagues praise your work',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Plagiarism Accusation',
    'outcome' => 'You are accused of copying research',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Lab Accident',
    'outcome' => 'An accident occurs in your lab',
    'stat_effects' => '-10 Health, -5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Team Collaboration',
    'outcome' => 'You lead a successful research team',
    'stat_effects' => '+10 Discipline, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Publication Rejection',
    'outcome' => 'Your paper is rejected by a journal',
    'stat_effects' => '-5 Ego, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Scientist',
    'event_choice' => 'Innovation Award',
    'outcome' => 'You win an award for innovation',
    'stat_effects' => '+15 Reputation, +10 Ego, +10 Wealth',
    'weight' => 1
],
// Lawyer Profession Events
[
    'profession' => 'Lawyer',
    'event_choice' => 'Court Victory',
    'outcome' => 'You win a high-profile case',
    'stat_effects' => '+15 Reputation, +10 Wealth',
    'weight' => 3
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Court Loss',
    'outcome' => 'You lose an important case',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Client Settlement',
    'outcome' => 'You negotiate a favorable settlement',
    'stat_effects' => '+10 Reputation, +5 Wealth',
    'weight' => 3
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Ethics Complaint',
    'outcome' => 'You face an ethics investigation',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Pro Bono Case',
    'outcome' => 'You help a client for free',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Media Spotlight',
    'outcome' => 'Your case is covered by the media',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Legal Seminar',
    'outcome' => 'You present at a legal seminar',
    'stat_effects' => '+10 Reputation, +5 Ego',
    'weight' => 2
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Client Betrayal',
    'outcome' => 'A client turns against you',
    'stat_effects' => '-10 Reputation, -5 Wealth',
    'weight' => 1
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a junior lawyer',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Lawyer',
    'event_choice' => 'Overwork',
    'outcome' => 'You burn out from long hours',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Engineer Profession Events
[
    'profession' => 'Engineer',
    'event_choice' => 'Successful Project',
    'outcome' => 'You complete a major technical project',
    'stat_effects' => '+10 Reputation, +10 Wealth',
    'weight' => 3
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Design Flaw',
    'outcome' => 'Your design has a critical flaw',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Innovation Award',
    'outcome' => 'You win recognition for innovation',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 1
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Team Collaboration',
    'outcome' => 'You lead a successful engineering team',
    'stat_effects' => '+10 Discipline, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Project Delay',
    'outcome' => 'Your project is delayed',
    'stat_effects' => '-5 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Patent Filing',
    'outcome' => 'You file a patent for your design',
    'stat_effects' => '+10 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Client Praise',
    'outcome' => 'A client praises your work',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Budget Cuts',
    'outcome' => 'Your project funding is reduced',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Conference Talk',
    'outcome' => 'You present at a technical conference',
    'stat_effects' => '+10 Reputation, +5 Ego',
    'weight' => 2
],
[
    'profession' => 'Engineer',
    'event_choice' => 'Overwork',
    'outcome' => 'You burn out from long hours',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Nurse Profession Events
[
    'profession' => 'Nurse',
    'event_choice' => 'Patient Care',
    'outcome' => 'You provide excellent care to a patient',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Medical Error',
    'outcome' => 'You make a mistake during treatment',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Team Support',
    'outcome' => 'You assist doctors during a critical procedure',
    'stat_effects' => '+10 Discipline, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Patient Complaint',
    'outcome' => 'A patient complains about your care',
    'stat_effects' => '-5 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Hospital Recognition',
    'outcome' => 'You are recognized for outstanding service',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Night Shift',
    'outcome' => 'You work a difficult overnight shift',
    'stat_effects' => '-5 Happiness, +5 Burnout',
    'weight' => 3
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a new nurse',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Stress Leave',
    'outcome' => 'You take time off to recover',
    'stat_effects' => '-10 Reputation, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Community Outreach',
    'outcome' => 'You volunteer in a health program',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Nurse',
    'event_choice' => 'Overwork',
    'outcome' => 'You feel exhausted from long shifts',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Soldier Profession Events
[
    'profession' => 'Soldier',
    'event_choice' => 'Successful Mission',
    'outcome' => 'You complete a dangerous mission',
    'stat_effects' => '+10 Reputation, +10 Discipline',
    'weight' => 3
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Battle Injury',
    'outcome' => 'You are injured in combat',
    'stat_effects' => '-10 Health, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Promotion',
    'outcome' => 'You are promoted for bravery',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Mission Failure',
    'outcome' => 'Your mission fails',
    'stat_effects' => '-10 Reputation, -5 Discipline',
    'weight' => 2
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Training Drill',
    'outcome' => 'You complete a rigorous training exercise',
    'stat_effects' => '+10 Discipline, +5 Strength',
    'weight' => 3
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Court Martial',
    'outcome' => 'You face disciplinary action',
    'stat_effects' => '-15 Reputation, -10 Discipline',
    'weight' => 1
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Medal of Honor',
    'outcome' => 'You receive a prestigious military award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Comradeship',
    'outcome' => 'You build strong bonds with fellow soldiers',
    'stat_effects' => '+10 Happiness, +10 Morality',
    'weight' => 2
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Deployment Abroad',
    'outcome' => 'You are deployed to a foreign country',
    'stat_effects' => '+10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Soldier',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant missions',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
// Athlete Profession Events
[
    'profession' => 'Athlete',
    'event_choice' => 'Championship Victory',
    'outcome' => 'You win a major sports championship',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Training Injury',
    'outcome' => 'You suffer an injury during training',
    'stat_effects' => '-10 Health, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Endorsement Deal',
    'outcome' => 'You sign a lucrative sponsorship',
    'stat_effects' => '+20 Wealth, +10 Reputation',
    'weight' => 1
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Team Conflict',
    'outcome' => 'You argue with teammates',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Training Success',
    'outcome' => 'You improve your performance in practice',
    'stat_effects' => '+10 Discipline, +5 Strength',
    'weight' => 3
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Fan Recognition',
    'outcome' => 'Fans cheer for you at an event',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Media Criticism',
    'outcome' => 'You are criticized in the press',
    'stat_effects' => '-10 Reputation, +5 Ego',
    'weight' => 1
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Charity Event',
    'outcome' => 'You participate in a charity match',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Retirement Decision',
    'outcome' => 'You consider retiring from sports',
    'stat_effects' => '-5 Happiness, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Athlete',
    'event_choice' => 'Comeback',
    'outcome' => 'You return after a setback',
    'stat_effects' => '+10 Reputation, +10 Discipline',
    'weight' => 2
],


// Fisher Profession Events
[
    'profession' => 'Fisher',
    'event_choice' => 'Big Catch',
    'outcome' => 'You catch a large amount of fish',
    'stat_effects' => '+15 Wealth, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Storm at Sea',
    'outcome' => 'You face dangerous weather while fishing',
    'stat_effects' => '-10 Health, -5 Wealth',
    'weight' => 2
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Market Sale',
    'outcome' => 'You sell your catch at the market',
    'stat_effects' => '+10 Wealth, +5 Reputation',
    'weight' => 3
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Boat Damage',
    'outcome' => 'Your boat is damaged during a trip',
    'stat_effects' => '-15 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Community Feast',
    'outcome' => 'You share your catch with the community',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Empty Nets',
    'outcome' => 'You return with no catch',
    'stat_effects' => '-5 Wealth, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Fishing Competition',
    'outcome' => 'You win a local fishing contest',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Accident at Sea',
    'outcome' => 'You are injured while fishing',
    'stat_effects' => '-10 Health, +5 Burnout',
    'weight' => 1
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Lucky Catch',
    'outcome' => 'You catch a rare fish',
    'stat_effects' => '+20 Wealth, +10 Reputation',
    'weight' => 1
],
[
    'profession' => 'Fisher',
    'event_choice' => 'Night Fishing',
    'outcome' => 'You fish overnight and succeed',
    'stat_effects' => '+10 Wealth, -5 Happiness',
    'weight' => 2
],


// Farmer Profession Events
[
    'profession' => 'Farmer',
    'event_choice' => 'Bountiful Harvest',
    'outcome' => 'Your crops yield an abundant harvest',
    'stat_effects' => '+20 Wealth, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Crop Failure',
    'outcome' => 'Your crops fail due to drought',
    'stat_effects' => '-15 Wealth, -10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Livestock Growth',
    'outcome' => 'Your animals thrive and multiply',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Pest Infestation',
    'outcome' => 'Pests damage your crops',
    'stat_effects' => '-10 Wealth, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Community Market',
    'outcome' => 'You sell produce at the local market',
    'stat_effects' => '+10 Wealth, +5 Reputation',
    'weight' => 3
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Festival Contribution',
    'outcome' => 'You donate crops to a festival',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Equipment Breakdown',
    'outcome' => 'Your farming tools break down',
    'stat_effects' => '-10 Wealth, +5 Burnout',
    'weight' => 1
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Government Subsidy',
    'outcome' => 'You receive financial support',
    'stat_effects' => '+15 Wealth, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Farmer',
    'event_choice' => 'Flood Damage',
    'outcome' => 'Flooding destroys part of your farm',
    'stat_effects' => '-20 Wealth, -10 Happiness',
    'weight' => 1
],
[
    'profession' => 'Farmer',
    'event_choice' => 'New Techniques',
    'outcome' => 'You adopt modern farming methods',
    'stat_effects' => '+10 Discipline, +10 Wealth',
    'weight' => 2
],
// Writer Profession Events
[
    'profession' => 'Writer',
    'event_choice' => 'Published Book',
    'outcome' => 'Your book is published and gains attention',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Writer’s Block',
    'outcome' => 'You struggle to write anything new',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Positive Review',
    'outcome' => 'A critic praises your work',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Negative Review',
    'outcome' => 'A critic harshly criticizes your work',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Literary Award',
    'outcome' => 'You win a prestigious literary award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Writer',
    'event_choice' => 'Rejected Manuscript',
    'outcome' => 'Your manuscript is rejected by publishers',
    'stat_effects' => '-5 Ego, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Community Workshop',
    'outcome' => 'You host a writing workshop',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Ghostwriting',
    'outcome' => 'You write anonymously for someone else',
    'stat_effects' => '+10 Wealth, -5 Ego',
    'weight' => 2
],
[
    'profession' => 'Writer',
    'event_choice' => 'Fan Recognition',
    'outcome' => 'Readers express admiration for your work',
    'stat_effects' => '+10 Happiness, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Writer',
    'event_choice' => 'Isolation',
    'outcome' => 'You spend long hours alone writing',
    'stat_effects' => '-5 Happiness, +10 Discipline',
    'weight' => 2
],


// Designer Profession Events
[
    'profession' => 'Designer',
    'event_choice' => 'Successful Project',
    'outcome' => 'Your design project is praised by clients',
    'stat_effects' => '+15 Reputation, +10 Wealth',
    'weight' => 3
],
[
    'profession' => 'Designer',
    'event_choice' => 'Design Rejection',
    'outcome' => 'Your design is rejected by clients',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Designer',
    'event_choice' => 'Innovation Award',
    'outcome' => 'You win an award for creativity',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Designer',
    'event_choice' => 'Team Collaboration',
    'outcome' => 'You collaborate successfully with a creative team',
    'stat_effects' => '+10 Discipline, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Designer',
    'event_choice' => 'Client Praise',
    'outcome' => 'A client praises your design work',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Designer',
    'event_choice' => 'Budget Cuts',
    'outcome' => 'Your project budget is reduced',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Designer',
    'event_choice' => 'Conference Talk',
    'outcome' => 'You present at a design conference',
    'stat_effects' => '+10 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Designer',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a junior designer',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Designer',
    'event_choice' => 'Overwork',
    'outcome' => 'You feel exhausted from long hours',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Designer',
    'event_choice' => 'Community Project',
    'outcome' => 'You contribute to a local design initiative',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],


// Musician Profession Events
[
    'profession' => 'Musician',
    'event_choice' => 'Concert Performance',
    'outcome' => 'You perform at a successful concert',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Musician',
    'event_choice' => 'Album Release',
    'outcome' => 'Your album is released and gains fans',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Musician',
    'event_choice' => 'Creative Block',
    'outcome' => 'You struggle to compose new music',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 2
],
[
    'profession' => 'Musician',
    'event_choice' => 'Positive Review',
    'outcome' => 'Critics praise your performance',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Musician',
    'event_choice' => 'Negative Review',
    'outcome' => 'Critics criticize your work',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Musician',
    'event_choice' => 'Music Award',
    'outcome' => 'You win a prestigious music award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Musician',
    'event_choice' => 'Tour Exhaustion',
    'outcome' => 'You feel exhausted from touring',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Musician',
    'event_choice' => 'Collaboration',
    'outcome' => 'You collaborate with another artist',
    'stat_effects' => '+10 Reputation, +10 Creativity',
    'weight' => 2
],
[
    'profession' => 'Musician',
    'event_choice' => 'Fan Recognition',
    'outcome' => 'Fans celebrate your music',
    'stat_effects' => '+10 Happiness, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Musician',
    'event_choice' => 'Charity Concert',
    'outcome' => 'You perform for a charitable cause',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
// Chef Profession Events
[
    'profession' => 'Chef',
    'event_choice' => 'Signature Dish Success',
    'outcome' => 'Your new dish becomes a customer favorite',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Chef',
    'event_choice' => 'Kitchen Accident',
    'outcome' => 'You suffer a minor injury while cooking',
    'stat_effects' => '-10 Health, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'Food Critic Praise',
    'outcome' => 'A critic praises your restaurant',
    'stat_effects' => '+20 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'Food Critic Criticism',
    'outcome' => 'A critic harshly reviews your food',
    'stat_effects' => '-15 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'Cooking Competition Win',
    'outcome' => 'You win a prestigious cooking competition',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 1
],
[
    'profession' => 'Chef',
    'event_choice' => 'Spoiled Ingredients',
    'outcome' => 'Your ingredients spoil before use',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a junior chef',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'Overwork',
    'outcome' => 'You feel exhausted from long shifts',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Chef',
    'event_choice' => 'Community Feast',
    'outcome' => 'You cook for a local festival',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Chef',
    'event_choice' => 'New Recipe Failure',
    'outcome' => 'Your experimental dish fails',
    'stat_effects' => '-5 Reputation, +5 Burnout',
    'weight' => 2
],


// Actor Profession Events
[
    'profession' => 'Actor',
    'event_choice' => 'Blockbuster Role',
    'outcome' => 'You star in a blockbuster film',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Actor',
    'event_choice' => 'Stage Performance',
    'outcome' => 'You deliver a powerful stage performance',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Actor',
    'event_choice' => 'Negative Review',
    'outcome' => 'Critics criticize your acting',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Actor',
    'event_choice' => 'Award Win',
    'outcome' => 'You win a prestigious acting award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Actor',
    'event_choice' => 'Casting Rejection',
    'outcome' => 'You are rejected for a role',
    'stat_effects' => '-5 Ego, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Actor',
    'event_choice' => 'Fan Recognition',
    'outcome' => 'Fans celebrate your performance',
    'stat_effects' => '+10 Happiness, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Actor',
    'event_choice' => 'Media Scandal',
    'outcome' => 'You are involved in a scandal',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Actor',
    'event_choice' => 'Charity Performance',
    'outcome' => 'You perform for a charitable cause',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Actor',
    'event_choice' => 'Overwork',
    'outcome' => 'You feel exhausted from constant rehearsals',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
[
    'profession' => 'Actor',
    'event_choice' => 'International Festival',
    'outcome' => 'You perform at an international film festival',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],


// Journalist Profession Events
[
    'profession' => 'Journalist',
    'event_choice' => 'Breaking Story',
    'outcome' => 'You publish a major breaking news story',
    'stat_effects' => '+20 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Investigative Report',
    'outcome' => 'Your investigation uncovers corruption',
    'stat_effects' => '+15 Reputation, +10 Morality',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Article Rejection',
    'outcome' => 'Your editor rejects your article',
    'stat_effects' => '-5 Ego, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Award Win',
    'outcome' => 'You win a journalism award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Negative Feedback',
    'outcome' => 'Readers criticize your reporting',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Positive Feedback',
    'outcome' => 'Readers praise your reporting',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Deadline Pressure',
    'outcome' => 'You struggle to meet a deadline',
    'stat_effects' => '-5 Happiness, +10 Burnout',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Media Scandal',
    'outcome' => 'You are accused of biased reporting',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Journalist',
    'event_choice' => 'Community Coverage',
    'outcome' => 'You cover a local community event',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Journalist',
    'event_choice' => 'International Assignment',
    'outcome' => 'You report from abroad',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
// Business Owner Profession Events
[
    'profession' => 'Business Owner',
    'event_choice' => 'Grand Opening',
    'outcome' => 'Your business opens successfully with many customers',
    'stat_effects' => '+15 Reputation, +10 Wealth, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Slow Sales',
    'outcome' => 'Your business struggles with low sales',
    'stat_effects' => '-10 Wealth, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Customer Praise',
    'outcome' => 'Customers praise your service',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 3
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Customer Complaint',
    'outcome' => 'A customer complains about your service',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Expansion',
    'outcome' => 'You expand your business to a new location',
    'stat_effects' => '+20 Wealth, +15 Reputation',
    'weight' => 1
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Tax Audit',
    'outcome' => 'You face a government tax audit',
    'stat_effects' => '-15 Wealth, -10 Reputation',
    'weight' => 1
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Community Sponsorship',
    'outcome' => 'You sponsor a local event',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Employee Conflict',
    'outcome' => 'Employees argue and disrupt operations',
    'stat_effects' => '-5 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Business Award',
    'outcome' => 'You win a local business award',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 1
],
[
    'profession' => 'Business Owner',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from managing your business',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Entrepreneur Profession Events
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Startup Launch',
    'outcome' => 'You launch a new startup successfully',
    'stat_effects' => '+15 Reputation, +10 Wealth',
    'weight' => 3
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Startup Failure',
    'outcome' => 'Your startup fails to attract customers',
    'stat_effects' => '-15 Wealth, -10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Investor Pitch Success',
    'outcome' => 'Investors fund your idea',
    'stat_effects' => '+20 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Investor Pitch Failure',
    'outcome' => 'Investors reject your idea',
    'stat_effects' => '-5 Ego, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Innovation Award',
    'outcome' => 'You win recognition for innovation',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Market Competition',
    'outcome' => 'Competitors challenge your business',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Networking Event',
    'outcome' => 'You build valuable connections',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Product Launch Failure',
    'outcome' => 'Your product launch flops',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Product Launch Success',
    'outcome' => 'Your product launch is a hit',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Entrepreneur',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant hustling',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Investor Profession Events
[
    'profession' => 'Investor',
    'event_choice' => 'Stock Market Gain',
    'outcome' => 'Your investments rise in value',
    'stat_effects' => '+20 Wealth, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Investor',
    'event_choice' => 'Stock Market Loss',
    'outcome' => 'Your investments lose value',
    'stat_effects' => '-20 Wealth, -10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Startup Investment Success',
    'outcome' => 'Your startup investment pays off',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Startup Investment Failure',
    'outcome' => 'Your startup investment fails',
    'stat_effects' => '-15 Wealth, -10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Real Estate Deal',
    'outcome' => 'You profit from a property deal',
    'stat_effects' => '+20 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Bad Real Estate Deal',
    'outcome' => 'You lose money on a property deal',
    'stat_effects' => '-15 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Investor',
    'event_choice' => 'Networking Event',
    'outcome' => 'You meet other investors',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Market Crash',
    'outcome' => 'The market crashes unexpectedly',
    'stat_effects' => '-30 Wealth, -15 Happiness',
    'weight' => 1
],
[
    'profession' => 'Investor',
    'event_choice' => 'Charity Donation',
    'outcome' => 'You donate profits to charity',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Investor',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant monitoring',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
// Politician Profession Events
[
    'profession' => 'Politician',
    'event_choice' => 'Election Victory',
    'outcome' => 'You win a major election',
    'stat_effects' => '+20 Reputation, +15 Ego, +10 Wealth',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'Election Loss',
    'outcome' => 'You lose an election',
    'stat_effects' => '-15 Reputation, -10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'Public Speech Success',
    'outcome' => 'Your speech inspires the crowd',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Politician',
    'event_choice' => 'Public Speech Failure',
    'outcome' => 'Your speech fails to connect',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'Scandal',
    'outcome' => 'You are caught in a political scandal',
    'stat_effects' => '-20 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Politician',
    'event_choice' => 'Policy Success',
    'outcome' => 'Your policy improves lives',
    'stat_effects' => '+15 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'Policy Failure',
    'outcome' => 'Your policy backfires',
    'stat_effects' => '-10 Reputation, -5 Morality',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'International Summit',
    'outcome' => 'You represent your country abroad',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Politician',
    'event_choice' => 'Community Outreach',
    'outcome' => 'You attend a local community event',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Politician',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from political duties',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Performer Profession Events
[
    'profession' => 'Performer',
    'event_choice' => 'Stage Success',
    'outcome' => 'You deliver a powerful performance',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Performer',
    'event_choice' => 'Stage Failure',
    'outcome' => 'Your performance disappoints the audience',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Performer',
    'event_choice' => 'Positive Review',
    'outcome' => 'Critics praise your performance',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Performer',
    'event_choice' => 'Negative Review',
    'outcome' => 'Critics criticize your performance',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Performer',
    'event_choice' => 'Award Win',
    'outcome' => 'You win a performance award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Performer',
    'event_choice' => 'Fan Recognition',
    'outcome' => 'Fans celebrate your work',
    'stat_effects' => '+10 Happiness, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Performer',
    'event_choice' => 'Charity Performance',
    'outcome' => 'You perform for a charitable cause',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Performer',
    'event_choice' => 'Media Scandal',
    'outcome' => 'You are involved in a scandal',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Performer',
    'event_choice' => 'International Festival',
    'outcome' => 'You perform at an international festival',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Performer',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant performances',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Salesperson Profession Events
[
    'profession' => 'Salesperson',
    'event_choice' => 'Big Sale',
    'outcome' => 'You close a major deal',
    'stat_effects' => '+20 Wealth, +10 Reputation',
    'weight' => 3
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Lost Sale',
    'outcome' => 'You fail to close a deal',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Customer Praise',
    'outcome' => 'A customer praises your service',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 3
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Customer Complaint',
    'outcome' => 'A customer complains about your service',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Sales Award',
    'outcome' => 'You win a sales award',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 1
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Networking Event',
    'outcome' => 'You build valuable connections',
    'stat_effects' => '+10 Reputation, +5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Product Launch Success',
    'outcome' => 'Your product launch is a hit',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Product Launch Failure',
    'outcome' => 'Your product launch flops',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 1
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Team Collaboration',
    'outcome' => 'You collaborate successfully with your team',
    'stat_effects' => '+10 Discipline, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Salesperson',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant selling',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
// Community Leader Profession Events
[
    'profession' => 'Community Leader',
    'event_choice' => 'Successful Project',
    'outcome' => 'You organize a project that benefits the community',
    'stat_effects' => '+15 Reputation, +10 Morality',
    'weight' => 3
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Community Conflict',
    'outcome' => 'A dispute arises among community members',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Festival Organization',
    'outcome' => 'You organize a successful local festival',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Failed Initiative',
    'outcome' => 'Your project fails to gain support',
    'stat_effects' => '-10 Reputation, +5 Burnout',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Recognition Award',
    'outcome' => 'You receive recognition for leadership',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Fundraising Success',
    'outcome' => 'You raise funds for community needs',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Fundraising Failure',
    'outcome' => 'Your fundraising effort falls short',
    'stat_effects' => '-10 Reputation, -5 Wealth',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Mentorship',
    'outcome' => 'You mentor a young leader',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Community Scandal',
    'outcome' => 'You are accused of mismanagement',
    'stat_effects' => '-15 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Community Leader',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant responsibilities',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Priest/Religious Leader Profession Events
[
    'profession' => 'Priest',
    'event_choice' => 'Inspirational Sermon',
    'outcome' => 'Your sermon inspires the congregation',
    'stat_effects' => '+15 Reputation, +10 Morality',
    'weight' => 3
],
[
    'profession' => 'Priest',
    'event_choice' => 'Controversial Sermon',
    'outcome' => 'Your sermon sparks controversy',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Community Outreach',
    'outcome' => 'You organize a charity event',
    'stat_effects' => '+15 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Religious Festival',
    'outcome' => 'You lead a successful religious festival',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Scandal',
    'outcome' => 'You are accused of misconduct',
    'stat_effects' => '-20 Reputation, -10 Morality',
    'weight' => 1
],
[
    'profession' => 'Priest',
    'event_choice' => 'Spiritual Guidance',
    'outcome' => 'You provide guidance to a troubled member',
    'stat_effects' => '+10 Morality, +5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Religious Award',
    'outcome' => 'You receive recognition for service',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Priest',
    'event_choice' => 'Interfaith Dialogue',
    'outcome' => 'You participate in interfaith discussions',
    'stat_effects' => '+10 Reputation, +10 Morality',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Congregation Growth',
    'outcome' => 'Your congregation grows in size',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Priest',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from spiritual duties',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Philosopher/Poet Profession Events
[
    'profession' => 'Philosopher',
    'event_choice' => 'Published Work',
    'outcome' => 'Your philosophical essay or poetry is published',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Positive Review',
    'outcome' => 'Critics praise your work',
    'stat_effects' => '+15 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Negative Review',
    'outcome' => 'Critics criticize your work',
    'stat_effects' => '-10 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Philosophy Lecture',
    'outcome' => 'You deliver a lecture that inspires students',
    'stat_effects' => '+15 Reputation, +10 Morality',
    'weight' => 3
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Poetry Recital',
    'outcome' => 'You perform your poetry at an event',
    'stat_effects' => '+10 Reputation, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Award Win',
    'outcome' => 'You win a literary or philosophy award',
    'stat_effects' => '+20 Reputation, +15 Ego',
    'weight' => 1
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Writer’s Block',
    'outcome' => 'You struggle to produce new work',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Community Dialogue',
    'outcome' => 'You lead a philosophical discussion in the community',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Isolation',
    'outcome' => 'You spend long hours alone reflecting',
    'stat_effects' => '-5 Happiness, +10 Discipline',
    'weight' => 2
],
[
    'profession' => 'Philosopher',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant reflection',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],
// Gambler Profession Events
[
    'profession' => 'Gambler',
    'event_choice' => 'Big Win',
    'outcome' => 'You win a large jackpot',
    'stat_effects' => '+20 Wealth, +15 Ego, +10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Big Loss',
    'outcome' => 'You lose a large bet',
    'stat_effects' => '-20 Wealth, -10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Lucky Streak',
    'outcome' => 'You win several games in a row',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Unlucky Streak',
    'outcome' => 'You lose several games in a row',
    'stat_effects' => '-15 Wealth, -10 Happiness',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Casino Ban',
    'outcome' => 'You are banned from a casino',
    'stat_effects' => '-15 Reputation, -10 Wealth',
    'weight' => 1
],
[
    'profession' => 'Gambler',
    'event_choice' => 'High Roller Recognition',
    'outcome' => 'You are recognized as a high roller',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Debt Trouble',
    'outcome' => 'You fall into debt',
    'stat_effects' => '-20 Wealth, -10 Reputation',
    'weight' => 1
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Charity Donation',
    'outcome' => 'You donate winnings to charity',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Card Counting',
    'outcome' => 'You win by counting cards',
    'stat_effects' => '+15 Wealth, -5 Morality',
    'weight' => 1
],
[
    'profession' => 'Gambler',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from constant gambling',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],


// Casino Owner Profession Events
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Grand Opening',
    'outcome' => 'Your casino opens successfully',
    'stat_effects' => '+20 Wealth, +15 Reputation',
    'weight' => 3
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Slow Business',
    'outcome' => 'Your casino struggles with low attendance',
    'stat_effects' => '-10 Wealth, -5 Reputation',
    'weight' => 2
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'VIP Visit',
    'outcome' => 'A celebrity visits your casino',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 2
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Regulatory Fine',
    'outcome' => 'You are fined for violations',
    'stat_effects' => '-20 Wealth, -10 Reputation',
    'weight' => 1
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Successful Promotion',
    'outcome' => 'Your promotion attracts many customers',
    'stat_effects' => '+15 Wealth, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Security Breach',
    'outcome' => 'A cheating scandal occurs in your casino',
    'stat_effects' => '-15 Reputation, -10 Wealth',
    'weight' => 1
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Charity Event',
    'outcome' => 'You host a charity event at your casino',
    'stat_effects' => '+10 Morality, +10 Reputation',
    'weight' => 2
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Employee Conflict',
    'outcome' => 'Staff disputes disrupt operations',
    'stat_effects' => '-5 Reputation, -5 Happiness',
    'weight' => 2
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Business Award',
    'outcome' => 'You win a hospitality award',
    'stat_effects' => '+15 Reputation, +10 Ego',
    'weight' => 1
],
[
    'profession' => 'Casino Owner',
    'event_choice' => 'Burnout',
    'outcome' => 'You feel exhausted from managing your casino',
    'stat_effects' => '-10 Happiness, +10 Burnout',
    'weight' => 1
],

        ];

        foreach ($events as $event) {
            ProfessionPathEvent::firstOrCreate(
                [
                    'profession' => $event['profession'],
                    'event_choice' => $event['event_choice']
                ],
                $event
            );
        }
    }
}
