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
        // Add choices column to daily_events
        Schema::table('daily_events', function (Blueprint $table) {
            $table->json('choices')->nullable()->after('stat_effects');
            $table->string('title')->nullable()->after('event_choice');
            $table->string('description')->nullable()->after('title');
            $table->string('image')->nullable()->after('description');
        });

        // Add choices column to cultural_events
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->json('choices')->nullable()->after('stat_effects');
            $table->string('title')->nullable()->after('event_choice');
            $table->string('description')->nullable()->after('title');
            $table->string('image')->nullable()->after('description');
        });

        // Add choices column to age_specific_events
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->json('choices')->nullable()->after('stat_effects');
            $table->string('title')->nullable()->after('event_choice');
            $table->string('description')->nullable()->after('title');
            $table->string('image')->nullable()->after('description');
        });

        // Add choices column to profession_path_events
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->json('choices')->nullable()->after('stat_effects');
            $table->string('title')->nullable()->after('event_choice');
            $table->string('description')->nullable()->after('title');
            $table->string('image')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn(['choices', 'title', 'description', 'image']);
        });

        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn(['choices', 'title', 'description', 'image']);
        });

        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->dropColumn(['choices', 'title', 'description', 'image']);
        });

        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->dropColumn(['choices', 'title', 'description', 'image']);
        });
    }
};
