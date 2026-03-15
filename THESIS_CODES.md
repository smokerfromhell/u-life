# Code Examples for Thesis

## 1. Modular Service Architecture

### EventService.php - Business Logic Layer

```php
<?php

namespace App\Services;

use App\Models\AgeSpecificEvent;
use App\Models\Character;
use App\Models\CulturalEvent;
use App\Models\DailyEvent;
use App\Models\ProfessionPathEvent;
use App\Models\StatTriggerCondition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EventService
{
    /**
     * Event categories for branching
     */
    const CATEGORIES = [
        'education_elementary' => ['age_group' => 'child', 'next' => 'education_high_school'],
        'education_high_school' => ['age_group' => 'teen', 'next' => 'education_college'],
        'education_college' => ['age_group' => 'adult', 'next' => null],
        'career_start' => ['age_group' => 'adult', 'next' => 'career_advancement'],
        'career_advancement' => ['age_group' => 'adult', 'next' => 'career_master'],
        'career_master' => ['age_group' => 'adult', 'next' => null],
        'family_relationship' => ['age_group' => 'teen', 'next' => 'family_marriage'],
        'family_marriage' => ['age_group' => 'adult', 'next' => 'family_parenthood'],
        'family_parenthood' => ['age_group' => 'adult', 'next' => null],
        'health_crisis' => ['age_group' => 'adult', 'next' => 'health_recovery'],
        'health_recovery' => ['age_group' => 'adult', 'next' => null],
        'social_friendship' => ['age_group' => 'child', 'next' => 'social_romantic'],
        'social_romantic' => ['age_group' => 'teen', 'next' => 'social_relationship'],
        'social_relationship' => ['age_group' => 'adult', 'next' => null],
        'skill_learning' => ['age_group' => 'child', 'next' => 'skill_development'],
        'skill_development' => ['age_group' => 'teen', 'next' => 'skill_mastery'],
        'skill_mastery' => ['age_group' => 'adult', 'next' => null],
    ];

    /**
     * State transition table (FSM)
     */
    const STATE_TRANSITIONS = [
        'life_stage' => [
            'child' => ['teenager' => 0.1, 'adult' => 0.05],
            'teenager' => ['adult' => 0.15],
            'adult' => ['old' => 0.08],
        ],
        'profession_state' => [
            'unemployed' => ['entry_level' => 0.12],
            'entry_level' => ['mid_level' => 0.1],
            'mid_level' => ['senior' => 0.08],
        ],
        'relationship_status' => [
            'single' => ['dating' => 0.15],
            'dating' => ['married' => 0.12, 'single' => 0.05],
            'married' => ['divorced' => 0.03, 'widowed' => 0.02],
        ],
        'health_condition' => [
            'healthy' => ['ill' => 0.07],
            'ill' => ['healthy' => 0.2],
        ],
    ];

    /**
     * Get events filtered by current FSM state
     */
    public function getStatefulEvents(Character $character, string $ageGroup): array
    {
        try {
            Log::info('EventService::getStatefulEvents called', [
                'character_id' => $character->id,
                'age_group' => $ageGroup,
                'current_state' => $character->current_state
            ]);
            
            $state = $character->current_state ?? ['life_stage' => 'child'];
            
            $eventsByCategory = [
                'daily' => $this->getFilteredDailyEvents($character, $state),
                'cultural' => $this->getFilteredCulturalEvents($character, $state),
                'ageSpecific' => $this->getFilteredAgeSpecificEvents($character, $ageGroup, $state),
                'profession' => $this->getFilteredProfessionEvents($character, $state),
            ];

            return $this->weightEventsByState($eventsByCategory, $state);
        } catch (\Exception $e) {
            Log::error('Error in getStatefulEvents', [
                'character_id' => $character->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
```

---

## 2. Eloquent Lazy Loading Relationships

### Character.php - Model with Lazy Loading

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'anon_character_id',
        'name',
        'age_group',
        'gender',
        'profession',
        'current_day',
        'stats',
        'hidden_stats',
    ];

    protected $casts = [
        'stats' => 'array',
        'hidden_stats' => 'array',
        'effective_stats' => 'array',
    ];

    /**
     * LAZY LOADING: Get the user that owns the character
     * Only loaded when $character->user is accessed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * LAZY LOADING: Get the skills associated with the character
     * Uses pivot table 'character_skill'
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'character_skill');
    }

    /**
     * LAZY LOADING: Get the talents associated with the character
     * Uses pivot table 'character_talent'
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'character_talent');
    }

    /**
     * LAZY LOADING: Get life stats history
     */
    public function lifeStatsSnapshots(): HasMany
    {
        return $this->hasMany(LifeStatsSnapshot::class, 'anon_character_id', 'anon_character_id');
    }
}
```

---

## 3. Controller Using Services + Eager Loading

### CharacterController.php - Presentation Layer

```php
<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller
{
    /**
     * INDEX: Eager loading with 'with()'
     * Loads skills and talents IN ONE QUERY to avoid N+1 problem
     */
    public function index()
    {
        $characters = Character::where('user_id', Auth::id())
            ->with(['skills', 'talents'])  // EAGER LOADING
            ->get();
        return response()->json($characters);
    }

    /**
     * SHOW: Lazy loading triggered by load() method
     * Relations loaded only when explicitly called
     */
    public function show(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // LAZY LOAD: Load skills and talents on demand
        $character->load(['skills', 'talents']);
        
        // Accessing relationships triggers lazy loading
        $skills = $character->skills->pluck('name')->toArray();
        $talents = $character->talents->pluck('name')->toArray();
        
        return response()->json([
            'character' => $character,
            'skills' => $skills,
            'talents' => $talents,
        ]);
    }

    /**
     * Store new character
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_group' => 'required|string|in:child,teenager,adult,old',
            'gender' => 'required|string|in:male,female,non-binary',
            'stats' => 'required|array',
        ]);

        $character = Character::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return response()->json($character, 201);
    }
}
```

---

## 4. DecisionLogService - Another Modular Service

```php
<?php

namespace App\Models;

use App\Models\Character;
use App\Models\CharacterDecisionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DecisionLogService
{
    /**
     * Log a decision made by a character
     */
    public function logDecision(
        Character $character, 
        string $eventId, 
        string $eventType, 
        array $choices, 
        ?string $outcome = null
    ): bool {
        try {
            CharacterDecisionLog::create([
                'character_id' => $character->id,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'choices' => json_encode($choices),
                'outcome' => $outcome,
                'age_group' => $character->age_group,
                'current_day' => $character->current_day,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error logging decision: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get decision logs for a character
     */
    public function getCharacterDecisionLogs(Character $character, int $limit = 50)
    {
        return CharacterDecisionLog::where('character_id', $character->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

---

## Summary: Key Patterns

| Pattern | Example | Benefit |
|---------|---------|---------|
| **Modular Service** | `EventService`, `DecisionLogService` | Separation of concerns |
| **Eager Loading** | `->with(['skills', 'talents'])` | Prevents N+1 queries |
| **Lazy Loading** | `$character->skills` | Load on demand |
| **FSM State** | `STATE_TRANSITIONS` array | Game state management |
| **Repository Pattern** | Controllers use Services | Testable code |
