<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('achievement_id')->unique(); // unique identifier like 'born', 'first_job', etc.
            $table->string('name');
            $table->text('description');
            $table->string('category'); // career, health, relationship, lifespan, special, milestone, social, luck
            $table->string('rarity'); // common, uncommon, rare, epic, legendary
            $table->string('icon')->nullable(); // mdi icon name
            $table->json('conditions')->nullable(); // JSON conditions
            $table->boolean('unlock_once')->default(true);
            $table->integer('points')->default(0); // points awarded for unlocking
            $table->timestamps();
        });

        // Create character_achievements pivot table
        Schema::create('character_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->timestamp('unlocked_at');
            $table->json('metadata')->nullable(); // Any additional data about the unlock
            $table->timestamps();
            
            // Unique constraint - each character can unlock each achievement only once
            $table->unique(['character_id', 'achievement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_achievements');
        Schema::dropIfExists('achievements');
    }
};
