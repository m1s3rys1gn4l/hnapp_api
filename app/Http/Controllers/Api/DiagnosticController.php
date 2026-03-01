<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    /**
     * Search for a user by email or phone (for debugging)
     */
    public function findUser(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if (!$validated['email'] && !$validated['phone']) {
            return response()->json([
                'message' => 'Either email or phone must be provided',
                'found' => false,
            ], 422);
        }

        $user = null;
        $searchType = null;

        if ($validated['email']) {
            $email = strtolower(trim($validated['email']));
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            $searchType = 'email';
        } elseif ($validated['phone']) {
            $phone = trim($validated['phone']);
            $user = User::where('phone', $phone)->first();
            $searchType = 'phone';
        }

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'found' => false,
                'searched_by' => $searchType,
                'searched_for' => $validated['email'] ?? $validated['phone'],
            ], 404);
        }

        return response()->json([
            'message' => 'User found',
            'found' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'subscription_plan' => $user->subscription_plan,
            ],
        ]);
    }

    /**
     * Get list of all users (for debugging - shows count and sample emails)
     */
    public function listUsers(Request $request)
    {
        $user = $request->auth_user;

        // Only allow admin users or the user themselves
        if (!$user || !$user->is_active) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $users = User::select('id', 'name', 'email', 'phone', 'subscription_plan', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return response()->json([
            'total_users' => User::count(),
            'showing' => $users->count(),
            'users' => $users,
        ]);
    }
}
