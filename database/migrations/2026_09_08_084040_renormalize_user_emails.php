<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Re-runs the same cleanup as 2026_03_02_000002_normalize_user_emails,
     * since AdminUserController::update() was saving edited emails without
     * trimming/lowercasing them (fixed alongside this migration), which let
     * dirty values back in and broke exact-match lookups (e.g. book sharing
     * by email) for any user edited via the admin panel since then.
     */
    public function up(): void
    {
        DB::statement("UPDATE users SET email = LOWER(TRIM(email)) WHERE email IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse normalization as original case/whitespace is lost.
    }
};
