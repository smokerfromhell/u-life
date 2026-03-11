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
        Schema::create('profession_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('profession'); // e.g., 'Doctor', 'Engineer', 'Artist'
            $table->text('unlock_condition'); // e.g., 'Intelligence >= 60 AND Creativity >= 40'
            $table->text('notes')->nullable(); // Additional notes about the profession
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profession_triggers');
    }
};
