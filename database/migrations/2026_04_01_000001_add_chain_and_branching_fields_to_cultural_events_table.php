<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add chain_id and age_group to cultural_events to match daily_events structure.
     * Already present: chain_order, parent_category, required_choice_outcome
     */
    public function up(): void
    {
        Schema::table('cultural_events', function (Blueprint $table) {
            // Add chain_id (already present in daily_events)
            $table->string('chain_id')->nullable()->after('conditions');
            
            // Add age_group (already present in daily_events)
            $table->string('age_group')->nullable()->after('chain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->dropColumn([
                'chain_id',
                'age_group',
            ]);
        });
    }
};