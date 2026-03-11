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
        Schema::create('daily_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_choice');
            $table->text('outcome');
            $table->text('stat_effects'); // JSON: e.g., '+10 Happiness, -5 Morality'
            $table->integer('weight')->default(1); // Probability weight for random selection
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_events');
    }
};
