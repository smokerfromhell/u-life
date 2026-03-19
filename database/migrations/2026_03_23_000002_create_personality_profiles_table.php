<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create personality profiles for cumulative MBTI tracking
     */
    public function up(): void
    {
        Schema::create('personality_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->onDelete('cascade');
            
            // Core MBTI dimensions (0-100 scale, 50 = balanced)
            $table->integer('energy_orientation')->default(50)->comment('E/I: Extraversion vs Introversion');
            $table->integer('information_gathering')->default(50)->comment('N/S: Intuition vs Sensing');
            $table->integer('decision_forming')->default(50)->comment('T/F: Thinking vs Feeling');
            $table->integer('lifestyle_approach')->default(50)->comment('J/P: Judging vs Perceiving');
            
            // Big Five (OCEAN) personality traits
            $table->integer('openness')->default(50)->comment('Openness to experience');
            $table->integer('conscientiousness')->default(50)->comment('Conscientiousness');
            $table->integer('extraversion')->default(50)->comment('Extraversion');
            $table->integer('agreeableness')->default(50)->comment('Agreeableness');
            $table->integer('neuroticism')->default(50)->comment('Neuroticism (inverted = emotional stability)');
            
            // Behavioral pattern scores
            $table->integer('social_boldness_score')->default(0)->comment('Cumulative social approach score');
            $table->integer('emotional_stability_score')->default(0)->comment('Cumulative emotional response score');
            $table->integer('agreeableness_score')->default(0)->comment('Cumulative interpersonal score');
            $table->integer('conscientiousness_score')->default(0)->comment('Cumulative achievement score');
            $table->integer('openness_score')->default(0)->comment('Cumulative curiosity score');
            
            // Decision history (weighted recent decisions)
            $table->json('decision_patterns')->nullable()->comment('Categorized choices over time');
            $table->json('recent_social_decisions')->nullable()->comment('Last 10 social choices');
            $table->json('recent_career_decisions')->nullable()->comment('Last 10 career choices');
            $table->json('recent_relationship_decisions')->nullable()->comment('Last 10 relationship choices');
            $table->json('recent_moral_decisions')->nullable()->comment('Last 10 moral/ethical choices');
            
            // Current calculated MBTI
            $table->string('current_mbti')->nullable()->comment('Current 4-letter MBTI type');
            $table->integer('mbti_confidence')->default(0)->comment('Confidence level 0-100');
            
            $table->timestamps();
            
            $table->index(['character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personality_profiles');
    }
};
