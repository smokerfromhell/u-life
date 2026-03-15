<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:Bug Report,Feature Suggestion,General Feedback,Other',
            'message' => 'required|string|max:5000',
        ]);

        Feedback::create($validated);

        return response()->json([
            'message' => 'Feedback submitted successfully!'
        ], 201);
    }
}
