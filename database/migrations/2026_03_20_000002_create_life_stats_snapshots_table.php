<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_stats_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('anon_user_id')->index();
            $table->string('anon_character_id')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->string('user_name')->nullable();
            $table->integer('day')->default(0);
            $table->string('event_type')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->integer('choice_index')->nullable();
            $table->string('event_title')->nullable();
            $table->text('choice_text')->nullable();
            // Life Stats
            $table->integer('health')->default(100);
            $table->integer('happiness')->default(100);
            $table->integer('finance')->default(0);
            $table->string('relationship_status')->default('single');
            $table->string('career_level')->default('unemployed');
            // MBTI
            $table->string('mbti')->nullable();
            // Additional data
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_stats_snapshots');
    }
};
