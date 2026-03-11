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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('age_group', ['child', 'teenager', 'adult', 'old'])->default('adult');
            $table->enum('gender', ['male', 'female', 'non-binary', 'transgender'])->default('male');
            
            // Base Stats (stored as JSON for flexibility)
            $table->json('stats')->nullable();
            
            // Hidden Stats (stored as JSON)
            $table->json('hidden_stats')->nullable();
            
            // Gender and Age Bonuses (stored as JSON)
            $table->json('gender_bonus')->nullable();
            $table->json('age_bonus')->nullable();
            
            // Effective stats after bonuses applied
            $table->json('effective_stats')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
