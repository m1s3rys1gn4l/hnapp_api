<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOfflineBackup extends Command
{
    protected $signature = 'import:offline-backup {file} {--user-email=} {--clear}';
    protected $description = 'Import offline Hive backup JSON to database';

    public function handle()
    {
        $file = $this->argument('file');
        $userEmail = $this->option('user-email');
        $shouldClear = $this->option('clear');

        // Validate file
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // Read JSON
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (!$data) {
            $this->error('Invalid JSON format');
            return 1;
        }

        // Get user
        $user = null;
        if ($userEmail) {
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                $this->error("User not found: $userEmail");
                return 1;
            }
        } else {
            $this->error('Please provide --user-email option');
            return 1;
        }

        // Confirm clear
        if ($shouldClear && !$this->confirm('Clear existing data for this user?')) {
            return 1;
        }

        DB::beginTransaction();
        try {
            // Clear existing data if requested
            if ($shouldClear) {
                Transaction::where('user_id', $user->id)->delete();
                Client::where('user_id', $user->id)->delete();
                Book::where('user_id', $user->id)->delete();
                $this->info('✓ Cleared existing data');
            }

            // Import books
            $bookCount = 0;
            foreach ($data['books'] ?? [] as $bookData) {
                Book::create([
                    'id' => $bookData['id'],
                    'user_id' => $user->id,
                    'name' => $bookData['name'],
                    'is_pinned' => $bookData['is_pinned'] ?? false,
                    'default_client_id' => $bookData['default_client_id'],
                    'created_at' => $bookData['created_at'],
                    'updated_at' => $bookData['updated_at'],
                ]);
                $bookCount++;
            }
            $this->info("✓ Imported {$bookCount} books");

            // Import clients
            $clientCount = 0;
            foreach ($data['clients'] ?? [] as $clientData) {
                Client::create([
                    'id' => $clientData['id'],
                    'user_id' => $user->id,
                    'name' => $clientData['name'],
                    'created_at' => $clientData['created_at'],
                    'updated_at' => $clientData['updated_at'],
                ]);
                $clientCount++;
            }
            $this->info("✓ Imported {$clientCount} clients");

            // Import transactions
            $txCount = 0;
            foreach ($data['transactions'] ?? [] as $txData) {
                Transaction::create([
                    'id' => $txData['id'],
                    'user_id' => $user->id,
                    'book_id' => $txData['book_id'],
                    'client_id' => $txData['client_id'],
                    'type' => $txData['type'],
                    'amount' => $txData['amount'],
                    'note' => $txData['note'] ?? null,
                    'category' => $txData['category'],
                    'date' => $txData['date'],
                    'created_at' => $txData['created_at'],
                    'updated_at' => $txData['updated_at'],
                ]);
                $txCount++;
            }
            $this->info("✓ Imported {$txCount} transactions");

            DB::commit();

            $this->info("\n✅ Import successful!");
            $this->info("Books: {$bookCount}, Clients: {$clientCount}, Transactions: {$txCount}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: {$e->getMessage()}");
            return 1;
        }
    }
}
