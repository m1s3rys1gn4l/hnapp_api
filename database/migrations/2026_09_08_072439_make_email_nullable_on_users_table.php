<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Users who sign up via phone OTP have no email at all, so the column
     * can no longer be NOT NULL. Written as raw SQL (rather than
     * Schema::table(...)->change()) to avoid a doctrine/dbal dependency.
     * Production runs MySQL only; SQLite (used only for local scratch
     * testing) is intentionally left as-is since altering column
     * nullability there requires a full table rebuild.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        }
    }
};
