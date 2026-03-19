<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add random outcome fields to character_choice_consequences table
     */
    public function up(): void
    {
        Schema::table('character_choice_consequences', function (Blueprint $table) {
            // Random outcome configuration
            $table->float('random_outcome_chance')->nullable()->after('consecutive_negative')
                ->comment('Probability of a random outcome occurring (0-100)');
            $table->string('random_outcome_type')->nullable()->after('random_outcome_chance')
                ->comment('Type: luck, misfortune, both');
            
            // JSON array of possible random outcomes
            $table->json('random_outcomes')->nullable()->after('random_outcome_type')
                ->comment('Array of possible random outcomes with stat effects');
            
            // The actual random outcome that occurred
            $table->string('last_random_outcome')->nullable()->after('random_outcomes')
                ->comment('The random outcome that was triggered');
            $table->json('random_outcome_effects')->nullable()->after('last_random_outcome')
                ->comment('Stat effects from the random outcome');
            
            // Outcome weight configuration
            $table->float('positive_outcome_weight')->nullable()->after('random_outcome_effects')
                ->comment('Weight for positive random outcomes (relative to negative)');
            $table->float('negative_outcome_weight')->nullable()->after('positive_outcome_weight')
                ->comment('Weight for negative random outcomes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_choice_consequences', function (Blueprint $table) {
            $table->dropColumn([
                'random_outcome_chance',
                'random_outcome_type',
                'random_outcomes',
                'last_random_outcome',
                'random_outcome_effects',
                'positive_outcome_weight',
                'negative_outcome_weight',
            ]);
        });
    }
};
