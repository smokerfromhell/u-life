<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:5',
                'password_confirmation' => 'required|same:password',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:5',
    ]);

    // Use separate session key for game/frontend
    $gameSessionKey = 'game_user_id';

    if (Auth::attempt($request->only('email', 'password'))) {
        $user = Auth::user();
        $hasCharacter = $user->characters()->exists();
        
        // Store user ID in separate session key for game
        $request->session()->put($gameSessionKey, $user->id);
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'has_character' => $hasCharacter
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}

public function googleAuth(Request $request)
{
    $request->validate([
        'token' => 'required|string'
    ]);

    try {
        // Verify the token with Google
        $client = new \Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        
        $payload = $client->verifyIdToken($request->token);
        
        if ($payload) {
            $email = $payload['email'];
            $name = $payload['name'] ?? $payload['given_name'] ?? 'User';
            
            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(bin2hex(random_bytes(32)))
                ]
            );
            
            Auth::login($user);
            $hasCharacter = $user->characters()->exists();
            
            // Store user ID in separate session key for game
            $request->session()->put('game_user_id', $user->id);
            
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'has_character' => $hasCharacter
            ], 200);
        }
        
        return response()->json(['message' => 'Invalid token'], 401);
    } catch (\Exception $e) {
        Log::error('Google Auth Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Google authentication failed: ' . $e->getMessage()
        ], 401);
    }
}

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    try {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Don't reveal if email exists for security
            return response()->json([
                'message' => 'If this email exists in our system, a password reset link has been sent.'
            ], 200);
        }

        // Generate reset token
        $token = Str::random(64);
        
        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Build reset link
        $resetLink = env('APP_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        // Send real email
        Mail::to($request->email)->send(new \App\Mail\PasswordResetMail($resetLink));
        
        return response()->json([
            'message' => 'Password reset link has been sent to your email.',
            'reset_link' => $resetLink, // Include for testing purposes (remove in production)
        ], 200);
    } catch (\Exception $e) {
        Log::error('Forgot Password Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to process password reset request.'
        ], 500);
    }
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:5',
        'password_confirmation' => 'required|same:password',
    ]);

    try {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => 'Invalid or expired reset token.'
            ], 401);
        }

        // Check if token matches
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'message' => 'Invalid reset token.'
            ], 401);
        }

        // Check if token has expired (24 hours)
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            return response()->json([
                'message' => 'Reset token has expired. Please request a new one.'
            ], 401);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    } catch (\Exception $e) {
        Log::error('Reset Password Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to reset password.'
        ], 500);
    }
}
}
