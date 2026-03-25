<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add relationship chain fields to event tables.
     * This enables NPC-based narrative chains (e.g., Meet → Befriend → Help → Ally)
     */
    public function up(): void
    {
        // Add to daily_events
        Schema::table('daily_events', function (Blueprint $table) {
            $table->string('related_npc')->nullable()->after('parent_category');
            $table->string('relationship_type')->nullable()->after('related_npc');
            $table->integer('min_relationship_level')->nullable()->after('relationship_type');
        });

        // Add to cultural_events
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->string('related_npc')->nullable()->after('parent_category');
            $table->string('relationship_type')->nullable()->after('related_npc');
            $table->integer('min_relationship_level')->nullable()->after('relationship_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn(['related_npc', 'relationship_type', 'min_relationship_level']);
        });

        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn(['related_npc', 'relationship_type', 'min_relationship_level']);
        });
    }
};
