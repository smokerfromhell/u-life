<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add display fields needed by DailyEventSeeder, CulturalEventSeeder, and AgeSpecificEventSeeder
     */
    public function up(): void
    {
        // Add display fields to daily_events table - only if they don't exist
        Schema::table('daily_events', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_events', 'title')) {
                $table->string('title')->nullable()->after('event_choice');
            }
            if (!Schema::hasColumn('daily_events', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('daily_events', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('daily_events', 'type')) {
                $table->string('type')->default('daily')->after('image');
            }
            if (!Schema::hasColumn('daily_events', 'deck_label')) {
                $table->string('deck_label')->default('Daily Event')->after('type');
            }
            if (!Schema::hasColumn('daily_events', 'repeatable')) {
                $table->boolean('repeatable')->default(true)->after('deck_label');
            }
            if (!Schema::hasColumn('daily_events', 'auto_resolve')) {
                $table->boolean('auto_resolve')->default(false)->after('repeatable');
            }
            if (!Schema::hasColumn('daily_events', 'days_to_advance')) {
                $table->integer('days_to_advance')->default(0)->after('auto_resolve');
            }
            if (!Schema::hasColumn('daily_events', 'display_order')) {
                $table->integer('display_order')->nullable()->after('days_to_advance');
            }
            if (!Schema::hasColumn('daily_events', 'conditions')) {
                $table->json('conditions')->nullable()->after('display_order');
            }
        });

        // Add display fields to cultural_events table
        Schema::table('cultural_events', function (Blueprint $table) {
            if (!Schema::hasColumn('cultural_events', 'title')) {
                $table->string('title')->nullable()->after('event_choice');
            }
            if (!Schema::hasColumn('cultural_events', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('cultural_events', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('cultural_events', 'type')) {
                $table->string('type')->default('cultural')->after('image');
            }
            if (!Schema::hasColumn('cultural_events', 'deck_label')) {
                $table->string('deck_label')->default('Cultural Event')->after('type');
            }
            if (!Schema::hasColumn('cultural_events', 'repeatable')) {
                $table->boolean('repeatable')->default(true)->after('deck_label');
            }
            if (!Schema::hasColumn('cultural_events', 'auto_resolve')) {
                $table->boolean('auto_resolve')->default(false)->after('repeatable');
            }
            if (!Schema::hasColumn('cultural_events', 'days_to_advance')) {
                $table->integer('days_to_advance')->default(0)->after('auto_resolve');
            }
            if (!Schema::hasColumn('cultural_events', 'display_order')) {
                $table->integer('display_order')->nullable()->after('days_to_advance');
            }
            if (!Schema::hasColumn('cultural_events', 'conditions')) {
                $table->json('conditions')->nullable()->after('display_order');
            }
        });

        // Add display fields to age_specific_events table
        Schema::table('age_specific_events', function (Blueprint $table) {
            if (!Schema::hasColumn('age_specific_events', 'title')) {
                $table->string('title')->nullable()->after('event_choice');
            }
            if (!Schema::hasColumn('age_specific_events', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('age_specific_events', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('age_specific_events', 'type')) {
                $table->string('type')->default('age_specific')->after('image');
            }
            if (!Schema::hasColumn('age_specific_events', 'deck_label')) {
                $table->string('deck_label')->default('Age Event')->after('type');
            }
            if (!Schema::hasColumn('age_specific_events', 'repeatable')) {
                $table->boolean('repeatable')->default(false)->after('deck_label');
            }
            if (!Schema::hasColumn('age_specific_events', 'auto_resolve')) {
                $table->boolean('auto_resolve')->default(false)->after('repeatable');
            }
            if (!Schema::hasColumn('age_specific_events', 'days_to_advance')) {
                $table->integer('days_to_advance')->default(1)->after('auto_resolve');
            }
            if (!Schema::hasColumn('age_specific_events', 'display_order')) {
                $table->integer('display_order')->nullable()->after('days_to_advance');
            }
            if (!Schema::hasColumn('age_specific_events', 'conditions')) {
                $table->json('conditions')->nullable()->after('display_order');
            }
        });

        // Add display fields to profession_path_events table
        Schema::table('profession_path_events', function (Blueprint $table) {
            if (!Schema::hasColumn('profession_path_events', 'title')) {
                $table->string('title')->nullable()->after('event_choice');
            }
            if (!Schema::hasColumn('profession_path_events', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('profession_path_events', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('profession_path_events', 'type')) {
                $table->string('type')->default('profession')->after('image');
            }
            if (!Schema::hasColumn('profession_path_events', 'deck_label')) {
                $table->string('deck_label')->default('Profession Event')->after('type');
            }
            if (!Schema::hasColumn('profession_path_events', 'repeatable')) {
                $table->boolean('repeatable')->default(true)->after('deck_label');
            }
            if (!Schema::hasColumn('profession_path_events', 'auto_resolve')) {
                $table->boolean('auto_resolve')->default(false)->after('repeatable');
            }
            if (!Schema::hasColumn('profession_path_events', 'days_to_advance')) {
                $table->integer('days_to_advance')->default(0)->after('auto_resolve');
            }
            if (!Schema::hasColumn('profession_path_events', 'display_order')) {
                $table->integer('display_order')->nullable()->after('days_to_advance');
            }
            if (!Schema::hasColumn('profession_path_events', 'conditions')) {
                $table->json('conditions')->nullable()->after('display_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback - too risky to drop columns that might be needed
    }
};
