<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedDecisionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharedDecisionLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->hasRole('Professional'))) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min(200, $perPage));

        $query = SharedDecisionLog::query()->orderByDesc('id');

        if ($user->hasRole('Professional')) {
            $query->select([
                'id',
                'created_at',
                'day',
                'event_type',
                'event_id',
                'choice_index',
                'is_guest',
            ]);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', (string) $request->query('event_type'));
        }

        if ($request->filled('is_guest')) {
            $query->where('is_guest', filter_var($request->query('is_guest'), FILTER_VALIDATE_BOOL));
        }

        return response()->json($query->paginate($perPage));
    }
}
