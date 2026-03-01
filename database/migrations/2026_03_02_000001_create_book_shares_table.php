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
            $table->uuid('shared_by_user_id'); // User who owns the book
            $table->uuid('shared_to_user_id'); // User who receives the book
            $table->string('permission')->default('view'); // 'view' only for now
            $table->string('status')->default('active'); // active, revoked
            $table->timestamp('shared_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('shared_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shared_to_user_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure a book can only be shared once with a specific user
            $table->unique(['book_id', 'shared_to_user_id']);

            // Indexes for queries
            $table->index('shared_by_user_id');
            $table->index('shared_to_user_id');
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
