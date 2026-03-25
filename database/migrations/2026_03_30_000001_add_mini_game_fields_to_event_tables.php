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
        // Add mini_game fields to daily_events
        Schema::table('daily_events', function (Blueprint $table) {
            $table->string('mini_game_type')->nullable()->after('conditions');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
            $table->string('mini_game_category')->nullable()->after('mini_game_difficulty');
        });

        // Add mini_game fields to cultural_events
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->string('mini_game_type')->nullable()->after('conditions');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
            $table->string('mini_game_category')->nullable()->after('mini_game_difficulty');
        });

        // Add mini_game fields to age_specific_events
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->string('mini_game_type')->nullable()->after('conditions');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
            $table->string('mini_game_category')->nullable()->after('mini_game_difficulty');
        });

        // Add mini_game fields to profession_path_events
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->string('mini_game_type')->nullable()->after('conditions');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
            $table->string('mini_game_category')->nullable()->after('mini_game_difficulty');
        });

        // Add mini_game fields to daily_actions (learning events)
        Schema::table('daily_actions', function (Blueprint $table) {
            $table->string('mini_game_type')->nullable()->after('conditions');
            $table->integer('mini_game_difficulty')->default(1)->after('mini_game_type');
            $table->string('mini_game_category')->nullable()->after('mini_game_difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game_type', 'mini_game_difficulty', 'mini_game_category']);
        });

        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game_type', 'mini_game_difficulty', 'mini_game_category']);
        });

        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game_type', 'mini_game_difficulty', 'mini_game_category']);
        });

        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->dropColumn(['mini_game_type', 'mini_game_difficulty', 'mini_game_category']);
        });

        Schema::table('daily_actions', function (Blueprint $table) {
            $table->dropColumn(['mini_game_type', 'mini_game_difficulty', 'mini_game_category']);
        });
    }
};