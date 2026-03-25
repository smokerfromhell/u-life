<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add narrative_description field to daily_events for enhanced storytelling.
     */
    public function up(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->text('narrative_description')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->dropColumn('narrative_description');
        });
    }
};
