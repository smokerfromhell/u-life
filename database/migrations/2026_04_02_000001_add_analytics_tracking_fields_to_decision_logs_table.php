<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fields for more precise analytics tracking:
     * - stats_before/stats_after: Full stat snapshots for detailed analysis
     * - time_spent_on_event: Time player spends on each event
     * - current_luck: Luck stat at time of decision
     * - mini_game_result: Mini-game performance data
     * - decision_timestamp: Exact decision time for pattern analysis
     */
    public function up(): void
    {
        Schema::table('decision_logs', function (Blueprint $table) {
            // Full stats snapshot for detailed before/after analysis
            $table->json('stats_before')->nullable()->after('data');
            $table->json('stats_after')->nullable()->after('stats_before');
            
            // Time spent on event (in seconds) - for engagement analytics
            $table->integer('time_spent_on_event')->nullable()->unsigned()->after('stats_after');
            
            // Luck stat at time of decision - for luck impact analysis
            $table->integer('current_luck')->nullable()->unsigned()->after('time_spent_on_event');
            
            // Mini-game performance data
            $table->json('mini_game_result')->nullable()->after('current_luck');
            
            // Exact timestamp for pattern analysis (more precise than created_at)
            $table->timestamp('decision_made_at')->nullable()->after('mini_game_result');
            
            // Add indexes for frequently queried fields
            $table->index('age_group');
            $table->index('profession');
            $table->index('event_type');
            $table->index('decision_made_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decision_logs', function (Blueprint $table) {
            $table->dropColumn([
                'stats_before',
                'stats_after',
                'time_spent_on_event',
                'current_luck',
                'mini_game_result',
                'decision_made_at',
            ]);
        });
    }
};