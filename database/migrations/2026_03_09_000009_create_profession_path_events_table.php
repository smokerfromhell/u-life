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
        Schema::create('profession_path_events', function (Blueprint $table) {
            $table->id();
            $table->string('profession'); // e.g., 'Doctor', 'Engineer', 'Artist'
            $table->string('event_choice');
            $table->text('outcome');
            $table->text('stat_effects'); // JSON: e.g., '+20 Intelligence, +10 Health'
            $table->integer('weight')->default(1); // Probability weight for random selection
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profession_path_events');
    }
};
