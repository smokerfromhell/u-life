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
        Schema::create('game_sessions_stats', function (Blueprint $table) {
            $table->foreignId('stats_id')->constrained()->onDelete("cascade");
            $table->foreignId('game_sessions_id')->constrained()->onDelete("cascade");
            $table->decimal('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions_stats');
    }
};
