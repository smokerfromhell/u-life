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
        Schema::create('character_decision_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('character_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('day')->nullable();

            $table->string('event_type', 32);
            $table->unsignedBigInteger('event_id');
            $table->string('event_title')->nullable();
            $table->text('event_description')->nullable();

            $table->unsignedInteger('choice_index');
            $table->string('choice_text')->nullable();
            $table->text('stat_effects_text')->nullable();

            $table->json('effects')->nullable();
            $table->json('stats_before')->nullable();
            $table->json('stats_after')->nullable();
            $table->json('hidden_stats_before')->nullable();
            $table->json('hidden_stats_after')->nullable();
            $table->json('effective_stats_before')->nullable();
            $table->json('effective_stats_after')->nullable();
            $table->json('effective_stats_delta')->nullable();

            $table->string('narrative_before')->nullable();
            $table->string('narrative_after')->nullable();
            $table->json('active_event_paths_before')->nullable();
            $table->json('active_event_paths_after')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['character_id', 'created_at']);
            $table->index(['event_type', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_decision_logs');
    }
};

