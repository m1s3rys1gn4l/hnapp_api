<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->auth_user;
        $clients = $user->clients()->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'user_id' => $client->user_id,
                'name' => $client->name,
                'created_at' => $client->created_at->toIso8601String(),
                'updated_at' => $client->updated_at->toIso8601String(),
            ];
        });
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->auth_user;

        if (!$user->canCreateClient()) {
            return response()->json([
                'message' => 'Customer limit reached for your current package.',
                'code' => 'CUSTOMER_LIMIT_REACHED',
                'limit' => $user->effectiveCustomerLimit(),
            ], 403);
        }
        
        $validated = $request->validate([
            'id' => 'nullable|uuid',
            'name' => 'required|string|max:255',
        ]);
        
        $validated['user_id'] = $user->id;
        $validated['id'] = $validated['id'] ?? (string) Str::uuid();
        
        $client = Client::create($validated);
        
        return response()->json([
            'id' => $client->id,
            'user_id' => $client->user_id,
            'name' => $client->name,
            'created_at' => $client->created_at->toIso8601String(),
            'updated_at' => $client->updated_at->toIso8601String(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->auth_user;
        $client = $user->clients()->findOrFail($id);
        
        return response()->json([
            'id' => $client->id,
            'user_id' => $client->user_id,
            'name' => $client->name,
            'created_at' => $client->created_at->toIso8601String(),
            'updated_at' => $client->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->auth_user;
        $client = $user->clients()->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);
        
        $client->update($validated);
        
        return response()->json([
            'id' => $client->id,
            'user_id' => $client->user_id,
            'name' => $client->name,
            'created_at' => $client->created_at->toIso8601String(),
            'updated_at' => $client->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->auth_user;
        $client = $user->clients()->findOrFail($id);
        
        $client->delete();
        
        return response()->json(['message' => 'Client deleted successfully']);
    }
}
