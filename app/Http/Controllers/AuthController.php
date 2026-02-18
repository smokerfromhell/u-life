<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:5|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user
    ]);
}

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:5',
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Login successful',
            'user' => Auth::user()
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}
}
