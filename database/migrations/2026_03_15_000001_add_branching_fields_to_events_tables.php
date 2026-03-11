<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add fields to support event branching and narrative chains
     */
    public function up(): void
    {
        // Add branching fields to age_specific_events table
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->string('event_category')->nullable()->after('weight')->comment('Category for grouping related events (e.g., education, career, relationships)');
            $table->integer('chain_order')->nullable()->after('event_category')->comment('Order in event chain (1 = start, 2 = follow-up, etc.)');
            $table->string('parent_category')->nullable()->after('chain_order')->comment('Category that must be completed first');
            $table->string('required_choice_outcome')->nullable()->after('parent_category')->comment('Outcome required from parent event (positive, negative, neutral)');
            $table->string('required_stat')->nullable()->after('required_choice_outcome')->comment('Stat that must meet threshold');
            $table->integer('stat_threshold')->nullable()->after('required_stat')->comment('Threshold value for required stat');
            $table->boolean('is_milestone')->default(false)->after('stat_threshold')->comment('Is this a major story milestone');
            $table->json('alternative_outcomes')->nullable()->after('is_milestone')->comment('Alternative paths if conditions not met');
        });

        // Add branching fields to daily_events table
        Schema::table('daily_events', function (Blueprint $table) {
            $table->string('event_category')->nullable()->after('weight');
            $table->integer('chain_order')->nullable()->after('event_category');
            $table->string('parent_category')->nullable()->after('chain_order');
            $table->string('required_choice_outcome')->nullable()->after('parent_category');
        });

        // Add branching fields to cultural_events table
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->string('event_category')->nullable()->after('weight');
            $table->integer('chain_order')->nullable()->after('event_category');
            $table->string('parent_category')->nullable()->after('chain_order');
            $table->string('required_choice_outcome')->nullable()->after('parent_category');
        });

        // Add fields to characters table for tracking event chains
        Schema::table('characters', function (Blueprint $table) {
            $table->json('completed_event_chains')->nullable()->after('shown_event_ids')->comment('Track completed event chains');
            $table->json('active_event_paths')->nullable()->after('completed_event_chains')->comment('Currently active event paths');
            $table->string('current_narrative')->nullable()->after('active_event_paths')->comment('Current story path (education_career, family_focused, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_category',
                'chain_order', 
                'parent_category',
                'required_choice_outcome',
                'required_stat',
                'stat_threshold',
                'is_milestone',
                'alternative_outcomes'
            ]);
        });

        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_category',
                'chain_order',
                'parent_category', 
                'required_choice_outcome'
            ]);
        });

        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_category',
                'chain_order',
                'parent_category',
                'required_choice_outcome'
            ]);
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'completed_event_chains',
                'active_event_paths',
                'current_narrative'
            ]);
        });
    }
};

