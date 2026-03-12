<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalAccountRequest;
use Illuminate\Http\Request;

class ProfessionalAccountRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'organization' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $existing = ProfessionalAccountRequest::query()
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Request already submitted',
                'request_id' => $existing->id,
                'status' => $existing->status,
            ], 200);
        }

        $record = ProfessionalAccountRequest::create([
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Request submitted',
            'request_id' => $record->id,
            'status' => $record->status,
        ], 201);
    }
}

