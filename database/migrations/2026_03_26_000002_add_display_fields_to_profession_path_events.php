<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add display fields to profession_path_events table
     */
    public function up(): void
    {
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
        // No rollback
    }
};
