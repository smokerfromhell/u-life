<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('age_specific_events', function (Blueprint $table) {
            $table->id();
$table->enum('age_group', ['child', 'teen', 'adult', 'elder', 'old']);
            $table->string('event_choice');
            $table->text('outcome'); // Description of the event outcome
            $table->text('stat_effects'); // JSON: e.g., '+5 Creativity, +3 Luck'
            $table->float('weight')->default(1); // Probability weight for random selection
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_specific_events');
    }
};
