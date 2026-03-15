<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Skill;
use App\Models\Talent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CharacterController extends Controller
{
    /**
     * Get all characters for the authenticated user
     */
    public function index()
    {
        $characters = Character::where('user_id', Auth::id())
            ->with(['skills', 'talents'])
            ->get();
        return response()->json($characters);
    }

    /**
     * Get a specific character
     */
    public function show(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $character->load(['skills', 'talents']);
        
        // Log for debugging
        Log::info('Character show response:', [
            'id' => $character->id,
            'stats' => $character->stats,
            'hidden_stats' => $character->hidden_stats,
            'effective_stats' => $character->effective_stats,
            'skills' => $character->skills->pluck('name')->toArray(),
            'talents' => $character->talents->pluck('name')->toArray(),
        ]);
        
        return response()->json($character);
    }

    /**
     * Store a newly created character
     */
    public function store(Request $request)
    {
        Log::info('Character creation request:', $request->all());

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_group' => 'required|string|in:child,teenager,adult,old',
            'gender' => 'required|string|in:male,female,non-binary,transgender',
            'stats' => 'required|array',
            'hidden_stats' => 'required|array',
            'skills' => 'array',
            'talents' => 'array',
            'effective_stats' => 'array',
        ]);

        Log::info('Validated data:', $validated);
        Log::info('Auth ID:', ['user_id' => Auth::id()]);

        try {
            $character = Character::create([
                'user_id' => $userId,
                'name' => $validated['name'],
                'age_group' => $validated['age_group'],
                'gender' => $validated['gender'],
                'current_day' => $request->input('start_day', 1),
                'stats' => $validated['stats'],
                'hidden_stats' => $validated['hidden_stats'],
                'effective_stats' => $validated['effective_stats'],
            ]);

            Log::info('Character created with start_day', [
                'id' => $character->id,
                'age_group' => $character->age_group,
                'current_day' => $character->current_day
            ]);

            Log::info('Character created with stats:', [
                'id' => $character->id,
                'stats' => $character->stats,
                'hidden_stats' => $character->hidden_stats,
                'effective_stats' => $character->effective_stats,
            ]);

            // Handle skills - look up by name if strings are provided
            $skillIds = [];
            $skillsInput = $validated['skills'] ?? [];
            if (is_array($skillsInput) && !empty($skillsInput)) {
                foreach ($skillsInput as $skill) {
                    // If it's already an ID (number), use it directly
                    if (is_numeric($skill)) {
                        $skillIds[] = (int) $skill;
                    } elseif (is_string($skill)) {
                        // Look up by name
                        $skillModel = Skill::where('name', $skill)->first();
                        if ($skillModel) {
                            $skillIds[] = $skillModel->id;
                        }
                    } elseif (is_array($skill)) {
                        // Handle array format with 'name' key
                        $skillName = $skill['name'] ?? ($skill['id'] ?? null);
                        if ($skillName) {
                            $skillModel = Skill::where('name', $skillName)->first();
                            if ($skillModel) {
                                $skillIds[] = $skillModel->id;
                            }
                        }
                    }
                }
            }
            if (!empty($skillIds)) {
                $character->skills()->attach(array_unique($skillIds));
            }

            // Handle talents - look up by name if strings are provided
            $talentIds = [];
            $talentsInput = $validated['talents'] ?? [];
            if (is_array($talentsInput) && !empty($talentsInput)) {
                foreach ($talentsInput as $talent) {
                    // If it's already an ID (number), use it directly
                    if (is_numeric($talent)) {
                        $talentIds[] = (int) $talent;
                    } elseif (is_string($talent)) {
                        // Look up by name
                        $talentModel = Talent::where('name', $talent)->first();
                        if ($talentModel) {
                            $talentIds[] = $talentModel->id;
                        }
                    } elseif (is_array($talent)) {
                        // Handle array format with 'name' key
                        $talentName = $talent['name'] ?? ($talent['id'] ?? null);
                        if ($talentName) {
                            $talentModel = Talent::where('name', $talentName)->first();
                            if ($talentModel) {
                                $talentIds[] = $talentModel->id;
                            }
                        }
                    }
                }
            }
            if (!empty($talentIds)) {
                $character->talents()->attach(array_unique($talentIds));
            }

            // Reload the character with skills and talents
            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Character created successfully',
                'character' => $character,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Character creation error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error creating character: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing character
     */
    public function update(Request $request, Character $character)
    {
        // Check if user owns this character
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'age_group' => 'string|in:child,teenager,adult,old',
            'gender' => 'string|in:male,female,non-binary,transgender',
            'stats' => 'array',
            'hidden_stats' => 'array',
            'effective_stats' => 'array',
'current_day' => 'integer|min:1',
            'start_day' => 'integer|min:1|max:120',
            'image' => 'string|max:500',
        ]);

        try {
            $character->update($validated);

            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Character updated successfully',
                'character' => $character,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Character update error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error updating character: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload character image
     */
    public function uploadImage(Request $request, Character $character)
    {
        // Check if user owns this character
        if ($character->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => 'string|max:255',
        ]);

        try {
            // Store the image
            $imagePath = $request->file('image')->store('character-images', 'public');
            $imageUrl = '/storage/' . $imagePath;

            // Update character with new image and name if provided
            $updateData = ['image' => $imageUrl];
            if ($request->has('name') && $request->name) {
                $updateData['name'] = $request->name;
            }
            $character->update($updateData);

            $character->load(['skills', 'talents']);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'character' => $character,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Image upload error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get decision logs (memory) for character
     */
    public function decisionLogs(Character $character)
    {
        if ($character->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = \App\Models\SharedDecisionLog::where('anon_character_id', $character->anon_character_id)
            ->latest('created_at')
            ->take(50)
            ->get();

        $formattedLogs = $logs->map(function ($log) {
            $data = $log->data ?? [];
            return [
                'event_title' => data_get($data, 'event_title', $log->event_title ?? 'Unknown Event'),
                'choice_text' => data_get($data, 'choice_text', $log->choice_text ?? 'Unknown Choice'),
                'effects' => data_get($data, 'effects', $log->effects ?? []),
                'created_at' => $log->created_at,
            ];
        });

        return response()->json($formattedLogs);
    }
}