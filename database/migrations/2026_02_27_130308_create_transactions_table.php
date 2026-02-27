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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('book_id');
            $table->uuid('client_id');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();
            $table->string('category')->default('General');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
