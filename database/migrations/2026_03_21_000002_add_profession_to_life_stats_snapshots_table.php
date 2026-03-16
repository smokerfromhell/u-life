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
        Schema::table('life_stats_snapshots', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('career_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('life_stats_snapshots', function (Blueprint $table) {
            $table->dropColumn(['profession']);
        });
    }
};
