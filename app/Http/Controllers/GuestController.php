<?php

namespace App\Http\Controllers;

use App\Support\Privacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
class GuestController extends Controller
{
    public function start(Request $request)
    {
        $validated = $request->validate([
            'share_consent' => 'nullable|boolean',
        ]);

        // Use separate session key for game/frontend
        $gameSessionKey = 'game_user_id';
        
        // Check if there's already a game session
        if ($request->session()->has($gameSessionKey)) {
            $userId = $request->session()->get($gameSessionKey);
            $user = User::find($userId);
            
            if ($user) {
                // Update consent if provided
                if (array_key_exists('share_consent', $validated)) {
                    $user->share_consent = $validated['share_consent'];
                    $user->save();
                }
                
                return response()->json([
                    'message' => 'Game session restored',
                    'user' => $user,
                    'has_character' => $user->characters()->exists(),
                ]);
            }
        }

        $uuid = (string) Str::uuid();

        // Use separate session key for game/frontend
        $gameSessionKey = 'game_user_id';
        
        $createData = [
            'name' => 'Guest',
            'email' => "guest_{$uuid}@ulife.local",
            'password' => Hash::make(Str::random(48)),
        ];

        if (Schema::hasColumn('users', 'is_guest')) {
            $createData['is_guest'] = true;
        }
        if (Schema::hasColumn('users', 'share_consent')) {
            $createData['share_consent'] = $validated['share_consent'] ?? null;
        }
        if (Schema::hasColumn('users', 'guest_started_at')) {
            $createData['guest_started_at'] = now();
        }

        $user = User::create($createData);

        // Store user ID in separate session key for game
        $request->session()->put($gameSessionKey, $user->id);

        return response()->json([
            'message' => 'Guest session started',
            'user' => $user,
            'has_character' => false,
        ], 201);
    }

    public function me()
    {
        // Use separate session key for game/frontend
        $gameSessionKey = 'game_user_id';
        
        if (!session()->has($gameSessionKey)) {
            return response()->json([
                'user' => null,
            ]);
        }
        
        $userId = session()->get($gameSessionKey);
        $user = User::find($userId);
        
        return response()->json([
            'user' => $user,
        ]);
    }

    public function setConsent(Request $request)
    {
        $validated = $request->validate([
            'share_consent' => 'required|boolean',
        ]);

        // Use separate session key for game/frontend
        $gameSessionKey = 'game_user_id';
        
        $userId = $request->session()->get($gameSessionKey);
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'No active game session',
            ], 401);
        }
        
        if (!Schema::hasColumn('users', 'share_consent')) {
            return response()->json([
                'message' => 'Consent feature not available (missing migration)',
            ], 501);
        }

        $oldConsent = $user->share_consent;
        $user->share_consent = $validated['share_consent'];
        $user->save();

        return response()->json([
            'message' => 'Consent updated',
            'user' => $user,
        ]);
    }

    public function exit(Request $request)
    {
        // Use separate session key for game/frontend
        $gameSessionKey = 'game_user_id';
        
        $userId = $request->session()->get($gameSessionKey);
        $user = User::find($userId);

        $request->session()->forget($gameSessionKey);

        if ($user && ($user->is_guest ?? false) && $user->share_consent !== true) {
            $user->delete();
        }

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
