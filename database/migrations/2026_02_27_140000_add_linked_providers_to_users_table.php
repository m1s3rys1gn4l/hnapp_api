<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Store linked authentication methods as JSON
            // Example: ["password", "google.com"]
            if (!Schema::hasColumn('users', 'linked_providers')) {
                $table->json('linked_providers')->nullable()->after('firebase_uid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'linked_providers')) {
                $table->dropColumn('linked_providers');
            }
        });
    }
};
