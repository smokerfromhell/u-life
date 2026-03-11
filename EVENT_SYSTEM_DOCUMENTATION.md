# U:LIFE Event System Database Documentation

This document explains the complete event system database structure for the U:LIFE character progression system.

## Database Tables Overview

### 1. **stat_trigger_conditions** 
When a character reaches a specific stat threshold, it triggers an event.

**Columns:**
- `id` - Primary key
- `trigger_stat_condition` - Full condition name (e.g., "Intelligence >= 50")
- `stat_name` - Name of the stat being tracked (e.g., "Intelligence")
- `threshold` - Required value to trigger (e.g., 50)
- `event_choice` - The event that triggers
- `outcome` - Description of what happens
- `stat_effects` - Effect on stats (e.g., "+10 Wealth, -5 Morality")
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Trigger Stat Condition, Event Choice, Outcome, Stat Effects

**Example Data:**
```
Trigger Stat Condition      | Event Choice           | Outcome                      | Stat Effects
Intelligence >= 50          | Academic Achievement   | You're offered a scholarship | +20 Wealth, +10 Reputation
Strength >= 60              | Athletic Recognition   | You're selected for the team | +15 Reputation, +5 Happiness
```

---

### 2. **daily_events**
Random events that occur daily with various stat effects.

**Columns:**
- `id` - Primary key
- `event_choice` - The event name/choice
- `outcome` - What happens
- `stat_effects` - Effect on stats
- `weight` - Probability weight (higher = more likely)
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Event Choice, Outcome, Stat Effects (Text), Weight

**Example Data:**
```
Event Choice            | Outcome                           | Stat Effects            | Weight
Found Money            | Lucky day! You find some cash     | +10 Wealth, +5 Happiness | 2
Had a Bad Day          | Things didn't go as planned       | -8 Happiness, +3 Stress  | 3
Met an Old Friend      | Great conversation and nostalgia | +5 Happiness, +3 Reputation | 2
```

---

### 3. **cultural_events**
Optional cultural events that the player can choose to participate in.

**Columns:**
- `id` - Primary key
- `event_choice` - The cultural event name
- `outcome` - What happens when you participate
- `stat_effects` - Effect on stats
- `weight` - Probability weight
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Event Choice, Outcome, Stat Effects (Text), Weight

**Example Data:**
```
Event Choice                | Outcome                                    | Stat Effects                    | Weight
Religious Ceremony         | Feel spiritually connected                 | +10 Morality, +5 Discipline     | 3
Cultural Festival          | Enjoy traditional music and food           | +8 Happiness, +5 Reputation     | 2
Community Volunteer Work   | Help others in your community              | +12 Morality, +10 Reputation   | 2
```

---

### 4. **profession_triggers**
Defines the conditions needed to unlock a profession path.

**Columns:**
- `id` - Primary key
- `profession` - The profession name (e.g., "Doctor", "Engineer", "Artist")
- `unlock_condition` - Required stats (e.g., "Intelligence >= 60 AND Creativity >= 40")
- `notes` - Additional information about the profession
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Profession, Unlock Condition (Stats), Notes

**Example Data:**
```
Profession  | Unlock Condition                           | Notes
Doctor      | Intelligence >= 60 AND Morality >= 50      | Requires dedication to healing
Engineer    | Intelligence >= 70 AND Strength >= 40      | Problem-solver with technical skills
Artist      | Creativity >= 75                           | Express yourself through creative work
Entrepreneur| Wealth >= 60 AND Charisma >= 50           | Build your own successful business
Chef        | Creativity >= 60 AND Discipline >= 50     | Master the culinary arts
```

---

### 5. **profession_path_events**
Events specific to each profession once unlocked.

**Columns:**
- `id` - Primary key
- `profession` - Which profession this event belongs to
- `event_choice` - The profession-specific event
- `outcome` - What happens
- `stat_effects` - Effect on stats
- `weight` - Probability weight
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Profession, Event Choice, Outcome, Stat Effects (Text), Weight

**Example Data:**
```
Profession  | Event Choice           | Outcome                                  | Stat Effects                    | Weight
Doctor      | Emergency Surgery      | Successfully save a patient's life       | +20 Morality, +15 Reputation    | 2
Doctor      | Medical Conference     | Learn new techniques and network         | +10 Intelligence, +5 Reputation | 2
Engineer    | Large Project Bid      | Win a major contract for your firm       | +25 Wealth, +15 Reputation     | 2
Engineer    | Technical Innovation   | Your invention is patented               | +20 Intelligence, +20 Wealth    | 1
Artist      | Gallery Exhibition     | Your artwork receives critical acclaim   | +20 Creativity, +15 Reputation  | 2
Artist      | Commission             | Create a masterpiece for a collector     | +15 Creativity, +25 Wealth     | 2
```

---

### 6. **age_specific_events**
Events that are only available for specific age groups.

**Columns:**
- `id` - Primary key
- `age_group` - Enum: 'child', 'teen', 'adult', 'old'
- `event_choice` - The event name
- `outcome` - What happens
- `stat_effects` - Effect on stats
- `weight` - Probability weight
- `created_at`, `updated_at` - Timestamps

**Excel Columns:** Age, Event Choice, Outcome/Description, Stat Effects (Text), Weight

**Example Data:**
```
Age   | Event Choice              | Outcome/Description                    | Stat Effects                | Weight
child | Playing with Friends      | Have fun and learn through play        | +5 Creativity, +3 Luck, +4 Happiness | 4
child | Reading Adventure Books   | Ignite your imagination                | +8 Creativity, +5 Luck, +3 Intelligence | 3
child | School Lessons            | Learn basic knowledge                  | +10 Intelligence, +3 Discipline | 3
teen  | School Project            | Work hard on a challenging assignment  | +8 Intelligence, +5 Discipline | 3
teen  | First Job                 | Earn money from part-time work         | +10 Wealth, +5 Discipline | 2
teen  | First Crush               | Experience romantic emotions           | +5 Happiness, +3 Charisma, -2 Discipline | 2
adult | Job Promotion             | Your work is recognized               | +15 Wealth, +15 Reputation, +8 Confidence | 2
adult | Start a Business          | Launch your entrepreneurial venture   | +20 Wealth, +10 Intelligence | 1
adult | Family Responsibilities  | Support loved ones                      | +10 Morality, -5 Wealth | 2
old   | Mentoring Youth           | Share wisdom with younger generations | +10 Morality, +5 Discipline, +8 Reputation | 3
old   | Retirement Reflection     | Reflect on a life well-lived          | +10 Happiness, +8 Morality | 2
old   | Health Challenges         | Deal with age-related issues           | -10 Strength, +5 Morality | 2
```

---

## How to Populate Database from Excel

### Step 1: Prepare Excel Files
Create separate sheets in your Excel file:
1. **Stat Triggers** - trigger_stat_condition, event_choice, outcome, stat_effects
2. **Daily Events** - event_choice, outcome, stat_effects, weight
3. **Cultural Events** - event_choice, outcome, stat_effects, weight
4. **Profession Triggers** - profession, unlock_condition, notes
5. **Profession Events** - profession, event_choice, outcome, stat_effects, weight
6. **Age Events** - age_group, event_choice, outcome, stat_effects, weight

### Step 2: Update Seeders
Update each seeder file in `database/seeders/`:
- `StatTriggerConditionSeeder.php`
- `DailyEventSeeder.php`
- `CulturalEventSeeder.php`
- `ProfessionTriggerSeeder.php`
- `ProfessionPathEventSeeder.php`
- `AgeSpecificEventSeeder.php`

### Step 3: Run Migrations
```bash
php artisan migrate --seed
```

---

## Models & Methods

All models are located in `app/Models/`:

### EventService (`app/Services/EventService.php`)
Utility service for handling events:

**Methods:**
- `getRandomEventByWeight(Collection $events)` - Get random event by weight probability
- `getRandomDailyEvent()` - Get random daily event
- `getRandomCulturalEvent()` - Get random cultural event
- `getRandomAgeSpecificEvent(string $ageGroup)` - Get random age-specific event
- `getRandomProfessionEvent(string $profession)` - Get profession path event
- `parseStatEffects(string $effectsText)` - Parse "+10 Happiness, -5 Morality" format
- `applyStatEffects(Character $character, string $effectsText)` - Apply effects to character
- `checkStatTriggers(Character $character)` - Check if any stat triggers are met
- `checkProfessionUnlock(Character $character)` - Check unlocked professions

---

## Usage Example

```php
use App\Services\EventService;
use App\Models\Character;

$eventService = new EventService();
$character = Character::find(1);

// Get a random daily event
$dailyEvent = $eventService->getRandomDailyEvent();

// Apply the event's effects to the character
$effects = $eventService->applyStatEffects($character, $dailyEvent->stat_effects);

// Check if any stat triggers are met
$triggeredEvents = $eventService->checkStatTriggers($character);

// Check if any professions are unlocked
$unlockedProfessions = $eventService->checkProfessionUnlock($character);

// Get profession-specific events
$professionEvents = $eventService->getRandomProfessionEvent('Doctor');
```

---

## Stats Format

All stat effects use the format:
```
+10 Happiness, -5 Morality, +3 Intelligence
```

Operators: `+` (increase), `-` (decrease)  
Format: `[+/-]<number> <StatName>, ...`

**Available Stats:**
- Base Stats: Intelligence, Strength, Charisma, Creativity, Wealth, Luck
- Hidden Stats: Debt, Health, Addiction, Burnout, Morality, Happiness, Reputation, Discipline, Isolation, Ego

---

## Profession Unlock Conditions Format

Conditions use logical operators:
```
Intelligence >= 60 AND Creativity >= 40
Strength >= 50 OR Discipline >= 70
Intelligence >= 80 AND Morality >= 60 AND Creativity >= 50
```

**Operators:** `>=`, `<=`, `>`, `<`, `==`, `!=`  
**Logical:** `AND`, `OR`

---

## Weight/Probability

The `weight` column determines how likely an event is to be selected.
- Higher weight = more likely
- Example weights: 1, 2, 3, 4, 5
- Total probability is weight/sum(all weights)

Example:
```
Event A - Weight 3
Event B - Weight 2
Event C - Weight 1
Total weight = 6

Event A: 3/6 = 50% chance
Event B: 2/6 = 33% chance
Event C: 1/6 = 17% chance
```

---

## Next Steps

1. Prepare your Excel data in the format specified above
2. Update the seeder files with your actual data
3. Run migrations: `php artisan migrate --seed`
4. Test the event system with the EventService
