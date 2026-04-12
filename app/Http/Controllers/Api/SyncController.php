<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Pull all data from server (download)
     */
    public function pull(Request $request)
    {
        $user = $request->auth_user;

        // Format books for response
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

        // Format clients for response
        $clients = $user->clients()->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'user_id' => $client->user_id,
                'name' => $client->name,
                'phone' => $client->phone,
                'created_at' => $client->created_at->toIso8601String(),
                'updated_at' => $client->updated_at->toIso8601String(),
            ];
        });

        // Format transactions for response
        $transactions = $user->transactions()->get()->map(function ($tx) {
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

        return response()->json([
            'books' => $books,
            'clients' => $clients,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Push local data to server (upload)
     */
    public function push(Request $request)
    {
        $validated = $request->validate([
            'books' => 'array',
            'books.*.id' => 'required|string',
            'books.*.name' => 'required|string',
            'books.*.is_pinned' => 'boolean',
            'books.*.default_client_id' => 'nullable|string',
            'books.*.created_at' => 'required|date',
            'books.*.updated_at' => 'required|date',
            
            'clients' => 'array',
            'clients.*.id' => 'required|string',
            'clients.*.name' => 'required|string',
            'clients.*.phone' => 'nullable|string|max:20',
            'clients.*.created_at' => 'required|date',
            'clients.*.updated_at' => 'required|date',
            
            'transactions' => 'array',
            'transactions.*.id' => 'required|string',
            'transactions.*.book_id' => 'required|string',
            'transactions.*.client_id' => 'required|string',
            'transactions.*.type' => 'required|in:in,out',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.note' => 'nullable|string',
            'transactions.*.category' => 'required|string',
            'transactions.*.date' => 'required|date',
            'transactions.*.created_at' => 'required|date',
            'transactions.*.updated_at' => 'required|date',
            
            // Deletion tracking
            'deleted_books' => 'array',
            'deleted_books.*' => 'string',
            'deleted_clients' => 'array',
            'deleted_clients.*' => 'string',
            'deleted_transactions' => 'array',
            'deleted_transactions.*' => 'string',
        ]);

        $user = $request->auth_user;

        $effectiveBookLimit = $user->effectiveBookLimit();
        if (isset($validated['books']) && $effectiveBookLimit !== null) {
            $existingBookIds = $user->books()->pluck('id')->all();
            $incomingBookIds = collect($validated['books'])->pluck('id')->unique()->all();
            $newBookCount = count(array_diff($incomingBookIds, $existingBookIds));

            if ($newBookCount > 0 && ($user->books()->count() + $newBookCount) > $effectiveBookLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book limit reached for your current package.',
                    'code' => 'BOOK_LIMIT_REACHED',
                    'limit' => $effectiveBookLimit,
                ], 403);
            }
        }

        $effectiveCustomerLimit = $user->effectiveCustomerLimit();
        if (isset($validated['clients']) && $effectiveCustomerLimit !== null) {
            $existingClientIds = $user->clients()->pluck('id')->all();
            $incomingClientIds = collect($validated['clients'])->pluck('id')->unique()->all();
            $newClientCount = count(array_diff($incomingClientIds, $existingClientIds));

            if ($newClientCount > 0 && ($user->clients()->count() + $newClientCount) > $effectiveCustomerLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer limit reached for your current package.',
                    'code' => 'CUSTOMER_LIMIT_REACHED',
                    'limit' => $effectiveCustomerLimit,
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Handle deletions FIRST before upserts
            if (isset($validated['deleted_books']) && !empty($validated['deleted_books'])) {
                Book::where('user_id', $user->id)
                    ->whereIn('id', $validated['deleted_books'])
                    ->delete();
            }

            if (isset($validated['deleted_clients']) && !empty($validated['deleted_clients'])) {
                Client::where('user_id', $user->id)
                    ->whereIn('id', $validated['deleted_clients'])
                    ->delete();
            }

            if (isset($validated['deleted_transactions']) && !empty($validated['deleted_transactions'])) {
                Transaction::where('user_id', $user->id)
                    ->whereIn('id', $validated['deleted_transactions'])
                    ->delete();
            }

            // Sync books
            if (isset($validated['books'])) {
                foreach ($validated['books'] as $bookData) {
                    Book::updateOrCreate(
                        ['id' => $bookData['id'], 'user_id' => $user->id],
                        [
                            'name' => $bookData['name'],
                            'is_pinned' => $bookData['is_pinned'] ?? false,
                            'default_client_id' => $bookData['default_client_id'] ?? null,
                            'created_at' => $bookData['created_at'],
                            'updated_at' => $bookData['updated_at'],
                        ]
                    );
                }
            }

            // Sync clients
            if (isset($validated['clients'])) {
                foreach ($validated['clients'] as $clientData) {
                    Client::updateOrCreate(
                        ['id' => $clientData['id'], 'user_id' => $user->id],
                        [
                            'name' => $clientData['name'],
                            'phone' => $clientData['phone'] ?? null,
                            'created_at' => $clientData['created_at'],
                            'updated_at' => $clientData['updated_at'],
                        ]
                    );
                }
            }

            // Sync transactions
            if (isset($validated['transactions'])) {
                foreach ($validated['transactions'] as $txData) {
                    Transaction::updateOrCreate(
                        ['id' => $txData['id'], 'user_id' => $user->id],
                        [
                            'book_id' => $txData['book_id'],
                            'client_id' => $txData['client_id'],
                            'type' => $txData['type'],
                            'amount' => $txData['amount'],
                            'note' => $txData['note'] ?? null,
                            'category' => $txData['category'],
                            'date' => $txData['date'],
                            'created_at' => $txData['created_at'],
                            'updated_at' => $txData['updated_at'],
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data synced successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Full bidirectional sync
     */
    public function sync(Request $request)
    {
        // Push local changes first
        $pushResponse = $this->push($request);
        
        if ($pushResponse->getStatusCode() !== 200) {
            return $pushResponse;
        }

        // Then pull latest server data
        return $this->pull($request);
    }
}
