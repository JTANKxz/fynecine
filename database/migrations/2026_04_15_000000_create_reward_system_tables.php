<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add reward_points to users
        if (!Schema::hasColumn('users', 'reward_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('reward_points')->default(0)->after('features');
            });
        }

        // Add points_cost to subscription_plans
        if (!Schema::hasColumn('subscription_plans', 'points_cost')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedInteger('points_cost')->nullable()->after('is_active');
            });
        }

        // Create reward_claims table
        Schema::create('reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('claimed_date');
            $table->timestamps();

            $table->unique(['user_id', 'claimed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_claims');

        if (Schema::hasColumn('subscription_plans', 'points_cost')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('points_cost');
            });
        }

        if (Schema::hasColumn('users', 'reward_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('reward_points');
            });
        }
    }
};
