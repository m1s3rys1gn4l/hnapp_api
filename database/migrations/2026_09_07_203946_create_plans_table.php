<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->unsignedInteger('book_limit')->nullable();
            $table->unsignedInteger('customer_limit')->nullable();
            $table->boolean('show_ads')->default(true);
            $table->unsignedInteger('monthly_price_bdt')->default(0);
            $table->unsignedInteger('yearly_price_bdt')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the plans that used to be hardcoded in App\Models\User::PLAN_DEFINITIONS.
        DB::table('plans')->insert([
            [
                'key' => 'free',
                'label' => 'Free',
                'book_limit' => 50,
                'customer_limit' => 200,
                'show_ads' => true,
                'monthly_price_bdt' => 0,
                'yearly_price_bdt' => 0,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'premium',
                'label' => 'Premium',
                'book_limit' => null,
                'customer_limit' => null,
                'show_ads' => false,
                'monthly_price_bdt' => 42,
                'yearly_price_bdt' => 500,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
