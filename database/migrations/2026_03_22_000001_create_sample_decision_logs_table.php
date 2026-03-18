<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a table for sample/shared decision logs that new users can view
     * as examples of different career paths and life decisions.
     */
    public function up(): void
    {
        Schema::create('sample_decision_logs', function (Blueprint $table) {
            $table->id();
            
            // Character identification (anonymized)
            $table->string('anon_character_id', 64)->nullable()->index();
            $table->string('character_name', 100)->nullable(); // Fictional name for display
            
            // Decision details
            $table->integer('day')->default(1);
            $table->string('age_group', 50)->nullable(); // child, teen, young_adult, adult, middle_aged, senior
            $table->string('profession', 100)->nullable(); // Current profession at time of decision
            
            // Event information
            $table->string('event_type', 50)->nullable(); // daily, cultural, age_specific, profession
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('event_title', 255)->nullable();
            $table->integer('choice_index')->nullable();
            $table->string('choice_text', 500)->nullable();
            $table->text('outcome')->nullable();
            $table->longText('effects')->nullable(); // JSON effects of the choice
            
            // MBTI personality type at time of decision
            $table->string('mbti', 4)->nullable();
            
            // Life stats before decision
            $table->integer('before_health')->default(100);
            $table->integer('before_happiness')->default(100);
            $table->integer('before_finance')->default(0);
            $table->string('before_relationship_status', 50)->default('single');
            $table->string('before_career_level', 50)->default('unemployed');
            $table->string('before_profession', 100)->nullable();
            
            // Life stats after decision
            $table->integer('after_health')->default(100);
            $table->integer('after_happiness')->default(100);
            $table->integer('after_finance')->default(0);
            $table->string('after_relationship_status', 50)->default('single');
            $table->string('after_career_level', 50)->default('unemployed');
            $table->string('after_profession', 100)->nullable();
            
            // Changes
            $table->integer('health_change')->default(0);
            $table->integer('happiness_change')->default(0);
            $table->integer('finance_change')->default(0);
            
            // Category tags for filtering
            $table->json('tags')->nullable(); // e.g., ["career", "health", "relationship"]
            
            // Display settings
            $table->boolean('is_featured')->default(false); // Featured example
            $table->integer('view_count')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['profession', 'age_group']);
            $table->index(['mbti', 'age_group']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_decision_logs');
    }
};
