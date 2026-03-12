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
        Schema::create('shared_decision_logs', function (Blueprint $table) {
            $table->id();

            $table->string('anon_user_id', 64)->index();
            $table->string('anon_character_id', 64)->index();
            $table->boolean('is_guest')->default(false)->index();

            $table->unsignedInteger('day')->nullable()->index();
            $table->string('event_type', 32)->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedInteger('choice_index')->index();

            // Encrypted at rest (Eloquent encrypted cast).
            $table->longText('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_decision_logs');
    }
};

