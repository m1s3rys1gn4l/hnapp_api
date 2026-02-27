<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->auth_user;
        
        $query = $user->transactions();
        
        // Filter by book_id if provided
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }
        
        // Filter by client_id if provided
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        // Filter by date range if provided
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $transactions = $query->get()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'user_id' => $tx->user_id,
                'book_id' => $tx->book_id,
                'client_id' => $tx->client_id,
                'type' => $tx->type,
                'amount' => (float) $tx->amount,
                'note' => $tx->note,
                'category' => $tx->category,
                'date' => $tx->date->format('Y-m-d'),
                'created_at' => $tx->created_at->toIso8601String(),
                'updated_at' => $tx->updated_at->toIso8601String(),
            ];
        });
        
        return response()->json($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->auth_user;
        
        $validated = $request->validate([
            'id' => 'nullable|uuid',
            'book_id' => 'required|uuid',
            'client_id' => 'nullable|uuid',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);
        
        $validated['user_id'] = $user->id;
        $validated['id'] = $validated['id'] ?? (string) Str::uuid();
        
        $transaction = Transaction::create($validated);
        
        return response()->json([
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'book_id' => $transaction->book_id,
            'client_id' => $transaction->client_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'note' => $transaction->note,
            'category' => $transaction->category,
            'date' => $transaction->date->format('Y-m-d'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->auth_user;
        $transaction = $user->transactions()->findOrFail($id);
        
        return response()->json([
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'book_id' => $transaction->book_id,
            'client_id' => $transaction->client_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'note' => $transaction->note,
            'category' => $transaction->category,
            'date' => $transaction->date->format('Y-m-d'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->auth_user;
        $transaction = $user->transactions()->findOrFail($id);
        
        $validated = $request->validate([
            'book_id' => 'sometimes|uuid',
            'client_id' => 'nullable|uuid',
            'type' => 'sometimes|in:in,out',
            'amount' => 'sometimes|numeric|min:0',
            'note' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'date' => 'sometimes|date',
        ]);
        
        $transaction->update($validated);
        
        return response()->json([
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'book_id' => $transaction->book_id,
            'client_id' => $transaction->client_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'note' => $transaction->note,
            'category' => $transaction->category,
            'date' => $transaction->date->format('Y-m-d'),
            'created_at' => $transaction->created_at->toIso8601String(),
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->auth_user;
        $transaction = $user->transactions()->findOrFail($id);
        
        $transaction->delete();
        
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
