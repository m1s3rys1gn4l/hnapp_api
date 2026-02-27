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
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan')->default('free')->after('disabled_at');
            }
            if (!Schema::hasColumn('users', 'subscription_cycle')) {
                $table->string('subscription_cycle')->nullable()->after('subscription_plan');
            }
            if (!Schema::hasColumn('users', 'book_limit')) {
                $table->integer('book_limit')->nullable()->after('subscription_cycle');
            }
            if (!Schema::hasColumn('users', 'customer_limit')) {
                $table->integer('customer_limit')->nullable()->after('book_limit');
            }
            if (!Schema::hasColumn('users', 'show_ads')) {
                $table->boolean('show_ads')->default(true)->after('customer_limit');
            }
            if (!Schema::hasColumn('users', 'subscription_started_at')) {
                $table->timestamp('subscription_started_at')->nullable()->after('show_ads');
            }
            if (!Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->dropColumn('subscription_expires_at');
            }
            if (Schema::hasColumn('users', 'subscription_started_at')) {
                $table->dropColumn('subscription_started_at');
            }
            if (Schema::hasColumn('users', 'show_ads')) {
                $table->dropColumn('show_ads');
            }
            if (Schema::hasColumn('users', 'customer_limit')) {
                $table->dropColumn('customer_limit');
            }
            if (Schema::hasColumn('users', 'book_limit')) {
                $table->dropColumn('book_limit');
            }
            if (Schema::hasColumn('users', 'subscription_cycle')) {
                $table->dropColumn('subscription_cycle');
            }
            if (Schema::hasColumn('users', 'subscription_plan')) {
                $table->dropColumn('subscription_plan');
            }
        });
    }
};
