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

        if (Auth::check()) {
            $user = Auth::user();

            if (($user->is_guest ?? false) && array_key_exists('share_consent', $validated)) {
                $user->share_consent = $validated['share_consent'];
                $user->save();
            }

            return response()->json([
                'message' => 'Already authenticated',
                'user' => $user,
                'has_character' => $user->characters()->exists(),
            ]);
        }

        $uuid = (string) Str::uuid();

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

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Guest session started',
            'user' => $user,
            'has_character' => false,
        ], 201);
    }

    public function me()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

    public function setConsent(Request $request)
    {
        $validated = $request->validate([
            'share_consent' => 'required|boolean',
        ]);

        $user = Auth::user();
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
        $user = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user && ($user->is_guest ?? false) && $user->share_consent !== true) {
            $user->delete();
        }

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
