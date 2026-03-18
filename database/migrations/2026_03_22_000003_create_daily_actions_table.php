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
        Schema::create('daily_actions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image')->default('/css/images/event-placeholder.jpg');
            $table->string('type')->default('system');
            $table->string('deck_label')->default('Action');
            $table->boolean('repeatable')->default(true);
            $table->integer('weight')->default(0);
            $table->boolean('auto_resolve')->default(false);
            $table->integer('days_to_advance')->default(0);
            $table->json('choices')->nullable();
            $table->json('conditions')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_actions');
    }
};
