<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add chain tracking fields to daily_events table.
     */
    public function up(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            // Chain tracking fields
            $table->string('chain_id')->nullable()->after('conditions');
            $table->integer('chain_order')->nullable()->after('chain_id');
            $table->string('parent_category')->nullable()->after('chain_order');
            
            // Outcome-based branching
            $table->string('required_choice_outcome')->nullable()->after('parent_category');
            $table->string('next_chain_event')->nullable()->after('required_choice_outcome');
            
            // Age transition events for continuity
            $table->string('age_transition')->nullable()->after('next_chain_event');
            $table->string('next_age_group')->nullable()->after('age_transition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn([
                'chain_id',
                'chain_order',
                'parent_category',
                'required_choice_outcome',
                'next_chain_event',
                'age_transition',
                'next_age_group',
            ]);
        });
    }
};
