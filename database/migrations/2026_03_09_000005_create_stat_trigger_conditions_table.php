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
        Schema::create('stat_trigger_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_stat_condition'); // e.g., 'Intelligence >= 50'
            $table->string('stat_name'); // e.g., 'Intelligence'
            $table->integer('threshold'); // e.g., 50
            $table->string('event_choice');
            $table->text('outcome');
            $table->text('stat_effects'); // JSON: stored as text, e.g., '+10 Happiness, -5 Morality'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_trigger_conditions');
    }
};
