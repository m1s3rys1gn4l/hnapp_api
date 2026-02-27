<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->auth_user;
        $books = $user->books()->get()->map(function ($book) {
            return [
                'id' => $book->id,
                'user_id' => $book->user_id,
                'name' => $book->name,
                'is_pinned' => (bool) $book->is_pinned,
                'default_client_id' => $book->default_client_id,
                'created_at' => $book->created_at->toIso8601String(),
                'updated_at' => $book->updated_at->toIso8601String(),
            ];
        });
        return response()->json($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->auth_user;

        if (!$user->canCreateBook()) {
            return response()->json([
                'message' => 'Book limit reached for your current package.',
                'code' => 'BOOK_LIMIT_REACHED',
                'limit' => $user->effectiveBookLimit(),
            ], 403);
        }
        
        $validated = $request->validate([
            'id' => 'nullable|uuid',
            'name' => 'required|string|max:255',
            'is_pinned' => 'boolean',
            'default_client_id' => 'nullable|uuid',
        ]);
        
        $validated['user_id'] = $user->id;
        $validated['id'] = $validated['id'] ?? (string) Str::uuid();
        
        $book = Book::create($validated);
        
        return response()->json([
            'id' => $book->id,
            'user_id' => $book->user_id,
            'name' => $book->name,
            'is_pinned' => (bool) $book->is_pinned,
            'default_client_id' => $book->default_client_id,
            'created_at' => $book->created_at->toIso8601String(),
            'updated_at' => $book->updated_at->toIso8601String(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->auth_user;
        $book = $user->books()->findOrFail($id);
        
        return response()->json([
            'id' => $book->id,
            'user_id' => $book->user_id,
            'name' => $book->name,
            'is_pinned' => (bool) $book->is_pinned,
            'default_client_id' => $book->default_client_id,
            'created_at' => $book->created_at->toIso8601String(),
            'updated_at' => $book->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->auth_user;
        $book = $user->books()->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_pinned' => 'sometimes|boolean',
            'default_client_id' => 'nullable|uuid',
        ]);
        
        $book->update($validated);
        
        return response()->json([
            'id' => $book->id,
            'user_id' => $book->user_id,
            'name' => $book->name,
            'is_pinned' => (bool) $book->is_pinned,
            'default_client_id' => $book->default_client_id,
            'created_at' => $book->created_at->toIso8601String(),
            'updated_at' => $book->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->auth_user;
        $book = $user->books()->findOrFail($id);
        
        $book->delete();
        
        return response()->json(['message' => 'Book deleted successfully']);
    }
}
