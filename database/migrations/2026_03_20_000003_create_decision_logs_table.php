<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('anon_user_id')->index();
            $table->string('anon_character_id')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->string('user_name')->nullable();
            $table->integer('day')->default(0);
            $table->string('age_group')->nullable();
            $table->string('event_type')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('event_title')->nullable();
            $table->integer('choice_index')->nullable();
            $table->text('choice_text')->nullable();
            $table->text('outcome')->nullable();
            $table->text('effects')->nullable();
            
            // Before state
            $table->integer('before_health')->default(100);
            $table->integer('before_happiness')->default(100);
            $table->integer('before_finance')->default(0);
            $table->string('before_relationship_status')->default('single');
            $table->string('before_career_level')->default('unemployed');
            
            // After state
            $table->integer('after_health')->default(100);
            $table->integer('after_happiness')->default(100);
            $table->integer('after_finance')->default(0);
            $table->string('after_relationship_status')->default('single');
            $table->string('after_career_level')->default('unemployed');
            
            // Changes
            $table->integer('health_change')->default(0);
            $table->integer('happiness_change')->default(0);
            $table->integer('finance_change')->default(0);
            
            // Additional
            $table->string('mbti')->nullable();
            $table->json('data')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_logs');
    }
};
