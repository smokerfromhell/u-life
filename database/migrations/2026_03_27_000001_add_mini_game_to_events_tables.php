<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add mini_game JSON field to daily_events
        Schema::table('daily_events', function (Blueprint $table) {
            $table->json('mini_game')->nullable()->after('conditions');
            $table->string('mini_game_type')->nullable()->after('mini_game');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
        });
        
        // Add mini_game JSON field to cultural_events
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->json('mini_game')->nullable()->after('conditions');
            $table->string('mini_game_type')->nullable()->after('mini_game');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
        });
        
        // Add mini_game JSON field to age_specific_events
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->json('mini_game')->nullable()->after('conditions');
            $table->string('mini_game_type')->nullable()->after('mini_game');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
        });
        
        // Add mini_game JSON field to profession_path_events
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->json('mini_game')->nullable()->after('conditions');
            $table->string('mini_game_type')->nullable()->after('mini_game');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
        });
        
        // Add mini_game JSON field to daily_actions (for learning events)
        Schema::table('daily_actions', function (Blueprint $table) {
            $table->json('mini_game')->nullable()->after('stat_effects');
            $table->string('mini_game_type')->nullable()->after('mini_game');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
        });
    }

    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game', 'mini_game_type', 'mini_game_difficulty']);
        });
        
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game', 'mini_game_type', 'mini_game_difficulty']);
        });
        
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game', 'mini_game_type', 'mini_game_difficulty']);
        });
        
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game', 'mini_game_type', 'mini_game_difficulty']);
        });
        
        Schema::table('daily_actions', function (Blueprint $table) {
            $table->dropColumn(['mini_game', 'mini_game_type', 'mini_game_difficulty']);
        });
    }
};
