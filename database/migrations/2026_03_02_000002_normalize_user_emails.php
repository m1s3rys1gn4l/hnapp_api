<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize all existing emails to lowercase and trim whitespace
        DB::statement("UPDATE users SET email = LOWER(TRIM(email)) WHERE email IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse normalization as original case is lost
        // This is a data transformation migration
    }
};
