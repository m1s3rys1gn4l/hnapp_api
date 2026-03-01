<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookShare;
use App\Models\User;
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

    /**
     * Share a book with another user by email or phone
     */
    public function share(Request $request, string $id)
    {
        $user = $request->auth_user;
        $book = $user->books()->findOrFail($id);

        $validated = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if (!$validated['email'] && !$validated['phone']) {
            return response()->json([
                'message' => 'Either email or phone must be provided',
            ], 422);
        }

        // Find user by email or phone (case-insensitive)
        $shareWithUser = null;
        if ($validated['email']) {
            $email = strtolower(trim($validated['email']));
            $shareWithUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
        } elseif ($validated['phone']) {
            $phone = trim($validated['phone']);
            $shareWithUser = User::where('phone', $phone)->first();
        }

        if (!$shareWithUser) {
            return response()->json([
                'message' => 'User not found. Please verify the email/phone address.',
                'searched_for' => $validated['email'] ?? $validated['phone'],
            ], 404);
        }

        if ($shareWithUser->id === $user->id) {
            return response()->json([
                'message' => 'Cannot share a book with yourself',
            ], 422);
        }

        // Check if already shared
        if ($book->activeShares()->where('shared_to_user_id', $shareWithUser->id)->exists()) {
            return response()->json([
                'message' => 'This book is already shared with this user',
            ], 422);
        }

        // Create the share
        $share = BookShare::create([
            'id' => (string) Str::uuid(),
            'book_id' => $book->id,
            'shared_by_user_id' => $user->id,
            'shared_to_user_id' => $shareWithUser->id,
            'permission' => 'view',
            'status' => 'active',
            'shared_at' => now(),
        ]);

        return response()->json([
            'message' => 'Book shared successfully',
            'share' => [
                'id' => $share->id,
                'book_id' => $share->book_id,
                'shared_with' => [
                    'id' => $shareWithUser->id,
                    'name' => $shareWithUser->name,
                    'email' => $shareWithUser->email,
                    'phone' => $shareWithUser->phone,
                ],
                'permission' => $share->permission,
                'shared_at' => $share->shared_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get books shared with the current user
     */
    public function sharedBooks(Request $request)
    {
        $user = $request->auth_user;

        $sharedBooks = $user->receivedShares()
            ->where('status', 'active')
            ->with('book', 'sharedByUser')
            ->get()
            ->map(function ($share) {
                $book = $share->book;
                return [
                    'id' => $book->id,
                    'name' => $book->name,
                    'is_pinned' => (bool) $book->is_pinned,
                    'default_client_id' => $book->default_client_id,
                    'created_at' => $book->created_at->toIso8601String(),
                    'updated_at' => $book->updated_at->toIso8601String(),
                    'shared_by' => [
                        'id' => $share->sharedByUser->id,
                        'name' => $share->sharedByUser->name,
                        'email' => $share->sharedByUser->email,
                    ],
                    'shared_at' => $share->shared_at->toIso8601String(),
                    'is_shared' => true,
                    'share_id' => $share->id,
                ];
            });

        return response()->json($sharedBooks);
    }

    /**
     * Get transactions for a shared book
     */
    public function getSharedBookTransactions(Request $request, string $bookId)
    {
        $user = $request->auth_user;

        // Verify the book is shared with the user
        $share = BookShare::where('book_id', $bookId)
            ->where('shared_to_user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$share) {
            return response()->json([
                'message' => 'Book not shared with you',
            ], 403);
        }

        // Get transactions from the book owner (original creator)
        $transactions = \App\Models\Transaction::where('book_id', $bookId)
            ->get()
            ->map(function ($tx) {
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
     * Get list of users a book is shared with (for the book owner)
     */
    public function getBookShares(Request $request, string $bookId)
    {
        $user = $request->auth_user;

        // Verify ownership
        $book = $user->books()->findOrFail($bookId);

        // Get all active shares
        $shares = $book->activeShares()
            ->with('sharedToUser')
            ->get()
            ->map(function ($share) {
                return [
                    'id' => $share->id,
                    'book_id' => $share->book_id,
                    'shared_to' => [
                        'id' => $share->sharedToUser->id,
                        'name' => $share->sharedToUser->name,
                        'email' => $share->sharedToUser->email,
                        'phone' => $share->sharedToUser->phone,
                    ],
                    'permission' => $share->permission,
                    'shared_at' => $share->shared_at->toIso8601String(),
                ];
            });

        return response()->json($shares);
    }

    /**
     * Revoke a book share
     */
    public function revokeShare(Request $request, string $shareId)
    {
        $user = $request->auth_user;

        $share = BookShare::where('id', $shareId)
            ->where('status', 'active')
            ->first();

        if (!$share) {
            return response()->json([
                'message' => 'Share not found',
            ], 404);
        }

        // Only the owner can revoke the share
        if ($share->shared_by_user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $share->revoke();

        return response()->json([
            'message' => 'Share revoked successfully',
        ]);
    }
}
