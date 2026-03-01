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
        Schema::create('book_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id');
            $table->foreignId('shared_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_to_user_id')->constrained('users')->onDelete('cascade');
            $table->string('permission')->default('view'); // 'view' only for now
            $table->string('status')->default('active'); // active, revoked
            $table->timestamp('shared_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            // Foreign key for books (UUID)
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');

            // Ensure a book can only be shared once with a specific user
            $table->unique(['book_id', 'shared_to_user_id']);

            // Index for status queries
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_shares');
    }
};
