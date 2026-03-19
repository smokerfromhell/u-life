<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Track choice consequences for the branching system
     */
    public function up(): void
    {
        Schema::create('character_choice_consequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->onDelete('cascade');
            
            // Chain tracking
            $table->string('chain_category')->nullable()->comment('education, career, family, health, social, skill');
            $table->string('choice_path')->nullable()->comment('positive, negative, neutral');
            $table->integer('chain_order')->default(0)->comment('Current position in chain');
            
            // Cumulative effects
            $table->json('cumulative_stat_modifiers')->nullable()->comment('Running total of stat changes');
            $table->json('locked_choices')->nullable()->comment('Choices no longer available');
            $table->json('unlocked_choices')->nullable()->comment('New choices available due to this choice');
            $table->json('pending_consequences')->nullable()->comment('Future events triggered by this choice');
            
            // Outcome tracking
            $table->string('last_outcome')->nullable()->comment('positive, negative, neutral');
            $table->integer('consecutive_positive')->default(0);
            $table->integer('consecutive_negative')->default(0);
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['character_id', 'chain_category']);
        });
        
        // Add new fields to characters table for enhanced tracking
        Schema::table('characters', function (Blueprint $table) {
            $table->json('choice_history')->nullable()->after('active_event_paths')->comment('All major choices made');
            $table->json('relationship_state')->nullable()->after('choice_history')->comment('Friend/enemy/rival tracking');
            $table->json('reputation_by_faction')->nullable()->after('relationship_state')->comment('Different group reputations');
            $table->json('trauma_flags')->nullable()->after('reputation_by_faction')->comment('Past negative events affecting future');
            $table->json('achievement_flags')->nullable()->after('trauma_flags')->comment('Milestones achieved');
            $table->json('pending_events')->nullable()->after('achievement_flags')->comment('Future triggered events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_choice_consequences');
        
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'choice_history',
                'relationship_state',
                'reputation_by_faction',
                'trauma_flags',
                'achievement_flags',
                'pending_events',
            ]);
        });
    }
};
